<?php
// audit-log-controller.php
// May session guard — para sa admin pages LANG

if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /student-discipline-and-incident-reporting-system/public');
    exit;
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../models/audit-log-model.php';
require_once __DIR__ . '/audit-log-recorder.php';

class AuditLogController {

    private $model;
    private $perPage = 20;

    public function __construct($pdo) {
        $this->model = new AuditLogModel($pdo);
        $this->model->createTable();
    }

    private function currentUser() {
        return $_SESSION['user'] ?? [];
    }

    /* ── Pwedeng tawagin mula sa ibang controller ── */
    public static function record($pdo, $action, $module, $description = '') {
        auditRecord($pdo, $action, $module, $description);
    }

    public function handleAjax() {
        header('Content-Type: application/json');
        $action = $_POST['ajax_action'] ?? '';

        switch ($action) {
            case 'clear_logs':
                $u = $this->currentUser();
                $this->model->log(
                    $u['user_id'] ?? null,
                    $u['name']    ?? 'Admin',
                    $u['role']    ?? 'admin',
                    'CLEAR_LOGS',
                    'Audit Log',
                    'Cleared all audit logs'
                );
                $ok = $this->model->clearLogs();
                echo json_encode([
                    'success' => $ok,
                    'message' => $ok ? 'Logs cleared.' : 'Failed to clear logs.',
                ]);
                break;

            default:
                echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        }
        exit;
    }

    public function index() {
        $page    = max(1, intval($_GET['p'] ?? 1));
        $filters = [
            'module'    => trim($_GET['module']    ?? ''),
            'action'    => trim($_GET['f_action']  ?? ''),
            'user_role' => trim($_GET['user_role'] ?? ''),
            'date_from' => trim($_GET['date_from'] ?? ''),
            'date_to'   => trim($_GET['date_to']   ?? ''),
            'search'    => trim($_GET['search']    ?? ''),
        ];

        $result     = $this->model->getLogs($filters, $page, $this->perPage);
        $stats      = $this->model->getSummaryStats();
        $modules    = $this->model->getModules();
        $actions    = $this->model->getActions();
        $logs       = $result['logs']        ?? [];
        $totalLogs  = $result['total']       ?? 0;
        $totalPages = $result['total_pages'] ?? 1;

        require_once __DIR__ . '/../views/audit-log.php';
    }
}

/* ── Bootstrap ── */
$auditLogController = new AuditLogController($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['ajax_action'])) {
    $auditLogController->handleAjax();
}

$auditLogController->index();