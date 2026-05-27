<?php
require_once '../includes/session.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../db/connection.php';
require_once '../db/auth.php';
require_once '../includes/mailer.php';

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');

    if (empty($email)) {
        $error = 'Please enter your email address.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } else {
        $pdo = getDatabaseConnection();
        $token = createPasswordResetToken($pdo, $email);

        if ($token !== false) {
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
            $host = $_SERVER['HTTP_HOST'];
            $base = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/\\');

            $resetLink = $protocol . $host . $base . '/reset_password.php?token=' . urlencode($token);

            sendPasswordResetEmail($email, $resetLink);
        }

        $success = 'If that email address is in our system, we have sent a password reset link.';
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