<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user'])) {
    header('Location: /student-discipline-and-incident-reporting-system/public/?page=log-in');
    exit;
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: /student-discipline-and-incident-reporting-system/public/?page=teacher-dashboard');
    exit;
}

// Load database connection
require __DIR__ . '/../../config/database.php';

// Get form data
$userId = $_SESSION['user']['user_id'] ?? $_SESSION['user']['id'] ?? null;
$newName = trim($_POST['name'] ?? '');
$newPassword = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirmPassword'] ?? '';
$userEmail = $_SESSION['user']['email'] ?? null;

// Validate inputs
$validation = validateCredentials($newName, $newPassword, $confirmPassword);

if (!$validation['valid']) {
    $_SESSION['error_message'] = $validation['message'];
    header('Location: /student-discipline-and-incident-reporting-system/public/?page=change-credentials-otp');
    exit;
}

// Update credentials in database
$updateResult = updateUserCredentials($pdo, $userId, $newName, $newPassword);

if (!$updateResult['success']) {
    $_SESSION['error_message'] = 'Failed to update credentials: ' . $updateResult['message'];
    header('Location: /student-discipline-and-incident-reporting-system/public/?page=change-credentials-otp');
    exit;
}

// Mark credentials as changed
markCredentialsChanged($pdo, $userId);

// Log the change
$ipAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
logCredentialsChange($pdo, $userId, $ipAddress);

// Send confirmation email
if ($userEmail) {
    sendCredentialsEmail($userEmail, $newName, $newPassword);
}

// Update session
$_SESSION['user']['name'] = $newName;
$_SESSION['user']['must_change_credentials'] = 0;
$_SESSION['user']['credentials_changed_at'] = date('Y-m-d H:i:s');

// Set success message
$_SESSION['success_message'] = 'Credentials updated successfully! Redirecting to dashboard...';

// Redirect to dashboard - FIXED FORMAT
header('Location: /student-discipline-and-incident-reporting-system/public/?page=teacher-dashboard');
exit;

/**
 * Validate credentials format and requirements
 */
function validateCredentials($name, $password, $confirmPassword) {
    // Validate name (minimum 8 characters)
    if (strlen($name) < 8) {
        return [
            'valid' => false,
            'message' => 'Name must be at least 8 characters long.'
        ];
    }

    // Validate password confirmation
    if ($password !== $confirmPassword) {
        return [
            'valid' => false,
            'message' => 'Passwords do not match.'
        ];
    }

    // Validate password strength (minimum 8 characters)
    if (strlen($password) < 8) {
        return [
            'valid' => false,
            'message' => 'Password must be at least 8 characters long.'
        ];
    }

    // Validate password has uppercase
    if (!preg_match('/[A-Z]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one uppercase letter.'
        ];
    }

    // Validate password has lowercase
    if (!preg_match('/[a-z]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one lowercase letter.'
        ];
    }

    // Validate password has number
    if (!preg_match('/[0-9]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one number.'
        ];
    }

    // Validate password has special character
    if (!preg_match('/[!@#$%^&*()_+\-=\[\]{};\':"\\|,.<>\/?]/', $password)) {
        return [
            'valid' => false,
            'message' => 'Password must contain at least one special character (!@#$%^&*).'
        ];
    }

    return ['valid' => true, 'message' => 'Validation passed.'];
}

/**
 * Update user name and password in database
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
 * Send credentials via email using PHPMailer or mail()
 */
function sendCredentialsEmail($email, $name, $password) {
    try {
        // Get email configuration from environment or .env file
        $mailFrom = getenv('MAIL_FROM_ADDRESS') ?: 'git762647@gmail.com';
        $mailFromName = 'SDIRS System';
        
        // Prepare email subject and body
        $subject = 'Your Account Credentials - Student Discipline and Incident Reporting System';
        
        $htmlBody = "
            <html>
            <head>
                <style>
                    body { font-family: Arial, sans-serif; color: #333; }
                    .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                    .header { background-color: #667eea; color: white; padding: 20px; border-radius: 5px 5px 0 0; text-align: center; }
                    .content { background-color: #f9f9f9; padding: 20px; border-radius: 0 0 5px 5px; }
                    .info-box { background-color: #e3f2fd; border-left: 4px solid #2196f3; padding: 15px; margin: 20px 0; }
                    .credentials { background-color: white; border: 1px solid #ddd; padding: 15px; margin: 20px 0; border-radius: 3px; }
                    .label { font-weight: bold; color: #667eea; }
                    .footer { font-size: 12px; color: #999; text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #ddd; }
                </style>
            </head>
            <body>
                <div class='container'>
                    <div class='header'>
                        <h2>Welcome to SDIRS!</h2>
                    </div>
                    <div class='content'>
                        <p>Hello " . htmlspecialchars($name) . ",</p>
                        <p>Your account credentials have been successfully created and updated in the Student Discipline and Incident Reporting System.</p>
                        
                        <div class='info-box'>
                            <strong>📌 Important Notice:</strong><br>
                            Keep your credentials safe and do not share them with anyone. If you did not make this request, please contact your administrator immediately.
                        </div>

                        <h3>Your Account Credentials:</h3>
                        <div class='credentials'>
                            <p><span class='label'>Username/Full Name:</span><br>" . htmlspecialchars($name) . "</p>
                            <p><span class='label'>Password:</span><br>" . htmlspecialchars($password) . "</p>
                        </div>

                        <h3>Login Information:</h3>
                        <p><strong>System URL:</strong><br><a href='http://localhost/student-discipline-and-incident-reporting-system/public'>http://localhost/student-discipline-and-incident-reporting-system/public</a></p>

                        <p><strong>Next Steps:</strong></p>
                        <ul>
                            <li>Login using the credentials above</li>
                            <li>You will be directed to your teacher dashboard</li>
                            <li>Update your profile information if needed</li>
                        </ul>

                        <div class='info-box' style='background-color: #fff3cd; border-left-color: #ffc107;'>
                            <strong>⚠️ Security Reminder:</strong><br>
                            After your first login, it is recommended to change your password to something only you know. Never share your login credentials with anyone.
                        </div>

                        <div class='footer'>
                            <p>This is an automated message. Please do not reply to this email.</p>
                            <p>&copy; 2026 Student Discipline and Incident Reporting System. All rights reserved.</p>
                        </div>
                    </div>
                </div>
            </body>
            </html>
        ";

        // Try to use PHPMailer if available
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
            $mail->isSMTP();
            $mail->Host = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = getenv('MAIL_USERNAME') ?: 'git762647@gmail.com';
            $mail->Password = getenv('MAIL_PASSWORD') ?: 'iiuhfntwupvglcnz';
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            
            $mail->setFrom($mailFrom, $mailFromName);
            $mail->addAddress($email);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $htmlBody;
            $mail->AltBody = 'Your username is: ' . $name . ' and your password is: ' . $password;
            
            $mail->send();
            return true;
        } else {
            // Fallback to PHP's mail() function
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: " . $mailFromName . " <" . $mailFrom . ">\r\n";
            $headers .= "Reply-To: " . $mailFrom . "\r\n";
            
            mail($email, $subject, $htmlBody, $headers);
            return true;
        }
    } catch (Exception $e) {
        // Log error but don't fail the credential change
        error_log('Email sending error: ' . $e->getMessage());
        return false;
    }
}

require_once __DIR__.'/../views/change-credentials.php';
?>