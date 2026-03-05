<?php 
$page = $_GET['page'] ?? '';

switch ($page) {
    case 'teacher-dashboard':
        include __DIR__.'/../app/teacher/controllers/teacher-dashboard-controller.php';
        break;
    case 'reports-incident':
        include __DIR__.'/../app/teacher/controllers/teacher-reports-controller.php';
        break;
    case 'my-advisories':
        include __DIR__.'/../app/teacher/controllers/teacher-advisory-controller.php';
        break;
    case 'change-credentials':
        include __DIR__.'/../app/teacher/controllers/credential-controller.php';
        break;
    default:
        http_response_code(404);
        echo "404 Page not found.";
        break;
}
?>