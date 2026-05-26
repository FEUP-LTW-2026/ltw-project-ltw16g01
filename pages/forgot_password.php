<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../db/connection.php';
require_once '../db/auth.php';

$error = '';
$success = '';
$resetLink = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        $pdo = getDatabaseConnection();
        $token = createPasswordResetToken($pdo, $email);
        if ($token === false) {
            $success = 'If that email address is in our system, we have sent a password reset link.';
        } else {
            // Construir link absoluto
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'];
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');
            $resetLink = $protocol . $host . $base . '/reset_password.php?token=' . urlencode($token);
            
            // Enviar email (opcional – pode ser mantido)
            $subject = 'Password Reset - FitPeak';
            $message = "Hello,\n\nClick the link below to reset your password:\n$resetLink\n\nThis link expires in 1 hour.\n\nIf you did not request this, please ignore this email.\n\nFitPeak Team";
            $headers = 'From: no-reply@fitpeak.com' . "\r\n" .
                       'Reply-To: support@fitpeak.com' . "\r\n" .
                       'X-Mailer: PHP/' . phpversion();
            mail($email, $subject, $message, $headers);
            
            $success = 'A reset link has been sent to your email address.';
        }
    }
}

output_header('auth.css');
?>

<main class="auth-main">
    <div class="auth-card">
        <div class="auth-header">
            <a href="../index.php" class="auth-logo">FIT<span>PEAK</span></a>
            <h1>Reset Password</h1>
            <p>We'll send you a link to reset your password</p>
        </div>

        <?php if ($error): ?>
            <div class="auth-alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="auth-alert alert-success" style="text-align: center;">
                <?= htmlspecialchars($success) ?>
                <?php if ($resetLink): ?>
                    <br><br>
                    <a href="<?= htmlspecialchars($resetLink) ?>" class="main-button" style="display: inline-block; margin-top: 10px;">
                        🔑 Go to Reset Password Page
                    </a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form method="post" class="auth-form">
            <div class="form-group">
                <label class="form-label">Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>
            <button type="submit" class="main-button full-button">Send Reset Link</button>
        </form>
        <?php endif; ?>

        <div class="auth-footer">
            <a href="login.php">← Back to login</a>
        </div>
    </div>
</main>

<?php output_footer('../pages/'); ?>