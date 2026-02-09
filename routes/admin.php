<?php 
$page = $_GET['page'] ?? 'admin';

switch ($page) {
    case 'admin-dashboard':
        include __DIR__.'/../app/admin/controllers/dashboard-controller.php';
        break;
    case 'incident-reports':
        include __DIR__.'/../app/admin/controllers/incident-controller.php';
        break;
    default:
    http_response_code(404);
    echo "404 Page not found.";
    break;
}