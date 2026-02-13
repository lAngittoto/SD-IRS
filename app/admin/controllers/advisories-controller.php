<?php
// 1. Check Admin Access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /student-discipline-and-incident-reporting-system/public');
    exit;
}

// 2. Load dependencies
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../models/advisories-model.php';

class AdvisoriesController {
    private $advisoriesModel;
    
    public function __construct($pdo) {
        $this->advisoriesModel = new AdvisoriesModel($pdo);
    }
    
    // ============================================
    // TEACHER ASSIGNMENT METHODS
    // ============================================
    
    /**
     * Assign a teacher as advisory teacher
     */
    public function assignAdvisoryTeacher() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'teacher_id' => intval($_POST['teacher_id'] ?? 0),
                'role_type' => trim($_POST['role_type'] ?? ''),
                'advisory_name' => trim($_POST['advisory_name'] ?? ''),
                'grade_level' => trim($_POST['grade_level'] ?? '')
            ];
            
            // Validation
            if (empty($data['teacher_id']) || $data['teacher_id'] <= 0) {
                echo json_encode(['success' => false, 'message' => 'Please select a valid teacher.']);
                exit;
            }
            
            if (empty($data['role_type']) || !in_array($data['role_type'], ['subject', 'advisory'])) {
                echo json_encode(['success' => false, 'message' => 'Please select a valid role type.']);
                exit;
            }
            
            // Additional validation for advisory teachers
            if ($data['role_type'] === 'advisory') {
                if (empty($data['advisory_name'])) {
                    echo json_encode(['success' => false, 'message' => 'Advisory class name is required.']);
                    exit;
                }
                
                // NEW: Validate grade level is selected
                if (empty($data['grade_level']) || !in_array($data['grade_level'], ['7', '8', '9', '10'])) {
                    echo json_encode(['success' => false, 'message' => 'Please select a valid grade level (7-10).']);
                    exit;
                }
            }
            
            $result = $this->advisoriesModel->assignAdvisoryTeacher($data);
            echo json_encode($result);
            exit;
        }
    }
    
    /**
     * Convert advisory teacher to subject teacher
     */
    public function convertToSubjectTeacher() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $advisory_id = intval($_POST['advisory_id'] ?? 0);
            
            if ($advisory_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid advisory ID.']);
                exit;
            }
            
            $result = $this->advisoriesModel->convertToSubjectTeacher($advisory_id);
            echo json_encode($result);
            exit;
        }
    }
    
    // ============================================
    // STUDENT ASSIGNMENT METHODS
    // ============================================
    
    /**
     * Assign students to advisory teacher
     */
    public function assignStudentsToAdvisory() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $advisory_id = intval($_POST['advisory_id'] ?? 0);
            $student_ids = $_POST['student_ids'] ?? [];
            $grade_levels = $_POST['grade_levels'] ?? [];
            
            // Validation
            if ($advisory_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Please select an advisory teacher.']);
                exit;
            }
            
            if (empty($student_ids) || !is_array($student_ids)) {
                echo json_encode(['success' => false, 'message' => 'Please select at least one student.']);
                exit;
            }
            
            // NEW: Validate grade level match with advisory
            $advisoryInfo = $this->advisoriesModel->getAdvisoryById($advisory_id);
            if (!$advisoryInfo) {
                echo json_encode(['success' => false, 'message' => 'Advisory class not found.']);
                exit;
            }
            
            $advisoryGrade = $advisoryInfo['grade_level'];
            foreach ($student_ids as $student_id) {
                $studentGrade = $grade_levels[$student_id] ?? '';
                if ($studentGrade !== $advisoryGrade) {
                    echo json_encode([
                        'success' => false, 
                        'message' => "Cannot assign students. This advisory class only accepts Grade {$advisoryGrade} students. Please select students of the correct grade level."
                    ]);
                    exit;
                }
            }
            
            $result = $this->advisoriesModel->assignStudentsToAdvisory($advisory_id, $student_ids, $grade_levels);
            echo json_encode($result);
            exit;
        }
    }
    
    /**
     * Reassign student to different advisory
     */
    public function reassignStudent() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $assignment_id = intval($_POST['assignment_id'] ?? 0);
            $new_advisory_id = intval($_POST['new_advisory_id'] ?? 0);
            $current_grade = $_POST['current_grade'] ?? '';
            
            if ($assignment_id <= 0 || $new_advisory_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid data provided.']);
                exit;
            }
            
            if (!in_array($current_grade, ['7', '8', '9', '10'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid grade level.']);
                exit;
            }
            
            // NEW: Validate grade match
            $advisoryInfo = $this->advisoriesModel->getAdvisoryById($new_advisory_id);
            if ($advisoryInfo && $advisoryInfo['grade_level'] !== $current_grade) {
                echo json_encode([
                    'success' => false, 
                    'message' => "Cannot reassign. Student is Grade {$current_grade} but selected advisory is for Grade {$advisoryInfo['grade_level']}."
                ]);
                exit;
            }
            
            $result = $this->advisoriesModel->reassignStudent($assignment_id, $new_advisory_id, $current_grade);
            echo json_encode($result);
            exit;
        }
    }
    
    /**
     * Remove student from advisory
     */
    public function removeFromAdvisory() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $assignment_id = intval($_POST['assignment_id'] ?? 0);
            
            if ($assignment_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid assignment ID.']);
                exit;
            }
            
            $result = $this->advisoriesModel->removeFromAdvisory($assignment_id);
            echo json_encode($result);
            exit;
        }
    }
    
    /**
     * NEW: Update student grade level
     */
    public function updateStudentGrade() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $assignment_id = intval($_POST['assignment_id'] ?? 0);
            $new_grade = trim($_POST['new_grade'] ?? '');
            
            if ($assignment_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid assignment ID.']);
                exit;
            }
            
            if (!in_array($new_grade, ['7', '8', '9', '10'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid grade level.']);
                exit;
            }
            
            $result = $this->advisoriesModel->updateStudentGrade($assignment_id, $new_grade);
            echo json_encode($result);
            exit;
        }
    }
    
    /**
     * NEW: Bulk update student grades (for grade promotion)
     */
    public function bulkUpdateStudentGrade() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $assignment_ids = $_POST['assignment_ids'] ?? [];
            $new_grade = trim($_POST['new_grade'] ?? '');
            
            // Validation
            if (empty($assignment_ids) || !is_array($assignment_ids)) {
                echo json_encode(['success' => false, 'message' => 'Please select at least one student.']);
                exit;
            }
            
            if (!in_array($new_grade, ['7', '8', '9', '10'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid grade level selected.']);
                exit;
            }
            
            // Sanitize assignment IDs
            $assignment_ids = array_map('intval', $assignment_ids);
            $assignment_ids = array_filter($assignment_ids, function($id) {
                return $id > 0;
            });
            
            if (empty($assignment_ids)) {
                echo json_encode(['success' => false, 'message' => 'Invalid student selection.']);
                exit;
            }
            
            $result = $this->advisoriesModel->bulkUpdateStudentGrade($assignment_ids, $new_grade);
            echo json_encode($result);
            exit;
        }
    }
    
    // ============================================
    // DATA RETRIEVAL METHODS
    // ============================================
    
    /**
     * Get all available teachers (for dropdown)
     */
    public function getAllTeachers() {
        return $this->advisoriesModel->getAllTeachers();
    }
    
    /**
     * Get all advisory teachers
     */
    public function getAdvisoryTeachers() {
        return $this->advisoriesModel->getAdvisoryTeachers();
    }
    
    /**
     * Get all subject teachers
     */
    public function getSubjectTeachers() {
        return $this->advisoriesModel->getSubjectTeachers();
    }
    
    /**
     * Get all students (for assignment modal)
     */
    public function getAllStudents() {
        return $this->advisoriesModel->getAllStudents();
    }
    
    /**
     * Get assigned students with filters
     */
    public function getAssignedStudents($filters = []) {
        $teacher_role = $filters['teacher_role'] ?? '';
        $grade_level = $filters['grade_level'] ?? '';
        $date_filter = $filters['date_filter'] ?? '';
        $search = $filters['search'] ?? '';
        $sort_by = $filters['sort_by'] ?? 'student_name';
        $sort_order = $filters['sort_order'] ?? 'ASC';
        
        return $this->advisoriesModel->getAssignedStudents($teacher_role, $grade_level, $date_filter, $search, $sort_by, $sort_order);
    }
    
    /**
     * Get students by advisory ID
     */
    public function getStudentsByAdvisory($advisory_id) {
        return $this->advisoriesModel->getStudentsByAdvisory($advisory_id);
    }
    
    // ============================================
    // AJAX HANDLERS
    // ============================================
    
    /**
     * Handle AJAX requests
     */
    public function handleAjaxRequest() {
        header('Content-Type: application/json');
        
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        
        switch ($action) {
            case 'get_filtered_data':
                $filters = [
                    'teacher_role' => $_POST['teacher_role'] ?? '',
                    'grade_level' => $_POST['grade_level'] ?? '',
                    'date_filter' => $_POST['date_filter'] ?? '',
                    'search' => $_POST['search'] ?? '',
                    'sort_by' => $_POST['sort_by'] ?? 'student_name',
                    'sort_order' => $_POST['sort_order'] ?? 'ASC'
                ];
                
                $data = $this->getAssignedStudents($filters);
                echo json_encode(['success' => true, 'data' => $data]);
                break;
                
            case 'get_unassigned_students':
                $advisory_id = $_POST['advisory_id'] ?? 0;
                $grade_level = $_POST['grade_level'] ?? '';
                $students = $this->advisoriesModel->getUnassignedStudents($advisory_id, $grade_level);
                echo json_encode(['success' => true, 'data' => $students]);
                break;
                
            case 'get_advisory_teachers':
                $teachers = $this->getAdvisoryTeachers();
                echo json_encode(['success' => true, 'data' => $teachers]);
                break;
                
            case 'get_subject_teachers':
                $teachers = $this->getSubjectTeachers();
                echo json_encode(['success' => true, 'data' => $teachers]);
                break;
                
            case 'get_advisory_list':
                $sort_by = $_POST['sort_by'] ?? 'advisory_name';
                $sort_order = $_POST['sort_order'] ?? 'ASC';
                $advisories = $this->advisoriesModel->getAdvisoryList($sort_by, $sort_order);
                echo json_encode(['success' => true, 'data' => $advisories]);
                break;
                
            case 'get_advisory_students':
                $advisory_id = $_POST['advisory_id'] ?? 0;
                $students = $this->getStudentsByAdvisory($advisory_id);
                echo json_encode(['success' => true, 'data' => $students]);
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
                break;
        }
        exit;
    }
}

