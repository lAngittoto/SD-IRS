<?php 
$page = $_GET['page'] ?? '';

switch ($page) {
    case 'student-dashboard':
        include __DIR__.'/../app/student/controllers/student-controller.php';
        break;
    case 'code-of-conduct':
        include __DIR__.'/../app/student/controllers/student-code-controller.php';
        break;
    case 'student-report':
        include __DIR__.'/../app/student/controllers/student-report-controller.php';
        break;

    default:
        http_response_code(404);
        echo "404 Page not found.";
        break;
}
?>