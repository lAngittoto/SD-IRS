<?php
// public/api/forgot-password-controller.php - COMPLETELY FIXED v2
// Issue: Token was being ignored. Now properly validates token from POST + SESSION

if (ob_get_level()) {
    ob_end_clean();
}

error_reporting(0);
ini_set('display_errors', 0);
header('Content-Type: application/json; charset=utf-8');
session_start();

$apiDir = dirname(__FILE__);
$publicDir = dirname($apiDir);
$appRoot = dirname($publicDir);

try {
    $configFile = $appRoot . '/config/database.php';
    
    if (!file_exists($configFile)) {
        throw new Exception('Database config not found at: ' . $configFile);
    }
    
    require_once $configFile;
    
    $modelFile = $appRoot . '/auth/models/forgot-password-model.php';
    if (!file_exists($modelFile)) {
        throw new Exception('Model file not found at: ' . $modelFile);
    }
    require_once $modelFile;

    class ForgotPasswordController {
        private $model;

        public function __construct() {
            $this->model = new ForgotPasswordModel();
        }

        public function handleRequest() {
            $action = $_GET['action'] ?? $_POST['action'] ?? null;
            
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                $this->sendResponse(false, 'Invalid request method');
                return;
            }

            switch ($action) {
                case 'step1_findEmail':
                    $this->step1FindEmail();
                    break;
                case 'step3_verifyOTP':
                    $this->step3VerifyOTP();
                    break;
                case 'step4_resetPassword':
                    $this->step4ResetPassword();
                    break;
                case 'resendOTP':
                    $this->resendOTP();
                    break;
                default:
                    $this->sendResponse(false, 'Invalid action');
            }
        }

        private function step1FindEmail() {
            $email = trim($_POST['email'] ?? '');

            if (empty($email)) {
                $this->sendResponse(false, 'Email is required');
                return;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->sendResponse(false, 'Invalid email format');
                return;
            }

            try {
                $teacher = $this->model->findTeacherByEmail($email);

                if ($teacher) {
                    $_SESSION['forgot_password_user_id'] = $teacher['user_id'];
                    $_SESSION['forgot_password_email'] = $teacher['email'];
                    $_SESSION['forgot_password_name'] = $teacher['name'];
                    $_SESSION['forgot_password_teacher_id'] = $teacher['teacher_id'];

                    error_log("✅ Step 1: Found teacher - ID: {$teacher['user_id']}, Email: {$email}");

                    $this->sendResponse(true, 'Teacher found', [
                        'teacher_name' => $teacher['name'],
                        'teacher_id' => $teacher['teacher_id'] ?? 'N/A'
                    ]);
                } else {
                    error_log("❌ Step 1: No teacher found for email: $email");
                    $this->sendResponse(false, 'No teacher found with this email');
                }
            } catch (Exception $e) {
                error_log("Error in step1: " . $e->getMessage());
                $this->sendResponse(false, 'Error: ' . $e->getMessage());
            }
        }

        private function step3VerifyOTP() {
            $otp = trim($_POST['otp'] ?? '');
            $token = trim($_POST['token'] ?? ''); // ✅ FIX: Read token from POST

            if (empty($otp)) {
                $this->sendResponse(false, 'OTP is required');
                return;
            }

            if (strlen($otp) !== 6 || !ctype_digit($otp)) {
                $this->sendResponse(false, 'OTP must be 6 digits');
                return;
            }

            // ✅ FIX: Check both POST token AND session token
            if (empty($token) && !isset($_SESSION['forgot_password_token'])) {
                error_log("❌ Step 3: No token provided!");
                $this->sendResponse(false, 'Session expired. Please go back and try again.');
                return;
            }

            // Use POST token if provided, otherwise fall back to session
            if (empty($token)) {
                $token = $_SESSION['forgot_password_token'];
            }

            error_log("🔍 Step 3: Verifying OTP - Token: " . substr($token, 0, 10) . "..., OTP: $otp");

            try {
                // Verify OTP with token
                $reset_record = $this->model->verifyOTP($token, $otp);

                if ($reset_record) {
                    // ✅ SUCCESS - Store reset_id in session
                    $_SESSION['forgot_password_verified'] = true;
                    $_SESSION['forgot_password_reset_id'] = $reset_record['reset_id'];
                    $_SESSION['forgot_password_token'] = $token; // ✅ Keep token in session

                    error_log("✅ Step 3: OTP verified! Reset ID: {$reset_record['reset_id']}");

                    $this->sendResponse(true, 'OTP verified successfully', [
                        'reset_id' => $reset_record['reset_id'],
                        'message' => '✓ OTP verified! Now create your new password'
                    ]);
                } else {
                    error_log("❌ Step 3: OTP verification failed - Token: " . substr($token, 0, 10) . "..., OTP: $otp");
                    $this->sendResponse(false, 'Invalid or expired OTP');
                }
            } catch (Exception $e) {
                error_log('Step 3 Exception: ' . $e->getMessage());
                $this->sendResponse(false, 'Error verifying OTP: ' . $e->getMessage());
            }
        }

        private function step4ResetPassword() {
            $new_password = $_POST['new_password'] ?? '';
            $confirm_password = $_POST['confirm_password'] ?? '';

            if (empty($new_password) || empty($confirm_password)) {
                $this->sendResponse(false, 'All fields are required');
                return;
            }

            if (!isset($_SESSION['forgot_password_verified'])) {
                error_log("❌ Step 4: OTP not verified - missing session flag");
                $this->sendResponse(false, 'OTP verification required. Please verify OTP first.');
                return;
            }

            if ($new_password !== $confirm_password) {
                $this->sendResponse(false, 'Passwords do not match');
                return;
            }

            // Validate password strength
            $errors = $this->model->validatePassword($new_password);
            if (!empty($errors)) {
                $this->sendResponse(false, implode(', ', $errors));
                return;
            }

            try {
                // Get reset_id from session
                $reset_id = $_SESSION['forgot_password_reset_id'] ?? null;
                
                if (!$reset_id) {
                    error_log("❌ Step 4: Reset ID not in session!");
                    $this->sendResponse(false, 'Invalid reset session. Please verify OTP again.');
                    return;
                }

                error_log("🔄 Step 4: Resetting password for reset_id: $reset_id");

                if ($this->model->resetPassword($reset_id, $new_password)) {
                    // Clear all session data
                    unset($_SESSION['forgot_password_user_id']);
                    unset($_SESSION['forgot_password_email']);
                    unset($_SESSION['forgot_password_verified']);
                    unset($_SESSION['forgot_password_token']);
                    unset($_SESSION['forgot_password_reset_id']);
                    unset($_SESSION['forgot_password_name']);
                    unset($_SESSION['forgot_password_teacher_id']);

                    error_log("✅ Step 4: Password reset successfully!");

                    $this->sendResponse(true, 'Password reset successfully. You can now login with your new password.');
                } else {
                    error_log("❌ Step 4: resetPassword returned false for reset_id: $reset_id");
                    $this->sendResponse(false, 'Invalid reset request');
                }
            } catch (Exception $e) {
                error_log('Step 4 Exception: ' . $e->getMessage());
                $this->sendResponse(false, 'Error: ' . $e->getMessage());
            }
        }

        private function resendOTP() {
            if (!isset($_SESSION['forgot_password_user_id'])) {
                error_log("❌ Resend: No user in session");
                $this->sendResponse(false, 'Please verify teacher email first');
                return;
            }

            try {
                $user_id = $_SESSION['forgot_password_user_id'];
                $email = $_SESSION['forgot_password_email'];
                $teacher_name = $_SESSION['forgot_password_name'];

                error_log("🔄 Resend: Generating new OTP for user $user_id");

                $otp_data = $this->model->resendOTP($user_id);

                if ($otp_data) {
                    $otp = $otp_data['otp'];
                    $token = $otp_data['token'];

                    // Send email
                    $subject = "Password Reset OTP - Student Discipline & Incident Reporting System";
                    $message = $this->buildEmailTemplate($teacher_name, $otp);
                    
                    $headers = "MIME-Version: 1.0\r\n";
                    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
                    $headers .= "From: noreply@sdirs.school.com\r\n";

                    mail($email, $subject, $message, $headers);
                    
                    // ✅ Store NEW token in session
                    $_SESSION['forgot_password_token'] = $token;

                    error_log("✅ Resend: New OTP sent - OTP: $otp");

                    $this->sendResponse(true, 'New OTP sent to your email', [
                        'token' => $token,
                        'email' => $this->maskEmail($email)
                    ]);
                } else {
                    error_log("❌ Resend: Failed to generate OTP");
                    $this->sendResponse(false, 'Unable to resend OTP');
                }
            } catch (Exception $e) {
                error_log('Resend OTP Exception: ' . $e->getMessage());
                $this->sendResponse(false, 'Error: ' . $e->getMessage());
            }
        }

        private function buildEmailTemplate($name, $otp) {
            $n = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
            $o = htmlspecialchars($otp, ENT_QUOTES, 'UTF-8');

            return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Password Reset OTP</title>
</head>
<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; margin: 0; padding: 0;">
    <div style="max-width: 600px; margin: 20px auto; background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 32px 24px; text-align: center;">
            <h1 style="margin: 0; font-size: 24px;">🔐 Password Reset OTP</h1>
            <p style="margin: 8px 0 0 0; opacity: 0.9;">SDIRS System</p>
        </div>
        <div style="padding: 32px 24px;">
            <p>Hello <strong>$n</strong>,</p>
            <p>Use this code to reset your password (valid for 10 minutes):</p>
            <div style="background: #f0f4ff; border: 2px solid #667eea; border-radius: 8px; padding: 24px; text-align: center; margin: 24px 0;">
                <div style="font-size: 48px; font-weight: bold; color: #667eea; letter-spacing: 8px; font-family: 'Courier New';">$o</div>
            </div>
            <p style="color: #666; font-size: 13px;">If you didn't request this, ignore this email.</p>
        </div>
    </div>
</body>
</html>
HTML;
        }

        private function maskEmail($email) {
            $parts = explode('@', $email);
            return substr($parts[0], 0, 3) . str_repeat('*', max(0, strlen($parts[0]) - 3)) . '@' . $parts[1];
        }

        private function sendResponse($success, $message, $data = null) {
            $response = [
                'success' => $success,
                'message' => $message
            ];

            if ($data) {
                $response = array_merge($response, $data);
            }

            echo json_encode($response);
            exit;
        }
    }

    $controller = new ForgotPasswordController();
    $controller->handleRequest();

} catch (Exception $e) {
    error_log('Fatal Error: ' . $e->getMessage());
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    exit;
}
?>