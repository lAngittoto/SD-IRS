<?php
/**
 * EMAIL HELPER FUNCTIONS
 * Sends credential emails to new users
 */

/**
 * Send credentials email to new teacher/admin
 * Uses .env configuration for SMTP settings
 */
function sendCredentialsEmail($email, $name, $password, $role = 'Teacher') {
    try {
        // Get email configuration from environment variables
        $mailHost = getenv('MAIL_HOST') ?: 'smtp.gmail.com';
        $mailPort = getenv('MAIL_PORT') ?: 587;
        $mailUsername = getenv('MAIL_USERNAME') ?: 'git762647@gmail.com';
        $mailPassword = getenv('MAIL_PASSWORD') ?: 'iiuhfntwupvglcnz';
        $mailEncryption = getenv('MAIL_ENCRYPTION') ?: 'tls';
        $mailFromAddress = getenv('MAIL_FROM_ADDRESS') ?: 'git762647@gmail.com';
        $mailFromName = getenv('MAIL_FROM_NAME') ?: 'SDIRS System';

        // Check if PHPMailer is available
        if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
            return sendEmailWithPHPMailer(
                $email,
                $name,
                $password,
                $role,
                $mailHost,
                $mailPort,
                $mailUsername,
                $mailPassword,
                $mailEncryption,
                $mailFromAddress,
                $mailFromName
            );
        } else {
            // Fallback to PHP mail() function
            return sendEmailWithMail($email, $name, $password, $role, $mailFromAddress, $mailFromName);
        }
    } catch (Exception $e) {
        error_log('Email Helper Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Send email using PHPMailer
 */
function sendEmailWithPHPMailer($email, $name, $password, $role, $mailHost, $mailPort, $mailUsername, $mailPassword, $mailEncryption, $mailFromAddress, $mailFromName) {
    try {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = $mailHost;
        $mail->SMTPAuth = true;
        $mail->Username = $mailUsername;
        $mail->Password = $mailPassword;
        $mail->SMTPSecure = strtolower($mailEncryption);
        $mail->Port = (int)$mailPort;

        // Recipients
        $mail->setFrom($mailFromAddress, $mailFromName);
        $mail->addAddress($email);

        // Content
        $mail->isHTML(true);
        $mail->Subject = 'Your ' . $role . ' Account Credentials - Student Discipline and Incident Reporting System';
        $mail->Body = getEmailTemplate($name, $password, $role);
        $mail->AltBody = "Your username is: " . $name . "\nYour password is: " . $password;

        return $mail->send();
    } catch (Exception $e) {
        error_log('PHPMailer Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Send email using PHP mail() function
 */
function sendEmailWithMail($email, $name, $password, $role, $mailFromAddress, $mailFromName) {
    try {
        $subject = 'Your ' . $role . ' Account Credentials - Student Discipline and Incident Reporting System';
        $htmlBody = getEmailTemplate($name, $password, $role);

        $headers = "MIME-Version: 1.0\r\n";
        $headers .= "Content-type: text/html; charset=UTF-8\r\n";
        $headers .= "From: " . $mailFromName . " <" . $mailFromAddress . ">\r\n";
        $headers .= "Reply-To: " . $mailFromAddress . "\r\n";

        return mail($email, $subject, $htmlBody, $headers);
    } catch (Exception $e) {
        error_log('Mail() Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get email HTML template
 */
function getEmailTemplate($name, $password, $role) {
    $loginUrl = 'http://localhost/student-discipline-and-incident-reporting-system/public';

    $html = "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <style>
            body {
                font-family: 'Segoe UI', Arial, sans-serif;
                color: #333;
                line-height: 1.6;
            }
            .container {
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
            }
            .header {
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                padding: 30px;
                border-radius: 8px 8px 0 0;
                text-align: center;
            }
            .header h2 {
                margin: 0;
                font-size: 28px;
                font-weight: 600;
            }
            .header p {
                margin: 5px 0 0 0;
                font-size: 14px;
                opacity: 0.9;
            }
            .content {
                background: #f9f9f9;
                padding: 30px;
                border-radius: 0 0 8px 8px;
            }
            .greeting {
                font-size: 16px;
                margin-bottom: 20px;
            }
            .notice-box {
                background: #e3f2fd;
                border-left: 4px solid #2196f3;
                padding: 15px;
                margin: 20px 0;
                border-radius: 4px;
            }
            .notice-box strong {
                color: #1565c0;
            }
            .credentials-box {
                background: white;
                border: 2px solid #ddd;
                padding: 20px;
                margin: 20px 0;
                border-radius: 6px;
                font-family: 'Courier New', monospace;
            }
            .credentials-box .label {
                font-weight: bold;
                color: #667eea;
                font-size: 12px;
                text-transform: uppercase;
                letter-spacing: 1px;
            }
            .credentials-box .value {
                font-size: 16px;
                color: #333;
                margin: 5px 0 15px 0;
                padding: 10px;
                background: #f5f5f5;
                border-radius: 4px;
                border-left: 3px solid #667eea;
            }
            .login-section {
                background: #f0f7ff;
                padding: 15px;
                border-radius: 6px;
                margin: 20px 0;
            }
            .login-url {
                display: inline-block;
                background: white;
                padding: 10px 15px;
                border-radius: 4px;
                text-decoration: none;
                color: #667eea;
                font-weight: bold;
                margin-top: 10px;
                border: 1px solid #667eea;
            }
            .important {
                background: #fff3cd;
                border-left: 4px solid #ffc107;
                padding: 15px;
                margin: 20px 0;
                border-radius: 4px;
                color: #856404;
            }
            .important strong {
                color: #856404;
            }
            .steps {
                background: white;
                padding: 15px;
                border-radius: 6px;
                margin: 20px 0;
            }
            .steps h3 {
                color: #667eea;
                margin-top: 0;
            }
            .steps ol {
                margin: 10px 0;
                padding-left: 20px;
            }
            .steps li {
                margin: 8px 0;
                color: #555;
            }
            .footer {
                text-align: center;
                color: #999;
                font-size: 12px;
                margin-top: 30px;
                padding-top: 20px;
                border-top: 1px solid #ddd;
            }
            .divider {
                border-top: 2px solid #e0e0e0;
                margin: 20px 0;
            }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h2>Welcome to SDIRS!</h2>
                <p>Student Discipline and Incident Reporting System</p>
            </div>

            <div class='content'>
                <div class='greeting'>
                    <p>Hello <strong>" . htmlspecialchars($name) . "</strong>,</p>
                    <p>Your <strong>" . htmlspecialchars($role) . "</strong> account has been successfully created in the Student Discipline and Incident Reporting System (SDIRS).</p>
                </div>

                <div class='notice-box'>
                    <strong>📌 Important Notice:</strong><br>
                    Your account credentials have been created. Please keep them safe and secure. Do not share your credentials with anyone.
                </div>

                <h3 style='color: #043915; margin-top: 25px;'>Your Account Credentials:</h3>
                <div class='credentials-box'>
                    <div class='label'>Username / Full Name</div>
                    <div class='value'>" . htmlspecialchars($name) . "</div>

                    <div class='label'>Password</div>
                    <div class='value'>" . htmlspecialchars($password) . "</div>
                </div>

                <div class='login-section'>
                    <h3 style='color: #667eea; margin-top: 0;'>Access Your Account:</h3>
                    <p>Use the credentials above to login to SDIRS:</p>
                    <a href='" . htmlspecialchars($loginUrl) . "' class='login-url'>Go to Login Page</a>
                    <p style='font-size: 12px; color: #666; margin-top: 10px;'>Or visit: <code>" . htmlspecialchars($loginUrl) . "</code></p>
                </div>

                <div class='steps'>
                    <h3>First Login Steps:</h3>
                    <ol>
                        <li>Visit the login page using the link above</li>
                        <li>Enter your username and password (credentials above)</li>
                        <li><strong>You will be prompted to change your credentials</strong> for security</li>
                        <li>Create a new, secure username and password</li>
                        <li>Access your " . htmlspecialchars($role) . " dashboard</li>
                    </ol>
                </div>

                <div class='important'>
                    <strong>⚠️ Security Reminder:</strong><br>
                    On your first login, you will be required to change your username and password to something only you know. This is for security purposes. Please create a strong password with uppercase, lowercase, numbers, and special characters.
                </div>

                <div class='divider'></div>

                <p style='color: #666; font-size: 14px;'>
                    If you have any questions or need assistance, please contact your administrator.
                </p>

                <div class='footer'>
                    <p>This is an automated message. Please do not reply to this email.</p>
                    <p>&copy; 2026 Student Discipline and Incident Reporting System. All rights reserved.</p>
                </div>
            </div>
        </div>
    </body>
    </html>
    ";

    return $html;
}

/**
 * Send test email
 * Use this to test if your email configuration works
 */
function sendTestEmail($testEmail) {
    return sendCredentialsEmail(
        $testEmail,
        'Test Teacher',
        'TestPassword123!',
        'Teacher'
    );
}