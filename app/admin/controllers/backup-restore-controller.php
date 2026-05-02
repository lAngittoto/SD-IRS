<?php
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /student-discipline-and-incident-reporting-system/public');
    exit;
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../models/backup-restore-model.php';

class BackupRestoreController {
    private BackupRestoreModel $model;
    private $pdo;

    public function __construct($pdo) {
        $this->pdo   = $pdo;
        $this->model = new BackupRestoreModel($pdo);
    }

    /* ── Router ── */
    public function handle(): void {
        $action = $_GET['action'] ?? 'index';

        match ($action) {
            'download' => $this->downloadBackup(),
            'restore'  => $this->restoreBackup(),
            default    => $this->index(),
        };
    }

    /* ── Dashboard view ── */
    private function index(): void {
        $tableRowCounts = $this->model->getTableRowCounts();
        $totalTables    = count($tableRowCounts);
        $totalRows      = array_sum($tableRowCounts);
        $feedback       = $_SESSION['backup_feedback'] ?? null;
        unset($_SESSION['backup_feedback']);

        require_once __DIR__ . '/../views/backuprestore.php';
    }

    /* ── Stream SQL file as download ── */
    private function downloadBackup(): void {
        $sql      = $this->model->generateSQLDump();
        $filename = 'sdirs_backup_' . date('Ymd_His') . '.sql';

        // Log the action
        $userId = $_SESSION['user']['user_id'] ?? 0;
        $ip     = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $this->model->logBackupAction($userId, 'backup_download', $ip);

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . strlen($sql));
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $sql;
        exit;
    }

    /* ── Handle uploaded SQL restore ── */
    private function restoreBackup(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirectWithFeedback('error', 'Invalid request method.');
        }

        // CSRF token check
        if (
            !isset($_POST['csrf_token'], $_SESSION['csrf_token']) ||
            !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])
        ) {
            $this->redirectWithFeedback('error', 'CSRF token mismatch. Please try again.');
        }

        // File validation
        if (empty($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
            $this->redirectWithFeedback('error', 'No file uploaded or upload error occurred.');
        }

        $file     = $_FILES['sql_file'];
        $ext      = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $maxBytes = 20 * 1024 * 1024; // 20 MB

        if ($ext !== 'sql') {
            $this->redirectWithFeedback('error', 'Only .sql files are accepted.');
        }

        if ($file['size'] > $maxBytes) {
            $this->redirectWithFeedback('error', 'File exceeds the 20 MB size limit.');
        }

        $sql = file_get_contents($file['tmp_name']);

        if ($sql === false || trim($sql) === '') {
            $this->redirectWithFeedback('error', 'Could not read the uploaded file or file is empty.');
        }

        $result = $this->model->restoreFromSQL($sql);

        if ($result['success']) {
            $userId = $_SESSION['user']['user_id'] ?? 0;
            $ip     = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
            $this->model->logBackupAction($userId, 'restore', $ip);
            $this->redirectWithFeedback('success', $result['message']);
        } else {
            $this->redirectWithFeedback('error', $result['message']);
        }
    }

    /* ── Helper: redirect back with a flash message ── */
    private function redirectWithFeedback(string $type, string $message): never {
        $_SESSION['backup_feedback'] = ['type' => $type, 'message' => $message];
        header('Location: /student-discipline-and-incident-reporting-system/public/admin/backup-restore');
        exit;
    }
}

// Regenerate CSRF token on every page load
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$controller = new BackupRestoreController($pdo);
$controller->handle();