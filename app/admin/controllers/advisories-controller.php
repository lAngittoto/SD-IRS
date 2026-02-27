<?php
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /student-discipline-and-incident-reporting-system/public');
    exit;
}

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
    
    public function uploadProfilePicture() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
            $file = $_FILES['profile_pic'];
            $studentId = intval($_POST['student_id'] ?? 0);
            
            if ($studentId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid student ID.']);
                exit;
            }
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $maxSize = 5 * 1024 * 1024;
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'File upload error.']);
                exit;
            }
            
            if (!in_array($file['type'], $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, WebP allowed.']);
                exit;
            }
            
            if ($file['size'] > $maxSize) {
                echo json_encode(['success' => false, 'message' => 'File too large.']);
                exit;
            }
            
            try {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'student_' . $studentId . '_' . time() . '.' . $ext;
                $filepath = $this->uploadDir . $filename;
                $relativePath = '/student-discipline-and-incident-reporting-system/public/storage/photos/' . $filename;
                
                if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                    echo json_encode(['success' => false, 'message' => 'Save failed.']);
                    exit;
                }
                
                echo json_encode(['success' => true, 'path' => $relativePath]);
                exit;
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                exit;
            }
        }
    }

    public function uploadTeacherProfilePicture() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profile_pic'])) {
            $file = $_FILES['profile_pic'];
            $teacherId = intval($_POST['teacher_id'] ?? 0);
            
            if ($teacherId <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid teacher ID.']);
                exit;
            }
            
            $allowedTypes = ['image/jpeg', 'image/png', 'image/webp'];
            $maxSize = 5 * 1024 * 1024;
            
            if ($file['error'] !== UPLOAD_ERR_OK) {
                echo json_encode(['success' => false, 'message' => 'File upload error.']);
                exit;
            }
            
            if (!in_array($file['type'], $allowedTypes)) {
                echo json_encode(['success' => false, 'message' => 'Only JPG, PNG, WebP allowed.']);
                exit;
            }
            
            if ($file['size'] > $maxSize) {
                echo json_encode(['success' => false, 'message' => 'File too large.']);
                exit;
            }
            
            try {
                $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
                $filename = 'teacher_' . $teacherId . '_' . time() . '.' . $ext;
                $filepath = $this->uploadDir . $filename;
                $relativePath = '/student-discipline-and-incident-reporting-system/public/storage/photos/' . $filename;
                
                if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                    echo json_encode(['success' => false, 'message' => 'Save failed.']);
                    exit;
                }
                
                echo json_encode(['success' => true, 'path' => $relativePath]);
                exit;
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
                exit;
            }
        }
    }
    
    public function assignAdvisoryTeacher() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'teacher_id' => intval($_POST['teacher_id'] ?? 0),
                'role_type' => trim($_POST['role_type'] ?? ''),
                'advisory_name' => trim($_POST['advisory_name'] ?? ''),
                'grade_level' => trim($_POST['grade_level'] ?? '')
            ];
            
            if (empty($data['teacher_id']) || $data['teacher_id'] <= 0) {
                echo json_encode(['success' => false, 'message' => 'Select a teacher.']);
                exit;
            }
            
            if (empty($data['role_type']) || !in_array($data['role_type'], ['subject', 'advisory'])) {
                echo json_encode(['success' => false, 'message' => 'Select a role.']);
                exit;
            }
            
            if ($data['role_type'] === 'advisory') {
                if (empty($data['advisory_name'])) {
                    echo json_encode(['success' => false, 'message' => 'Class name required.']);
                    exit;
                }
                if (empty($data['grade_level']) || !in_array($data['grade_level'], ['7', '8', '9', '10'])) {
                    echo json_encode(['success' => false, 'message' => 'Select grade 7-10.']);
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
                echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
                exit;
            }
            
            $result = $this->advisoriesModel->convertToSubjectTeacher($advisory_id);
            echo json_encode($result);
            exit;
        }
    }
    
    public function assignStudentsToAdvisory() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $advisory_id = intval($_POST['advisory_id'] ?? 0);
            $student_ids = $_POST['student_ids'] ?? [];
            $grade_levels = $_POST['grade_levels'] ?? [];
            
            if ($advisory_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Select advisory.']);
                exit;
            }
            
            if (empty($student_ids) || !is_array($student_ids)) {
                echo json_encode(['success' => false, 'message' => 'Select students.']);
                exit;
            }
            
            $advisoryInfo = $this->advisoriesModel->getAdvisoryById($advisory_id);
            if (!$advisoryInfo) {
                echo json_encode(['success' => false, 'message' => 'Advisory not found.']);
                exit;
            }
            
            $advisoryGrade = $advisoryInfo['grade_level'];
            foreach ($student_ids as $student_id) {
                $studentGrade = $grade_levels[$student_id] ?? '';
                if ($studentGrade !== $advisoryGrade) {
                    echo json_encode(['success' => false, 'message' => "Advisory only accepts Grade {$advisoryGrade}."]);
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
                echo json_encode(['success' => false, 'message' => 'Invalid data.']);
                exit;
            }
            
            if (!in_array($current_grade, ['7', '8', '9', '10'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid grade.']);
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
                echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
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
                echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
                exit;
            }
            
            if (!in_array($new_grade, ['7', '8', '9', '10'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid grade.']);
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
                echo json_encode(['success' => false, 'message' => 'Select students.']);
                exit;
            }
            
            if (!in_array($new_grade, ['7', '8', '9', '10'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid grade.']);
                exit;
            }
            
            $assignment_ids = array_map('intval', $assignment_ids);
            $assignment_ids = array_filter($assignment_ids, function($id) { return $id > 0; });
            
            if (empty($assignment_ids)) {
                echo json_encode(['success' => false, 'message' => 'Invalid.']);
                exit;
            }
            
            $result = $this->advisoriesModel->bulkUpdateStudentGrade($assignment_ids, $new_grade);
            echo json_encode($result);
            exit;
        }
    }

    public function getStudentProfile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $student_id = intval($_POST['student_id'] ?? 0);

            if ($student_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
                exit;
            }

            $profile = $this->advisoriesModel->getStudentProfile($student_id);
            if (!$profile) {
                echo json_encode(['success' => false, 'message' => 'Not found.']);
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
                echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
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
                echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
                exit;
            }

            if (!in_array($new_grade, ['7','8','9','10','11','12'])) {
                echo json_encode(['success' => false, 'message' => 'Invalid grade.']);
                exit;
            }

            $result = $this->advisoriesModel->updateStudentGradeByStudentId($student_id, $new_grade);
            echo json_encode($result);
            exit;
        }
    }

    public function getTeacherProfile() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $advisory_id = intval($_POST['advisory_id'] ?? 0);
            
            error_log("getTeacherProfile called with advisory_id: " . $advisory_id);

            if ($advisory_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
                exit;
            }

            $profile = $this->advisoriesModel->getTeacherProfile($advisory_id);
            
            if (!$profile) {
                error_log("No teacher profile found for advisory_id: " . $advisory_id);
                echo json_encode(['success' => false, 'message' => 'Teacher not found.']);
                exit;
            }

            echo json_encode(['success' => true, 'data' => $profile]);
            exit;
        }
    }

    public function updateTeacherInfo() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $teacher_id = intval($_POST['teacher_id'] ?? 0);

            if ($teacher_id <= 0) {
                echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
                exit;
            }

            $data = [
                'teacher_id_field' => trim($_POST['teacher_id_field'] ?? ''),
                'name'             => trim($_POST['name'] ?? ''),
                'email'            => trim($_POST['email'] ?? ''),
                'contact_no'       => trim($_POST['contact_no'] ?? ''),
                'department'       => trim($_POST['department'] ?? ''),
                'specialization'   => trim($_POST['specialization'] ?? ''),
                'profile_pix'      => trim($_POST['profile_pix'] ?? ''),
            ];

            $result = $this->advisoriesModel->updateTeacherInfo($teacher_id, $data);
            echo json_encode($result);
            exit;
        }
    }
    
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
    
    public function handleAjaxRequest() {
        header('Content-Type: application/json');
        
        $action = $_POST['action'] ?? $_GET['action'] ?? '';
        
        switch ($action) {
            case 'get_filtered_data':
                $filters = ['teacher_role' => $_POST['teacher_role'] ?? '', 'grade_level' => $_POST['grade_level'] ?? '', 'date_filter' => $_POST['date_filter'] ?? '', 'search' => $_POST['search'] ?? '', 'sort_by' => $_POST['sort_by'] ?? 'student_name', 'sort_order' => $_POST['sort_order'] ?? 'ASC'];
                $data = $this->getAssignedStudents($filters);
                echo json_encode(['success' => true, 'data' => $data]);
                break;
                
            case 'get_unassigned_students':
                $students = $this->advisoriesModel->getUnassignedStudents();
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
                $advisories = $this->advisoriesModel->getAdvisoryList($_POST['sort_by'] ?? 'advisory_name', $_POST['sort_order'] ?? 'ASC');
                echo json_encode(['success' => true, 'data' => $advisories]);
                break;
                
            case 'get_advisory_students':
                $students = $this->getStudentsByAdvisory($_POST['advisory_id'] ?? 0);
                echo json_encode(['success' => true, 'data' => $students]);
                break;

            case 'get_student_profile':
                $student_id = intval($_POST['student_id'] ?? 0);
                if ($student_id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
                    break;
                }
                $profile = $this->advisoriesModel->getStudentProfile($student_id);
                if (!$profile) {
                    echo json_encode(['success' => false, 'message' => 'Not found.']);
                    break;
                }
                echo json_encode(['success' => true, 'data' => $profile]);
                break;

            case 'get_teacher_profile':
                $advisory_id = intval($_POST['advisory_id'] ?? 0);
                if ($advisory_id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
                    break;
                }
                $profile = $this->advisoriesModel->getTeacherProfile($advisory_id);
                if (!$profile) {
                    echo json_encode(['success' => false, 'message' => 'Not found.']);
                    break;
                }
                echo json_encode(['success' => true, 'data' => $profile]);
                break;

            case 'update_student_info':
                $student_id = intval($_POST['student_id'] ?? 0);
                if ($student_id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
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

            case 'update_teacher_info':
                $teacher_id = intval($_POST['teacher_id'] ?? 0);
                if ($teacher_id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
                    break;
                }
                $data = [
                    'teacher_id_field' => trim($_POST['teacher_id_field'] ?? ''),
                    'name'             => trim($_POST['name'] ?? ''),
                    'email'            => trim($_POST['email'] ?? ''),
                    'contact_no'       => trim($_POST['contact_no'] ?? ''),
                    'department'       => trim($_POST['department'] ?? ''),
                    'specialization'   => trim($_POST['specialization'] ?? ''),
                    'profile_pix'      => trim($_POST['profile_pix'] ?? ''),
                ];
                $result = $this->advisoriesModel->updateTeacherInfo($teacher_id, $data);
                echo json_encode($result);
                break;

            case 'update_student_grade_by_id':
                $student_id = intval($_POST['student_id'] ?? 0);
                $new_grade  = trim($_POST['new_grade'] ?? '');
                if ($student_id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
                    break;
                }
                if (!in_array($new_grade, ['7','8','9','10','11','12'])) {
                    echo json_encode(['success' => false, 'message' => 'Invalid grade.']);
                    break;
                }
                $result = $this->advisoriesModel->updateStudentGradeByStudentId($student_id, $new_grade);
                echo json_encode($result);
                break;
                
            case 'upload_profile_pic':
                $this->uploadProfilePicture();
                break;

            case 'upload_teacher_profile_pic':
                $this->uploadTeacherProfilePicture();
                break;
                
            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action']);
                break;
        }
        exit;
    }
}

$advisoriesController = new AdvisoriesController($pdo);

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
        case 'get_teacher_profile':
        case 'update_student_info':
        case 'update_teacher_info':
        case 'get_filtered_data':
        case 'get_unassigned_students':
        case 'get_advisory_teachers':
        case 'get_subject_teachers':
        case 'get_advisory_list':
        case 'get_advisory_students':
        case 'upload_profile_pic':
        case 'upload_teacher_profile_pic':
            $advisoriesController->handleAjaxRequest();
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax'])) {
    $advisoriesController->handleAjaxRequest();
}

require_once __DIR__ . '/../views/advisories.php';
?>