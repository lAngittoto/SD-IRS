<?php


// 1. I-check ang Admin Access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /student-discipline-and-incident-reporting-system/public');
    exit;
}

// 2. Load dependencies
require_once __DIR__ . '/../../../config/database.php'; // Siguraduhin na nandito ang $pdo variable
require_once __DIR__ . '/../models/users-model.php';

class UserController {
    private $userModel;

    public function __construct($pdo) {
        $this->userModel = new UserModel($pdo);
    }

    public function createUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name' => trim($_POST['name'] ?? ''),
                'lrn' => trim($_POST['lrn'] ?? ''),
                'email' => trim($_POST['email'] ?? ''),
                'role' => trim($_POST['role'] ?? ''),
                'password' => $_POST['password'] ?? ''
            ];
            $result = $this->userModel->createUser($data);
            $_SESSION[$result['success'] ? 'success_message' : 'error_message'] = $result['message'];
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }

    // Eto ang gagamitin ng View (users.php)
// Sa loob ng UserController class
public function getUsersPaginated($limit, $offset, $role = '', $sort = 'latest', $search = '') {
    return $this->userModel->getUsersPaginated($limit, $offset, $role, $sort, $search);
}

    public function getTotalUsers($role = '') {
        return $this->userModel->getTotalUsers($role);
    }
}

// 3. Eto ang nag-aayos ng Fatal Error:
// I-instantiate ang controller DITO, para pag-include ng view, available na ang $userController
$userController = new UserController($pdo);

// 4. Tawagin ang create user kung may post data
if (isset($_POST['name'])) {
    $userController->createUser();
}

// 5. Sa huli, i-load ang View
require_once __DIR__ . '/../views/users.php';