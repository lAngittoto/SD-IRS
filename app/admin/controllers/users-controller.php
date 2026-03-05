<?php
// 1. Check Admin Access
if (!isset($_SESSION['user']) || $_SESSION['user']['role'] !== 'admin') {
    header('Location: /student-discipline-and-incident-reporting-system/public');
    exit;
}

// 2. Load dependencies
require_once __DIR__ . '/../../../config/database.php';
require_once __DIR__ . '/../models/users-model.php';
require_once __DIR__ . '/../../../helpers/email-helper.php';

/**
 * 3. UserController Class
 */
class UserController {
    private $userModel;
    private $pdo;

    public function __construct($pdo) {
        $this->userModel = new UserModel($pdo);
        $this->pdo = $pdo;
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

            // Create the user
            $result = $this->userModel->createUser($data);

            if ($result['success']) {
                // Get the newly created user ID
                $newUserId = $this->userModel->getLastInsertedId();

                // If Teacher or Admin, send email and set must_change_credentials
                if (($data['role'] === 'Teacher' || $data['role'] === 'Admin') && !empty($data['email'])) {
                    // Set must_change_credentials = 1
                    $this->setMustChangeCredentials($newUserId);

                    // Send email with credentials
                    $emailSent = sendCredentialsEmail(
                        $data['email'],
                        $data['name'],
                        $data['password'],
                        $data['role']
                    );

                    if ($emailSent) {
                        $result['message'] = $data['role'] . ' created successfully! Email sent with credentials.';
                    } else {
                        $result['message'] = $data['role'] . ' created successfully! (Email sending failed, but account was created)';
                    }
                } else {
                    $result['message'] = 'User created successfully!';
                }
            }

            $_SESSION[$result['success'] ? 'success_message' : 'error_message'] = $result['message'];
            header('Location: ' . $_SERVER['HTTP_REFERER']);
            exit;
        }
    }

    /**
     * Set must_change_credentials to 1 for new users
     */
    private function setMustChangeCredentials($userId) {
        try {
            $stmt = $this->pdo->prepare("
                UPDATE user_management 
                SET must_change_credentials = 1 
                WHERE user_id = ?
            ");
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

/**
 * 4. Instantiate and Run
 */
$userController = new UserController($pdo);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'])) {
    $userController->createUser();
}

// 5. Load the View
require_once __DIR__ . '/../views/users.php';