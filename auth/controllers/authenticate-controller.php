<?php
require __DIR__.'/../../config/database.php';
require __DIR__.'/../models/authenticate-model.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $user = getUser($pdo, $username);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;

        if ($user['role'] === 'admin') {
            header('Location: /student-discipline-and-incident-reporting-system/public/admin-dashboard');
        } else {
       
        }
        exit;
    } else {
        $_SESSION['error'] = "Invalid username or password";
        header('Location: /student-discipline-and-incident-reporting-system/public/log-in');
        exit;
    }
}