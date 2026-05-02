<?php
class BackupRestoreModel {
    private $pdo;

    public function __construct($pdo) {
        $this->pdo = $pdo;
    }

    /**
     * Get all table names from the current database.
     */
    public function getAllTables(): array {
        $stmt = $this->pdo->query("SHOW TABLES");
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Generate a full SQL dump of all tables.
     * Returns the SQL string.
     */
    public function generateSQLDump(): string {
        $tables = $this->getAllTables();
        $sql    = "-- Student Discipline & Incident Reporting System\n";
        $sql   .= "-- Backup generated: " . date('Y-m-d H:i:s') . "\n";
        $sql   .= "-- Server version: MySQL\n\n";
        $sql   .= "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
        $sql   .= "START TRANSACTION;\n";
        $sql   .= "SET time_zone = \"+00:00\";\n\n";
        $sql   .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

        foreach ($tables as $table) {
            // CREATE TABLE
            $createStmt = $this->pdo->query("SHOW CREATE TABLE `{$table}`");
            $createRow  = $createStmt->fetch(PDO::FETCH_NUM);
            $sql .= "-- --------------------------------------------------------\n";
            $sql .= "-- Table structure for `{$table}`\n";
            $sql .= "--\n\n";
            $sql .= "DROP TABLE IF EXISTS `{$table}`;\n";
            $sql .= $createRow[1] . ";\n\n";

            // Data rows
            $dataStmt = $this->pdo->query("SELECT * FROM `{$table}`");
            $rows     = $dataStmt->fetchAll(PDO::FETCH_ASSOC);

            if (!empty($rows)) {
                $sql .= "-- Data for `{$table}`\n--\n\n";
                $columns = '`' . implode('`, `', array_keys($rows[0])) . '`';

                foreach ($rows as $row) {
                    $values = array_map(function($val) {
                        if ($val === null) return 'NULL';
                        return "'" . addslashes($val) . "'";
                    }, array_values($row));

                    $sql .= "INSERT INTO `{$table}` ({$columns}) VALUES (" . implode(', ', $values) . ");\n";
                }
                $sql .= "\n";
            }
        }

        $sql .= "SET FOREIGN_KEY_CHECKS=1;\n\n";
        $sql .= "COMMIT;\n";

        return $sql;
    }

    /**
     * Get row counts per table for the backup info panel.
     */
    public function getTableRowCounts(): array {
        $tables = $this->getAllTables();
        $result = [];

        foreach ($tables as $table) {
            $stmt         = $this->pdo->query("SELECT COUNT(*) FROM `{$table}`");
            $result[$table] = (int)$stmt->fetchColumn();
        }

        return $result;
    }

    /**
     * Execute a full SQL restore from a string.
     * Returns ['success' => bool, 'message' => string].
     */
    public function restoreFromSQL(string $sql): array {
        try {
            $this->pdo->exec("SET FOREIGN_KEY_CHECKS=0;");

            // Split on semicolons, filtering empties / comments
            $statements = array_filter(
                array_map('trim', explode(';', $sql)),
                fn($s) => $s !== '' && !preg_match('/^--/', $s) && !preg_match('/^\/\*/', $s)
            );

            foreach ($statements as $statement) {
                if (trim($statement) === '') continue;
                $this->pdo->exec($statement);
            }

            $this->pdo->exec("SET FOREIGN_KEY_CHECKS=1;");

            return ['success' => true, 'message' => 'Database restored successfully.'];
        } catch (PDOException $e) {
            return ['success' => false, 'message' => 'Restore failed: ' . $e->getMessage()];
        }
    }

    /**
     * Save backup metadata log (optional audit trail using credentials_change_logs style).
     */
    public function logBackupAction(int $userId, string $action, string $ip): void {
        // Silently fail if table doesn't exist — non-critical
        try {
            $stmt = $this->pdo->prepare(
                "INSERT INTO credentials_change_logs (user_id, ip_address) VALUES (?, ?)"
            );
            $stmt->execute([$userId, $ip]);
        } catch (PDOException $e) {
            // Non-critical
        }
    }
}