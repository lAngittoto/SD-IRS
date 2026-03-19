<?php
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Teacher') {
    header('Location: /student-discipline-and-incident-reporting-system/public');
    exit;
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../models/teacher-dashboard-model.php';

class TeacherDashboardController {
    private $model;
    private $teacherId;

    public function __construct($pdo) {
        $this->model     = new TeacherDashboardModel($pdo);
        $this->teacherId = (int)$_SESSION['user']['user_id'];
    }

    public function index() {
        $teacher       = $_SESSION['user'];
        $schoolYear    = $this->model->getActiveSchoolYear();
        $stats         = $this->model->getSummaryStats($this->teacherId);

        $recentReports = $this->model->getMyRecentReports($this->teacherId, 6);
        $monthlyTrend  = $this->model->getMyMonthlyTrend($this->teacherId);

        $advisoryStudents = [];
        if ($stats['advisory_id']) {
            $advisoryStudents = $this->model->getAdvisoryStudentSummary($stats['advisory_id'], 5);
        }

        require_once __DIR__ . '/../views/teacher-dasboard.php';
    }
}

$controller = new TeacherDashboardController($pdo);
$controller->index();
?>