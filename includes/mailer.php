<?php
/**
 * mailer.php – Email sending helper using PHPMailer.
 * Provides a function to send password reset emails via Gmail SMTP.
 */

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Load Composer autoloader for PHPMailer
require_once __DIR__ . '/../vendor/autoload.php';

function sendPasswordResetEmail($toEmail, $resetLink) {
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration using Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        // FitPeak default email account (app password)
        $mail->Username = 'fitpeakgym2026@gmail.com';
        $mail->Password = 'wglj bhev xmnb jyvt';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Sender information
        $mail->setFrom('fitpeakgym2026@gmail.com', 'FitPeak');

        // Recipient
        $mail->addAddress($toEmail);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Password Reset - FitPeak';

        $mail->Body = "
            <h2>Password Reset</h2>
            <p>Hello,</p>
            <p>Click the link below to reset your password:</p>
            <p><a href='$resetLink'>Reset Password</a></p>
            <p>This link expires in 1 hour.</p>
            <p>If you did not request this, please ignore this email.</p>
            <p>FitPeak Team</p>
        ";

        $mail->AltBody = "Click this link to reset your password: $resetLink";

        return $mail->send();

    } catch (Exception $e) {
        error_log("Email error: " . $mail->ErrorInfo);
        return false;
    }
}

function sendAccountCredentialsEmail($toEmail, $name, $username, $password, $role) {
    $mail = new PHPMailer(true);

    try {
        // SMTP configuration using Gmail
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;

        // FitPeak default email account (app password)
        $mail->Username = 'fitpeakgym2026@gmail.com';
        $mail->Password = 'wglj bhev xmnb jyvt';

        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        // Sender information
        $mail->setFrom('fitpeakgym2026@gmail.com', 'FitPeak');

        // Recipient
        $mail->addAddress($toEmail, $name);

        // Email content
        $mail->isHTML(true);
        $mail->Subject = 'Your FitPeak account credentials';

        $roleLabel = ucfirst($role);

        $safeName = htmlspecialchars($name, ENT_QUOTES, 'UTF-8');
        $safeUsername = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
        $safePassword = htmlspecialchars($password, ENT_QUOTES, 'UTF-8');
        $safeRoleLabel = htmlspecialchars($roleLabel, ENT_QUOTES, 'UTF-8');

        $mail->Body = "
            <h2>Welcome to FitPeak, {$safeName}!</h2>

            <p>Your {$safeRoleLabel} account has been created successfully.</p>

            <p>You can now log in using the following credentials:</p>

            <ul>
                <li><strong>Username:</strong> {$safeUsername}</li>
                <li><strong>Password:</strong> {$safePassword}</li>
            </ul>

            <p>For security reasons, we recommend changing your password after your first login.</p>

            <p>Best regards,<br>FitPeak Team</p>
        ";

        $mail->AltBody =
            "Welcome to FitPeak, {$name}!\n\n" .
            "Your {$roleLabel} account has been created successfully.\n\n" .
            "Username: {$username}\n" .
            "Password: {$password}\n\n" .
            "For security reasons, we recommend changing your password after your first login.\n\n" .
            "FitPeak Team";

        return $mail->send();

    } catch (Exception $e) {
        error_log("Account credentials email error: " . $mail->ErrorInfo);
        return false;
    }
}