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
     * Assign a teacher as advisory or subject teacher
     */
    public function assignAdvisoryTeacher($data) {
        try {
            $this->conn->beginTransaction();
            
            // Check if teacher already has advisory assignment (only for advisory role)
            if ($data['role_type'] === 'advisory') {
                $checkQuery = "SELECT advisory_id FROM {$this->advisory_table} 
                              WHERE teacher_id = :teacher_id";
                $checkStmt = $this->conn->prepare($checkQuery);
                $checkStmt->execute([':teacher_id' => $data['teacher_id']]);
                
                if ($checkStmt->rowCount() > 0) {
                    $this->conn->rollBack();
                    return ['success' => false, 'message' => 'This teacher is already assigned as an advisory teacher.'];
                }
            }
            
            // Check for existing role and delete it to prevent duplication
            $deleteRoleQuery = "DELETE FROM {$this->teacher_roles_table} WHERE teacher_id = :teacher_id";
            $deleteRoleStmt = $this->conn->prepare($deleteRoleQuery);
            $deleteRoleStmt->execute([':teacher_id' => $data['teacher_id']]);
            
            // Insert new teacher role
            $roleQuery = "INSERT INTO {$this->teacher_roles_table} (teacher_id, role_type) 
                         VALUES (:teacher_id, :role_type)";
            $roleStmt = $this->conn->prepare($roleQuery);
            $roleStmt->execute([
                ':teacher_id' => $data['teacher_id'],
                ':role_type' => $data['role_type']
            ]);
            
            // If advisory teacher, insert advisory class WITH GRADE LEVEL
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
            
            // Delete existing role first to prevent duplication
            $deleteRoleQuery = "DELETE FROM {$this->teacher_roles_table} WHERE teacher_id = :teacher_id";
            $deleteRoleStmt = $this->conn->prepare($deleteRoleQuery);
            $deleteRoleStmt->execute([':teacher_id' => $teacher['teacher_id']]);
            
            // Insert new subject role
            $insertRoleQuery = "INSERT INTO {$this->teacher_roles_table} (teacher_id, role_type) 
                               VALUES (:teacher_id, 'subject')";
            $insertRoleStmt = $this->conn->prepare($insertRoleQuery);
            $insertRoleStmt->execute([':teacher_id' => $teacher['teacher_id']]);
            
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
            
            // Check if advisory exists
            $advisoryQuery = "SELECT advisory_id, grade_level FROM {$this->advisory_table} 
                             WHERE advisory_id = :advisory_id";
            $advisoryStmt = $this->conn->prepare($advisoryQuery);
            $advisoryStmt->execute([':advisory_id' => $advisory_id]);
            $advisory = $advisoryStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$advisory) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Advisory class not found.'];
            }
            
            // Check current student count
            $countQuery = "SELECT COUNT(*) as current_count FROM {$this->assignment_table} 
                          WHERE advisory_id = :advisory_id";
            $countStmt = $this->conn->prepare($countQuery);
            $countStmt->execute([':advisory_id' => $advisory_id]);
            $currentCount = $countStmt->fetch(PDO::FETCH_ASSOC)['current_count'];
            
            // Check if adding these students would exceed 40
            $newTotal = $currentCount + count($student_ids);
            if ($newTotal > 40) {
                $this->conn->rollBack();
                $remaining = 40 - $currentCount;
                return ['success' => false, 'message' => "Cannot assign " . count($student_ids) . " students. Only $remaining slots available (Maximum: 40 students per advisory)."];
            }
            
            $successCount = 0;
            $skippedCount = 0;
            
            foreach ($student_ids as $student_id) {
                $student_id = intval($student_id);
                $grade_level = isset($grade_levels[$student_id]) ? $grade_levels[$student_id] : $advisory['grade_level'];
                
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
    public function reassignStudent($assignment_id, $new_advisory_id, $current_grade) {
        try {
            $this->conn->beginTransaction();
            
            // Check if new advisory has space
            $countQuery = "SELECT COUNT(*) as current_count FROM {$this->assignment_table} 
                          WHERE advisory_id = :advisory_id";
            $countStmt = $this->conn->prepare($countQuery);
            $countStmt->execute([':advisory_id' => $new_advisory_id]);
            $currentCount = $countStmt->fetch(PDO::FETCH_ASSOC)['current_count'];
            
            if ($currentCount >= 40) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Cannot reassign. The selected advisory class is full (40/40 students).'];
            }
            
            $query = "UPDATE {$this->assignment_table} 
                     SET advisory_id = :new_advisory_id, 
                         assigned_date = NOW()
                     WHERE assignment_id = :assignment_id";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':new_advisory_id' => $new_advisory_id,
                ':assignment_id' => $assignment_id
            ]);
            
            $this->conn->commit();
            return ['success' => true, 'message' => 'Student reassigned successfully!'];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
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
     * NEW: Update student grade level
     */
    public function updateStudentGrade($assignment_id, $new_grade) {
        try {
            $this->conn->beginTransaction();
            
            // Get current assignment info
            $getQuery = "SELECT aa.advisory_id, a.grade_level as advisory_grade 
                        FROM {$this->assignment_table} aa
                        INNER JOIN {$this->advisory_table} a ON aa.advisory_id = a.advisory_id
                        WHERE aa.assignment_id = :assignment_id";
            $getStmt = $this->conn->prepare($getQuery);
            $getStmt->execute([':assignment_id' => $assignment_id]);
            $assignment = $getStmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$assignment) {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'Assignment not found.'];
            }
            
            // If new grade doesn't match advisory grade, remove from advisory
            if ($new_grade !== $assignment['advisory_grade']) {
                $deleteQuery = "DELETE FROM {$this->assignment_table} 
                               WHERE assignment_id = :assignment_id";
                $deleteStmt = $this->conn->prepare($deleteQuery);
                $deleteStmt->execute([':assignment_id' => $assignment_id]);
                
                $this->conn->commit();
                return [
                    'success' => true, 
                    'message' => "Student promoted to Grade {$new_grade}. They have been removed from their current advisory and are now available for assignment to a Grade {$new_grade} advisory class.",
                    'grade_changed' => true
                ];
            }
            
            // Update grade level if it matches advisory
            $updateQuery = "UPDATE {$this->assignment_table} 
                           SET grade_level = :new_grade 
                           WHERE assignment_id = :assignment_id";
            $updateStmt = $this->conn->prepare($updateQuery);
            $updateStmt->execute([
                ':new_grade' => $new_grade,
                ':assignment_id' => $assignment_id
            ]);
            
            $this->conn->commit();
            return ['success' => true, 'message' => 'Student grade updated successfully!'];
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    /**
     * NEW: Bulk update student grades (for grade promotion)
     */
    public function bulkUpdateStudentGrade($assignment_ids, $new_grade) {
        try {
            $this->conn->beginTransaction();
            
            $successCount = 0;
            $errorMessages = [];
            $promotedStudents = [];
            
            foreach ($assignment_ids as $assignment_id) {
                // Get current assignment info including student_id
                $getQuery = "SELECT aa.assignment_id, aa.student_id, aa.advisory_id, aa.grade_level as current_grade, 
                                    a.grade_level as advisory_grade, s.name as student_name
                            FROM {$this->assignment_table} aa
                            INNER JOIN {$this->advisory_table} a ON aa.advisory_id = a.advisory_id
                            INNER JOIN {$this->users_table} s ON aa.student_id = s.user_id
                            WHERE aa.assignment_id = :assignment_id";
                $getStmt = $this->conn->prepare($getQuery);
                $getStmt->execute([':assignment_id' => $assignment_id]);
                $assignment = $getStmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$assignment) {
                    $errorMessages[] = "Assignment ID {$assignment_id} not found";
                    continue;
                }
                
                // Validate promotion is to higher grade
                if (intval($new_grade) <= intval($assignment['current_grade'])) {
                    $errorMessages[] = "{$assignment['student_name']} cannot be promoted to same or lower grade";
                    continue;
                }
                
                // Store promoted student info
                $promotedStudents[] = [
                    'student_id' => $assignment['student_id'],
                    'old_grade' => $assignment['current_grade'],
                    'new_grade' => $new_grade
                ];
                
                // Create a promotion record BEFORE deleting (audit trail)
                // First check if student_promotions table exists, if not create it
                $createTableQuery = "CREATE TABLE IF NOT EXISTS student_promotions (
                    promotion_id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    student_id INT UNSIGNED NOT NULL,
                    from_grade ENUM('7', '8', '9', '10') NOT NULL,
                    to_grade ENUM('7', '8', '9', '10') NOT NULL,
                    promoted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX idx_student (student_id)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
                
                $this->conn->exec($createTableQuery);
                
                // Insert promotion record
                $insertPromotionQuery = "INSERT INTO student_promotions (student_id, from_grade, to_grade) 
                                        VALUES (:student_id, :from_grade, :to_grade)";
                $insertPromotionStmt = $this->conn->prepare($insertPromotionQuery);
                $insertPromotionStmt->execute([
                    ':student_id' => $assignment['student_id'],
                    ':from_grade' => $assignment['current_grade'],
                    ':to_grade' => $new_grade
                ]);
                
                // Remove from current advisory (since they're moving to new grade)
                $deleteQuery = "DELETE FROM {$this->assignment_table} 
                               WHERE assignment_id = :assignment_id";
                $deleteStmt = $this->conn->prepare($deleteQuery);
                $deleteStmt->execute([':assignment_id' => $assignment_id]);
                
                $successCount++;
            }
            
            $this->conn->commit();
            
            if ($successCount > 0) {
                $message = "{$successCount} student(s) promoted to Grade {$new_grade}. ";
                $message .= "They have been removed from their current advisory and are now available for assignment to Grade {$new_grade} advisory classes.";
                
                if (!empty($errorMessages)) {
                    $message .= " Note: " . implode(', ', $errorMessages);
                }
                
                return ['success' => true, 'message' => $message, 'promoted_grade' => $new_grade];
            } else {
                $this->conn->rollBack();
                return ['success' => false, 'message' => 'No students were promoted. ' . implode(', ', $errorMessages)];
            }
            
        } catch (PDOException $e) {
            $this->conn->rollBack();
            return ['success' => false, 'message' => 'Error: ' . $e->getMessage()];
        }
    }
    
    // ============================================
    // DATA RETRIEVAL
    // ============================================
    
    /**
     * Get advisory by ID
     */
    public function getAdvisoryById($advisory_id) {
        try {
            $query = "SELECT advisory_id, teacher_id, advisory_name, grade_level 
                     FROM {$this->advisory_table} 
                     WHERE advisory_id = :advisory_id";
            $stmt = $this->conn->prepare($query);
            $stmt->execute([':advisory_id' => $advisory_id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
    
    /**
     * Get all teachers (for dropdown selection) - exclude those with advisory
     */
    public function getAllTeachers() {
        try {
            $query = "SELECT u.user_id, u.name, u.email 
                     FROM {$this->users_table} u
                     LEFT JOIN {$this->advisory_table} a ON u.user_id = a.teacher_id
                     WHERE u.role = 'Teacher' AND a.advisory_id IS NULL
                     ORDER BY u.name ASC";
            
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
     * Get all subject teachers
     */
    public function getSubjectTeachers() {
        try {
            $query = "SELECT 
                        u.user_id as teacher_id,
                        u.name as teacher_name,
                        u.email as teacher_email,
                        tr.role_type,
                        tr.assigned_at
                     FROM {$this->teacher_roles_table} tr
                     INNER JOIN {$this->users_table} u ON tr.teacher_id = u.user_id
                     WHERE tr.role_type = 'subject'
                     ORDER BY tr.assigned_at DESC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Get all students - only unassigned ones
     */
    public function getAllStudents() {
        try {
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
     * Get unassigned students - with their current grade from promotion history
     */
    public function getUnassignedStudents($advisory_id = 0, $grade_level = '') {
        try {
            // Get unassigned students with their current grade from latest promotion record
            // If no promotion record exists, default to Grade 7
            $query = "SELECT 
                        u.user_id, 
                        u.name, 
                        u.lrn,
                        COALESCE(
                            (SELECT sp.to_grade 
                             FROM student_promotions sp 
                             WHERE sp.student_id = u.user_id 
                             ORDER BY sp.promoted_at DESC 
                             LIMIT 1),
                            '7'
                        ) as grade_level
                     FROM {$this->users_table} u
                     WHERE u.role = 'Student' 
                     AND u.user_id NOT IN (
                         SELECT student_id FROM {$this->assignment_table}
                     )
                     ORDER BY u.name ASC";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Get assigned students with filters, search, and sorting
     */
    public function getAssignedStudents($teacher_role = '', $grade_level = '', $date_filter = '', $search = '', $sort_by = 'student_name', $sort_order = 'ASC') {
        try {
            // Validate sort parameters
            $validSortColumns = ['student_name', 'lrn', 'grade_level', 'teacher_name', 'advisory_name', 'assigned_date'];
            $sort_by = in_array($sort_by, $validSortColumns) ? $sort_by : 'student_name';
            $sort_order = strtoupper($sort_order) === 'DESC' ? 'DESC' : 'ASC';
            
            // Map sort column names to actual database columns
            $sortColumnMap = [
                'student_name' => 's.name',
                'lrn' => 's.lrn',
                'grade_level' => 'aa.grade_level',
                'teacher_name' => 't.name',
                'advisory_name' => 'at.advisory_name',
                'assigned_date' => 'aa.assigned_date'
            ];
            
            $orderByColumn = $sortColumnMap[$sort_by] ?? 's.name';
            
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
            
            $query .= " ORDER BY {$orderByColumn} {$sort_order}";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return [];
        }
    }
    
    /**
     * Get advisory list for filter view with sorting
     */
    public function getAdvisoryList($sort_by = 'advisory_name', $sort_order = 'ASC') {
        try {
            // Validate sort parameters
            $validSortColumns = ['advisory_name', 'teacher_name', 'grade_level', 'student_count'];
            $sort_by = in_array($sort_by, $validSortColumns) ? $sort_by : 'advisory_name';
            $sort_order = strtoupper($sort_order) === 'DESC' ? 'DESC' : 'ASC';
            
            // Map sort columns
            $sortColumnMap = [
                'advisory_name' => 'a.advisory_name',
                'teacher_name' => 'u.name',
                'grade_level' => 'a.grade_level',
                'student_count' => 'student_count'
            ];
            
            $orderByColumn = $sortColumnMap[$sort_by] ?? 'a.advisory_name';
            
            $query = "SELECT 
                        a.advisory_id,
                        a.advisory_name,
                        a.grade_level,
                        u.name as teacher_name,
                        COUNT(aa.assignment_id) as student_count
                     FROM {$this->advisory_table} a
                     INNER JOIN {$this->users_table} u ON a.teacher_id = u.user_id
                     LEFT JOIN {$this->assignment_table} aa ON a.advisory_id = aa.advisory_id
                     GROUP BY a.advisory_id, a.advisory_name, a.grade_level, u.name
                     ORDER BY {$orderByColumn} {$sort_order}";
            
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
            
        } catch (PDOException $e) {
            return [];
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