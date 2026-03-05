<?php

// Only admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'Teacher') {
    header('Location: /student-discipline-and-incident-reporting-system/public');
    exit;
}





require_once __DIR__ . '/../views/teacher-reports.php';