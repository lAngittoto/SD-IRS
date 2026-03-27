<?php

class StudentReportModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getViolationList(): array
    {
        $stmt = $this->db->query("
            SELECT discipline_id, violation_name
            FROM discipline
            ORDER BY violation_name ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTeacherList(): array
    {
        $stmt = $this->db->query("
            SELECT user_id, name
            FROM user_management
            WHERE role = 'Teacher'
            ORDER BY name ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStudentList(int $excludeUserId): array
    {
        $stmt = $this->db->prepare("
            SELECT user_id, name, lrn
            FROM user_management
            WHERE role = 'Student'
              AND user_id != :uid
            ORDER BY name ASC
        ");
        $stmt->execute([':uid' => $excludeUserId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function submitStudentReport(array $data): int|false
    {
        // Find the advisory teacher of this student; fall back to admin (user_id = 11)
        $stmt = $this->db->prepare("
            SELECT ac.teacher_id
            FROM student_advisory_assignments saa
            JOIN advisory_classes ac ON ac.advisory_id = saa.advisory_id
            WHERE saa.student_id = :uid
            ORDER BY saa.assigned_date DESC
            LIMIT 1
        ");
        $stmt->execute([':uid' => $data['reporter_id']]);
        $row       = $stmt->fetch(PDO::FETCH_ASSOC);
        $teacherId = $row ? (int) $row['teacher_id'] : 11;

        // Get active school year; fall back to 1
        $syStmt       = $this->db->query("SELECT school_year_id FROM school_years WHERE status = 'ACTIVE' LIMIT 1");
        $sy           = $syStmt->fetch(PDO::FETCH_ASSOC);
        $schoolYearId = $sy ? (int) $sy['school_year_id'] : 1;

        $insert = $this->db->prepare("
            INSERT INTO teacher_incident_reports
                (teacher_id, report_target, student_id, teacher_involved_id, other_name,
                 grade_level, location, violation_id, custom_violation,
                 description, evidence_path, evidence_type, status, school_year_id)
            VALUES
                (:teacher_id, :report_target, :student_id, :teacher_involved_id, :other_name,
                 :grade_level, :location, :violation_id, :custom_violation,
                 :description, :evidence_path, :evidence_type, 'pending', :school_year_id)
        ");

        $result = $insert->execute([
            ':teacher_id'          => $teacherId,
            ':report_target'       => $data['report_target'],
            ':student_id'          => $data['student_id']          ?? null,
            ':teacher_involved_id' => $data['teacher_involved_id'] ?? null,
            ':other_name'          => $data['other_name']          ?? null,
            ':grade_level'         => $data['grade_level']         ?? null,
            ':location'            => $data['location'],
            ':violation_id'        => $data['violation_id']        ?? null,
            ':custom_violation'    => $data['custom_violation']    ?? null,
            ':description'         => $data['description'],
            ':evidence_path'       => $data['evidence_path']       ?? null,
            ':evidence_type'       => $data['evidence_type']       ?? null,
            ':school_year_id'      => $schoolYearId,
        ]);

        return $result ? (int) $this->db->lastInsertId() : false;
    }
}