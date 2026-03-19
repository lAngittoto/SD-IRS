<?php
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Teacher') {
    header('Location: /student-discipline-and-incident-reporting-system/public');
    exit;
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../models/teacher-advisory-model.php';

class TeacherAdvisoryController {
    private $model;
    private $teacherId;

    public function __construct($pdo) {
        $this->model     = new TeacherAdvisoryModel($pdo);
        $this->teacherId = (int)$_SESSION['user']['user_id'];
    }

    public function handleAjax() {
        header('Content-Type: application/json');
        $action = $_POST['action'] ?? '';

        switch ($action) {
            case 'get_student_incidents':
                $studentId = intval($_POST['student_id'] ?? 0);
                if ($studentId <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid student.']);
                    break;
                }
                $data = $this->model->getStudentIncidents($studentId);
                echo json_encode(['success' => true, 'data' => $data]);
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        }
        exit;
    }

    public function index() {
        // Handle AJAX
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handleAjax();
        }

        $advisoryClass   = $this->model->getAdvisoryClass($this->teacherId);
        $assignedStudents = [];

        if ($advisoryClass) {
            $assignedStudents = $this->model->getAssignedStudents($advisoryClass['advisory_id']);
        }

        // Pass search filter
        $searchQuery = trim($_GET['search'] ?? '');

        require_once __DIR__ . '/../views/teacher-advisory.php';
    }
}

$controller = new TeacherAdvisoryController($pdo);
$controller->index();
?>