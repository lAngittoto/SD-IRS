<?php
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /student-discipline-and-incident-reporting-system/public');
    exit;
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../models/reports-model.php';

class ReportsController {
    private $model;

    public function __construct($pdo) {
        $this->model = new ReportsModel($pdo);
    }

    public function index() {
        /* ── Filter params from GET — strict validation ── */
        $rawYear  = trim($_GET['year']  ?? '');
        $rawMonth = trim($_GET['month'] ?? '');

        // Year must be numeric and within a sane range
        $filterYear  = ($rawYear !== '' && ctype_digit($rawYear) && (int)$rawYear >= 2020 && (int)$rawYear <= 2099)
                       ? (int)$rawYear : null;

        // Month must be 1-12
        $filterMonth = ($rawMonth !== '' && ctype_digit($rawMonth) && (int)$rawMonth >= 1 && (int)$rawMonth <= 12)
                       ? (int)$rawMonth : null;

        // Month only makes sense when year is also set
        if ($filterMonth !== null && $filterYear === null) {
            $filterYear = (int)date('Y'); // default to current year if only month given
        }

        /* ── Summary stats ── */
        $stats              = $this->model->getSummaryStats();
        $totalStudents      = $stats['total_students'];
        $totalFaculty       = $stats['total_faculty'];
        $totalIncidents     = $stats['total_incidents'];
        $pendingReports     = $stats['pending_reports'];
        $reviewedReports    = $stats['reviewed_reports'];
        $resolvedCases      = $stats['resolved_cases'];
        $assignedStudents   = $stats['assigned_students'];
        $unassignedStudents = $stats['unassigned_students'];
        $activeAdvisories   = $stats['active_advisories'];

        /* ── Charts & tables ── */
        $monthlyTrends    = $this->model->getMonthlyTrends($filterYear, $filterMonth);
        $statusBreakdown  = $this->model->getStatusBreakdown();
        $recentResolutions= $this->model->getRecentResolutions(10);
        $topViolations    = $this->model->getTopViolations(5);
        $advisorySummary  = $this->model->getAdvisorySummary();
        $incidentsByGrade = $this->model->getIncidentsByGrade();
        $allViolations    = $this->model->getAllViolations();

        require_once __DIR__ . '/../views/reports.php';
    }
}

$controller = new ReportsController($pdo);
$controller->index();
?>