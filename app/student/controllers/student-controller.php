<?php

if (!isset($_SESSION['user'])) {
    header('Location: /student-discipline-and-incident-reporting-system/public/?page=log-in');
    exit;
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../models/student-models.php';

$model  = new StudentModel($pdo);

// FIX: $_SESSION['user'] is an array — access user_id and name from within it
$userId  = (int) ($_SESSION['user']['user_id'] ?? 0);

if ($userId === 0) {
    session_destroy();
    header('Location: /student-discipline-and-incident-reporting-system/public/?page=log-in');
    exit;
}

$student = $model->getStudentById($userId);

// If student record not found in DB, build a safe fallback from session
if (!$student) {
    $student = [
        'user_id'          => $userId,
        'name'             => $_SESSION['user']['name'] ?? 'Student', // FIX: access array key, not the array itself
        'lrn'              => '—',
        'email'            => '',
        'contact_no'       => '',
        'home_address'     => '',
        'profile_pix'      => null,
        'guardian_name'    => '',
        'guardian_contact' => '',
        'grade_level'      => '',
        'advisory_name'    => '',
        'adviser_name'     => '',
    ];
}

// Filters
$filterStatus    = $_GET['status']    ?? '';
$filterViolation = $_GET['violation'] ?? '';
$filterSort      = $_GET['sort']      ?? 'desc';

$validStatuses = ['', 'pending', 'reviewed', 'resolved', 'dismissed'];
if (!in_array($filterStatus, $validStatuses, true)) {
    $filterStatus = '';
}
$filterSort = in_array(strtolower($filterSort), ['asc', 'desc']) ? $filterSort : 'desc';

$incidents  = $model->getIncidentRecords($userId, $filterStatus, $filterViolation, $filterSort);
$summary    = $model->getIncidentSummary($userId);
$violations = $model->getViolationList();

// Flash message
$flash = '';
if (!empty($_SESSION['flash'])) {
    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
}

require_once __DIR__ . '/../views/student-dashboard.php';