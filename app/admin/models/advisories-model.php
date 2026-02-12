<?php
class AdvisoriesModel {
    private $conn;
    private $advisory_table = 'advisory_classes';
    private $assignment_table = 'student_advisory_assignments';
    private $users_table = 'user_management';
    private $teacher_roles_table = 'teacher_roles';
    
    public function __construct($pdo) {
        $this->conn = $pdo;
    }
    
    // ============================================
    // TEACHER MANAGEMENT
    // ============================================
    
    /**
     * Assign a teacher as advisory teacher
     */
    public function assignAdvisoryTeacher($data) {
        try {
            $this->conn->beginTransaction();
            
            // Check if teacher already has advisory assignment
            $checkQuery = "SELECT advisory_id FROM {$this->advisory_table} 
                          WHERE teacher_id = :teacher_id";
            $checkStmt = $this->conn->prepare($checkQuery);
            $checkStmt->execute([':teacher_id' => $data['teacher_id']]);
            
            if ($checkStmt->rowCount() > 0) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'This teacher is already assigned as an advisory teacher.'];
            }
            
            // Insert or update teacher role
            $roleQuery = "INSERT INTO {$this->teacher_roles_table} (teacher_id, role_type) 
                         VALUES (:teacher_id, :role_type)
                         ON DUPLICATE KEY UPDATE role_type = :role_type";
            $roleStmt = $this->conn->prepare($roleQuery);
            $roleStmt->execute([
                ':teacher_id' => $data['teacher_id'],
                ':role_type' => $data['role_type']
            ]);
            
            // If advisory teacher, insert advisory class
            if ($data['role_type'] === 'advisory') {
                $query = "INSERT INTO {$this->advisory_table} 
                         (teacher_id, advisory_name, grade_level, created_at) 
                         VALUES (:teacher_id, :advisory_name, :grade_level, NOW())";
                
                $stmt = $this->conn->prepare($query);
                $stmt->execute([
                    ':teacher_id' => $data['teacher_id'],
                    ':advisory_name' => $data['advisory_name'],
                    ':grade_level' => $data['grade_level']
                ]);
            }
            
            $this->conn->commit();
            
            $message = $data['role_type'] === 'advisory' 
                ? 'Advisory teacher assigned successfully!' 
                : 'Subject teacher assigned successfully!';
            
            return ['success' => true, 'message' => $message];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Convert advisory teacher to subject teacher (removes all students)
     */
    public function convertToSubjectTeacher($advisory_id) {
        try {
            $this->conn->beginTransaction();
            
            // Get teacher_id from advisory
            $getTeacherQuery = "SELECT teacher_id FROM {$this->advisory_table} 
                               WHERE advisory_id = :advisory_id";
            $teacherStmt = $this->conn->prepare($getTeacherQuery);
            $teacherStmt->execute([':advisory_id' => $advisory_id]);
            $teacher = $teacherStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$teacher) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Advisory class not found.'];
            }
            
            // Delete all student assignments
            $deleteStudentsQuery = "DELETE FROM {$this->assignment_table} 
                                   WHERE advisory_id = :advisory_id";
            $deleteStmt = $this->conn->prepare($deleteStudentsQuery);
            $deleteStmt->execute([':advisory_id' => $advisory_id]);
            
            // Delete advisory class
            $deleteAdvisoryQuery = "DELETE FROM {$this->advisory_table} 
                                   WHERE advisory_id = :advisory_id";
            $deleteAdvisoryStmt = $this->conn->prepare($deleteAdvisoryQuery);
            $deleteAdvisoryStmt->execute([':advisory_id' => $advisory_id]);
            
            // Update teacher role to subject
            $updateRoleQuery = "UPDATE {$this->teacher_roles_table} 
                               SET role_type = 'subject' 
                               WHERE teacher_id = :teacher_id";
            $updateRoleStmt = $this->conn->prepare($updateRoleQuery);
            $updateRoleStmt->execute([':teacher_id' => $teacher['teacher_id']]);
            
            $this->conn->commit();
            return ['success' => true, 'message' => 'Teacher converted to subject teacher successfully. All students removed.'];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    // ============================================
    // STUDENT ASSIGNMENT MANAGEMENT
    // ============================================
    
