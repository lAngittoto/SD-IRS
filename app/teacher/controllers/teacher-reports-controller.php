<?php
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Teacher') {
    header('Location: /student-discipline-and-incident-reporting-system/public');
    exit;
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../models/teacher-reports-model.php';

class TeacherReportController {
    private $model;
    private $uploadDir = __DIR__ . '/../../../public/storage/evidence/';
    private $allowedTypes = ['image/jpeg','image/png','image/webp','video/mp4','video/webm','audio/mpeg','audio/ogg','audio/wav'];
    private $maxFileSize = 20971520; // 20MB

    public function __construct($pdo) {
        $this->model = new TeacherReportModel($pdo);
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0755, true);
        }
    }

    private function validateFile($file) {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['valid' => false, 'message' => 'File upload error.'];
        }
        if (!in_array($file['type'], $this->allowedTypes)) {
            return ['valid' => false, 'message' => 'Only JPG, PNG, WebP, MP4, WebM, MP3, OGG, WAV allowed.'];
        }
        if ($file['size'] > $this->maxFileSize) {
            return ['valid' => false, 'message' => 'File too large (max 20MB).'];
        }
        return ['valid' => true];
    }

    private function getEvidenceType($mime) {
        if (str_starts_with($mime, 'image/')) return 'image';
        if (str_starts_with($mime, 'video/')) return 'video';
        if (str_starts_with($mime, 'audio/')) return 'audio';
        return 'file';
    }

    public function handleAjaxRequest() {
        header('Content-Type: application/json');
        $action = $_POST['action'] ?? $_GET['action'] ?? '';

        switch ($action) {
            case 'get_students_by_grade':
                $grade = trim($_POST['grade_level'] ?? '');
                if (!in_array($grade, ['7','8','9','10','11','12'])) {
                    echo json_encode(['success' => false, 'message' => 'Invalid grade.']);
                    break;
                }
                $data = $this->model->getStudentsByGrade($grade);
                echo json_encode(['success' => true, 'data' => $data]);
                break;

            case 'get_teachers':
                $exclude = intval($_SESSION['user']['user_id'] ?? 0);
                $data = $this->model->getAllTeachers($exclude);
                echo json_encode(['success' => true, 'data' => $data]);
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action.']);
                break;
        }
        exit;
    }

    public function submitReport() {
        header('Content-Type: application/json');

        $teacher_id    = intval($_SESSION['user']['user_id'] ?? 0);
        $report_target = trim($_POST['report_target'] ?? '');
        $location      = trim($_POST['location']      ?? '');
        $description   = trim($_POST['description']   ?? '');
        $violation_id  = intval($_POST['violation_id'] ?? 0) ?: null;
        $custom_violation = trim($_POST['custom_violation'] ?? '');

        // --- Validations ---
        if (!in_array($report_target, ['student','teacher','other'])) {
            echo json_encode(['success' => false, 'message' => 'Select who you are reporting.']); exit;
        }
        if (empty($location)) {
            echo json_encode(['success' => false, 'message' => 'Location is required.']); exit;
        }
        if (empty($description)) {
            echo json_encode(['success' => false, 'message' => 'Description is required.']); exit;
        }
        if (empty($violation_id) && empty($custom_violation)) {
            echo json_encode(['success' => false, 'message' => 'Select or enter a violation type.']); exit;
        }

        $student_id          = null;
        $teacher_involved_id = null;
        $other_name          = null;
        $grade_level         = null;

        if ($report_target === 'student') {
            $grade_level = trim($_POST['grade_level'] ?? '');
            $student_id  = intval($_POST['student_id'] ?? 0) ?: null;
            if (!in_array($grade_level, ['7','8','9','10','11','12'])) {
                echo json_encode(['success' => false, 'message' => 'Select the student\'s grade level.']); exit;
            }
            if (!$student_id) {
                echo json_encode(['success' => false, 'message' => 'Select the student involved.']); exit;
            }
        } elseif ($report_target === 'teacher') {
            $teacher_involved_id = intval($_POST['teacher_involved_id'] ?? 0) ?: null;
            if (!$teacher_involved_id) {
                echo json_encode(['success' => false, 'message' => 'Select the teacher involved.']); exit;
            }
            if ($teacher_involved_id === $teacher_id) {
                echo json_encode(['success' => false, 'message' => 'You cannot report yourself.']); exit;
            }
        } elseif ($report_target === 'other') {
            $other_name = trim($_POST['other_name'] ?? '');
            if (empty($other_name)) {
                echo json_encode(['success' => false, 'message' => 'Enter the name of the person involved.']); exit;
            }
        }

        // --- Handle file upload ---
        $evidence_path = null;
        $evidence_type = null;

        if (isset($_FILES['evidence']) && $_FILES['evidence']['error'] !== UPLOAD_ERR_NO_FILE) {
            $file       = $_FILES['evidence'];
            $validation = $this->validateFile($file);
            if (!$validation['valid']) {
                echo json_encode(['success' => false, 'message' => $validation['message']]); exit;
            }

            $ext           = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $filename      = 'evidence_' . $teacher_id . '_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
            $filepath      = $this->uploadDir . $filename;
            $evidence_type = $this->getEvidenceType($file['type']);

            if (!move_uploaded_file($file['tmp_name'], $filepath)) {
                echo json_encode(['success' => false, 'message' => 'Could not save evidence file.']); exit;
            }

            chmod($filepath, 0644);
            $evidence_path = '/student-discipline-and-incident-reporting-system/public/storage/evidence/' . $filename;
        }

        $result = $this->model->submitReport([
            'teacher_id'          => $teacher_id,
            'report_target'       => $report_target,
            'student_id'          => $student_id,
            'teacher_involved_id' => $teacher_involved_id,
            'other_name'          => $other_name,
            'grade_level'         => $grade_level,
            'location'            => $location,
            'violation_id'        => $violation_id,
            'custom_violation'    => $custom_violation,
            'description'         => $description,
            'evidence_path'       => $evidence_path,
            'evidence_type'       => $evidence_type,
        ]);

        echo json_encode($result);
        exit;
    }

    public function getViolations()   { return $this->model->getViolations(); }
    public function getMyReports()    { return $this->model->getReportsByTeacher($_SESSION['user']['user_id']); }
}

$teacherReportController = new TeacherReportController($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    switch ($action) {
        case 'submit_report':
            $teacherReportController->submitReport();
            break;
        case 'get_students_by_grade':
        case 'get_teachers':
            $teacherReportController->handleAjaxRequest();
            break;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['ajax'])) {
    $teacherReportController->handleAjaxRequest();
}

require_once __DIR__ . '/../views/teacher-reports.php';