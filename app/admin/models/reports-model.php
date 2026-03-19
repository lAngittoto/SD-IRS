<?php
class ReportsModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    /* ══════════════════════════════════════════
       SUMMARY STATS
    ══════════════════════════════════════════ */
    public function getSummaryStats() {
        try {
            $rows = [];

            // Total students
            $rows['total_students'] = (int) $this->db
                ->query("SELECT COUNT(*) FROM user_management WHERE role = 'Student'")
                ->fetchColumn();

            // Total faculty
            $rows['total_faculty'] = (int) $this->db
                ->query("SELECT COUNT(*) FROM user_management WHERE role = 'Teacher'")
                ->fetchColumn();

            // Incident counts by status
            $stmt = $this->db->query("
                SELECT
                    COUNT(*)                                                    AS total,
                    SUM(status = 'pending')                                     AS pending,
                    SUM(status = 'reviewed')                                    AS reviewed,
                    SUM(status = 'resolved')                                    AS resolved
                FROM teacher_incident_reports
            ");
            $inc = $stmt->fetch(PDO::FETCH_ASSOC);
            $rows['total_incidents'] = (int)($inc['total']    ?? 0);
            $rows['pending_reports'] = (int)($inc['pending']   ?? 0);
            $rows['reviewed_reports']= (int)($inc['reviewed']  ?? 0);
            $rows['resolved_cases']  = (int)($inc['resolved']  ?? 0);

            // Advisory stats
            $rows['assigned_students']   = (int) $this->db
                ->query("SELECT COUNT(DISTINCT student_id) FROM student_advisory_assignments")
                ->fetchColumn();

            $rows['unassigned_students'] = (int) $this->db
                ->query("
                    SELECT COUNT(*) FROM user_management
                    WHERE role = 'Student'
                      AND user_id NOT IN (SELECT DISTINCT student_id FROM student_advisory_assignments)
                ")
                ->fetchColumn();

            $rows['active_advisories'] = (int) $this->db
                ->query("SELECT COUNT(*) FROM advisory_classes")
                ->fetchColumn();

            return $rows;
        } catch (PDOException $e) {
            error_log('ReportsModel::getSummaryStats — ' . $e->getMessage());
            return [
                'total_students' => 0, 'total_faculty' => 0,
                'total_incidents' => 0, 'pending_reports' => 0,
                'reviewed_reports' => 0, 'resolved_cases' => 0,
                'assigned_students' => 0, 'unassigned_students' => 0,
                'active_advisories' => 0,
            ];
        }
    }

    /* ══════════════════════════════════════════
       MONTHLY TRENDS
       - year + month → exact month only
       - year only    → all months of that year
       - neither      → last 6 months from today
    ══════════════════════════════════════════ */
    public function getMonthlyTrends($year = null, $month = null) {
        try {
            $conditions = [];
            $params     = [];

            if ($year !== null && $month !== null) {
                // Exact month of exact year
                $conditions[] = "YEAR(r.created_at)  = :yr";
                $conditions[] = "MONTH(r.created_at) = :mo";
                $params[':yr'] = $year;
                $params[':mo'] = $month;
            } elseif ($year !== null) {
                // Whole year
                $conditions[] = "YEAR(r.created_at) = :yr";
                $params[':yr'] = $year;
            } else {
                // Default: last 6 months
                $conditions[] = "r.created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)";
            }

            $where = "WHERE " . implode(" AND ", $conditions);

            $stmt = $this->db->prepare("
                SELECT
                    DATE_FORMAT(r.created_at, '%b %Y') AS month_name,
                    DATE_FORMAT(r.created_at, '%Y-%m') AS month_key,
                    COUNT(*)                            AS incident_count
                FROM teacher_incident_reports r
                {$where}
                GROUP BY month_key, month_name
                ORDER BY month_key ASC
            ");
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('ReportsModel::getMonthlyTrends — ' . $e->getMessage());
            return [];
        }
    }

    /* ══════════════════════════════════════════
       STATUS BREAKDOWN
    ══════════════════════════════════════════ */
    public function getStatusBreakdown() {
        try {
            $stmt = $this->db->query("
                SELECT status, COUNT(*) AS total
                FROM teacher_incident_reports
                GROUP BY status
            ");
            $map = ['pending' => 0, 'reviewed' => 0, 'resolved' => 0];
            foreach ($stmt->fetchAll(PDO::FETCH_ASSOC) as $r) {
                if (isset($map[$r['status']])) $map[$r['status']] = (int)$r['total'];
            }
            return $map;
        } catch (PDOException $e) {
            error_log('ReportsModel::getStatusBreakdown — ' . $e->getMessage());
            return ['pending' => 0, 'reviewed' => 0, 'resolved' => 0];
        }
    }

    /* ══════════════════════════════════════════
       RECENT RESOLUTIONS
    ══════════════════════════════════════════ */
    public function getRecentResolutions($limit = 10) {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    r.report_id,
                    r.reviewed_at,
                    r.status,
                    COALESCE(s.name, ti.name, r.other_name, 'Unknown') AS reported_name,
                    r.report_target,
                    COALESCE(d.violation_name, r.custom_violation, 'N/A') AS violation_display,
                    w.name AS severity,
                    rev.name AS reviewed_by_name
                FROM teacher_incident_reports r
                LEFT JOIN user_management s   ON r.student_id          = s.user_id
                LEFT JOIN user_management ti  ON r.teacher_involved_id = ti.user_id
                LEFT JOIN discipline d        ON r.violation_id        = d.discipline_id
                LEFT JOIN warning_levels w    ON d.id_warning          = w.id_warning
                LEFT JOIN user_management rev ON r.reviewed_by         = rev.user_id
                WHERE r.status = 'resolved'
                ORDER BY r.reviewed_at DESC
                LIMIT :lim
            ");
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('ReportsModel::getRecentResolutions — ' . $e->getMessage());
            return [];
        }
    }

    /* ══════════════════════════════════════════
       TOP VIOLATIONS
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
                LEFT JOIN teacher_incident_reports r ON r.violation_id = d.discipline_id
                LEFT JOIN warning_levels w            ON d.id_warning   = w.id_warning
                LEFT JOIN sanctions s                 ON d.id_sanctions = s.id_sanctions
                GROUP BY d.discipline_id, d.violation_name, w.name, s.name
                ORDER BY violation_count DESC
                LIMIT :lim
            ");
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('ReportsModel::getTopViolations — ' . $e->getMessage());
            return [];
        }
    }

    /* ══════════════════════════════════════════
       ADVISORY SUMMARY TABLE
    ══════════════════════════════════════════ */
    public function getAdvisorySummary() {
        try {
            $stmt = $this->db->query("
                SELECT
                    ac.advisory_name,
                    ac.grade_level,
                    t.name  AS teacher_name,
                    COUNT(saa.student_id) AS student_count
                FROM advisory_classes ac
                LEFT JOIN user_management t  ON ac.teacher_id  = t.user_id
                LEFT JOIN student_advisory_assignments saa ON saa.advisory_id = ac.advisory_id
                GROUP BY ac.advisory_id, ac.advisory_name, ac.grade_level, t.name
                ORDER BY ac.grade_level ASC, ac.advisory_name ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('ReportsModel::getAdvisorySummary — ' . $e->getMessage());
            return [];
        }
    }

    /* ══════════════════════════════════════════
       INCIDENTS PER GRADE (bar chart)
    ══════════════════════════════════════════ */
    public function getIncidentsByGrade() {
        try {
            $stmt = $this->db->query("
                SELECT
                    COALESCE(r.grade_level, 'N/A') AS grade_level,
                    COUNT(*) AS total
                FROM teacher_incident_reports r
                WHERE r.report_target = 'student'
                GROUP BY grade_level
                ORDER BY grade_level ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('ReportsModel::getIncidentsByGrade — ' . $e->getMessage());
            return [];
        }
    }

    /* ══════════════════════════════════════════
       ALL VIOLATIONS LIST (for table)
    ══════════════════════════════════════════ */
    public function getAllViolations() {
        try {
            $stmt = $this->db->query("
                SELECT
                    d.violation_name,
                    s.name  AS sanction,
                    w.name  AS severity,
                    COUNT(r.report_id) AS incident_count
                FROM discipline d
                LEFT JOIN sanctions s                 ON d.id_sanctions = s.id_sanctions
                LEFT JOIN warning_levels w            ON d.id_warning   = w.id_warning
                LEFT JOIN teacher_incident_reports r  ON r.violation_id = d.discipline_id
                GROUP BY d.discipline_id, d.violation_name, s.name, w.name
                ORDER BY d.violation_name ASC
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('ReportsModel::getAllViolations — ' . $e->getMessage());
            return [];
        }
    }
}
?>