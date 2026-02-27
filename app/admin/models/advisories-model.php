<?php
class AdvisoriesModel {
    private $conn;
    private $advisory_table = 'advisory_classes';
    private $assignment_table = 'student_advisory_assignments';
    private $users_table = 'user_management';
    private $teacher_roles_table = 'teacher_roles';
    private $student_info_table = 'student_info';
    
    public function __construct($pdo) {
        $this->conn = $pdo;
    }
    
    public function assignAdvisoryTeacher($data) {
        try {
            $this->conn->beginTransaction();
            
            if ($data['role_type'] === 'advisory') {
                $checkQuery = "SELECT advisory_id FROM {$this->advisory_table} WHERE teacher_id = :teacher_id";
                $checkStmt = $this->conn->prepare($checkQuery);
                $checkStmt->execute([':teacher_id' => $data['teacher_id']]);
                
                if ($checkStmt->rowCount() > 0) {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'This teacher is already assigned as an advisory teacher.'];
                }
                
                $checkNameQuery = "SELECT advisory_id FROM {$this->advisory_table} WHERE LOWER(advisory_name) = LOWER(:advisory_name)";
                $checkNameStmt = $this->conn->prepare($checkNameQuery);
                $checkNameStmt->execute([':advisory_name' => $data['advisory_name']]);
                
                if ($checkNameStmt->rowCount() > 0) {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'An advisory class with this name already exists.'];
                }
            }
            
            $deleteRoleQuery = "DELETE FROM {$this->teacher_roles_table} WHERE teacher_id = :teacher_id";
            $deleteRoleStmt = $this->conn->prepare($deleteRoleQuery);
            $deleteRoleStmt->execute([':teacher_id' => $data['teacher_id']]);
            
            $roleQuery = "INSERT INTO {$this->teacher_roles_table} (teacher_id, role_type) VALUES (:teacher_id, :role_type)";
            $roleStmt = $this->conn->prepare($roleQuery);
            $roleStmt->execute([':teacher_id' => $data['teacher_id'], ':role_type' => $data['role_type']]);
            
            if ($data['role_type'] === 'advisory') {
                $query = "INSERT INTO {$this->advisory_table} (teacher_id, advisory_name, grade_level, created_at) VALUES (:teacher_id, :advisory_name, :grade_level, NOW())";
                $stmt = $this->conn->prepare($query);
                $stmt->execute([':teacher_id' => $data['teacher_id'], ':advisory_name' => $data['advisory_name'], ':grade_level' => $data['grade_level']]);
            }
            
            $this->conn->commit();
            return ['success' => true, 'message' => 'Teacher assigned successfully!'];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    public function convertToSubjectTeacher($advisory_id) {
        try {
            $this->conn->beginTransaction();
            
            $getTeacherQuery = "SELECT teacher_id FROM {$this->advisory_table} WHERE advisory_id = :advisory_id";
            $teacherStmt = $this->conn->prepare($getTeacherQuery);
            $teacherStmt->execute([':advisory_id' => $advisory_id]);
            $teacher = $teacherStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$teacher) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Advisory class not found.'];
            }
            
            $deleteStudentsQuery = "DELETE FROM {$this->assignment_table} WHERE advisory_id = :advisory_id";
            $deleteStmt = $this->conn->prepare($deleteStudentsQuery);
            $deleteStmt->execute([':advisory_id' => $advisory_id]);
            
            $deleteAdvisoryQuery = "DELETE FROM {$this->advisory_table} WHERE advisory_id = :advisory_id";
            $deleteAdvisoryStmt = $this->conn->prepare($deleteAdvisoryQuery);
            $deleteAdvisoryStmt->execute([':advisory_id' => $advisory_id]);
            
            $deleteRoleQuery = "DELETE FROM {$this->teacher_roles_table} WHERE teacher_id = :teacher_id";
            $deleteRoleStmt = $this->conn->prepare($deleteRoleQuery);
            $deleteRoleStmt->execute([':teacher_id' => $teacher['teacher_id']]);
            
