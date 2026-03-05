<?php
/**
 * public/api/send-otp.php
 * Web-accessible OTP sender.
 * Place this at: public/api/send-otp.php
 */
session_start();

// Load config from project root (two levels up from public/api/)
require __DIR__ . '/../../config/database.php';

// Load PHPMailer if available
$autoload = __DIR__ . '/../../vendor/autoload.php';
if (file_exists($autoload)) {
    require $autoload;
}

header('Content-Type: application/json');

// ── Auth guard ────────────────────────────────────────────────────────────────
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

// ── Input ─────────────────────────────────────────────────────────────────────
$userId = $_POST['userId'] ?? null;
$email  = $_POST['email']  ?? null;

if (!$userId || !$email) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing parameters']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid email address']);
    exit;
}

// ── Generate & store OTP ──────────────────────────────────────────────────────
try {
    $otp       = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $expiresAt = date('Y-m-d H:i:s', strtotime('+10 minutes'));

    // Invalidate old unused OTPs for this user
    $pdo->prepare("
        UPDATE otp_verifications
        SET is_used = 1
        WHERE user_id = ? AND purpose = 'credential_change' AND is_used = 0
    ")->execute([$userId]);

    // Insert new OTP
    $pdo->prepare("
        INSERT INTO otp_verifications
            (user_id, email, otp, purpose, is_used, created_at, expires_at)
        VALUES (?, ?, ?, 'credential_change', 0, NOW(), ?)
    ")->execute([$userId, $email, $otp, $expiresAt]);

    // Send email
    $userName  = $_SESSION['user']['name'] ?? 'User';
    $emailSent = sendOtpEmail($email, $userName, $otp);

    if ($emailSent) {
        echo json_encode(['success' => true, 'message' => 'OTP sent successfully']);
    } else {
        // Invalidate the OTP we just saved since email failed
        $pdo->prepare("
            UPDATE otp_verifications
            SET is_used = 1
            WHERE user_id = ? AND otp = ? AND is_used = 0
        ")->execute([$userId, $otp]);

        echo json_encode(['success' => false, 'message' => 'Failed to send OTP email. Check SMTP config.']);
    }

} catch (Exception $e) {
    error_log('OTP Error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Server error: ' . $e->getMessage()]);
}

// ── Email sending ─────────────────────────────────────────────────────────────
function sendOtpEmail($email, $name, $otp) {
    $host     = getenv('MAIL_HOST')         ?: 'smtp.gmail.com';
    $port     = (int)(getenv('MAIL_PORT')   ?: 587);
    $user     = getenv('MAIL_USERNAME')      ?: 'git762647@gmail.com';
    $pass     = getenv('MAIL_PASSWORD')      ?: 'iiuhfntwupvglcnz';
    $enc      = getenv('MAIL_ENCRYPTION')   ?: 'tls';
    $from     = getenv('MAIL_FROM_ADDRESS') ?: 'git762647@gmail.com';
    $fromName = getenv('MAIL_FROM_NAME')    ?: 'SDIRS System';

    $subject  = 'Your OTP Code - SDIRS';
    $htmlBody = buildOtpHtml($name, $otp);

    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        try {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host       = $host;
            $mail->SMTPAuth   = true;
            $mail->Username   = $user;
            $mail->Password   = $pass;
            $mail->SMTPSecure = (strtolower($enc) === 'ssl')
                ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
                : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = $port;

            $mail->setFrom($from, $fromName);
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;
            $mail->AltBody = 'Your OTP is: ' . $otp . ' (expires in 10 minutes)';

            $mail->send();
            return true;
        } catch (\PHPMailer\PHPMailer\Exception $e) {
            error_log('PHPMailer: ' . $e->getMessage());
            return false;
        }
    }

    // Fallback
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$fromName} <{$from}>\r\n";
    return (bool) @mail($email, $subject, $htmlBody, $headers);
}

function buildOtpHtml($name, $otp) {
    $n = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
    $o = htmlspecialchars($otp,  ENT_QUOTES, 'UTF-8');
    return "<!DOCTYPE html><html><head><meta charset='UTF-8'>
<style>
  body{font-family:'Segoe UI',Arial,sans-serif;color:#333;margin:0;padding:0}
  .wrap{max-width:600px;margin:0 auto;padding:20px}
  .hdr{background:linear-gradient(135deg,#667eea,#764ba2);color:#fff;
       padding:30px;text-align:center;border-radius:8px 8px 0 0}
  .hdr h2{margin:0;font-size:24px}
  .hdr p{margin:6px 0 0;opacity:.9;font-size:14px}
  .body{background:#f9f9f9;padding:30px;border-radius:0 0 8px 8px}
  .otp-box{background:#fff;border:2px solid #667eea;
           padding:20px;text-align:center;border-radius:8px;margin:20px 0}
  .otp-code{font-size:48px;font-weight:bold;color:#667eea;
            letter-spacing:8px;font-family:'Courier New',monospace}
  .timer{color:#d32f2f;font-weight:bold;margin-top:10px;font-size:13px}
  .warn{background:#fff3cd;border-left:4px solid #ffc107;
        padding:14px;margin:20px 0;border-radius:4px;color:#856404;font-size:13px}
  .foot{text-align:center;color:#aaa;font-size:11px;
        margin-top:30px;padding-top:20px;border-top:1px solid #e0e0e0}
</style></head>
<body><div class='wrap'>
  <div class='hdr'><h2>&#128272; Verify Your Identity</h2>
    <p>One-Time Password (OTP) Verification</p></div>
  <div class='body'>
    <p>Hello <strong>{$n}</strong>,</p>
    <p>You requested to update your account credentials. Use the code below:</p>
    <div class='otp-box'>
      <p style='margin:0 0 8px;color:#666;font-size:13px'>Your Verification Code:</p>
      <div class='otp-code'>{$o}</div>
      <p class='timer'>&#9200; Expires in 10 minutes</p>
    </div>
    <div class='warn'><strong>&#128274; Security Notice:</strong><br>
      Never share this code. SDIRS staff will never ask for your OTP.</div>
    <div class='foot'><p>Automated message — do not reply.</p>
      <p>&copy; 2026 Student Discipline and Incident Reporting System.</p></div>
  </div>
</div></body></html>";
}