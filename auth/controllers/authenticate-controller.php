<?php
require __DIR__ . '/../../config/database.php';
require __DIR__ . '/../models/authenticate-model.php';
require_once __DIR__ . '/../../app/admin/controllers/audit-log-recorder.php'; // ← DITO ANG PAGBABAGO, hindi na full controller

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $user     = getUser($pdo, $username);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user'] = $user;

        // ✅ I-set muna ang session BAGO mag-log para makuha ng auditRecord() ang user info
        auditRecord($pdo, 'LOGIN', 'Authentication', "User logged in: {$user['name']} ({$user['role']})");

        if ($user['must_change_credentials'] == 1) {
            header('Location: /student-discipline-and-incident-reporting-system/public/change-credentials-otp');
            exit;
        }

        if ($user['role'] === 'admin') {
            header('Location: /student-discipline-and-incident-reporting-system/public/admin-dashboard');
        } elseif ($user['role'] === 'Teacher') {
            header('Location: /student-discipline-and-incident-reporting-system/public/teacher-dashboard');
        } elseif ($user['role'] === 'Student') {
            header('Location: /student-discipline-and-incident-reporting-system/public/student-dashboard');
        }
        exit;

    } else {
        $_SESSION['error'] = "Invalid username or password";
        header('Location: /student-discipline-and-incident-reporting-system/public/log-in');
        exit;
    }
}