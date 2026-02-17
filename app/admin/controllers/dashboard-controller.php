<?php
// Only admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /student-discipline-and-incident-reporting-system/public');
    exit;
}

// Include necessary files
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../models/dashboard-model.php';

class DashboardController {
    private $dashboardModel;
    
    public function __construct() {
        global $pdo;
        $this->dashboardModel = new DashboardModel($pdo);
    }
    
    public function index() {
        // Get dashboard summary
        $summary = $this->dashboardModel->getDashboardSummary();
        
        // Extract and pass to view
        $totalStudents = $summary['total_students'];
        $totalFaculty = $summary['total_faculty'];
        
        // Get all violations
        $allViolations = $this->dashboardModel->getAllViolationNames();
        
        // Get top violations (most common)
        $topViolations = $this->dashboardModel->getTopViolations();
        
        // Get monthly trends (if you have incident data)
        $monthlyTrends = $this->dashboardModel->getMonthlyTrends();
        
        // Get violation breakdown by severity
        $violationBreakdown = $this->dashboardModel->getViolationBreakdown();
        
        // Get incident statistics (if you have incident table)
        $totalIncidents = $this->dashboardModel->getTotalIncidents();
        $pendingReports = $this->dashboardModel->getPendingReports();
        $resolvedCases = $this->dashboardModel->getResolvedCases();
        
        // Include the view
        require_once __DIR__ . '/../views/dashboard.php';
    }
    
    public function getStatistics() {
        header('Content-Type: application/json');
        echo json_encode($this->dashboardModel->getDashboardSummary());
    }
}

// Initialize controller
$controller = new DashboardController();
$controller->index();
?>