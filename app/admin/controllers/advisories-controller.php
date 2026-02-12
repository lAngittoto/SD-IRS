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
                $_SESSION['error_message'] = 'Please select a valid teacher.';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            
            if (empty($data['role_type']) || !in_array($data['role_type'], ['subject', 'advisory'])) {
                $_SESSION['error_message'] = 'Please select a valid role type.';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            
            // Additional validation for advisory teachers
            if ($data['role_type'] === 'advisory') {
                if (empty($data['advisory_name'])) {
                    $_SESSION['error_message'] = 'Advisory class name is required.';
                    header('Location: ' . $_SERVER['HTTP_REFERER']);
                    exit;
                }
                
                if (empty($data['grade_level']) || !in_array($data['grade_level'], ['7', '8', '9', '10'])) {
                    $_SESSION['error_message'] = 'Please select a valid grade level.';
                    header('Location: ' . $_SERVER['HTTP_REFERER']);
                    exit;
                }
            }
            
            $result = $this->advisoriesModel->assignAdvisoryTeacher($data);
            $_SESSION[$result['success'] ? 'success_message' : 'error_message'] = $result['message'];
            
            header('Location: ' . $_SERVER['HTTP_REFERER']);
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
                $_SESSION['error_message'] = 'Invalid advisory ID.';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            
            $result = $this->advisoriesModel->convertToSubjectTeacher($advisory_id);
            $_SESSION[$result['success'] ? 'success_message' : 'error_message'] = $result['message'];
            
            header('Location: ' . $_SERVER['HTTP_REFERER']);
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
                $_SESSION['error_message'] = 'Please select an advisory teacher.';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            
            if (empty($student_ids) || !is_array($student_ids)) {
                $_SESSION['error_message'] = 'Please select at least one student.';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            
            $result = $this->advisoriesModel->assignStudentsToAdvisory($advisory_id, $student_ids, $grade_levels);
            $_SESSION[$result['success'] ? 'success_message' : 'error_message'] = $result['message'];
            
            header('Location: ' . $_SERVER['HTTP_REFERER']);
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
            $grade_level = intval($_POST['grade_level'] ?? 0);
            
            if ($assignment_id <= 0 || $new_advisory_id <= 0) {
                $_SESSION['error_message'] = 'Invalid data provided.';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            
            if ($grade_level < 7 || $grade_level > 10) {
                $_SESSION['error_message'] = 'Invalid grade level.';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            
            $result = $this->advisoriesModel->reassignStudent($assignment_id, $new_advisory_id, $grade_level);
            $_SESSION[$result['success'] ? 'success_message' : 'error_message'] = $result['message'];
            
            header('Location: ' . $_SERVER['HTTP_REFERER']);
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
                $_SESSION['error_message'] = 'Invalid assignment ID.';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            
            $result = $this->advisoriesModel->removeFromAdvisory($assignment_id);
            $_SESSION[$result['success'] ? 'success_message' : 'error_message'] = $result['message'];
            
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }
    
    /**
     * Update student grade level
     */
    public function updateStudentGrade() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $assignment_id = intval($_POST['assignment_id'] ?? 0);
            $grade_level = intval($_POST['grade_level'] ?? 0);
            
            if ($assignment_id <= 0 || $grade_level < 7 || $grade_level > 10) {
                $_SESSION['error_message'] = 'Invalid data provided.';
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit;
            }
            
            $result = $this->advisoriesModel->updateStudentGrade($assignment_id, $grade_level);
            $_SESSION[$result['success'] ? 'success_message' : 'error_message'] = $result['message'];
            
            header('Location: ' . $_SERVER['HTTP_REFERER']);
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
        
        return $this->advisoriesModel->getAssignedStudents($teacher_role, $grade_level, $date_filter, $search);
    }
    
    /**
     * Get unassigned students
     */
    public function getUnassignedStudents() {
        return $this->advisoriesModel->getUnassignedStudents();
    }
    
    /**
     * Get advisory details by ID
     */
    public function getAdvisoryDetails($advisory_id) {
        return $this->advisoriesModel->getAdvisoryDetails($advisory_id);
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
                    'search' => $_POST['search'] ?? ''
                ];
                
                $data = $this->getAssignedStudents($filters);
                echo json_encode(['success' => true, 'data' => $data]);
                break;
                
            case 'get_unassigned_students':
                $students = $this->getUnassignedStudents();
                echo json_encode(['success' => true, 'data' => $students]);
                break;
                
            case 'get_advisory_teachers':
                $teachers = $this->getAdvisoryTeachers();
                echo json_encode(['success' => true, 'data' => $teachers]);
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
            
        case 'update_grade':
            $advisoriesController->updateStudentGrade();
            break;
            
        case 'convert_to_subject':
            $advisoriesController->convertToSubjectTeacher();
            break;
            
        case 'get_filtered_data':
        case 'get_unassigned_students':
        case 'get_advisory_teachers':
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