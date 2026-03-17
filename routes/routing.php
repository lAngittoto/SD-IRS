<?php
$page = $_GET['page'] ?? '';
if ($page === '') {
    header('Location: home-page');
    exit;
}

// ✅ UPDATED - Added 'change-credentials-otp' sa authPages
$authPages = ['home-page', 'log-in', 'authenticate', 'change-credentials-otp', 'forgot-password'];
$admin = ['admin-dashboard', 'incident-reports', 'discipline-records', 'advisories', 'user-management','reports'];
$teacher = ['teacher-dashboard', 'reports-incident', 'my-advisories','change-credentials'];
$student = [''];

if (in_array($page, $authPages)) {
    switch ($page) {
        case 'home-page':
            include __DIR__ . '/../auth/views/home-page.php';
            break;
        case 'authenticate':
            include __DIR__. '/../auth/controllers/authenticate-controller.php';
            break;
        case 'log-in':
            include __DIR__. '/../auth/views/log-in.php';
            break;
        // ✅ ADDED - Handle change-credentials-otp page
        case 'change-credentials-otp':
            include __DIR__. '/../app/teacher/views/change-credentials.php';
            break;
        case 'forgot-password':
            include __DIR__. '/../auth/views/forgot-password.php';
            break;
    }
} elseif (in_array($page, $admin)) {
    require_once __DIR__ . '/admin.php';
} elseif (in_array($page, $teacher)) {
    require_once __DIR__ . '/teacher.php';
} else {
    http_response_code(404);
    echo '404 Page not found.';
}
?>