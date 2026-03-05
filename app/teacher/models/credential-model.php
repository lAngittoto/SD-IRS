<?php
/**
 * Credential Model Functions
 * All database operations related to user credentials
 */

/**
 * Update user name/username and password
 * Returns success/failure status with message
 */
function updateUserCredentials($pdo, $userId, $newName, $newPassword) {
    try {
        // Hash the password using bcrypt with cost of 12
        $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);

        // Prepare update query
        $stmt = $pdo->prepare("
            UPDATE user_management 
            SET name = ?, 
                password = ?,
                credentials_changed_at = NOW()
            WHERE user_id = ?
        ");
        
        // Execute with parameters
        $result = $stmt->execute([$newName, $hashedPassword, $userId]);

        if ($result && $stmt->rowCount() > 0) {
            return [
                'success' => true,
                'message' => 'Credentials updated successfully.'
            ];
        } else {
            return [
                'success' => false,
                'message' => 'No rows updated. Please verify your information.'
            ];
        }
    } catch (PDOException $e) {
        return [
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ];
    }
}

/**
 * Mark credentials as changed (set must_change_credentials to 0)
 */
function markCredentialsChanged($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("
            UPDATE user_management 
            SET must_change_credentials = 0,
                credentials_changed_at = NOW()
            WHERE user_id = ?
        ");
        return $stmt->execute([$userId]);
    } catch (PDOException $e) {
        error_log('Error marking credentials changed: ' . $e->getMessage());
        return false;
    }
}

/**
 * Log credentials change for audit trail
 */
function logCredentialsChange($pdo, $userId, $ipAddress = null) {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO credentials_change_logs 
            (user_id, changed_at, ip_address) 
            VALUES (?, NOW(), ?)
        ");
        return $stmt->execute([$userId, $ipAddress ?? 'unknown']);
    } catch (PDOException $e) {
        error_log('Error logging credentials change: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get user's credential change history
 */
function getCredentialChangeHistory($pdo, $userId, $limit = 10) {
    try {
        $stmt = $pdo->prepare("
            SELECT changed_at, ip_address 
            FROM credentials_change_logs 
            WHERE user_id = ? 
            ORDER BY changed_at DESC 
            LIMIT ?
        ");
        $stmt->bindParam(1, $userId, PDO::PARAM_INT);
        $stmt->bindParam(2, $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error fetching credential change history: ' . $e->getMessage());
        return [];
    }
}

/**
 * Check if user must change credentials
 */
function mustChangeCredentials($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("
            SELECT must_change_credentials 
            FROM user_management 
            WHERE user_id = ?
        ");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? (bool)$result['must_change_credentials'] : false;
    } catch (PDOException $e) {
        error_log('Error checking credential status: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get all users who must change their credentials
 */
function getUsersWhoMustChangeCredentials($pdo) {
    try {
        $stmt = $pdo->prepare("
            SELECT user_id, name, email, role, created_at 
            FROM user_management 
            WHERE must_change_credentials = 1 
            AND role IN ('Teacher', 'Student')
            ORDER BY created_at DESC
        ");
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error fetching users needing credential change: ' . $e->getMessage());
        return [];
    }
}

/**
 * Force credential change for specific user
 */
function forceCredentialChange($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("
            UPDATE user_management 
            SET must_change_credentials = 1 
            WHERE user_id = ?
        ");
        return $stmt->execute([$userId]);
    } catch (PDOException $e) {
        error_log('Error forcing credential change: ' . $e->getMessage());
        return false;
    }
}

/**
 * Verify password for a user
 */
function verifyPassword($pdo, $userId, $password) {
    try {
        $stmt = $pdo->prepare("SELECT password FROM user_management WHERE user_id = ?");
        $stmt->execute([$userId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result) {
            return password_verify($password, $result['password']);
        }
        return false;
    } catch (PDOException $e) {
        error_log('Error verifying password: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get user credential change history with user info
 */
function getCredentialChangeHistoryWithUserInfo($pdo, $userId) {
    try {
        $stmt = $pdo->prepare("
            SELECT ccl.changed_at, ccl.ip_address, um.name, um.email, um.role
            FROM credentials_change_logs ccl
            JOIN user_management um ON ccl.user_id = um.user_id
            WHERE ccl.user_id = ?
            ORDER BY ccl.changed_at DESC
            LIMIT 5
        ");
        $stmt->execute([$userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('Error fetching credential history with info: ' . $e->getMessage());
        return [];
    }
}

/**
 * Check if user has recently changed credentials
 */
function hasRecentlyChangedCredentials($pdo, $userId, $hoursAgo = 1) {
    try {
        $stmt = $pdo->prepare("
            SELECT changed_at 
            FROM credentials_change_logs 
            WHERE user_id = ? 
            AND changed_at > DATE_SUB(NOW(), INTERVAL ? HOUR)
            ORDER BY changed_at DESC 
            LIMIT 1
        ");
        $stmt->execute([$userId, $hoursAgo]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ? true : false;
    } catch (PDOException $e) {
        error_log('Error checking recent credential changes: ' . $e->getMessage());
        return false;
    }
}

/**
 * Validate password strength
 */
function validatePasswordStrength($password) {
    $errors = [];

    if (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters long.';
    }

    if (!preg_match('/[A-Z]/', $password)) {
        $errors[] = 'Password must contain at least one uppercase letter.';
    }

    if (!preg_match('/[a-z]/', $password)) {
        $errors[] = 'Password must contain at least one lowercase letter.';
    }

    if (!preg_match('/[0-9]/', $password)) {
        $errors[] = 'Password must contain at least one number.';
    }

    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
        $errors[] = 'Password must contain at least one special character (!@#$%^&*).';
    }

    return [
        'valid' => count($errors) === 0,
        'errors' => $errors
    ];
}

/**
 * Get password strength score (0-5)
 */
function getPasswordStrengthScore($password) {
    $score = 0;

    if (strlen($password) >= 8) $score++;
    if (preg_match('/[A-Z]/', $password)) $score++;
    if (preg_match('/[a-z]/', $password)) $score++;
    if (preg_match('/[0-9]/', $password)) $score++;
    if (preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) $score++;

    return $score;
}

/**
 * Get password strength label
 */
function getPasswordStrengthLabel($score) {
    switch ($score) {
        case 0:
        case 1:
        case 2:
            return 'Weak';
        case 3:
        case 4:
            return 'Fair';
        case 5:
            return 'Strong';
        default:
            return 'Unknown';
    }
}