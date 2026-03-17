<?php
class IncidentModel {
    private $conn;

    public function __construct($pdo) {
        $this->conn = $pdo;
    }

    /**
     * Get all incident reports with comprehensive filtering
     * ====================================================
     * FIXED: Better status filtering, proper field handling
     */
    public function getAllReports($filters = []) {
        try {
            $where  = [];
            $params = [];

            // role filter: who is the person being reported
            if (!empty($filters['role'])) {
                switch ($filters['role']) {
                    case 'Student':
                        $where[] = "r.report_target = 'student'";
                        break;
                    case 'Faculty':
                        $where[] = "r.report_target = 'teacher'";
                        break;
                    case 'Other':
                        $where[] = "r.report_target = 'other'";
                        break;
                    case 'Unknown':
                        $where[] = "(r.student_id IS NULL AND r.teacher_involved_id IS NULL AND (r.other_name IS NULL OR r.other_name = ''))";
                        break;
                }
            }

            if (!empty($filters['status'])) {
                $where[] = "r.status = :status";
                $params[':status'] = $filters['status'];
            }

            if (!empty($filters['violation_id'])) {
                if ($filters['violation_id'] === 'custom') {
                    $where[] = "(r.violation_id IS NULL AND r.custom_violation IS NOT NULL AND r.custom_violation != '')";
                } else {
                    $where[] = "r.violation_id = :violation_id";
                    $params[':violation_id'] = intval($filters['violation_id']);
                }
            }

            if (!empty($filters['search'])) {
                $where[] = "(
                    s.name  LIKE :search OR
                    ti.name LIKE :search OR
                    r.other_name LIKE :search OR
                    reporter.name LIKE :search OR
                    d.violation_name LIKE :search OR
                    r.custom_violation LIKE :search OR
                    r.location LIKE :search
                )";
                $params[':search'] = '%' . $filters['search'] . '%';
            }

            $whereSQL = $where ? ('WHERE ' . implode(' AND ', $where)) : '';

            $sql = "
                SELECT
                    r.report_id,
                    r.report_target,
                    r.grade_level,
                    r.location,
                    r.description,
                    r.evidence_path,
                    r.evidence_type,
                    r.status,
                    r.admin_notes,
                    r.created_at,
                    r.custom_violation,

                    -- person reported
                    COALESCE(s.name, ti.name, r.other_name, 'Unknown') AS reported_name,
                    s.lrn   AS student_lrn,
                    s.user_id AS student_id,
                    ti.user_id AS teacher_involved_id,

                    -- teacher who filed report
                    reporter.name  AS reporter_name,
                    reporter.email AS reporter_email,
                    r.teacher_id   AS reporter_id,

                    -- violation
                    d.violation_name,
                    COALESCE(d.violation_name, r.custom_violation, 'N/A') AS violation_display,

                    -- school year
                    CONCAT(sy.start_year,'-',sy.end_year) AS school_year,

                    -- advisory of reported student
                    ac.advisory_name,
                    at_teacher.name AS advisory_teacher_name

                FROM teacher_incident_reports r
                LEFT JOIN user_management s        ON r.student_id          = s.user_id
                LEFT JOIN user_management ti       ON r.teacher_involved_id = ti.user_id
                LEFT JOIN user_management reporter ON r.teacher_id          = reporter.user_id
                LEFT JOIN discipline d             ON r.violation_id        = d.discipline_id
                LEFT JOIN school_years sy          ON r.school_year_id      = sy.school_year_id
                LEFT JOIN student_advisory_assignments saa ON s.user_id = saa.student_id
                LEFT JOIN advisory_classes ac      ON saa.advisory_id       = ac.advisory_id
                LEFT JOIN user_management at_teacher ON ac.teacher_id       = at_teacher.user_id
                {$whereSQL}
                ORDER BY r.created_at DESC
            ";

            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);

        } catch (PDOException $e) {
            error_log('IncidentModel::getAllReports — ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get a single report by ID with all details
     * ==========================================
     * FIXED: Includes all necessary fields for display
     */
    public function getReportById($report_id) {
        try {
            $sql = "
                SELECT
                    r.*,
                    COALESCE(s.name, ti.name, r.other_name, 'Unknown') AS reported_name,
                    s.lrn AS student_lrn,
                    s.user_id AS student_id,
                    ti.email AS teacher_involved_email,
                    reporter.name  AS reporter_name,
                    reporter.email AS reporter_email,
                    COALESCE(d.violation_name, r.custom_violation, 'N/A') AS violation_display,
                    d.description AS violation_description,
                    CONCAT(sy.start_year,'-',sy.end_year) AS school_year,
                    ac.advisory_name,
                    at_teacher.name AS advisory_teacher_name,
                    rev.name AS reviewed_by_name
                FROM teacher_incident_reports r
                LEFT JOIN user_management s        ON r.student_id          = s.user_id
                LEFT JOIN user_management ti       ON r.teacher_involved_id = ti.user_id
                LEFT JOIN user_management reporter ON r.teacher_id          = reporter.user_id
                LEFT JOIN discipline d             ON r.violation_id        = d.discipline_id
                LEFT JOIN school_years sy          ON r.school_year_id      = sy.school_year_id
                LEFT JOIN student_advisory_assignments saa ON s.user_id    = saa.student_id
                LEFT JOIN advisory_classes ac      ON saa.advisory_id       = ac.advisory_id
                LEFT JOIN user_management at_teacher ON ac.teacher_id       = at_teacher.user_id
                LEFT JOIN user_management rev      ON r.reviewed_by         = rev.user_id
                WHERE r.report_id = :report_id
                LIMIT 1
            ";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([':report_id' => $report_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('IncidentModel::getReportById — ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update incident status - STRICTLY controlled
     * ============================================
     * FIXED: Only allows pending → reviewed → resolved
     * Blocks updates to already resolved cases
     */
    public function updateStatus($report_id, $status, $admin_id, $admin_notes = '') {
        try {
            // Only allow these valid statuses
            $validStatuses = ['pending', 'reviewed', 'resolved'];
            if (!in_array($status, $validStatuses)) {
                return ['success' => false, 'message' => 'Invalid status update. Only pending, reviewed, or resolved are allowed.'];
            }

            // Get current report to check status
            $checkStmt = $this->conn->prepare("SELECT status FROM teacher_incident_reports WHERE report_id = :report_id");
            $checkStmt->execute([':report_id' => $report_id]);
            $current = $checkStmt->fetch(PDO::FETCH_ASSOC);

            if (!$current) {
                return ['success' => false, 'message' => 'Report not found.'];
            }

            // Block if already resolved
            if ($current['status'] === 'resolved') {
                return ['success' => false, 'message' => 'This case is already resolved and cannot be modified.'];
            }

            // Allow transitions: pending→reviewed, pending→resolved, reviewed→resolved
            $allowedTransitions = [
                'pending'  => ['reviewed', 'resolved'],
                'reviewed' => ['resolved'],
            ];

            if (!isset($allowedTransitions[$current['status']]) || 
                !in_array($status, $allowedTransitions[$current['status']])) {
                return ['success' => false, 'message' => 'Invalid status transition.'];
            }

            $stmt = $this->conn->prepare("
                UPDATE teacher_incident_reports
                SET status      = :status,
                    reviewed_by = :admin_id,
                    reviewed_at = NOW(),
                    admin_notes = :notes
                WHERE report_id = :report_id
            ");
            $stmt->execute([
                ':status'    => $status,
                ':admin_id'  => $admin_id,
                ':notes'     => $admin_notes,
                ':report_id' => $report_id,
            ]);
            return ['success' => true, 'message' => 'Status updated to ' . ucfirst($status) . '.'];
        } catch (PDOException $e) {
            error_log('IncidentModel::updateStatus — ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Delete incident report and associated evidence file
     * ===================================================
     * FIXED: Properly removes report and cleans up file
     */
    public function deleteReport($report_id) {
        try {
            // get evidence path to delete file
            $stmt = $this->conn->prepare("SELECT evidence_path FROM teacher_incident_reports WHERE report_id = :id");
            $stmt->execute([':id' => $report_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            // Delete from database
            $this->conn->prepare("DELETE FROM teacher_incident_reports WHERE report_id = :id")
                       ->execute([':id' => $report_id]);

            // delete evidence file if exists
            if (!empty($row['evidence_path'])) {
                $absPath = __DIR__ . '/../../../public' . parse_url($row['evidence_path'], PHP_URL_PATH);
                if (file_exists($absPath)) {
                    @unlink($absPath);
                }
            }

            return ['success' => true, 'message' => 'Report and evidence deleted successfully.'];
        } catch (PDOException $e) {
            error_log('IncidentModel::deleteReport — ' . $e->getMessage());
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    /**
     * Get list of violations for filtering
     * ====================================
     */
    public function getViolationsForFilter() {
        try {
            $stmt = $this->conn->prepare("SELECT discipline_id, violation_name FROM discipline ORDER BY violation_name ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('IncidentModel::getViolationsForFilter — ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get summary statistics of all incident reports
     * ==============================================
     * FIXED: Proper status counting
     */
    public function getSummaryStats() {
        try {
            $stmt = $this->conn->query("
                SELECT
                    COUNT(*) AS total,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) AS pending,
                    SUM(CASE WHEN status = 'reviewed' THEN 1 ELSE 0 END) AS reviewed,
                    SUM(CASE WHEN status = 'resolved' THEN 1 ELSE 0 END) AS resolved
                FROM teacher_incident_reports
            ");
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result ?: ['total'=>0,'pending'=>0,'reviewed'=>0,'resolved'=>0];
        } catch (PDOException $e) {
            error_log('IncidentModel::getSummaryStats — ' . $e->getMessage());
            return ['total'=>0,'pending'=>0,'reviewed'=>0,'resolved'=>0];
        }
    }
}
?>