<?php
/**
 * public/api/verify-otp-and-update.php
 */
session_start();

require __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

$userId      = $_POST['userId']      ?? null;
$otpCode     = trim($_POST['otpCode']     ?? '');
$newName     = trim($_POST['newName']     ?? '');
$newPassword = $_POST['newPassword'] ?? '';

if (!$userId || !$otpCode || !$newName || !$newPassword) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

try {
    // Fetch the latest unused OTP — NO expiry check in SQL (we check in PHP)
    $stmt = $pdo->prepare("
        SELECT *
        FROM otp_verifications
        WHERE user_id = ?
          AND purpose = 'credential_change'
          AND is_used = 0
        ORDER BY created_at DESC
        LIMIT 1
    ");
    $stmt->execute([$userId]);
    $record = $stmt->fetch(PDO::FETCH_ASSOC);

    // Debug payload — remove after confirming fix works
    $debug = [
        'php_now'       => date('Y-m-d H:i:s'),
        'php_now_ts'    => time(),
        'found_record'  => $record ? true : false,
        'stored_otp'    => $record['otp']        ?? null,
        'submitted_otp' => $otpCode,
        'expires_at'    => $record['expires_at'] ?? null,
        'expires_ts'    => $record ? strtotime($record['expires_at']) : null,
        'diff_seconds'  => $record ? (strtotime($record['expires_at']) - time()) : null,
        'otp_match'     => $record ? ($record['otp'] === $otpCode) : null,
    ];

    if (!$record) {
        echo json_encode(['success' => false, 'message' => 'No pending OTP found. Please request a new one.', 'debug' => $debug]);
        exit;
    }

    if ($record['otp'] !== $otpCode) {
        echo json_encode(['success' => false, 'message' => 'Incorrect OTP code.', 'debug' => $debug]);
        exit;
    }

    // Expiry check purely in PHP — immune to DB timezone issues
    // 10 min OTP + 10 min grace for any clock/timezone skew = 20 min total window
    $expiresTs = strtotime($record['expires_at']);
    $graceSecs = 600; // 10 minute grace on top of 10 minute OTP = safe for +8 offset
    if ($expiresTs < (time() - $graceSecs)) {
        echo json_encode(['success' => false, 'message' => 'OTP has expired. Please request a new one.', 'debug' => $debug]);
        exit;
    }

    // Valid — mark used
    $pdo->prepare("UPDATE otp_verifications SET is_used = 1, verified_at = NOW() WHERE otp_id = ?")
        ->execute([$record['otp_id']]);

    // Update credentials
    $hashed = password_hash($newPassword, PASSWORD_BCRYPT, ['cost' => 12]);
    $updated = $pdo->prepare("
        UPDATE user_management
        SET name = ?, password = ?, must_change_credentials = 0, credentials_changed_at = NOW()
        WHERE user_id = ?
    ");
    $updated->execute([$newName, $hashed, $userId]);

    if ($updated->rowCount() === 0) {
        echo json_encode(['success' => false, 'message' => 'User not found in database.', 'debug' => $debug]);
        exit;
    }

    // Audit log
    try {
        $pdo->prepare("INSERT INTO credentials_change_logs (user_id, changed_at, ip_address) VALUES (?, NOW(), ?)")
            ->execute([$userId, $_SERVER['REMOTE_ADDR'] ?? 'unknown']);
    } catch (Exception $e) {
        error_log('Audit log: ' . $e->getMessage());
    }

    // Update session
    $_SESSION['user']['name']                    = $newName;
    $_SESSION['user']['must_change_credentials'] = 0;
    $_SESSION['user']['credentials_changed_at']  = date('Y-m-d H:i:s');

    echo json_encode(['success' => true, 'message' => 'Credentials updated successfully']);

} catch (Exception $e) {
    error_log('Verify OTP Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}