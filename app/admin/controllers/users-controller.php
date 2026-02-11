<?php

// Only admin
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /student-discipline-and-incident-reporting-system/public');
    exit;
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../models/users-model.php';

class UserController {
    private $userModel;

    public function __construct($pdo) {
        $this->userModel = new UserModel($pdo);
    }

    /**
     * Create user
     */
    public function createUser() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return;
        }

        $data = [
            'name' => trim($_POST['name'] ?? ''),
            'lrn' => trim($_POST['lrn'] ?? ''),
            'email' => trim($_POST['email'] ?? ''),
            'role' => trim($_POST['role'] ?? ''),
            'password' => $_POST['password'] ?? ''
        ];

        $result = $this->userModel->createUser($data);

        if ($result['success']) {
            $_SESSION['success_message'] = $result['message'];
        } else {
            $_SESSION['error_message'] = $result['message'];
        }

        header('Location: ' . $_SERVER['HTTP_REFERER']);
        exit;
    }

    /**
     * Get paginated users
     */
    public function getUsersPaginated($limit, $offset) {
        return $this->userModel->getUsersPaginated($limit, $offset);
    }

    /**
     * Get total user count
     */
    public function getTotalUsers() {
        return $this->userModel->getTotalUsers();
    }
}

require_once __DIR__ . '/../views/users.php';
