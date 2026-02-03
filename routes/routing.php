<?php

$page = $_GET['page'] ?? '';

if ($page === '') {
    header('Location: home-page');
    exit;
}

$authPages = ['home-page', 'log-in'];

$admin = [''];
$guidance = [''];
$teacher = [''];
$student = [''];

if (in_array($page, $authPages)) {

    switch ($page) {

        case 'home-page':
            include __DIR__ . '/../auth/views/home-page.php';
            break;
        case 'log-in':
            include __DIR__. '/../auth/views/log-in.php';
            break;
    }
} elseif (in_array($page, $admin)) {

    require_once __DIR__ . '/end-user.php';
} elseif (in_array($page, $guidance)) {

    require_once __DIR__ . '/admin.php';
} else {

    http_response_code(404);
    echo '404 Page not found.';
}
