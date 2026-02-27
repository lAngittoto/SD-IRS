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
    private $uploadDir = __DIR__ . '/../../../public/storage/photos/';
    
    public function __construct($pdo) {
        $this->advisoriesModel = new AdvisoriesModel($pdo);
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }
    
    // ============================================
    // PROFILE PICTURE UPLOAD
    // ============================================
    
    public function uploadProfilePicture() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
            $file = $_FILES['profile_pic'];
            $studentId = intval($_POST['student_id'] ?? 0);
            
            if ($studentId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid student ID.']);
                exit;
            }
            
            // Validate file
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $maxSize = 5 * 1024 * 1024; // 5MB
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'File upload error.']);
                exit;
            }
            
            if (!in_array($file['type'], $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, and WebP images are allowed.']);
                exit;
            }
            
            if ($file['size'] > $maxSize) {
                echo json_encode(['success' => false, 'message' => 'File size exceeds 5MB limit.']);
                exit;
            }
            
            try {
                // Generate unique filename
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'student_' . $studentId . '_' . time() . '.' . $ext;
                $filepath = $this->uploadDir . $filename;
                $relativePath = '/student-discipline-and-incident-reporting-system/public/storage/photos/' . $filename;
                
                // Move uploaded file
                if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                    echo json_encode(['success' => false, 'message' => 'Failed to save file.']);
                    exit;
                }
                
                echo json_encode(['success' => true, 'path' => $relativePath, 'filename' => $filename]);
                exit;
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                exit;
            }
        }
    }
    
    // ============================================
    // TEACHER ASSIGNMENT METHODS
    // ============================================
    
    public function assignAdvisoryTeacher() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'teacher_id' => intval($_POST['teacher_id'] ?? 0),
                'role_type' => trim($_POST['role_type'] ?? ''),
                'advisory_name' => trim($_POST['advisory_name'] ?? ''),
                'grade_level' => trim($_POST['grade_level'] ?? '')
            ];
            
            if (empty($data['teacher_id']) || $data['teacher_id'] <= 0) {
                echo json_encode(['success' => false, 'message' => 'Please select a valid teacher.']);
                exit;
            }
            
            if (empty($data['role_type']) || !in_array($data['role_type'], ['subject', 'advisory'])) {
                echo json_encode(['success' => false, 'message' => 'Please select a valid role type.']);
                exit;
            }
            
            if ($data['role_type'] === 'advisory') {
                if (empty($data['advisory_name'])) {
                    echo json_encode(['success' => false, 'message' => 'Advisory class name is required.']);
                    exit;
                }
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
    
    public function assignStudentsToAdvisory() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $advisory_id = intval($_POST['advisory_id'] ?? 0);
            $student_ids = $_POST['student_ids'] ?? [];
            $grade_levels = $_POST['grade_levels'] ?? [];
            
            if ($advisory_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Please select an advisory teacher.']);
                exit;
            }
            
            if (empty($student_ids) || !is_array($student_ids)) {
                echo json_encode(['success' => false, 'message' => 'Please select at least one student.']);
                exit;
            }
            
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
    
    public function bulkUpdateStudentGrade() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $assignment_ids = $_POST['assignment_ids'] ?? [];
            $new_grade = trim($_POST['new_grade'] ?? '');
            
            if (empty($assignment_ids) || !is_array($assignment_ids)) {
                echo json_encode(['success' => false, 'message' => 'Please select at least one student.']);
                exit;
            }
            
            if (!in_array($new_grade, ['7', '8', '9', '10'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid grade level selected.']);
                exit;
            }
            
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
    // STUDENT PROFILE
    // ============================================

    public function getStudentProfile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $student_id = intval($_POST['student_id'] ?? 0);

            if ($student_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid student ID.']);
                exit;
            }

            $profile = $this->advisoriesModel->getStudentProfile($student_id);
            if (!$profile) {
                echo json_encode(['success' => false, 'message' => 'Student not found.']);
                exit;
            }

            echo json_encode(['success' => true, 'data' => $profile]);
            exit;
        }
    }

    public function updateStudentInfo() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $student_id = intval($_POST['student_id'] ?? 0);

            if ($student_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid student ID.']);
                exit;
            }

            $data = [
                'first_name'   => trim($_POST['first_name']   ?? ''),
                'mi'           => trim($_POST['mi']            ?? ''),
                'last_name'    => trim($_POST['last_name']     ?? ''),
                'lrn'          => trim($_POST['lrn']           ?? ''),
                'contact_no'   => trim($_POST['contact_no']    ?? ''),
                'home_address' => trim($_POST['home_address']  ?? ''),
                'profile_pix'  => trim($_POST['profile_pix']   ?? ''),
            ];

            $result = $this->advisoriesModel->updateStudentInfo($student_id, $data);
            echo json_encode($result);
            exit;
        }
    }

    public function updateStudentGradeById() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $student_id = intval($_POST['student_id'] ?? 0);
            $new_grade  = trim($_POST['new_grade'] ?? '');

            if ($student_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid student ID.']);
                exit;
            }

            if (!in_array($new_grade, ['7','8','9','10','11','12'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid grade level.']);
                exit;
            }

            $result = $this->advisoriesModel->updateStudentGradeByStudentId($student_id, $new_grade);
            echo json_encode($result);
            exit;
        }
    }
    
    // ============================================
    // DATA RETRIEVAL METHODS
    // ============================================
    
    public function getAllTeachers() {
        return $this->advisoriesModel->getAllTeachers();
    }
    
    public function getAdvisoryTeachers() {
        return $this->advisoriesModel->getAdvisoryTeachers();
    }
    
    public function getSubjectTeachers() {
        return $this->advisoriesModel->getSubjectTeachers();
    }
    
    public function getAllStudents() {
        return $this->advisoriesModel->getAllStudents();
    }
    
    public function getAssignedStudents($filters = []) {
        $teacher_role = $filters['teacher_role'] ?? '';
        $grade_level = $filters['grade_level'] ?? '';
        $date_filter = $filters['date_filter'] ?? '';
        $search = $filters['search'] ?? '';
        $sort_by = $filters['sort_by'] ?? 'student_name';
        $sort_order = $filters['sort_order'] ?? 'ASC';
        
        return $this->advisoriesModel->getAssignedStudents($teacher_role, $grade_level, $date_filter, $search, $sort_by, $sort_order);
    }
    
    public function getStudentsByAdvisory($advisory_id) {
        return $this->advisoriesModel->getStudentsByAdvisory($advisory_id);
    }
    
    // ============================================
    // AJAX HANDLERS
    // ============================================
    
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

            case 'get_student_profile':
                $student_id = intval($_POST['student_id'] ?? 0);
                if ($student_id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid student ID.']);
                    break;
                }
                $profile = $this->advisoriesModel->getStudentProfile($student_id);
                if (!$profile) {
                    echo json_encode(['success' => false, 'message' => 'Student not found.']);
                    break;
                }
                echo json_encode(['success' => true, 'data' => $profile]);
                break;

            case 'update_student_info':
                $student_id = intval($_POST['student_id'] ?? 0);
                if ($student_id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid student ID.']);
                    break;
                }
                $data = [
                    'first_name'   => trim($_POST['first_name']   ?? ''),
                    'mi'           => trim($_POST['mi']            ?? ''),
                    'last_name'    => trim($_POST['last_name']     ?? ''),
                    'lrn'          => trim($_POST['lrn']           ?? ''),
                    'contact_no'   => trim($_POST['contact_no']    ?? ''),
                    'home_address' => trim($_POST['home_address']  ?? ''),
                    'profile_pix'  => trim($_POST['profile_pix']   ?? ''),
                ];
                $result = $this->advisoriesModel->updateStudentInfo($student_id, $data);
                echo json_encode($result);
                break;

            case 'update_student_grade_by_id':
                $student_id = intval($_POST['student_id'] ?? 0);
                $new_grade  = trim($_POST['new_grade'] ?? '');
                if ($student_id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid student ID.']);
                    break;
                }
                if (!in_array($new_grade, ['7','8','9','10','11','12'])) {
                    echo json_encode(['success' => false, 'message' => 'Invalid grade level.']);
                    break;
                }
                $result = $this->advisoriesModel->updateStudentGradeByStudentId($student_id, $new_grade);
                echo json_encode($result);
                break;
                
            case 'upload_profile_pic':
                $this->uploadProfilePicture();
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

        case 'update_student_grade_by_id':
            $advisoriesController->updateStudentGradeById();
            break;

        case 'get_student_profile':
        case 'update_student_info':
        case 'get_filtered_data':
        case 'get_unassigned_students':
        case 'get_advisory_teachers':
        case 'get_subject_teachers':
        case 'get_advisory_list':
        case 'get_advisory_students':
        case 'upload_profile_pic':
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