// 3. Instantiate the controller
$advisoriesController = new AdvisoriesController($pdo);

// 4. Handle POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    
    switch ($action) {
        case 'assign_teacher':
            $advisoriesController->assignAdvisoryTeacher();
            break;
            
        case 'assign_students':
            $advisoriesController->assignStudentsToAdvisory();
            break;
            
        case 'reassign_student':
            $advisoriesController->reassignStudent();
            break;
            
        case 'remove_from_advisory':
            $advisoriesController->removeFromAdvisory();
            break;
            
        case 'convert_to_subject':
            $advisoriesController->convertToSubjectTeacher();
            break;
            
        case 'update_student_grade':
            $advisoriesController->updateStudentGrade();
            break;
            
        case 'bulk_update_student_grade':
            $advisoriesController->bulkUpdateStudentGrade();
            break;
            
        case 'get_filtered_data':
        case 'get_unassigned_students':
        case 'get_advisory_teachers':
        case 'get_subject_teachers':
        case 'get_advisory_list':
        case 'get_advisory_students':
            $advisoriesController->handleAjaxRequest();
            break;
    }
}

// 5. Handle AJAX GET requests
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax'])) {
    $advisoriesController->handleAjaxRequest();
}

// 6. Load the View
require_once __DIR__ . '/../views/advisories.php';
?>