<?php
class AuditLogModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    public function createTable() {
        $this->pdo->exec("
            CREATE TABLE IF NOT EXISTS audit_logs (
                id           INT AUTO_INCREMENT PRIMARY KEY,
                user_id      INT          NULL,
                user_name    VARCHAR(255) NULL,
                user_role    VARCHAR(50)  NULL,
                action       VARCHAR(100) NOT NULL,
                module       VARCHAR(100) NOT NULL,
                description  TEXT         NULL,
                ip_address   VARCHAR(45)  NULL,
                user_agent   TEXT         NULL,
                created_at   DATETIME     DEFAULT CURRENT_TIMESTAMP,
                INDEX idx_user_id   (user_id),
                INDEX idx_action    (action),
                INDEX idx_module    (module),
                INDEX idx_created   (created_at)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
        ");
    }

    public function log($userId, $userName, $userRole, $action, $module, $description = '') {
        $stmt = $this->pdo->prepare("
            INSERT INTO audit_logs (user_id, user_name, user_role, action, module, description, ip_address, user_agent)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");
        return $stmt->execute([
            $userId,
            $userName,
            $userRole,
            $action,
            $module,
            $description,
            $_SERVER['REMOTE_ADDR']          ?? null,
            $_SERVER['HTTP_USER_AGENT']       ?? null,
        ]);
    }

    public function getLogs($filters = [], $page = 1, $perPage = 20) {
        $where  = [];
        $params = [];

        if (!empty($filters['module'])) {
            $where[]  = 'module = ?';
            $params[] = $filters['module'];
        }
        if (!empty($filters['action'])) {
            $where[]  = 'action = ?';
            $params[] = $filters['action'];
        }
        if (!empty($filters['user_role'])) {
            $where[]  = 'user_role = ?';
            $params[] = $filters['user_role'];
        }
        if (!empty($filters['date_from'])) {
            $where[]  = 'DATE(created_at) >= ?';
            $params[] = $filters['date_from'];
        }
        if (!empty($filters['date_to'])) {
            $where[]  = 'DATE(created_at) <= ?';
            $params[] = $filters['date_to'];
        }
        if (!empty($filters['search'])) {
            $where[]  = '(user_name LIKE ? OR description LIKE ? OR module LIKE ?)';
            $s        = '%'.$filters['search'].'%';
            $params   = array_merge($params, [$s, $s, $s]);
        }

        $sql = 'SELECT * FROM audit_logs';
        if ($where) $sql .= ' WHERE '.implode(' AND ', $where);
        $sql .= ' ORDER BY created_at DESC';

        // Total count
        $countStmt = $this->pdo->prepare('SELECT COUNT(*) FROM audit_logs'.($where ? ' WHERE '.implode(' AND ', $where) : ''));
        $countStmt->execute($params);
        $total = (int)$countStmt->fetchColumn();

        // Paginate
        $offset   = ($page - 1) * $perPage;
        $sql     .= " LIMIT $perPage OFFSET $offset";
        $stmt     = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return [
            'logs'        => $stmt->fetchAll(PDO::FETCH_ASSOC),
            'total'       => $total,
            'total_pages' => max(1, ceil($total / $perPage)),
            'page'        => $page,
        ];
    }

    public function getModules() {
        $stmt = $this->pdo->query('SELECT DISTINCT module FROM audit_logs ORDER BY module ASC');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getActions() {
        $stmt = $this->pdo->query('SELECT DISTINCT action FROM audit_logs ORDER BY action ASC');
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public function getSummaryStats() {
        $stmt = $this->pdo->query("
            SELECT
                COUNT(*)                                              AS total_logs,
                COUNT(CASE WHEN DATE(created_at)=CURDATE() THEN 1 END) AS today_logs,
                COUNT(DISTINCT user_id)                               AS unique_users,
                COUNT(DISTINCT module)                                AS modules_touched
            FROM audit_logs
        ");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function clearLogs($beforeDate = null) {
        if ($beforeDate) {
            $stmt = $this->pdo->prepare('DELETE FROM audit_logs WHERE DATE(created_at) <= ?');
            return $stmt->execute([$beforeDate]);
        }
        return $this->pdo->exec('DELETE FROM audit_logs') !== false;
    }
}