            $insertRoleQuery = "INSERT INTO {$this->teacher_roles_table} (teacher_id, role_type) VALUES (:teacher_id, 'subject')";
            $insertRoleStmt = $this->conn->prepare($insertRoleQuery);
            $insertRoleStmt->execute([':teacher_id' => $teacher['teacher_id']]);
            
            $this->conn->commit();
            return ['success' => true, 'message' => 'Teacher converted successfully.'];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    public function assignStudentsToAdvisory($advisory_id, $student_ids, $grade_levels) {
        try {
            $this->conn->beginTransaction();
            
            $advisoryQuery = "SELECT advisory_id, grade_level FROM {$this->advisory_table} WHERE advisory_id = :advisory_id";
            $advisoryStmt = $this->conn->prepare($advisoryQuery);
            $advisoryStmt->execute([':advisory_id' => $advisory_id]);
            $advisory = $advisoryStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$advisory) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Advisory class not found.'];
            }
            
            $countQuery = "SELECT COUNT(*) as current_count FROM {$this->assignment_table} WHERE advisory_id = :advisory_id";
            $countStmt = $this->conn->prepare($countQuery);
            $countStmt->execute([':advisory_id' => $advisory_id]);
            $currentCount = $countStmt->fetch(PDO::FETCH_ASSOC)['current_count'];
            
            $newTotal = $currentCount + count($student_ids);
            if ($newTotal > 40) {
                $this->conn->rollBack();
                $remaining = 40 - $currentCount;
                return ['success' => false, 'message' => "Cannot assign. Only $remaining slots available."];
            }
            
            $successCount = 0;
            $skippedCount = 0;
            
            foreach ($student_ids as $student_id) {
                $student_id = intval($student_id);
                $grade_level = isset($grade_levels[$student_id]) ? $grade_levels[$student_id] : $advisory['grade_level'];
                
                $checkQuery = "SELECT assignment_id FROM {$this->assignment_table} WHERE student_id = :student_id";
                $checkStmt = $this->conn->prepare($checkQuery);
                $checkStmt->execute([':student_id' => $student_id]);
                
                if ($checkStmt->rowCount() > 0) {
                    $skippedCount++;
                    continue;
                }
                
                $insertQuery = "INSERT INTO {$this->assignment_table} (advisory_id, student_id, grade_level, assigned_date) VALUES (:advisory_id, :student_id, :grade_level, NOW())";
                $insertStmt = $this->conn->prepare($insertQuery);
                $insertStmt->execute([':advisory_id' => $advisory_id, ':student_id' => $student_id, ':grade_level' => $grade_level]);
                $successCount++;
            }
            
