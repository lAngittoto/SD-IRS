<?php
class TeacherReportModel {
    private $conn;

    public function __construct($pdo) {
        $this->conn = $pdo;
        $this->ensureTable();
    }

    private function ensureTable() {
        try {
            $this->conn->exec("
                CREATE TABLE IF NOT EXISTS `teacher_incident_reports` (
                  `report_id` int UNSIGNED NOT NULL AUTO_INCREMENT,
                  `teacher_id` int UNSIGNED NOT NULL,
                  `report_target` enum('student','teacher','other') NOT NULL DEFAULT 'student',
                  `student_id` int UNSIGNED DEFAULT NULL,
                  `teacher_involved_id` int UNSIGNED DEFAULT NULL,
                  `other_name` varchar(150) DEFAULT NULL,
                  `grade_level` enum('7','8','9','10','11','12') DEFAULT NULL,
                  `location` varchar(255) NOT NULL,
                  `violation_id` int DEFAULT NULL,
                  `custom_violation` varchar(255) DEFAULT NULL,
                  `description` text NOT NULL,
                  `evidence_path` varchar(500) DEFAULT NULL,
                  `evidence_type` varchar(50) DEFAULT NULL,
                  `status` enum('pending','reviewed','resolved','dismissed') NOT NULL DEFAULT 'pending',
                  `reviewed_by` int UNSIGNED DEFAULT NULL,
                  `reviewed_at` timestamp NULL DEFAULT NULL,
                  `admin_notes` text DEFAULT NULL,
                  `school_year_id` int UNSIGNED NOT NULL,
                  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                  PRIMARY KEY (`report_id`),
                  KEY `fk_tir_teacher` (`teacher_id`),
                  KEY `fk_tir_student` (`student_id`),
                  KEY `idx_status` (`status`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci
            ");
        } catch (PDOException $e) {
            // Table may already exist
        }
    }

    public function getActiveSchoolYear() {
        try {
            $stmt = $this->conn->prepare("SELECT school_year_id FROM school_years WHERE status = 'ACTIVE' LIMIT 1");
            $stmt->execute();
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }

    public function getViolations() {
        try {
            $stmt = $this->conn->prepare("SELECT discipline_id, violation_name FROM discipline ORDER BY violation_name ASC");
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getStudentsByGrade($grade_level) {
        try {
            $stmt = $this->conn->prepare("
                SELECT u.user_id, u.name, u.lrn
                FROM user_management u
                INNER JOIN student_advisory_assignments saa ON u.user_id = saa.student_id
                WHERE u.role = 'Student'
                  AND saa.grade_level = :grade_level
                ORDER BY u.name ASC
            ");
            $stmt->execute([':grade_level' => $grade_level]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function getAllTeachers($exclude_id = null) {
        try {
            $query = "SELECT user_id, name, email FROM user_management WHERE role = 'Teacher'";
            $params = [];
            if ($exclude_id) {
                $query .= " AND user_id != :exclude_id";
                $params[':exclude_id'] = $exclude_id;
            }
            $query .= " ORDER BY name ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function submitReport($data) {
        try {
            $sy = $this->getActiveSchoolYear();
            $school_year_id = $sy ? $sy['school_year_id'] : 1;

            $stmt = $this->conn->prepare("
                INSERT INTO teacher_incident_reports
                  (teacher_id, report_target, student_id, teacher_involved_id, other_name,
                   grade_level, location, violation_id, custom_violation, description,
                   evidence_path, evidence_type, school_year_id)
                VALUES
                  (:teacher_id, :report_target, :student_id, :teacher_involved_id, :other_name,
                   :grade_level, :location, :violation_id, :custom_violation, :description,
                   :evidence_path, :evidence_type, :school_year_id)
            ");

            $stmt->execute([
                ':teacher_id'          => $data['teacher_id'],
                ':report_target'       => $data['report_target'],
                ':student_id'          => $data['student_id'] ?: null,
                ':teacher_involved_id' => $data['teacher_involved_id'] ?: null,
                ':other_name'          => $data['other_name'] ?: null,
                ':grade_level'         => $data['grade_level'] ?: null,
                ':location'            => $data['location'],
                ':violation_id'        => $data['violation_id'] ?: null,
                ':custom_violation'    => $data['custom_violation'] ?: null,
                ':description'         => $data['description'],
                ':evidence_path'       => $data['evidence_path'] ?: null,
                ':evidence_type'       => $data['evidence_type'] ?: null,
                ':school_year_id'      => $school_year_id,
            ]);

            return ['success' => true, 'message' => 'Incident report submitted successfully!', 'report_id' => $this->conn->lastInsertId()];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function getReportsByTeacher($teacher_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT
                    r.*,
                    s.name  AS student_name,
                    ti.name AS teacher_involved_name,
                    d.violation_name,
                    CONCAT(sy.start_year, '-', sy.end_year) AS school_year
                FROM teacher_incident_reports r
                LEFT JOIN user_management s  ON r.student_id          = s.user_id
                LEFT JOIN user_management ti ON r.teacher_involved_id = ti.user_id
                LEFT JOIN discipline d        ON r.violation_id        = d.discipline_id
                LEFT JOIN school_years sy     ON r.school_year_id      = sy.school_year_id
                WHERE r.teacher_id = :teacher_id
                ORDER BY r.created_at DESC
            ");
            $stmt->execute([':teacher_id' => $teacher_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}