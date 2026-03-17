<?php
// public/api/send-otp-forgot.php

if (ob_get_level()) ob_end_clean();

error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
session_start();

$apiDir    = dirname(__FILE__);
$publicDir = dirname($apiDir);
$appRoot   = dirname($publicDir);

try {
    $configFile = $appRoot . '/config/database.php';
    if (!file_exists($configFile)) throw new Exception('Database config not found');
    require_once $configFile;

    $modelFile = $appRoot . '/auth/models/forgot-password-model.php';
    if (!file_exists($modelFile)) throw new Exception('Model file not found');
    require_once $modelFile;

    class SendOTPForgot {
        private $model;
        private $appRoot;

        public function __construct($appRoot) {
            $this->model   = new ForgotPasswordModel();
            $this->appRoot = $appRoot;
        }

        public function handleRequest() {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') { $this->sendResponse(false, 'Invalid request method'); return; }
            switch ($_POST['action'] ?? null) {
                case 'verifyAndSendOTP': $this->verifyAndSendOTP(); break;
                default: $this->sendResponse(false, 'Invalid action');
            }
        }

        private function verifyAndSendOTP() {
            if (!isset($_SESSION['forgot_password_user_id'], $_SESSION['forgot_password_email'])) {
                $this->sendResponse(false, 'Please verify email first'); return;
            }

            $input_name  = trim($_POST['teacher_name']  ?? '');
            $input_tid   = trim($_POST['teacher_id']    ?? '');
            $input_email = trim($_POST['teacher_email'] ?? '');

            if (!$input_name || !$input_tid || !$input_email) {
                $this->sendResponse(false, 'All fields are required'); return;
            }

            $user_id      = $_SESSION['forgot_password_user_id'];
            $email        = $_SESSION['forgot_password_email'];
            $teacher_name = $_SESSION['forgot_password_name'];
            $teacher_id   = $_SESSION['forgot_password_teacher_id'];

            if (strtolower(trim($input_name)) !== strtolower(trim($teacher_name))) {
                $this->sendResponse(false, 'Teacher name does not match our records'); return;
            }

            if ($teacher_id === null || $teacher_id === '') {
                if (strtoupper($input_tid) !== 'N/A') {
                    $this->sendResponse(false, 'Teacher ID should be N/A for your account'); return;
                }
            } else {
                if ($input_tid !== $teacher_id) {
                    $this->sendResponse(false, 'Teacher ID does not match our records'); return;
                }
            }

            if (strtolower(trim($input_email)) !== strtolower(trim($email))) {
                $this->sendResponse(false, 'Email address does not match our records'); return;
            }

            try {
                $otp_data = $this->model->generateAndSaveOTP($user_id, $email);
                $otp      = $otp_data['otp'];
                $token    = $otp_data['token'];

                $mail_sent = false;
                if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
                    $mail_sent = $this->sendViaPhpMailer($email, $teacher_name, $otp);
                }
                if (!$mail_sent) {
                    $mail_sent = $this->sendViaPhpMail($email, $teacher_name, $otp);
                }

                $_SESSION['forgot_password_token']        = $token;
                $_SESSION['forgot_password_verified_step2'] = true;

                if ($mail_sent) {
                    $this->sendResponse(true, 'Verification successful! OTP sent to your email', [
                        'token' => $token,
                        'email' => $this->maskEmail($email)
                    ]);
                } else {
                    $this->sendResponse(false, 'Failed to send OTP email');
                }
            } catch (Exception $e) {
                error_log('OTP Exception: ' . $e->getMessage());
                $this->sendResponse(false, 'Error: ' . $e->getMessage());
            }
        }

        private function loadEnvFile() {
            $env     = [];
            $envFile = $this->appRoot . '/.env';
            if (!file_exists($envFile)) return $env;
            foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                if (strpos(trim($line), '#') === 0) continue;
                if (strpos($line, '=') !== false) {
                    [$key, $value] = explode('=', $line, 2);
                    $env[trim($key)] = trim(trim($value), '\'"');
                }
            }
            return $env;
        }

        private function sendViaPhpMailer($email, $name, $otp) {
            try {
                $cfg      = $this->loadEnvFile();
                $host     = $cfg['MAIL_HOST']         ?? 'smtp.gmail.com';
                $port     = (int)($cfg['MAIL_PORT']   ?? 587);
                $username = $cfg['MAIL_USERNAME']      ?? '';
                $password = $cfg['MAIL_PASSWORD']      ?? '';
                $enc      = $cfg['MAIL_ENCRYPTION']    ?? 'tls';
                $from     = $cfg['MAIL_FROM_ADDRESS']  ?? $username;
                $fromName = $cfg['MAIL_FROM_NAME']     ?? 'SDIRS System';

                if (!$username || !$password) { error_log('PHPMailer: credentials empty'); return false; }

                $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
                $mail->isSMTP();
                $mail->Host       = $host;
                $mail->SMTPAuth   = true;
                $mail->Username   = $username;
                $mail->Password   = $password;
                $mail->SMTPSecure = strtolower($enc) === 'ssl'
                    ? \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_SMTPS
                    : \PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port       = $port;
                $mail->Timeout    = 10;
                $mail->SMTPDebug  = 0;
                $mail->setFrom($from, $fromName);
                $mail->addAddress($email);
                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8'; $mail->Subject = 'Password Reset OTP - SDIRS';
                $mail->Body    = $this->buildEmailTemplate($name, $otp);
                $mail->AltBody = "Your OTP is: $otp (expires in 10 minutes)";

                return $mail->send();
            } catch (Exception $e) {
                error_log('PHPMailer Error: ' . $e->getMessage()); return false;
            }
        }

        private function sendViaPhpMail($email, $name, $otp) {
            try {
                $cfg      = $this->loadEnvFile();
                $from     = $cfg['MAIL_FROM_ADDRESS'] ?? 'noreply@sdirs.local';
                $fromName = $cfg['MAIL_FROM_NAME']    ?? 'SDIRS System';
                $headers  = "MIME-Version: 1.0\r\nContent-Type: text/html; charset=UTF-8\r\nFrom: {$fromName} <{$from}>\r\nReply-To: {$from}\r\n";
                $subject = '=?UTF-8?B?' . base64_encode('Password Reset OTP - SDIRS') . '?='; return mail($email, $subject, $this->buildEmailTemplate($name, $otp), $headers);
            } catch (Exception $e) {
                error_log('PHP Mail Error: ' . $e->getMessage()); return false;
            }
        }

        private function buildEmailTemplate($name, $otp) {
            $n = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
            $o = htmlspecialchars($otp,  ENT_QUOTES, 'UTF-8');
            return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1.0">
<title>Password Reset OTP</title>
</head>
<body style="margin:0;padding:0;background:#f4f4f4;font-family:'Segoe UI',Tahoma,Geneva,sans-serif;">

<table width="100%" cellpadding="0" cellspacing="0" style="background:#f4f4f4;padding:24px 0;">
<tr><td align="center">
<table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff;border-radius:12px;overflow:hidden;box-shadow:0 4px 20px rgba(0,0,0,.12);max-width:600px;width:100%;">

  <!-- Header -->
  <tr>
    <td style="background:linear-gradient(135deg,#032a0f 0%,#043915 60%,#065c22 100%);padding:36px 32px 28px;text-align:center;border-bottom:4px solid #f8c922;">
      <div style="width:60px;height:60px;background:#f8c922;border-radius:14px;display:inline-flex;align-items:center;justify-content:center;margin-bottom:16px;">
        <span style="font-size:28px;">&#128274;</span>
      </div>
      <h1 style="margin:0;font-size:22px;font-weight:800;color:#ffffff;letter-spacing:-.02em;">Password Reset OTP</h1>
      <p style="margin:6px 0 0;font-size:13px;color:rgba(255,255,255,.55);">SDIRS - Student Discipline & Incident Reporting System</p>
    </td>
  </tr>

  <!-- Body -->
  <tr>
    <td style="padding:32px;">
      <p style="margin:0 0 12px;font-size:15px;color:#1f2937;">Hello <strong style="color:#043915;">$n</strong>,</p>
      <p style="margin:0 0 24px;font-size:14px;color:#6b7280;line-height:1.6;">You requested to reset your password. Use the OTP code below to verify your identity. This code is valid for <strong>10 minutes</strong>.</p>

      <!-- OTP Box -->
      <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
        <tr>
          <td style="background:#f0fdf4;border:2px solid #043915;border-radius:12px;padding:28px 24px;text-align:center;">
            <p style="margin:0 0 10px;font-size:12px;font-weight:700;color:#6b7280;text-transform:uppercase;letter-spacing:.08em;">Your Verification Code</p>
            <div style="font-size:44px;font-weight:900;color:#043915;letter-spacing:12px;font-family:'Courier New',monospace;">$o</div>
            <p style="margin:12px 0 0;font-size:12px;font-weight:700;color:#dc2626;">Expires in 10 minutes</p>
          </td>
        </tr>
      </table>

      <!-- Security notice -->
      <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom:24px;">
        <tr>
          <td style="background:#fffbea;border-left:4px solid #f8c922;border-radius:0 8px 8px 0;padding:14px 16px;">
            <p style="margin:0;font-size:13px;color:#92400e;line-height:1.5;">
              <strong>Security Notice:</strong><br>
              Never share this code with anyone. SDIRS staff will never ask for your OTP.
            </p>
          </td>
        </tr>
      </table>

      <hr style="border:none;border-top:1px solid #e5e7eb;margin:0 0 16px;">
      <p style="margin:0;font-size:12px;color:#9ca3af;">If you didn't request this, please ignore this email or contact your administrator.</p>
    </td>
  </tr>

  <!-- Footer -->
  <tr>
    <td style="background:#043915;padding:18px 32px;text-align:center;">
      <p style="margin:0;font-size:11px;color:rgba(255,255,255,.5);">
        &copy; 2026 Student Discipline and Incident Reporting System. All rights reserved.<br>
        Automated message - do not reply.
      </p>
    </td>
  </tr>

</table>
</td></tr>
</table>

</body>
</html>
HTML;
        }

        private function maskEmail($email) {
            $p = explode('@', $email);
            return substr($p[0], 0, 3) . str_repeat('*', max(0, strlen($p[0]) - 3)) . '@' . $p[1];
        }

        private function sendResponse($success, $message, $data = null) {
            echo json_encode(array_merge(['success' => $success, 'message' => $message], $data ?? []));
            exit;
        }
    }

    (new SendOTPForgot($appRoot))->handleRequest();

} catch (Exception $e) {
    error_log('Fatal Error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Server error']);
    exit;
}
?>