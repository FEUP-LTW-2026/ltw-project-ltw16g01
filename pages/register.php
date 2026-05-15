<?php
session_start();

require_once '../db/connection.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';

$error = '';
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm = $_POST['confirm'];

    $old = [
        'name' => $name,
        'username' => $username,
        'email' => $email
    ];

    if ($name === '' || $username === '' || $email === '' || $password === '' || $confirm === '') {
        $error = 'Please fill in all fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $pdo = getDatabaseConnection();

        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? OR username = ?");
        $stmt->execute([$email, $username]);

        if ($stmt->fetch()) {
            $error = 'That email or username is already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare("
                INSERT INTO users (name, username, email, password, role)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $username, $email, $hash, 'member']);

            $userId = $pdo->lastInsertId();

            $stmt = $pdo->prepare("
                INSERT INTO member_profiles (user_id, membership_plan, plan_start, plan_end)
                VALUES (?, ?, DATE('now'), DATE('now', '+1 month'))
            ");
            $stmt->execute([$userId, 'basic']);

            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $name;
            $_SESSION['role'] = 'member';

            header('Location: index.php');
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
            <h1>Create Account</h1>
            <p>Join FitPeak and start your fitness journey</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="auth-alert alert-error">
                <span class="auth-alert-icon">✕</span>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <form class="auth-form" method="post" action="register.php" id="registerForm">

            <div class="form-group">
                <label class="form-label" for="name">Full Name</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-input"
                    placeholder="Ana Silva"
                    value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                    required
                >
                <span class="field-error" id="err-name">Please enter your full name.</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    class="form-input"
                    placeholder="anasilva"
                    value="<?= htmlspecialchars($old['username'] ?? '') ?>"
                    required
                >
                <span class="field-error" id="err-username">Username must have at least 3 characters.</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">Email Address</label>
                <input
                    type="email"
                    id="email"
                    name="email"
                    class="form-input"
                    placeholder="ana@email.com"
                    value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                    required
                >
                <span class="field-error" id="err-email">Please enter a valid email address.</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">Password</label>

                <div class="input-wrap">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-input"
                        placeholder="Minimum 8 characters"
                        required
                    >
                    <button type="button" class="toggle-pw" data-target="password">👁</button>
                </div>

                <div class="password-strength" id="pwStrength">
                    <div class="strength-bar">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                    <span class="strength-label" id="strengthLabel"></span>
                </div>

                <span class="field-error" id="err-password">Password must be at least 8 characters.</span>
            </div>

            <div class="form-group">
                <label class="form-label" for="confirm">Confirm Password</label>

                <div class="input-wrap">
                    <input
                        type="password"
                        id="confirm"
                        name="confirm"
                        class="form-input"
                        placeholder="Repeat your password"
                        required
                    >
                    <button type="button" class="toggle-pw" data-target="confirm">👁</button>
                </div>

                <span class="field-error" id="err-confirm">Passwords do not match.</span>
            </div>

            <button type="submit" class="main-button full-button auth-submit">
                Create Free Account
            </button>

            <p class="auth-terms">
                By signing up, you agree to our
                <a href="#">Terms of Service</a> and <a href="#">Privacy Policy</a>.
            </p>

        </form>

        <div class="auth-divider">already a member?</div>

        <div class="auth-footer">
            <a href="login.php">Sign in to your account</a>
        </div>

    </div>
</main>

<script src="../js/register.js"></script>

<?php
output_footer();
?>