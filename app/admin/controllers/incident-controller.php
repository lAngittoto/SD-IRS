<?php
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /student-discipline-and-incident-reporting-system/public');
    exit;
}
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../models/incident-model.php';

class IncidentController {
    private $model;

    public function __construct($pdo) {
        $this->model = new IncidentModel($pdo);
    }

    /* ─── AJAX dispatcher ─── */
    public function handleAjax() {
        header('Content-Type: application/json');
        $action = $_POST['action'] ?? $_GET['action'] ?? '';

        switch ($action) {
            case 'get_reports':
                $filters = [
                    'role'         => trim($_POST['role']         ?? ''),
                    'status'       => trim($_POST['status']       ?? ''),
                    'violation_id' => trim($_POST['violation_id'] ?? ''),
                    'search'       => trim($_POST['search']       ?? ''),
                ];
                $data = $this->model->getAllReports($filters);
                echo json_encode(['success' => true, 'data' => $data, 'count' => count($data)]);
                break;

            case 'get_report_detail':
                $id = intval($_POST['report_id'] ?? 0);
                if ($id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
                    break;
                }
                $row = $this->model->getReportById($id);
                if ($row) {
                    echo json_encode(['success' => true, 'data' => $row]);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Report not found.']);
                }
                break;

            case 'update_status':
                $id     = intval($_POST['report_id'] ?? 0);
                $status = trim($_POST['status']      ?? '');
                $notes  = trim($_POST['admin_notes'] ?? '');

                if ($id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
                    break;
                }

                // Only allow pending → reviewed → resolved; no dismissed, no reset
                $allowed = ['reviewed', 'resolved'];
                if (!in_array($status, $allowed)) {
                    echo json_encode(['success' => false, 'message' => 'Invalid status update.']);
                    break;
                }

                // Block any update if report is already resolved
                $current = $this->model->getReportById($id);
                if (!$current) {
                    echo json_encode(['success' => false, 'message' => 'Report not found.']);
                    break;
                }
                if ($current['status'] === 'resolved') {
                    echo json_encode(['success' => false, 'message' => 'This case is already resolved and cannot be modified.']);
                    break;
                }

                $result = $this->model->updateStatus($id, $status, $_SESSION['user']['user_id'], $notes);
                echo json_encode($result);
                break;

            case 'delete_report':
                $id = intval($_POST['report_id'] ?? 0);
                if ($id <= 0) {
                    echo json_encode(['success' => false, 'message' => 'Invalid ID.']);
                    break;
                }
                $result = $this->model->deleteReport($id);
                echo json_encode($result);
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Unknown action.']);
        }
        exit;
    }

    public function getInitialReports()  { return $this->model->getAllReports(); }
    public function getViolationFilters(){ return $this->model->getViolationsForFilter(); }
    public function getSummaryStats()    { return $this->model->getSummaryStats(); }
}

$incidentController = new IncidentController($pdo);

// Handle POST / AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['ajax'])) {
    $action = $_POST['action'] ?? $_GET['action'] ?? '';
    if (in_array($action, ['get_reports', 'get_report_detail', 'update_status', 'delete_report'])) {
        $incidentController->handleAjax();
    }
}

require_once __DIR__ . '/../views/incident.php';