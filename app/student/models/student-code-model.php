<?php

class StudentCodeModel
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function getConductRules(): array
    {
        $stmt = $this->db->query("
            SELECT
                d.discipline_id,
                d.violation_name,
                d.description,
                s.name AS sanction_name,
                w.name AS warning_level
            FROM discipline d
            LEFT JOIN sanctions      s ON s.id_sanctions = d.id_sanctions
            LEFT JOIN warning_levels w ON w.id_warning   = d.id_warning
            ORDER BY w.id_warning DESC, d.violation_name ASC
        ");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}