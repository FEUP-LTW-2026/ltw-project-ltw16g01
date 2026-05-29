<?php
/**
 * reset_password.php – Allows a user to set a new password using a valid reset token.
 */

require_once '../includes/session.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../db/connection.php';
require_once '../db/auth.php';

$token = $_GET['token'] ?? '';
$error = '';
$success = '';

if (empty($token)) {
    die('No reset token provided.');
}

$pdo = getDatabaseConnection();
$valid = validateResetToken($pdo, $token);

if (!$valid) {
    die('Invalid or expired reset token. Please request a new password reset.');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || $_SESSION['csrf'] !== $_POST['csrf']) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }

    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        if (resetPassword($pdo, $token, $password)) {
            $success = 'Your password has been reset successfully. You can now log in.';
            header('Refresh: 3; url=login.php');
        } else {
            $error = 'Failed to reset password. Please try again.';
        }
    }
}

output_header('auth.css');
?>

<main class="auth-main">
    <div class="auth-card">
        <div class="auth-header">
            <a href="../index.php" class="auth-logo">FIT<span>PEAK</span></a>
            <h1>Set New Password</h1>
            <p>Enter your new password below</p>
        </div>

        <?php if ($error): ?>
            <div class="auth-alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="auth-alert alert-success"><?= htmlspecialchars($success) ?> Redirecting to login...</div>
        <?php endif; ?>

        <?php if (!$success): ?>
            <form method="post" class="auth-form">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

                <div class="form-group">
                    <label class="form-label">New Password</label>
                    <input type="password" name="password" class="form-control" required minlength="8">
                </div>

                <div class="form-group">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" class="form-control" required minlength="8">
                </div>

                <button type="submit" class="main-button full-button">Reset Password</button>
            </form>
        <?php endif; ?>

        <div class="auth-footer">
            <a href="login.php">← Back to login</a>
        </div>
    </div>
</main>

<?php output_footer('../public/'); ?>