    /**
     * Assign multiple students to advisory teacher
     */
    public function assignStudentsToAdvisory($advisory_id, $student_ids, $grade_levels) {
        try {
            $this->conn->beginTransaction();
            
            $successCount = 0;
            $skippedCount = 0;
            
            foreach ($student_ids as $student_id) {
                $student_id = intval($student_id);
                $grade_level = isset($grade_levels[$student_id]) ? intval($grade_levels[$student_id]) : 7;
                
                // Check if student already assigned
                $checkQuery = "SELECT assignment_id FROM {$this->assignment_table} 
                              WHERE student_id = :student_id";
                $checkStmt = $this->conn->prepare($checkQuery);
                $checkStmt->execute([':student_id' => $student_id]);
                
                if ($checkStmt->rowCount() > 0) {
                    $skippedCount++;
                    continue;
                }
                
                // Assign student
                $insertQuery = "INSERT INTO {$this->assignment_table} 
                               (advisory_id, student_id, grade_level, assigned_date) 
                               VALUES (:advisory_id, :student_id, :grade_level, NOW())";
                
                $insertStmt = $this->conn->prepare($insertQuery);
                $insertStmt->execute([
                    ':advisory_id' => $advisory_id,
                    ':student_id' => $student_id,
                    ':grade_level' => $grade_level
                ]);
                
                $successCount++;
            }
            
            $this->conn->commit();
            
            $message = "$successCount student(s) assigned successfully.";
            if ($skippedCount > 0) {
                $message .= " $skippedCount student(s) were already assigned.";
            }
            
            return ['success' => true, 'message' => $message];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Reassign student to different advisory teacher
     */
    public function reassignStudent($assignment_id, $new_advisory_id, $grade_level) {
        try {
            $query = "UPDATE {$this->assignment_table} 
                     SET advisory_id = :new_advisory_id, 
                         grade_level = :grade_level,
                         assigned_date = NOW()
                     WHERE assignment_id = :assignment_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':new_advisory_id' => $new_advisory_id,
                ':grade_level' => $grade_level,
                ':assignment_id' => $assignment_id
            ]);
            
            return ['success' => true, 'message' => 'Student reassigned successfully!'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Remove student from advisory (unassign)
     */
    public function removeFromAdvisory($assignment_id) {
        try {
            $query = "DELETE FROM {$this->assignment_table} 
                     WHERE assignment_id = :assignment_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':assignment_id' => $assignment_id]);
            
            return ['success' => true, 'message' => 'Student removed from advisory successfully!'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * Update student grade level
     */
    public function updateStudentGrade($assignment_id, $grade_level) {
        try {
            $query = "UPDATE {$this->assignment_table} 
                     SET grade_level = :grade_level 
                     WHERE assignment_id = :assignment_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':grade_level' => $grade_level,
                ':assignment_id' => $assignment_id
            ]);
            
            return ['success' => true, 'message' => 'Grade level updated successfully!'];
            
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    // ============================================
    // DATA RETRIEVAL
    // ============================================
    
    /**
     * Get all teachers (for dropdown selection)
     */
    public function getAllTeachers() {
        try {
            $query = "SELECT user_id, name, email 
                     FROM {$this->users_table} 
                     WHERE role = 'Teacher' 
                     ORDER BY name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Get all advisory teachers with their details
     */
    public function getAdvisoryTeachers() {
        try {
            $query = "SELECT 
                        a.advisory_id,
                        a.teacher_id,
                        u.name as teacher_name,
                        u.email as teacher_email,
                        a.advisory_name,
                        a.grade_level,
                        tr.role_type,
                        a.created_at as assigned_date,
                        COUNT(aa.assignment_id) as student_count
                     FROM {$this->advisory_table} a
                     INNER JOIN {$this->users_table} u ON a.teacher_id = u.user_id
                     LEFT JOIN {$this->teacher_roles_table} tr ON a.teacher_id = tr.teacher_id
                     LEFT JOIN {$this->assignment_table} aa ON a.advisory_id = aa.advisory_id
                     GROUP BY a.advisory_id, a.teacher_id, u.name, u.email, a.advisory_name, a.grade_level, tr.role_type, a.created_at
                     ORDER BY a.created_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Get all students (for assignment modal) - only unassigned students
     */
    public function getAllStudents() {
        try {
            // Get only students who are NOT currently assigned to any advisory
            $query = "SELECT DISTINCT
                        u.user_id, 
                        u.name, 
                        u.lrn,
                        '7' as grade_level
                     FROM {$this->users_table} u
                     LEFT JOIN {$this->assignment_table} aa ON u.user_id = aa.student_id
                     WHERE u.role = 'Student' AND aa.assignment_id IS NULL
                     ORDER BY u.name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Get assigned students with filters and search
     */
    public function getAssignedStudents($teacher_role = '', $grade_level = '', $date_filter = '', $search = '') {
        try {
            $query = "SELECT 
                        aa.assignment_id,
                        aa.student_id,
                        s.name as student_name,
                        s.lrn,
                        aa.grade_level,
                        aa.assigned_date,
                        at.advisory_id,
                        at.advisory_name,
                        tr.role_type,
                        t.name as teacher_name,
                        t.email as teacher_email
                     FROM {$this->assignment_table} aa
                     INNER JOIN {$this->users_table} s ON aa.student_id = s.user_id
                     INNER JOIN {$this->advisory_table} at ON aa.advisory_id = at.advisory_id
                     INNER JOIN {$this->users_table} t ON at.teacher_id = t.user_id
                     LEFT JOIN {$this->teacher_roles_table} tr ON at.teacher_id = tr.teacher_id
                     WHERE 1=1";
            
            $params = [];
            
            // Teacher role filter
            if (!empty($teacher_role)) {
                $query .= " AND tr.role_type = :teacher_role";
                $params[':teacher_role'] = $teacher_role;
            }
            
            // Grade level filter
            if (!empty($grade_level)) {
                $query .= " AND aa.grade_level = :grade_level";
                $params[':grade_level'] = $grade_level;
            }
            
            // Date filter
            if (!empty($date_filter)) {
                $query .= " AND DATE(aa.assigned_date) = :date_filter";
                $params[':date_filter'] = $date_filter;
            }
            
            // Search filter
            if (!empty($search)) {
                $query .= " AND (s.name LIKE :search OR s.lrn LIKE :search OR t.name LIKE :search OR at.advisory_name LIKE :search)";
                $params[':search'] = "%$search%";
            }
            
            $query .= " ORDER BY aa.assigned_date DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Get unassigned students
     */
    public function getUnassignedStudents() {
        try {
            $query = "SELECT u.user_id, u.name, u.lrn 
                     FROM {$this->users_table} u
                     LEFT JOIN {$this->assignment_table} aa ON u.user_id = aa.student_id
                     WHERE u.role = 'Student' AND aa.assignment_id IS NULL
                     ORDER BY u.name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Get advisory details by ID
     */
    public function getAdvisoryDetails($advisory_id) {
        try {
            $query = "SELECT 
                        a.advisory_id,
                        a.teacher_id,
                        u.name as teacher_name,
                        u.email as teacher_email,
                        a.advisory_name,
                        tr.role_type,
                        a.created_at as assigned_date
                     FROM {$this->advisory_table} a
                     INNER JOIN {$this->users_table} u ON a.teacher_id = u.user_id
                     LEFT JOIN {$this->teacher_roles_table} tr ON a.teacher_id = tr.teacher_id
                     WHERE a.advisory_id = :advisory_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':advisory_id' => $advisory_id]);
            
            return $stmt->fetch(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Get students by advisory ID
     */
    public function getStudentsByAdvisory($advisory_id) {
        try {
            $query = "SELECT 
                        aa.assignment_id,
                        aa.student_id,
                        s.name as student_name,
                        s.lrn,
                        aa.grade_level,
                        aa.assigned_date
                     FROM {$this->assignment_table} aa
                     INNER JOIN {$this->users_table} s ON aa.student_id = s.user_id
                     WHERE aa.advisory_id = :advisory_id
                     ORDER BY aa.grade_level, s.name";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':advisory_id' => $advisory_id]);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>