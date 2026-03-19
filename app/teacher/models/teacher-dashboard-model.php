<?php
class TeacherDashboardModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    /* ══════════════════════════════════════════
       SUMMARY STATS for this teacher
    ══════════════════════════════════════════ */
    public function getSummaryStats($teacherId) {
        try {
            $stats = [];

            // Total reports submitted by this teacher
            $stmt = $this->db->prepare("
                SELECT
                    COUNT(*)                            AS total,
                    SUM(status = 'pending')             AS pending,
                    SUM(status = 'reviewed')            AS reviewed,
                    SUM(status = 'resolved')            AS resolved
                FROM teacher_incident_reports
                WHERE teacher_id = :tid
            ");
            $stmt->execute([':tid' => $teacherId]);
            $inc = $stmt->fetch(PDO::FETCH_ASSOC);

            $stats['total_reports']    = (int)($inc['total']    ?? 0);
            $stats['pending_reports']  = (int)($inc['pending']  ?? 0);
            $stats['reviewed_reports'] = (int)($inc['reviewed'] ?? 0);
            $stats['resolved_reports'] = (int)($inc['resolved'] ?? 0);

            // Advisory class info
            $stmt2 = $this->db->prepare("
                SELECT
                    ac.advisory_id,
                    ac.advisory_name,
                    ac.grade_level,
                    COUNT(saa.student_id) AS student_count
                FROM advisory_classes ac
                LEFT JOIN student_advisory_assignments saa ON saa.advisory_id = ac.advisory_id
                WHERE ac.teacher_id = :tid
                GROUP BY ac.advisory_id, ac.advisory_name, ac.grade_level
                LIMIT 1
            ");
            $stmt2->execute([':tid' => $teacherId]);
            $advisory = $stmt2->fetch(PDO::FETCH_ASSOC);

            $stats['advisory_name']    = $advisory['advisory_name'] ?? null;
            $stats['advisory_grade']   = $advisory['grade_level']   ?? null;
            $stats['advisory_id']      = $advisory['advisory_id']   ?? null;
            $stats['advisory_students']= (int)($advisory['student_count'] ?? 0);

            // Students with unresolved incidents in this teacher's advisory
            if ($stats['advisory_id']) {
                $stmt3 = $this->db->prepare("
                    SELECT COUNT(DISTINCT tir.student_id) AS flagged
                    FROM teacher_incident_reports tir
                    JOIN student_advisory_assignments saa
                        ON tir.student_id = saa.student_id
                        AND saa.advisory_id = :aid
                    WHERE tir.status != 'resolved'
                ");
                $stmt3->execute([':aid' => $stats['advisory_id']]);
                $stats['flagged_students'] = (int)($stmt3->fetchColumn() ?? 0);
            } else {
                $stats['flagged_students'] = 0;
            }

            return $stats;
        } catch (PDOException $e) {
            error_log('TeacherDashboardModel::getSummaryStats — ' . $e->getMessage());
            return [
                'total_reports' => 0, 'pending_reports' => 0,
                'reviewed_reports' => 0, 'resolved_reports' => 0,
                'advisory_name' => null, 'advisory_grade' => null,
                'advisory_id' => null, 'advisory_students' => 0,
                'flagged_students' => 0,
            ];
        }
    }

    /* ══════════════════════════════════════════
       RECENT REPORTS submitted by this teacher
    ══════════════════════════════════════════ */
    public function getMyRecentReports($teacherId, $limit = 6) {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    r.report_id,
                    r.status,
                    r.created_at,
                    r.report_target,
                    r.location,
                    COALESCE(s.name, ti.name, r.other_name, 'Unknown') AS reported_name,
                    COALESCE(d.violation_name, r.custom_violation, 'N/A') AS violation_display
                FROM teacher_incident_reports r
                LEFT JOIN user_management s  ON r.student_id          = s.user_id
                LEFT JOIN user_management ti ON r.teacher_involved_id = ti.user_id
                LEFT JOIN discipline d       ON r.violation_id        = d.discipline_id
                WHERE r.teacher_id = :tid
                ORDER BY r.created_at DESC
                LIMIT :lim
            ");
            $stmt->bindValue(':tid', $teacherId, PDO::PARAM_INT);
            $stmt->bindValue(':lim', $limit,     PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('TeacherDashboardModel::getMyRecentReports — ' . $e->getMessage());
            return [];
        }
    }

    /* ══════════════════════════════════════════
       MONTHLY TREND of this teacher's reports
    ══════════════════════════════════════════ */
    public function getMyMonthlyTrend($teacherId) {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    DATE_FORMAT(created_at, '%b %Y') AS month_name,
                    DATE_FORMAT(created_at, '%Y-%m') AS month_key,
                    COUNT(*)                          AS report_count
                FROM teacher_incident_reports
                WHERE teacher_id = :tid
                  AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
                GROUP BY month_key, month_name
                ORDER BY month_key ASC
            ");
            $stmt->execute([':tid' => $teacherId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('TeacherDashboardModel::getMyMonthlyTrend — ' . $e->getMessage());
            return [];
        }
    }

    /* ══════════════════════════════════════════
       ADVISORY STUDENTS with incident summary
    ══════════════════════════════════════════ */
    public function getAdvisoryStudentSummary($advisoryId, $limit = 5) {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    u.user_id,
                    u.name,
                    u.lrn,
                    saa.grade_level,
                    si.profile_pix,
                    COUNT(tir.report_id)                                         AS incident_count,
                    SUM(CASE WHEN tir.status != 'resolved' THEN 1 ELSE 0 END)   AS unresolved_count
                FROM student_advisory_assignments saa
                JOIN user_management u    ON saa.student_id  = u.user_id
                LEFT JOIN student_info si ON si.user_id      = u.user_id
                LEFT JOIN teacher_incident_reports tir ON tir.student_id = u.user_id
                WHERE saa.advisory_id = :aid
                GROUP BY u.user_id, u.name, u.lrn, saa.grade_level, si.profile_pix
                ORDER BY unresolved_count DESC, incident_count DESC, u.name ASC
                LIMIT :lim
            ");
            $stmt->bindValue(':aid', $advisoryId, PDO::PARAM_INT);
            $stmt->bindValue(':lim', $limit,      PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('TeacherDashboardModel::getAdvisoryStudentSummary — ' . $e->getMessage());
            return [];
        }
    }

    /* ══════════════════════════════════════════
       ACTIVE SCHOOL YEAR
    ══════════════════════════════════════════ */
    public function getActiveSchoolYear() {
        try {
            $stmt = $this->db->query("
                SELECT start_year, end_year
                FROM school_years
                WHERE status = 'ACTIVE'
                LIMIT 1
            ");
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('TeacherDashboardModel::getActiveSchoolYear — ' . $e->getMessage());
            return null;
        }
    }
}
?>