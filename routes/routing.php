<?php

$page = $_GET['page'] ?? '';

if ($page === '') {
    header('Location: home-page');
    exit;
}

$authPages = ['home-page', 'log-in', 'authenticate'];

$admin = ['admin-dashboard'];
$guidance = [''];
$teacher = [''];
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
    }
} elseif (in_array($page, $admin)) {
    require_once __DIR__ . '/admin.php';
} elseif (in_array($page, $guidance)) {

    require_once __DIR__ . '/';
} else {

    http_response_code(404);
    echo '404 Page not found.';
}
