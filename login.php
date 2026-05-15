<?php
session_start();

require_once 'db/connection.php';
require_once 'includes/header.php';
require_once 'includes/footer.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier']);
    $password = $_POST['password'];

    if ($identifier === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        $pdo = getDatabaseConnection();

        $stmt = $pdo->prepare("
            SELECT id, name, password, role, active
            FROM users
            WHERE email = ? OR username = ?
        ");
        $stmt->execute([$identifier, $identifier]);

        $user = $stmt->fetch();

        if (!$user || $password !== $user['password']) {
            $error = 'Incorrect credentials. Please try again.';
        } elseif ($user['active'] == 0) {
            $error = 'Your account has been deactivated.';
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];

            if ($user['role'] === 'admin') {
                header('Location: admin-dashboard.php');
            } else {
                header('Location: index.php');
            }

            exit;
        }
    }
}

output_header('auth.css');
?>

<main class="auth-main">
    <div class="auth-card">

        <div class="auth-header">
            <a href="index.php" class="auth-logo">FIT<span>PEAK</span></a>
            <h1>Welcome Back</h1>
            <p>Sign in to access your account</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="auth-alert alert-error">
                <span class="auth-alert-icon">✕</span>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form class="auth-form" method="post" action="login.php" id="loginForm">

            <div class="form-group">
                <label class="form-label" for="identifier">Email or Username</label>
                <input
                    type="text"
                    id="identifier"
                    name="identifier"
                    class="form-input"
                    placeholder="your@email.com"
                    value="<?= htmlspecialchars($_POST['identifier'] ?? '') ?>"
                    required
                >
                <span class="field-error" id="err-identifier">
                    Please enter your email or username.
                </span>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>

                <div class="input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        placeholder="••••••••"
                        required
                    >
                    <button type="button" class="toggle-pw" data-target="password">👁</button>
                </div>

                <span class="field-error" id="err-password">
                    Please enter your password.
                </span>
            </div>

            <button type="submit" class="main-button full-button auth-submit">
                Sign In
            </button>

        </form>

        <div class="auth-divider">or</div>

        <div class="auth-footer">
            Don't have an account? <a href="register.php">Sign up for free</a>
        </div>

    </div>
</main>

<script src="js/login.js"></script>

<?php
output_footer();
?>