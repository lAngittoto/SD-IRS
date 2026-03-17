<?php

class ForgotPasswordModel {
    private $conn;

    public function __construct() {
        global $pdo;
        
        if (!isset($pdo) || !$pdo) {
            $configPath = __DIR__ . '/../../config/database.php';
            if (file_exists($configPath)) {
                require_once $configPath;
            } else {
                throw new Exception('Database config not found');
            }
        }
        
        $this->conn = $pdo;
        
        if (!$this->conn) {
            throw new Exception('Database connection not established');
        }

        // ✅ FIX: Force MySQL session timezone to match PHP
        // This ensures NOW() in MySQL = date() in PHP
        $this->conn->exec("SET time_zone = '+08:00'");
    }

    public function findTeacherByEmail($email) {
        try {
            $stmt = $this->conn->prepare("
                SELECT u.user_id, u.name, u.email, t.teacher_no AS teacher_id
                FROM user_management u
                LEFT JOIN teacher_info t ON u.user_id = t.user_id
                WHERE u.email = ? AND u.role = 'Teacher'
            ");
            $stmt->execute([$email]);
            $teacher = $stmt->fetch(PDO::FETCH_ASSOC);
            
            return $teacher ?: false;
        } catch (PDOException $e) {
            throw new Exception('Database error: ' . $e->getMessage());
        }
    }

    public function generateAndSaveOTP($user_id, $email) {
        try {
            // Mark ALL old unused OTPs as used
            $markOldStmt = $this->conn->prepare("
                UPDATE password_reset_tokens
                SET is_used = 1
                WHERE user_id = ? AND is_used = 0
            ");
            $markOldStmt->execute([$user_id]);
            error_log("✅ Marked old OTPs as used for user $user_id");

            // Generate fresh OTP
            $otp   = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
            $token = bin2hex(random_bytes(32));

            // ✅ FIX: Use DATE_ADD(NOW(), INTERVAL 10 MINUTE) — pure MySQL time math
            // This avoids any PHP ↔ MySQL timezone mismatch completely
            $stmt = $this->conn->prepare("
                INSERT INTO password_reset_tokens
                    (user_id, token, otp, email, expires_at, created_at)
                VALUES
                    (?, ?, ?, ?, DATE_ADD(NOW(), INTERVAL 10 MINUTE), NOW())
            ");
            $stmt->execute([$user_id, $token, $otp, $email]);

            // Read back what was stored so we can log it
            $readStmt = $this->conn->prepare("
                SELECT expires_at FROM password_reset_tokens
                WHERE token = ?
            ");
            $readStmt->execute([$token]);
            $row = $readStmt->fetch(PDO::FETCH_ASSOC);
            $expires_at = $row['expires_at'] ?? 'unknown';

            error_log("✅ New OTP Generated  : $otp");
            error_log("✅ Token              : $token");
            error_log("✅ Expires (MySQL NOW): $expires_at");

            return [
                'otp'        => $otp,
                'token'      => $token,
                'expires_at' => $expires_at,
            ];
        } catch (PDOException $e) {
            error_log('Error generating OTP: ' . $e->getMessage());
            throw new Exception('Error generating OTP: ' . $e->getMessage());
        }
    }

    public function verifyOTP($token, $otp) {
        try {
            error_log("🔍 Verifying OTP — Token: $token | OTP: $otp");

            // Debug: show what's stored
            $debugStmt = $this->conn->prepare("
                SELECT reset_id, token, otp, is_used, expires_at,
                       NOW() AS server_now,
                       (expires_at > NOW()) AS not_expired
                FROM password_reset_tokens
                WHERE token = ?
            ");
            $debugStmt->execute([$token]);
            $debug = $debugStmt->fetch(PDO::FETCH_ASSOC);

            if ($debug) {
                error_log("📋 DB Record  — stored OTP : {$debug['otp']}");
                error_log("📋 DB Record  — is_used    : {$debug['is_used']}");
                error_log("📋 DB Record  — expires_at : {$debug['expires_at']}");
                error_log("📋 DB Record  — server NOW : {$debug['server_now']}");
                error_log("📋 DB Record  — not_expired: {$debug['not_expired']}");
            } else {
                error_log("❌ Token NOT found in database: $token");
            }

            // ✅ The comparison stays 100% in MySQL — no PHP date involved
            $stmt = $this->conn->prepare("
                SELECT reset_id, user_id
                FROM password_reset_tokens
                WHERE token    = ?
                  AND otp      = ?
                  AND is_used  = 0
                  AND expires_at > NOW()
            ");
            $stmt->execute([$token, $otp]);
            $reset_record = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($reset_record) {
                error_log("✅ OTP Verified! Reset ID: {$reset_record['reset_id']}");

                $updateStmt = $this->conn->prepare("
                    UPDATE password_reset_tokens
                    SET verified_at = NOW()
                    WHERE reset_id = ?
                ");
                $updateStmt->execute([$reset_record['reset_id']]);

                return $reset_record;
            }

            error_log("❌ OTP Verification Failed — no matching record");
            return false;

        } catch (PDOException $e) {
            error_log('Database error: ' . $e->getMessage());
            throw new Exception('Database error: ' . $e->getMessage());
        }
    }

    public function resetPassword($reset_id, $new_password) {
        try {
            $stmt = $this->conn->prepare("
                SELECT user_id
                FROM password_reset_tokens
                WHERE reset_id     = ?
                  AND verified_at  IS NOT NULL
                  AND is_used      = 0
                  AND expires_at   > NOW()
            ");
            $stmt->execute([$reset_id]);
            $reset_record = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$reset_record) {
                error_log("❌ resetPassword: no valid record for reset_id $reset_id");
                return false;
            }

            $user_id         = $reset_record['user_id'];
            $hashed_password = password_hash($new_password, PASSWORD_BCRYPT, ['cost' => 12]);

            $this->conn->beginTransaction();

            try {
                $this->conn->prepare("
                    UPDATE user_management
                    SET password = ?, credentials_changed_at = NOW()
                    WHERE user_id = ?
                ")->execute([$hashed_password, $user_id]);

                $this->conn->prepare("
                    UPDATE password_reset_tokens
                    SET is_used = 1
                    WHERE reset_id = ?
                ")->execute([$reset_id]);

                $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
                $this->conn->prepare("
                    INSERT INTO credentials_change_logs (user_id, changed_at, ip_address)
                    VALUES (?, NOW(), ?)
                ")->execute([$user_id, $ip]);

                $this->conn->commit();
                error_log("✅ Password reset successfully for user_id $user_id");
                return true;

            } catch (Exception $e) {
                $this->conn->rollBack();
                throw $e;
            }

        } catch (PDOException $e) {
            throw new Exception('Error resetting password: ' . $e->getMessage());
        }
    }

    public function validatePassword($password) {
        $errors = [];
        if (strlen($password) < 8)              $errors[] = 'Password must be at least 8 characters';
        if (!preg_match('/[A-Z]/', $password))  $errors[] = 'Password must contain at least one uppercase letter';
        if (!preg_match('/[a-z]/', $password))  $errors[] = 'Password must contain at least one lowercase letter';
        if (!preg_match('/[0-9]/', $password))  $errors[] = 'Password must contain at least one number';
        return $errors;
    }

    public function resendOTP($user_id) {
        try {
            $stmt = $this->conn->prepare("
                SELECT email
                FROM password_reset_tokens
                WHERE user_id = ? AND is_used = 0
                ORDER BY created_at DESC
                LIMIT 1
            ");
            $stmt->execute([$user_id]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$result) {
                // Fallback: grab email from user_management
                $stmt2 = $this->conn->prepare("
                    SELECT email FROM user_management WHERE user_id = ?
                ");
                $stmt2->execute([$user_id]);
                $u = $stmt2->fetch(PDO::FETCH_ASSOC);
                if (!$u) return false;
                $result = ['email' => $u['email']];
            }

            return $this->generateAndSaveOTP($user_id, $result['email']);

        } catch (PDOException $e) {
            throw new Exception('Error resending OTP: ' . $e->getMessage());
        }
    }
}
?>