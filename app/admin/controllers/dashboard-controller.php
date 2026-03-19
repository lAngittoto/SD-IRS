<?php
// Only admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /student-discipline-and-incident-reporting-system/public');
    exit;
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../models/dashboard-model.php';

class DashboardController {
    private $model;

    public function __construct($pdo) {
        $this->model = new DashboardModel($pdo);
    }

    public function index() {
        /* ── Summary stats ── */
        $summary         = $this->model->getDashboardSummary();
        $totalStudents   = $summary['total_students'];
        $totalFaculty    = $summary['total_faculty'];
        $totalIncidents  = $summary['total_incidents'];
        $pendingReports  = $summary['pending_reports'];
        $reviewedReports = $summary['reviewed_reports'];
        $resolvedCases   = $summary['resolved_cases'];

        /* ── Charts & lists ── */
        $monthlyTrends      = $this->model->getMonthlyTrends();
        $statusBreakdown    = $this->model->getStatusBreakdown();
        $allViolations      = $this->model->getAllViolationNames();
        $topViolations      = $this->model->getTopViolations(5);
        $recentIncidents    = $this->model->getRecentIncidents(5);

        require_once __DIR__ . '/../views/dashboard.php';
    }
}

$controller = new DashboardController($pdo);
$controller->index();
?>