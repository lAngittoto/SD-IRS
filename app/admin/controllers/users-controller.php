<?php
// 1. I-check ang Admin Access (STAYS AT THE TOP)
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /student-discipline-and-incident-reporting-system/public');
    exit;
}

// 2. Load dependencies
require_once __DIR__ . '/../../../config/database.php'; 
require_once __DIR__ . '/../models/users-model.php';

/**
 * 3. DEFINE THE CLASS
 * We define this BEFORE we call "new UserController" so Intelephense 
 * and PHP know exactly what it is.
 */
class UserController {
    private $userModel;

    public function __construct($pdo) {
        $this->userModel = new UserModel($pdo);
    }

    public function createUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name'     => trim($_POST['name'] ?? ''),
                'lrn'      => trim($_POST['lrn'] ?? ''),
                'email'    => trim($_POST['email'] ?? ''),
                'role'     => trim($_POST['role'] ?? ''),
                'password' => $_POST['password'] ?? ''
            ];
            $result = $this->userModel->createUser($data);
            $_SESSION[$result['success'] ? 'success_message' : 'error_message'] = $result['message'];
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }

    public function getUsersPaginated($limit, $offset, $role = '', $sort = 'latest', $search = '') {
        return $this->userModel->getUsersPaginated($limit, $offset, $role, $sort, $search);
    }

    public function getTotalUsers($role = '', $search = '') {
        return $this->userModel->getTotalUsers($role, $search);
    }
}

/**
 * 4. INSTANTIATE AND RUN
 * Now that the class is defined above, this "new" call will not throw an error.
 */
$userController = new UserController($pdo);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $userController->createUser();
}

// 5. Load the View logic and UI
require_once __DIR__ . '/../views/users.php';