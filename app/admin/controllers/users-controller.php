<?php
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /student-discipline-and-incident-reporting-system/public');
    exit;
}

require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../models/users-model.php';
require_once __DIR__ . '/../../../helpers/email-helper.php';
require_once __DIR__ . '/audit-log-recorder.php'; // ← ADD

class UserController {
    private $userModel;
    private $pdo;

    public function __construct($pdo) {
        $this->userModel = new UserModel($pdo);
        $this->pdo       = $pdo;
    }

    public function createUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $data = [
                'name'     => trim($_POST['name']     ?? ''),
                'lrn'      => trim($_POST['lrn']      ?? ''),
                'email'    => trim($_POST['email']     ?? ''),
                'role'     => trim($_POST['role']      ?? ''),
                'password' => $_POST['password']       ?? ''
            ];

            $result = $this->userModel->createUser($data);

            if ($result['success']) {
                $newUserId = $this->userModel->getLastInsertedId();

                if (($data['role'] === 'Teacher' || $data['role'] === 'Admin') && !empty($data['email'])) {
                    $this->setMustChangeCredentials($newUserId);
                    $emailSent = sendCredentialsEmail($data['email'], $data['name'], $data['password'], $data['role']);
                    $result['message'] = $emailSent
                        ? "{$data['role']} created successfully! Email sent with credentials."
                        : "{$data['role']} created successfully! (Email sending failed, but account was created)";
                } else {
                    $result['message'] = 'User created successfully!';
                }

                // ← LOG
                auditRecord($this->pdo, 'CREATE', 'Users', "Created new {$data['role']} account: {$data['name']}");
            }

            $_SESSION[$result['success'] ? 'success_message' : 'error_message'] = $result['message'];
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }

    private function setMustChangeCredentials($userId) {
        try {
            $stmt = $this->pdo->prepare("UPDATE user_management SET must_change_credentials = 1 WHERE user_id = ?");
            return $stmt->execute([$userId]);
        } catch (PDOException $e) {
            error_log('Error setting must_change_credentials: ' . $e->getMessage());
            return false;
        }
    }

    public function getUsersPaginated($limit, $offset, $role = '', $sort = 'latest', $search = '') {
        return $this->userModel->getUsersPaginated($limit, $offset, $role, $sort, $search);
    }

    public function getTotalUsers($role = '', $search = '') {
        return $this->userModel->getTotalUsers($role, $search);
    }
}

$userController = new UserController($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $userController->createUser();
}

require_once __DIR__ . '/../views/users.php';