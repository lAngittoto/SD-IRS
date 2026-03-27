<?php

class StudentModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getStudentById(int $userId): array|false
    {
        $stmt = $this->db->prepare("
            SELECT
                u.user_id,
                u.name,
                u.lrn,
                u.email,
                si.contact_no,
                si.home_address,
                si.profile_pix,
                si.guardian_name,
                si.guardian_contact,
                saa.grade_level,
                ac.advisory_name,
                u2.name AS adviser_name
            FROM user_management u
            LEFT JOIN student_info si                  ON si.user_id     = u.user_id
            LEFT JOIN student_advisory_assignments saa ON saa.student_id = u.user_id
            LEFT JOIN advisory_classes ac              ON ac.advisory_id  = saa.advisory_id
            LEFT JOIN user_management u2               ON u2.user_id     = ac.teacher_id
            WHERE u.user_id = :uid
              AND u.role    = 'Student'
            ORDER BY saa.assigned_date DESC
            LIMIT 1
        ");
        $stmt->execute([':uid' => $userId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getIncidentRecords(
        int    $userId,
        string $status      = '',
        string $violationId = '',
        string $sort        = 'desc'
    ): array {
        $sortDir = strtoupper($sort) === 'ASC' ? 'ASC' : 'DESC';

        $sql = "
            SELECT
                tir.report_id,
                tir.location,
                tir.description,
                tir.status,
                tir.created_at,
                tir.evidence_path,
                tir.evidence_type,
                tir.admin_notes,
                tir.reviewed_at,
                d.violation_name,
                tir.custom_violation,
                COALESCE(d.violation_name, tir.custom_violation) AS violation_display,
                u.name   AS reported_by,
                rev.name AS reviewed_by_name,
                s.name   AS sanction_name,
                w.name   AS warning_level
            FROM teacher_incident_reports tir
            LEFT JOIN discipline      d   ON d.discipline_id   = tir.violation_id
            LEFT JOIN sanctions       s   ON s.id_sanctions    = d.id_sanctions
            LEFT JOIN warning_levels  w   ON w.id_warning      = d.id_warning
            LEFT JOIN user_management u   ON u.user_id         = tir.teacher_id
            LEFT JOIN user_management rev ON rev.user_id       = tir.reviewed_by
            WHERE tir.student_id = :uid
        ";

        $params = [':uid' => $userId];

        if ($status !== '') {
            $sql .= " AND tir.status = :status";
            $params[':status'] = $status;
        }
        if ($violationId !== '') {
            $sql .= " AND tir.violation_id = :vid";
            $params[':vid'] = (int) $violationId;
        }

        $sql .= " ORDER BY tir.created_at $sortDir";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getIncidentSummary(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT
                COUNT(*)                                                AS total,
                SUM(CASE WHEN status = 'pending'   THEN 1 ELSE 0 END) AS pending,
                SUM(CASE WHEN status = 'reviewed'  THEN 1 ELSE 0 END) AS under_review,
                SUM(CASE WHEN status = 'resolved'  THEN 1 ELSE 0 END) AS resolved,
                SUM(CASE WHEN status = 'dismissed' THEN 1 ELSE 0 END) AS dismissed
            FROM teacher_incident_reports
            WHERE student_id = :uid
        ");
        $stmt->execute([':uid' => $userId]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: [
            'total'        => 0,
            'pending'      => 0,
            'under_review' => 0,
            'resolved'     => 0,
            'dismissed'    => 0,
        ];
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

    public function getIncidentDetail(int $reportId, int $studentId): array|false
    {
        $stmt = $this->db->prepare("
            SELECT
                tir.*,
                COALESCE(d.violation_name, tir.custom_violation) AS violation_display,
                d.description AS violation_desc,
                s.name   AS sanction_name,
                w.name   AS warning_level,
                u.name   AS reported_by,
                rev.name AS reviewed_by_name
            FROM teacher_incident_reports tir
            LEFT JOIN discipline      d   ON d.discipline_id = tir.violation_id
            LEFT JOIN sanctions       s   ON s.id_sanctions  = d.id_sanctions
            LEFT JOIN warning_levels  w   ON w.id_warning    = d.id_warning
            LEFT JOIN user_management u   ON u.user_id       = tir.teacher_id
            LEFT JOIN user_management rev ON rev.user_id     = tir.reviewed_by
            WHERE tir.report_id  = :rid
              AND tir.student_id = :sid
        ");
        $stmt->execute([':rid' => $reportId, ':sid' => $studentId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}