<?php

if (!isset($_SESSION['user'])) {
    header('Location: /student-discipline-and-incident-reporting-system/public/?page=log-in');
    exit;
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../models/student-code-model.php';

$model = new StudentCodeModel($pdo);
$rules = $model->getConductRules();

ob_start();
require_once __DIR__ . '/../views/student-code-conduct.php';
$content = ob_get_clean();
include __DIR__ . '/../../../includes/structure.php';