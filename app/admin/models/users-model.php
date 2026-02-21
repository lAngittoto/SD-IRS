<?php
class UserModel {
    private $conn;
    private $table = 'user_management';

    public function __construct($pdo) {
        $this->conn = $pdo;
    }

    // CREATE USER
    public function createUser($data) {
        try {
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);

            $query = "INSERT INTO {$this->table} (name, email, lrn, role, password)
                      VALUES (:name, :email, :lrn, :role, :password)";

            $stmt = $this->conn->prepare($query);
            $stmt->execute([
                ':name'     => $data['name'],
                ':email'    => ($data['role'] !== 'Student' ? $data['email'] : null),
                ':lrn'      => ($data['role'] === 'Student' ? $data['lrn'] : null),
                ':role'     => $data['role'],
                ':password' => $hashedPassword
            ]);

            return ['success' => true, 'message' => 'User created successfully!'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    // PAGINATED USERS WITH FILTER + SEARCH
    public function getUsersPaginated($limit, $offset, $role = '', $sort = 'latest', $search = '') {
        $query = "SELECT user_id, name, email, lrn, role FROM {$this->table} WHERE 1=1";

        // Role Filter (includes Admin)
        if (!empty($role)) {
            $query .= " AND role = :role";
        }

        // Search
        if (!empty($search)) {
            $query .= " AND (name LIKE :search OR lrn LIKE :search OR email LIKE :search)";
        }

        // Sort
        if ($sort === 'asc')       $query .= " ORDER BY name ASC";
        elseif ($sort === 'desc')  $query .= " ORDER BY name DESC";
        else                       $query .= " ORDER BY user_id DESC";

        $query .= " LIMIT :limit OFFSET :offset";

        $stmt = $this->conn->prepare($query);

        if (!empty($role)) {
            $stmt->bindValue(':role', $role);
        }
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit',  (int)$limit,  PDO::PARAM_INT);
        $stmt->bindValue(':offset', (int)$offset, PDO::PARAM_INT);

        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // TOTAL COUNT FOR PAGINATION (with role + search filter)
    public function getTotalUsers($role = '', $search = '') {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE 1=1";

        if (!empty($role)) {
            $query .= " AND role = :role";
        }
        if (!empty($search)) {
            $query .= " AND (name LIKE :search OR lrn LIKE :search OR email LIKE :search)";
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($role)) {
            $stmt->bindValue(':role', $role);
        }
        if (!empty($search)) {
            $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
        }

        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result['total'];
    }
}