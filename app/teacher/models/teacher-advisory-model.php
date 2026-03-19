<?php
class TeacherAdvisoryModel {
    private $db;

    public function __construct($pdo) {
        $this->db = $pdo;
    }

    /* ══════════════════════════════════════════
       Get the advisory class of the logged-in teacher
    ══════════════════════════════════════════ */
    public function getAdvisoryClass($teacherId) {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    ac.advisory_id,
                    ac.advisory_name,
                    ac.grade_level,
                    ac.created_at,
                    sy.start_year,
                    sy.end_year,
                    sy.status AS sy_status
                FROM advisory_classes ac
                LEFT JOIN school_years sy ON sy.status = 'ACTIVE'
                WHERE ac.teacher_id = :tid
                LIMIT 1
            ");
            $stmt->execute([':tid' => $teacherId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('TeacherAdvisoryModel::getAdvisoryClass — ' . $e->getMessage());
            return null;
        }
    }

    /* ══════════════════════════════════════════
       Get all students assigned to this advisory
    ══════════════════════════════════════════ */
    public function getAssignedStudents($advisoryId) {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    u.user_id,
                    u.name,
                    u.lrn,
                    saa.grade_level,
                    saa.assigned_date,
                    si.contact_no,
                    si.home_address,
                    si.profile_pix,
                    si.guardian_name,
                    si.guardian_contact,
                    -- Incident count for this student
                    COUNT(DISTINCT tir.report_id) AS incident_count,
                    -- Unresolved incidents
                    SUM(CASE WHEN tir.status != 'resolved' THEN 1 ELSE 0 END) AS unresolved_count
                FROM student_advisory_assignments saa
                JOIN user_management u     ON saa.student_id  = u.user_id
                LEFT JOIN student_info si  ON si.user_id      = u.user_id
                LEFT JOIN teacher_incident_reports tir ON tir.student_id = u.user_id
                WHERE saa.advisory_id = :aid
                GROUP BY
                    u.user_id, u.name, u.lrn, saa.grade_level,
                    saa.assigned_date, si.contact_no, si.home_address,
                    si.profile_pix, si.guardian_name, si.guardian_contact
                ORDER BY u.name ASC
            ");
            $stmt->execute([':aid' => $advisoryId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('TeacherAdvisoryModel::getAssignedStudents — ' . $e->getMessage());
            return [];
        }
    }

    /* ══════════════════════════════════════════
       Get incident history of a specific student
    ══════════════════════════════════════════ */
    public function getStudentIncidents($studentId) {
        try {
            $stmt = $this->db->prepare("
                SELECT
                    tir.report_id,
                    tir.status,
                    tir.created_at,
                    tir.location,
                    tir.description,
                    tir.admin_notes,
                    COALESCE(d.violation_name, tir.custom_violation, 'N/A') AS violation_display,
                    w.name  AS severity,
                    rep.name AS reported_by
                FROM teacher_incident_reports tir
                LEFT JOIN discipline d     ON tir.violation_id = d.discipline_id
                LEFT JOIN warning_levels w ON d.id_warning     = w.id_warning
                LEFT JOIN user_management rep ON tir.teacher_id = rep.user_id
                WHERE tir.student_id = :sid
                ORDER BY tir.created_at DESC
            ");
            $stmt->execute([':sid' => $studentId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('TeacherAdvisoryModel::getStudentIncidents — ' . $e->getMessage());
            return [];
        }
    }
}
?>