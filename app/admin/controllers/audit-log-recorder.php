<?php
// audit-log-recorder.php
// Ligtas i-include kahit saan — WALANG session guard dito

require_once __DIR__ . '/../models/audit-log-model.php';

function auditRecord($pdo, $action, $module, $description = '') {
    $m = new AuditLogModel($pdo);
    $m->createTable();
    $user = $_SESSION['user'] ?? [];
    $m->log(
        $user['user_id'] ?? null,
        $user['name']    ?? 'System',
        $user['role']    ?? 'unknown',
        $action,
        $module,
        $description
    );
}