<?php
class UserModel {
    private $conn;
    private $table = 'user_management';
    private $lastInsertedId = null;

    public function __construct($pdo) {
        $this->conn = $pdo;
    }

    /**
     * CREATE USER
     * Sets must_change_credentials = 1 for Teachers and Admins
     */
    public function createUser($data) {
        try {
            $hashedPassword = password_hash($data['password'], PASSWORD_BCRYPT, ['cost' => 12]);
            
            // Set must_change_credentials = 1 for Teachers/Admins, 0 for Students
            $mustChangeCredentials = ($data['role'] === 'Teacher' || $data['role'] === 'Admin') ? 1 : 0;

            $query = "INSERT INTO {$this->table} (name, email, lrn, role, password, must_change_credentials)
                      VALUES (:name, :email, :lrn, :role, :password, :must_change_credentials)";
            
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([
                ':name'                    => $data['name'],
                ':email'                   => ($data['role'] !== 'Student' ? $data['email'] : null),
                ':lrn'                     => ($data['role'] === 'Student' ? $data['lrn'] : null),
                ':role'                    => $data['role'],
                ':password'                => $hashedPassword,
                ':must_change_credentials' => $mustChangeCredentials
            ]);

            if ($result) {
                // Store the last inserted ID
                $this->lastInsertedId = $this->conn->lastInsertId();
            }

            return ['success' => true, 'message' => 'User created successfully!'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Get the last inserted user ID
     */
    public function getLastInsertedId() {
        return $this->lastInsertedId;
    }

    /**
     * PAGINATED USERS WITH FILTER + SEARCH
     */
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

    /**
     * TOTAL COUNT FOR PAGINATION (with role + search filter)
     */
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

    /**
     * Get user by ID
     */
    public function getUserById($userId) {
        try {
            $stmt = $this->conn->prepare("SELECT * FROM {$this->table} WHERE user_id = ?");
            $stmt->execute([$userId]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log('Error fetching user: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if user exists by email
     */
    public function userExistsByEmail($email) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE email = ?");
            $stmt->execute([$email]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log('Error checking user existence: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if user exists by LRN
     */
    public function userExistsByLrn($lrn) {
        try {
            $stmt = $this->conn->prepare("SELECT COUNT(*) as count FROM {$this->table} WHERE lrn = ?");
            $stmt->execute([$lrn]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            return $result['count'] > 0;
        } catch (PDOException $e) {
            error_log('Error checking user existence: ' . $e->getMessage());
            return false;
        }
    }
}