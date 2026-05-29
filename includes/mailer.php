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