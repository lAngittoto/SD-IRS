<?php

class UserModel {
    private $conn;
    private $table = 'user_management';

    public function __construct($pdo) {
        $this->conn = $pdo;
    }

    // =========================
    // CREATE USER
    // =========================
    public function createUser($data) {
        try {
            if (empty($data['name']) || empty($data['role']) || empty($data['password'])) {
                return [
                    'success' => false,
                    'message' => 'Name, role, and password are required.'
                ];
            }

            $email = null;
            $lrn = null;

            if ($data['role'] === 'Student') {
                if (empty($data['lrn'])) {
                    return [
                        'success' => false,
                        'message' => 'LRN is required for students.'
                    ];
                }

                if (!preg_match('/^\d{12}$/', $data['lrn'])) {
                    return [
                        'success' => false,
                        'message' => 'LRN must be exactly 12 digits.'
                    ];
                }

                // Duplicate LRN check
                $checkQuery = "SELECT user_id FROM {$this->table} WHERE lrn = :lrn LIMIT 1";
                $checkStmt = $this->conn->prepare($checkQuery);
                $checkStmt->bindParam(':lrn', $data['lrn']);
                $checkStmt->execute();

                if ($checkStmt->rowCount() > 0) {
                    return [
                        'success' => false,
                        'message' => 'This LRN is already registered.'
                    ];
                }

                $lrn = $data['lrn'];
                $email = null;

            } elseif ($data['role'] === 'Teacher') {
                if (empty($data['email'])) {
                    return [
                        'success' => false,
                        'message' => 'Email is required for teachers.'
                    ];
                }

                if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                    return [
                        'success' => false,
                        'message' => 'Invalid email format.'
                    ];
                }

                // Duplicate email check
                $checkQuery = "SELECT user_id FROM {$this->table} WHERE email = :email LIMIT 1";
                $checkStmt = $this->conn->prepare($checkQuery);
                $checkStmt->bindParam(':email', $data['email']);
                $checkStmt->execute();

                if ($checkStmt->rowCount() > 0) {
                    return [
                        'success' => false,
                        'message' => 'This email is already registered.'
                    ];
                }

                $email = $data['email'];
                $lrn = null;
            }

            // Password hashing
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);

            // Insert
            $query = "INSERT INTO {$this->table} (name, email, lrn, role, password)
                      VALUES (:name, :email, :lrn, :role, :password)";
            $stmt = $this->conn->prepare($query);
            $stmt->bindParam(':name', $data['name']);
            $stmt->bindParam(':email', $email);
            $stmt->bindParam(':lrn', $lrn);
            $stmt->bindParam(':role', $data['role']);
            $stmt->bindParam(':password', $hashedPassword);

            if ($stmt->execute()) {
                return [
                    'success' => true,
                    'message' => $data['role'] . ' created successfully!'
                ];
            }

            return [
                'success' => false,
                'message' => 'Failed to create user.'
            ];

        } catch (PDOException $e) {
            error_log("User Creation Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Database error occurred.'
            ];
        }
    }

    // =========================
    // GET ALL USERS
    // =========================
    public function getAllUsers() {
        try {
            $query = "SELECT user_id, name, email, lrn, role, created_at 
                      FROM {$this->table} 
                      ORDER BY created_at DESC";
            $stmt = $this->conn->prepare($query);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Get Users Error: " . $e->getMessage());
            return [];
        }
    }

    // =========================
    // PAGINATION SUPPORT
    // =========================
    public function getUsersPaginated($limit, $offset) {
        $query = "SELECT user_id, name, email, lrn, role
                  FROM {$this->table}
                  ORDER BY user_id DESC
                  LIMIT :limit OFFSET :offset";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limit', (int)$limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getTotalUsers() {
        $query = "SELECT COUNT(*) as total FROM {$this->table}";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['total'];
    }
}
