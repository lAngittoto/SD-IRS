<?php
class DashboardModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    /* ══════════════════════════════════════════
       STUDENTS
    ══════════════════════════════════════════ */
    public function getTotalStudents() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) AS total FROM user_management WHERE role = 'student'");
            return (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (PDOException $e) {
            error_log('DashboardModel::getTotalStudents — ' . $e->getMessage());
            return 0;
        }
    }

    /* ══════════════════════════════════════════
       FACULTY
    ══════════════════════════════════════════ */
    public function getTotalFaculty() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) AS total FROM user_management WHERE role = 'teacher'");
            return (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (PDOException $e) {
            error_log('DashboardModel::getTotalFaculty — ' . $e->getMessage());
            return 0;
        }
    }

    /* ══════════════════════════════════════════
       INCIDENT TOTALS  (uses teacher_incident_reports)
    ══════════════════════════════════════════ */
    public function getTotalIncidents() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) AS total FROM teacher_incident_reports");
            return (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (PDOException $e) {
            error_log('DashboardModel::getTotalIncidents — ' . $e->getMessage());
            return 0;
        }
    }

    public function getPendingReports() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) AS total FROM teacher_incident_reports WHERE status = 'pending'");
            return (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (PDOException $e) {
            error_log('DashboardModel::getPendingReports — ' . $e->getMessage());
            return 0;
        }
    }

    public function getReviewedReports() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) AS total FROM teacher_incident_reports WHERE status = 'reviewed'");
            return (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (PDOException $e) {
            error_log('DashboardModel::getReviewedReports — ' . $e->getMessage());
            return 0;
        }
    }

    public function getResolvedCases() {
        try {
            $stmt = $this->db->query("SELECT COUNT(*) AS total FROM teacher_incident_reports WHERE status = 'resolved'");
            return (int)($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);
        } catch (PDOException $e) {
            error_log('DashboardModel::getResolvedCases — ' . $e->getMessage());
            return 0;
        }
    }

    /* ══════════════════════════════════════════
       MONTHLY TRENDS — last 6 months
    ══════════════════════════════════════════ */
    public function getMonthlyTrends() {
        try {
            $stmt = $this->db->query("
                SELECT
                    DATE_FORMAT(created_at, '%b %Y')  AS month_name,
                    DATE_FORMAT(created_at, '%Y-%m')  AS month_key,
                    COUNT(*)                           AS incident_count
                FROM teacher_incident_reports
                WHERE created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY month_key, month_name
                ORDER BY month_key ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('DashboardModel::getMonthlyTrends — ' . $e->getMessage());
            return [];
        }
    }

    /* ══════════════════════════════════════════
       STATUS BREAKDOWN (for doughnut chart)
    ══════════════════════════════════════════ */
    public function getStatusBreakdown() {
        try {
            $stmt = $this->db->query("
                SELECT
                    status,
                    COUNT(*) AS total
                FROM teacher_incident_reports
                GROUP BY status
            ");
            $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $map  = ['pending' => 0, 'reviewed' => 0, 'resolved' => 0];
            foreach ($rows as $r) {
                if (isset($map[$r['status']])) {
                    $map[$r['status']] = (int)$r['total'];
                }
            }
            return $map;
        } catch (PDOException $e) {
            error_log('DashboardModel::getStatusBreakdown — ' . $e->getMessage());
            return ['pending' => 0, 'reviewed' => 0, 'resolved' => 0];
        }
    }

    /* ══════════════════════════════════════════
       VIOLATIONS LIST
    ══════════════════════════════════════════ */
    public function getAllViolationNames() {
        try {
            $stmt = $this->db->query("
                SELECT
                    d.discipline_id,
                    d.violation_name,
                    d.description,
                    s.name  AS sanction,
                    w.name  AS severity
                FROM discipline d
                LEFT JOIN sanctions     s ON d.id_sanctions = s.id_sanctions
                LEFT JOIN warning_levels w ON d.id_warning  = w.id_warning
                ORDER BY d.violation_name ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('DashboardModel::getAllViolationNames — ' . $e->getMessage());
            return [];
        }
    }

    /* ══════════════════════════════════════════
       TOP VIOLATIONS (by incident count)
    ══════════════════════════════════════════ */
    public function getTopViolations($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    d.violation_name,
                    w.name  AS severity,
                    s.name  AS sanction,
                    COUNT(r.report_id) AS violation_count
                FROM discipline d
                LEFT JOIN teacher_incident_reports r ON r.violation_id   = d.discipline_id
                LEFT JOIN warning_levels w            ON d.id_warning     = w.id_warning
                LEFT JOIN sanctions s                 ON d.id_sanctions   = s.id_sanctions
                GROUP BY d.discipline_id, d.violation_name, w.name, s.name
                ORDER BY violation_count DESC
                LIMIT :lim
            ");
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('DashboardModel::getTopViolations — ' . $e->getMessage());
            return [];
        }
    }

    /* ══════════════════════════════════════════
       RECENT INCIDENTS (last 5)
    ══════════════════════════════════════════ */
    public function getRecentIncidents($limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    r.report_id,
                    r.status,
                    r.created_at,
                    COALESCE(s.name, ti.name, r.other_name, 'Unknown') AS reported_name,
                    r.report_target,
                    COALESCE(d.violation_name, r.custom_violation, 'N/A') AS violation_display
                FROM teacher_incident_reports r
                LEFT JOIN user_management s  ON r.student_id          = s.user_id
                LEFT JOIN user_management ti ON r.teacher_involved_id = ti.user_id
                LEFT JOIN discipline d       ON r.violation_id        = d.discipline_id
                ORDER BY r.created_at DESC
                LIMIT :lim
            ");
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('DashboardModel::getRecentIncidents — ' . $e->getMessage());
            return [];
        }
    }

    /* ══════════════════════════════════════════
       FULL SUMMARY (convenience)
    ══════════════════════════════════════════ */
    public function getDashboardSummary() {
        return [
            'total_students'  => $this->getTotalStudents(),
            'total_faculty'   => $this->getTotalFaculty(),
            'total_incidents' => $this->getTotalIncidents(),
            'pending_reports' => $this->getPendingReports(),
            'reviewed_reports'=> $this->getReviewedReports(),
            'resolved_cases'  => $this->getResolvedCases(),
        ];
    }
}
?>