            $this->conn->commit();
            $message = "$successCount student(s) assigned successfully.";
            if ($skippedCount > 0) {
                $message .= " $skippedCount already assigned.";
            }
            return ['success' => true, 'message' => $message];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    public function reassignStudent($assignment_id, $new_advisory_id, $current_grade) {
        try {
            $this->conn->beginTransaction();
            
            $countQuery = "SELECT COUNT(*) as current_count FROM {$this->assignment_table} WHERE advisory_id = :advisory_id";
            $countStmt = $this->conn->prepare($countQuery);
            $countStmt->execute([':advisory_id' => $new_advisory_id]);
            $currentCount = $countStmt->fetch(PDO::FETCH_ASSOC)['current_count'];
            
            if ($currentCount >= 40) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Advisory class is full (40/40).'];
            }
            
            $query = "UPDATE {$this->assignment_table} SET advisory_id = :new_advisory_id, assigned_date = NOW() WHERE assignment_id = :assignment_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':new_advisory_id' => $new_advisory_id, ':assignment_id' => $assignment_id]);
            
            $this->conn->commit();
            return ['success' => true, 'message' => 'Student reassigned successfully!'];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    public function removeFromAdvisory($assignment_id) {
        try {
            $query = "DELETE FROM {$this->assignment_table} WHERE assignment_id = :assignment_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':assignment_id' => $assignment_id]);
            return ['success' => true, 'message' => 'Student removed successfully!'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    public function updateStudentGrade($assignment_id, $new_grade) {
        try {
            $this->conn->beginTransaction();
            
            $getQuery = "SELECT aa.advisory_id, a.grade_level as advisory_grade FROM {$this->assignment_table} aa INNER JOIN {$this->advisory_table} a ON aa.advisory_id = a.advisory_id WHERE aa.assignment_id = :assignment_id";
            $getStmt = $this->conn->prepare($getQuery);
            $getStmt->execute([':assignment_id' => $assignment_id]);
            $assignment = $getStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$assignment) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Assignment not found.'];
            }
            
            if ($new_grade !== $assignment['advisory_grade']) {
                $deleteQuery = "DELETE FROM {$this->assignment_table} WHERE assignment_id = :assignment_id";
                $deleteStmt = $this->conn->prepare($deleteQuery);
                $deleteStmt->execute([':assignment_id' => $assignment_id]);
                $this->conn->commit();
                return ['success' => true, 'message' => "Student promoted to Grade {$new_grade}.", 'grade_changed' => true];
            }
            
            $updateQuery = "UPDATE {$this->assignment_table} SET grade_level = :new_grade WHERE assignment_id = :assignment_id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->execute([':new_grade' => $new_grade, ':assignment_id' => $assignment_id]);
            
            $this->conn->commit();
            return ['success' => true, 'message' => 'Grade updated successfully!'];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    public function bulkUpdateStudentGrade($assignment_ids, $new_grade) {
        try {
            $this->conn->beginTransaction();
            $successCount = 0;
            $errorMessages = [];
            
            foreach ($assignment_ids as $assignment_id) {
                $getQuery = "SELECT aa.assignment_id, aa.student_id, aa.grade_level as current_grade, s.name as student_name FROM {$this->assignment_table} aa INNER JOIN {$this->users_table} s ON aa.student_id = s.user_id WHERE aa.assignment_id = :assignment_id";
                $getStmt = $this->conn->prepare($getQuery);
                $getStmt->execute([':assignment_id' => $assignment_id]);
                $assignment = $getStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$assignment) {
                    $errorMessages[] = "Assignment not found";
                    continue;
                }
                
                if (intval($new_grade) <= intval($assignment['current_grade'])) {
                    $errorMessages[] = "{$assignment['student_name']} cannot be promoted to same/lower grade";
                    continue;
                }
                
                $this->conn->exec("CREATE TABLE IF NOT EXISTS student_promotions (promotion_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, student_id INT UNSIGNED NOT NULL, from_grade ENUM('7','8','9','10','11','12') NOT NULL, to_grade ENUM('7','8','9','10','11','12') NOT NULL, promoted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_student (student_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                
                $insertPromotionQuery = "INSERT INTO student_promotions (student_id, from_grade, to_grade) VALUES (:student_id, :from_grade, :to_grade)";
                $insertPromotionStmt = $this->conn->prepare($insertPromotionQuery);
                $insertPromotionStmt->execute([':student_id' => $assignment['student_id'], ':from_grade' => $assignment['current_grade'], ':to_grade' => $new_grade]);
                
                $deleteQuery = "DELETE FROM {$this->assignment_table} WHERE assignment_id = :assignment_id";
                $deleteStmt = $this->conn->prepare($deleteQuery);
                $deleteStmt->execute([':assignment_id' => $assignment_id]);
                
                $successCount++;
            }
            
            $this->conn->commit();
            
            if ($successCount > 0) {
                $message = "{$successCount} student(s) promoted to Grade {$new_grade}.";
                if (!empty($errorMessages)) {
                    $message .= " Note: " . implode(', ', $errorMessages);
                }
                return ['success' => true, 'message' => $message, 'promoted_grade' => $new_grade];
            } else {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'No students promoted.'];
            }
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function getStudentProfile($student_id) {
        try {
            $query = "SELECT u.user_id, u.name, u.email, u.lrn, COALESCE(si.contact_no, '') as contact_no, COALESCE(si.home_address, '') as home_address, COALESCE(si.profile_pix, '') as profile_pix, COALESCE(aa.grade_level, '') as current_grade, COALESCE(ac.advisory_name, '') as advisory_name, COALESCE(t.name, '') as teacher_name FROM {$this->users_table} u LEFT JOIN {$this->student_info_table} si ON u.user_id = si.user_id LEFT JOIN {$this->assignment_table} aa ON u.user_id = aa.student_id LEFT JOIN {$this->advisory_table} ac ON aa.advisory_id = ac.advisory_id LEFT JOIN {$this->users_table} t ON ac.teacher_id = t.user_id WHERE u.user_id = :student_id AND u.role = 'Student' LIMIT 1";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':student_id' => $student_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("getStudentProfile error: " . $e->getMessage());
            return null;
        }
    }

    public function updateStudentInfo($student_id, $data) {
        try {
            $this->conn->beginTransaction();
            $firstName = trim($data['first_name'] ?? '');
            $mi = trim($data['mi'] ?? '');
            $lastName = trim($data['last_name'] ?? '');
            $fullName = $firstName;
            if ($mi !== '') {
                if (substr($mi, -1) !== '.') $mi .= '.';
                $fullName .= ' ' . $mi;
            }
            if ($lastName !== '') {
                $fullName .= ' ' . $lastName;
            }
            $fullName = trim($fullName);

            $updateUserQuery = "UPDATE {$this->users_table} SET name = :name, lrn = :lrn WHERE user_id = :user_id AND role = 'Student'";
            $updateUserStmt = $this->conn->prepare($updateUserQuery);
            $updateUserStmt->execute([':name' => $fullName ?: ($data['name'] ?? ''), ':lrn' => $data['lrn'] ?? null, ':user_id' => $student_id]);

            $profilePicPath = $data['profile_pix'] ?? null;
            $upsertQuery = "INSERT INTO {$this->student_info_table} (user_id, contact_no, home_address, profile_pix) VALUES (:user_id, :contact_no, :home_address, :profile_pix) ON DUPLICATE KEY UPDATE contact_no = VALUES(contact_no), home_address = VALUES(home_address), profile_pix = COALESCE(VALUES(profile_pix), profile_pix)";
            $upsertStmt = $this->conn->prepare($upsertQuery);
            $upsertStmt->execute([':user_id' => $student_id, ':contact_no' => $data['contact_no'] ?? null, ':home_address' => $data['home_address'] ?? null, ':profile_pix' => $profilePicPath]);

            $this->conn->commit();
            return ['success' => true, 'message' => 'Student record updated!'];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function updateStudentGradeByStudentId($student_id, $new_grade) {
        try {
            $this->conn->beginTransaction();
            $currentQuery = "SELECT assignment_id, grade_level FROM {$this->assignment_table} WHERE student_id = :student_id LIMIT 1";
            $currentStmt = $this->conn->prepare($currentQuery);
            $currentStmt->execute([':student_id' => $student_id]);
            $current = $currentStmt->fetch(PDO::FETCH_ASSOC);

            if ($current && $current['grade_level'] !== $new_grade) {
                $this->conn->exec("CREATE TABLE IF NOT EXISTS student_promotions (promotion_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, student_id INT UNSIGNED NOT NULL, from_grade ENUM('7','8','9','10','11','12') NOT NULL, to_grade ENUM('7','8','9','10','11','12') NOT NULL, promoted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX idx_student (student_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
                $logQuery = "INSERT INTO student_promotions (student_id, from_grade, to_grade) VALUES (:student_id, :from_grade, :to_grade)";
                $logStmt = $this->conn->prepare($logQuery);
                $logStmt->execute([':student_id' => $student_id, ':from_grade' => $current['grade_level'], ':to_grade' => $new_grade]);

                $updateQuery = "UPDATE {$this->assignment_table} SET grade_level = :new_grade WHERE student_id = :student_id";
                $updateStmt = $this->conn->prepare($updateQuery);
                $updateStmt->execute([':new_grade' => $new_grade, ':student_id' => $student_id]);
            }

            $this->conn->commit();
            return ['success' => true, 'message' => "Grade updated to Grade {$new_grade}."];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }

    public function getTeacherProfile($advisory_id) {
        try {
            error_log("getTeacherProfile: Fetching for advisory_id = " . $advisory_id);
            
            // Simple query that doesn't depend on teacher_info existing
            $query = "SELECT u.user_id, u.name, u.email, a.advisory_id, a.teacher_id, a.advisory_name, a.grade_level, COALESCE(tr.role_type, 'advisory') as role_type FROM {$this->advisory_table} a INNER JOIN {$this->users_table} u ON a.teacher_id = u.user_id LEFT JOIN {$this->teacher_roles_table} tr ON a.teacher_id = tr.teacher_id WHERE a.advisory_id = :advisory_id LIMIT 1";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':advisory_id' => $advisory_id]);
            $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$teacher) {
                error_log("No teacher found for advisory_id: " . $advisory_id);
                return null;
            }
            
            error_log("Found teacher: " . $teacher['name']);
            
            // Add default values for teacher_info fields
            $teacher['teacher_no'] = '';
            $teacher['contact_no'] = '';
            $teacher['department'] = '';
            $teacher['advisory_section'] = '';
            $teacher['profile_pix'] = '';
            
            // Try to get teacher_info if it exists
            try {
                $infoQuery = "SELECT teacher_no, contact_no, department, advisory_section, profile_pix FROM teacher_info WHERE user_id = :user_id LIMIT 1";
                $infoStmt = $this->conn->prepare($infoQuery);
                $infoStmt->execute([':user_id' => $teacher['user_id']]);
                $info = $infoStmt->fetch(PDO::FETCH_ASSOC);
                
                if ($info) {
                    $teacher['teacher_no'] = $info['teacher_no'] ?? '';
                    $teacher['contact_no'] = $info['contact_no'] ?? '';
                    $teacher['department'] = $info['department'] ?? '';
                    $teacher['advisory_section'] = $info['advisory_section'] ?? '';
                    $teacher['profile_pix'] = $info['profile_pix'] ?? '';
                    error_log("Loaded teacher_info for user_id: " . $teacher['user_id']);
                }
            } catch (PDOException $e) {
                error_log("teacher_info table might not exist, using defaults");
            }
            
            return $teacher;
        } catch (PDOException $e) {
            error_log("getTeacherProfile error: " . $e->getMessage());
            return null;
        }
    }

    public function updateTeacherInfo($teacher_id, $data) {
        try {
            $this->conn->beginTransaction();
            $name = trim($data['name'] ?? '');

            $updateUserQuery = "UPDATE {$this->users_table} SET name = :name, email = :email WHERE user_id = :user_id AND role = 'Teacher'";
            $updateUserStmt = $this->conn->prepare($updateUserQuery);
            $updateUserStmt->execute([':name' => $name, ':email' => $data['email'] ?? null, ':user_id' => $teacher_id]);

            $profilePicPath = $data['profile_pix'] ?? null;

            // Create teacher_info table if it doesn't exist
            $this->conn->exec("CREATE TABLE IF NOT EXISTS teacher_info (
                teacher_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                user_id INT UNSIGNED NOT NULL UNIQUE,
                teacher_no VARCHAR(50),
                contact_no VARCHAR(15),
                department VARCHAR(100),
                advisory_section VARCHAR(100),
                profile_pix VARCHAR(255),
                updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                CONSTRAINT fk_teacher_user FOREIGN KEY (user_id) REFERENCES user_management(user_id) ON DELETE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

            // Use INSERT ... ON DUPLICATE KEY UPDATE
            $upsertQuery = "INSERT INTO teacher_info (user_id, teacher_no, contact_no, department, advisory_section, profile_pix) 
                            VALUES (:user_id, :teacher_no, :contact_no, :department, :advisory_section, :profile_pix) 
                            ON DUPLICATE KEY UPDATE 
                              teacher_no = VALUES(teacher_no),
                              contact_no = VALUES(contact_no),
                              department = VALUES(department),
                              advisory_section = VALUES(advisory_section),
                              profile_pix = COALESCE(VALUES(profile_pix), profile_pix)";
            
            $upsertStmt = $this->conn->prepare($upsertQuery);
            $upsertStmt->execute([
                ':user_id' => $teacher_id,
                ':teacher_no' => $data['teacher_id_field'] ?? null,
                ':contact_no' => $data['contact_no'] ?? null,
                ':department' => $data['department'] ?? null,
                ':advisory_section' => $data['specialization'] ?? null,
                ':profile_pix' => $profilePicPath
            ]);

            $this->conn->commit();
            return ['success' => true, 'message' => 'Teacher record updated!'];
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    public function getAdvisoryById($advisory_id) {
        try {
            $query = "SELECT advisory_id, teacher_id, advisory_name, grade_level FROM {$this->advisory_table} WHERE advisory_id = :advisory_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':advisory_id' => $advisory_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
    
    public function getAllTeachers() {
        try {
            $query = "SELECT u.user_id, u.name, u.email FROM {$this->users_table} u LEFT JOIN {$this->advisory_table} a ON u.user_id = a.teacher_id WHERE u.role = 'Teacher' AND a.advisory_id IS NULL ORDER BY u.name ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getAdvisoryTeachers() {
        try {
            $query = "SELECT a.advisory_id, a.teacher_id, u.name as teacher_name, u.email as teacher_email, a.advisory_name, a.grade_level, tr.role_type, a.created_at as assigned_date, COUNT(aa.assignment_id) as student_count FROM {$this->advisory_table} a INNER JOIN {$this->users_table} u ON a.teacher_id = u.user_id LEFT JOIN {$this->teacher_roles_table} tr ON a.teacher_id = tr.teacher_id LEFT JOIN {$this->assignment_table} aa ON a.advisory_id = aa.advisory_id GROUP BY a.advisory_id, a.teacher_id, u.name, u.email, a.advisory_name, a.grade_level, tr.role_type, a.created_at ORDER BY a.created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getSubjectTeachers() {
        try {
            $query = "SELECT u.user_id as teacher_id, u.name as teacher_name, u.email as teacher_email, tr.role_type, tr.assigned_at FROM {$this->teacher_roles_table} tr INNER JOIN {$this->users_table} u ON tr.teacher_id = u.user_id WHERE tr.role_type = 'subject' ORDER BY tr.assigned_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getAllStudents() {
        try {
            $query = "SELECT DISTINCT u.user_id, u.name, u.lrn, '7' as grade_level FROM {$this->users_table} u LEFT JOIN {$this->assignment_table} aa ON u.user_id = aa.student_id WHERE u.role = 'Student' AND aa.assignment_id IS NULL ORDER BY u.name ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getUnassignedStudents($advisory_id = 0, $grade_level = '') {
        try {
            $query = "SELECT u.user_id, u.name, u.lrn, COALESCE((SELECT sp.to_grade FROM student_promotions sp WHERE sp.student_id = u.user_id ORDER BY sp.promoted_at DESC LIMIT 1), '7') as grade_level FROM {$this->users_table} u WHERE u.role = 'Student' AND u.user_id NOT IN (SELECT student_id FROM {$this->assignment_table}) ORDER BY u.name ASC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getAssignedStudents($teacher_role = '', $grade_level = '', $date_filter = '', $search = '', $sort_by = 'student_name', $sort_order = 'ASC') {
        try {
            $sort_by = in_array($sort_by, ['student_name', 'lrn', 'grade_level', 'teacher_name', 'advisory_name', 'assigned_date']) ? $sort_by : 'student_name';
            $sort_order = strtoupper($sort_order) === 'DESC' ? 'DESC' : 'ASC';
            
            $query = "SELECT aa.assignment_id, aa.student_id, s.name as student_name, s.lrn, aa.grade_level, aa.assigned_date, at.advisory_id, at.advisory_name, tr.role_type, t.name as teacher_name, t.email as teacher_email FROM {$this->assignment_table} aa INNER JOIN {$this->users_table} s ON aa.student_id = s.user_id INNER JOIN {$this->advisory_table} at ON aa.advisory_id = at.advisory_id INNER JOIN {$this->users_table} t ON at.teacher_id = t.user_id LEFT JOIN {$this->teacher_roles_table} tr ON at.teacher_id = tr.teacher_id WHERE 1=1";
            
            $params = [];
            
            if (!empty($teacher_role)) {
                $query .= " AND tr.role_type = :teacher_role";
                $params[':teacher_role'] = $teacher_role;
            }
            
            if (!empty($grade_level)) {
                $query .= " AND aa.grade_level = :grade_level";
                $params[':grade_level'] = $grade_level;
            }
            
            if (!empty($date_filter)) {
                $query .= " AND DATE(aa.assigned_date) = :date_filter";
                $params[':date_filter'] = $date_filter;
            }
            
            if (!empty($search)) {
                $query .= " AND (s.name LIKE :search OR s.lrn LIKE :search OR t.name LIKE :search OR at.advisory_name LIKE :search)";
                $params[':search'] = "%$search%";
            }
            
            $sortColumnMap = ['student_name' => 's.name', 'lrn' => 's.lrn', 'grade_level' => 'aa.grade_level', 'teacher_name' => 't.name', 'advisory_name' => 'at.advisory_name', 'assigned_date' => 'aa.assigned_date'];
            $orderByColumn = $sortColumnMap[$sort_by] ?? 's.name';
            
            $query .= " ORDER BY {$orderByColumn} {$sort_order}";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getAdvisoryList($sort_by = 'advisory_name', $sort_order = 'ASC') {
        try {
            $sort_by = in_array($sort_by, ['advisory_name', 'teacher_name', 'grade_level', 'student_count']) ? $sort_by : 'advisory_name';
            $sort_order = strtoupper($sort_order) === 'DESC' ? 'DESC' : 'ASC';
            
            $sortColumnMap = ['advisory_name' => 'a.advisory_name', 'teacher_name' => 'u.name', 'grade_level' => 'a.grade_level', 'student_count' => 'student_count'];
            $orderByColumn = $sortColumnMap[$sort_by] ?? 'a.advisory_name';
            
            $query = "SELECT a.advisory_id, a.advisory_name, a.grade_level, u.name as teacher_name, COUNT(aa.assignment_id) as student_count FROM {$this->advisory_table} a INNER JOIN {$this->users_table} u ON a.teacher_id = u.user_id LEFT JOIN {$this->assignment_table} aa ON a.advisory_id = aa.advisory_id GROUP BY a.advisory_id, a.advisory_name, a.grade_level, u.name ORDER BY {$orderByColumn} {$sort_order}";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
    
    public function getStudentsByAdvisory($advisory_id) {
        try {
            $query = "SELECT aa.assignment_id, aa.student_id, s.name as student_name, s.lrn, aa.grade_level, aa.assigned_date FROM {$this->assignment_table} aa INNER JOIN {$this->users_table} s ON aa.student_id = s.user_id WHERE aa.advisory_id = :advisory_id ORDER BY aa.grade_level, s.name";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':advisory_id' => $advisory_id]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>