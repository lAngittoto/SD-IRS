<?php
require __DIR__.'/../../config/database.php';
require __DIR__.'/../models/authenticate-model.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $user = getUser($pdo, $username);
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;
        
        // ✅ CHECK IF USER MUST CHANGE CREDENTIALS
        if ($user['must_change_credentials'] == 1) {
            header('Location: /student-discipline-and-incident-reporting-system/public/change-credentials-otp');
            exit;
        }
        
        // Original redirect logic
        if ($user['role'] === 'admin') {
            header('Location: /student-discipline-and-incident-reporting-system/public/admin-dashboard');
        } elseif ($user['role'] === 'Teacher') {
            header('Location: /student-discipline-and-incident-reporting-system/public/teacher-dashboard');
        }
        exit;
    } else {
        $_SESSION['error'] = "Invalid username or password";
        header('Location: /student-discipline-and-incident-reporting-system/public/log-in');
        exit;
    }
}
?>