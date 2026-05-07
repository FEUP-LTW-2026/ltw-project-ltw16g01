<?php
session_start();

// If already logged in, redirect to appropriate page
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'db/connection.php';

$error   = '';
$success = '';

// --- CSRF token generation ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// --- Handle POST submission ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF check
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $identifier = trim($_POST['identifier'] ?? '');
        $password   = $_POST['password'] ?? '';

        if ($identifier === '' || $password === '') {
            $error = 'Please fill in all fields.';
        } else {
            $pdo  = getDatabaseConnection();
            $stmt = $pdo->prepare(
                'SELECT id, name, password, role, active FROM users
                 WHERE (email = :id OR username = :id)
                 LIMIT 1'
            );
            $stmt->execute([':id' => $identifier]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user || !password_verify($password, $user['password'])) {
                $error = 'Incorrect credentials. Please try again.';
            } elseif (!$user['active']) {
                $error = 'Your account has been deactivated. Contact support.';
            } else {
                // Regenerate session ID to prevent fixation
                session_regenerate_id(true);

                $_SESSION['user_id']   = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_role'] = $user['role'];

                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header('Location: admin-dashboard.php');
                } else {
                    header('Location: index.php');
                }
                exit;
            }
        }
    }
}

require_once 'includes/header.php';
output_header();
?>

    <link rel="stylesheet" href="css/pages/auth.css">

    <main class="auth-main">
        <div class="auth-card">

            <!-- Card Header -->
            <div class="auth-header" id="loginHeader">
                <a href="index.php" class="auth-logo">FIT<span>PEAK</span></a>
                <h1>Welcome Back</h1>
                <p>Sign in to access your account</p>
            </div>

            <!-- Server-side error alert -->
            <?php if ($error !== ''): ?>
                <div class="auth-alert alert-error" role="alert">
                    <span class="auth-alert-icon">✕</span>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <?php if ($success !== ''): ?>
                <div class="auth-alert alert-success" role="alert">
                    <span class="auth-alert-icon">✓</span>
                    <span><?= htmlspecialchars($success) ?></span>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form class="auth-form" method="post" action="login.php" novalidate id="loginForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <!-- Email or Username -->
                <div class="form-group">
                    <label class="form-label" for="identifier">Email or Username</label>
                    <input
                        type="text"
                        id="identifier"
                        name="identifier"
                        class="form-input"
                        placeholder="your@email.com"
                        value="<?= htmlspecialchars($_POST['identifier'] ?? '') ?>"
                        autocomplete="username"
                        required
                    >
                    <span class="field-error" id="err-identifier">Please enter your email or username.</span>
                </div>

                <!-- Password -->
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <div class="input-wrap">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            class="form-input"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                        >
                        <button type="button" class="toggle-pw" aria-label="Show password" data-target="password">👁</button>
                    </div>
                    <span class="field-error" id="err-password">Please enter your password.</span>
                </div>

                <!-- Submit -->
                <button type="submit" class="main-button full-button auth-submit">Sign In</button>
            </form>

            <div class="auth-divider">or</div>

            <div class="auth-footer">
                Don't have an account? <a href="register.php">Sign up for free</a>
            </div>

        </div>
    </main>

    <script>
    (function () {
        // Client-side validation before submit
        const form       = document.getElementById('loginForm');
        const identifier = document.getElementById('identifier');
        const password   = document.getElementById('password');

        function showError(inputEl, msgId) {
            inputEl.classList.add('input-error');
            document.getElementById(msgId).classList.add('visible');
        }
        function clearError(inputEl, msgId) {
            inputEl.classList.remove('input-error');
            document.getElementById(msgId).classList.remove('visible');
        }

        [identifier, password].forEach(el => {
            el.addEventListener('input', () => {
                const msgId = 'err-' + el.id;
                if (el.value.trim() !== '') clearError(el, msgId);
            });
        });

        form.addEventListener('submit', function (e) {
            let valid = true;
            if (identifier.value.trim() === '') { showError(identifier, 'err-identifier'); valid = false; }
            if (password.value === '')           { showError(password,   'err-password');   valid = false; }
            if (!valid) e.preventDefault();
        });

        // Password show/hide toggle
        document.querySelectorAll('.toggle-pw').forEach(btn => {
            btn.addEventListener('click', function () {
                const input = document.getElementById(this.dataset.target);
                if (input.type === 'password') {
                    input.type = 'text';
                    this.textContent = '🙈';
                    this.setAttribute('aria-label', 'Hide password');
                } else {
                    input.type = 'password';
                    this.textContent = '👁';
                    this.setAttribute('aria-label', 'Show password');
                }
            });
        });
    })();
    </script>

<?php
require_once 'includes/footer.php';
output_footer();
?>
