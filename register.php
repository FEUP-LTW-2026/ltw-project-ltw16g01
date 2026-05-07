<?php
session_start();

// If already logged in, redirect
if (isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

require_once 'db/connection.php';

$error   = '';
$success = '';
$old     = []; // repopulate fields on error

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
        // Sanitise inputs
        $name     = trim($_POST['name']     ?? '');
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email']    ?? '');
        $password = $_POST['password']      ?? '';
        $confirm  = $_POST['confirm']       ?? '';

        $old = compact('name', 'username', 'email');

        // Validation
        if ($name === '' || $username === '' || $email === '' || $password === '' || $confirm === '') {
            $error = 'Please fill in all fields.';
        } elseif (strlen($name) < 2 || strlen($name) > 80) {
            $error = 'Name must be between 2 and 80 characters.';
        } elseif (!preg_match('/^[a-z0-9_]{3,30}$/i', $username)) {
            $error = 'Username must be 3–30 characters and contain only letters, numbers or underscores.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Please enter a valid email address.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirm) {
            $error = 'Passwords do not match.';
        } else {
            $pdo = getDatabaseConnection();
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // Check uniqueness
            $check = $pdo->prepare('SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1');
            $check->execute([$email, $username]);

            if ($check->fetch()) {
                $error = 'That email or username is already registered.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $pdo->beginTransaction();
                try {
                    // Insert user
                    $ins = $pdo->prepare(
                        'INSERT INTO users (name, username, email, password, role)
                         VALUES (?, ?, ?, ?, ?)'
                    );
                    $ins->execute([$name, $username, $email, $hash, 'member']);
                    $userId = $pdo->lastInsertId();

                    // Create member profile with default basic plan
                    $mp = $pdo->prepare(
                        'INSERT INTO member_profiles (user_id, membership_plan, plan_start, plan_end)
                         VALUES (?, ?, DATE("now"), DATE("now", "+1 month"))'
                    );
                    $mp->execute([$userId, 'basic']);

                    $pdo->commit();

                    // Auto-login after registration
                    session_regenerate_id(true);
                    $_SESSION['user_id']   = $userId;
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_role'] = 'member';

                    header('Location: index.php');
                    exit;

                } catch (Exception $e) {
                    $pdo->rollBack();
                    $error = 'Something went wrong. Please try again later.';
                }
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
            <div class="auth-header">
                <a href="index.php" class="auth-logo">FIT<span>PEAK</span></a>
                <h1>Create Account</h1>
                <p>Join FitPeak and start your fitness journey</p>
            </div>

            <!-- Server-side alert -->
            <?php if ($error !== ''): ?>
                <div class="auth-alert alert-error" role="alert">
                    <span class="auth-alert-icon">✕</span>
                    <span><?= htmlspecialchars($error) ?></span>
                </div>
            <?php endif; ?>

            <!-- Register Form -->
            <form class="auth-form" method="post" action="register.php" novalidate id="registerForm">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

                <!-- Full Name -->
                <div class="form-group">
                    <label class="form-label" for="name">Full Name</label>
                    <input
                        type="text"
                        id="name"
                        name="name"
                        class="form-input"
                        placeholder="Ana Silva"
                        value="<?= htmlspecialchars($old['name'] ?? '') ?>"
                        autocomplete="name"
                        required
                    >
                    <span class="field-error" id="err-name">Please enter your full name.</span>
                </div>

                <!-- Username -->
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input
                        type="text"
                        id="username"
                        name="username"
                        class="form-input"
                        placeholder="anasilva"
                        value="<?= htmlspecialchars($old['username'] ?? '') ?>"
                        autocomplete="username"
                        pattern="[a-zA-Z0-9_]{3,30}"
                        required
                    >
                    <span class="field-error" id="err-username">3–30 characters, letters, numbers and underscores only.</span>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-input"
                        placeholder="ana@email.com"
                        value="<?= htmlspecialchars($old['email'] ?? '') ?>"
                        autocomplete="email"
                        required
                    >
                    <span class="field-error" id="err-email">Please enter a valid email address.</span>
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
                            placeholder="Minimum 8 characters"
                            autocomplete="new-password"
                            required
                        >
                        <button type="button" class="toggle-pw" aria-label="Show password" data-target="password">👁</button>
                    </div>
                    <!-- Password strength indicator -->
                    <div class="password-strength" id="pwStrength">
                        <div class="strength-bar">
                            <div class="strength-fill" id="strengthFill"></div>
                        </div>
                        <span class="strength-label" id="strengthLabel"></span>
                    </div>
                    <span class="field-error" id="err-password">Password must be at least 8 characters.</span>
                </div>

                <!-- Confirm Password -->
                <div class="form-group">
                    <label class="form-label" for="confirm">Confirm Password</label>
                    <div class="input-wrap">
                        <input
                            type="password"
                            id="confirm"
                            name="confirm"
                            class="form-input"
                            placeholder="Repeat your password"
                            autocomplete="new-password"
                            required
                        >
                        <button type="button" class="toggle-pw" aria-label="Show password" data-target="confirm">👁</button>
                    </div>
                    <span class="field-error" id="err-confirm">Passwords do not match.</span>
                </div>

                <!-- Submit -->
                <button type="submit" class="main-button full-button auth-submit">Create Free Account</button>

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

    <script>
    (function () {
        const form     = document.getElementById('registerForm');
        const fields   = {
            name:     document.getElementById('name'),
            username: document.getElementById('username'),
            email:    document.getElementById('email'),
            password: document.getElementById('password'),
            confirm:  document.getElementById('confirm'),
        };

        // --- Helpers ---
        function showError(id, msg) {
            const el = document.getElementById('err-' + id);
            fields[id].classList.add('input-error');
            if (msg) el.textContent = msg;
            el.classList.add('visible');
        }
        function clearError(id) {
            fields[id].classList.remove('input-error');
            document.getElementById('err-' + id).classList.remove('visible');
        }

        // Clear error on user input
        Object.keys(fields).forEach(id => {
            fields[id].addEventListener('input', () => clearError(id));
        });

        // --- Password strength ---
        const strengthWrap  = document.getElementById('pwStrength');
        const strengthFill  = document.getElementById('strengthFill');
        const strengthLabel = document.getElementById('strengthLabel');

        fields.password.addEventListener('input', function () {
            const val = this.value;
            if (val.length === 0) {
                strengthWrap.classList.remove('visible');
                return;
            }
            strengthWrap.classList.add('visible');

            let score = 0;
            if (val.length >= 8)                       score++;
            if (/[A-Z]/.test(val) && /[a-z]/.test(val)) score++;
            if (/\d/.test(val))                         score++;
            if (/[^A-Za-z0-9]/.test(val))              score++;

            const levels = ['', 'weak', 'weak', 'fair', 'strong'];
            const labels = ['', 'Weak', 'Weak', 'Fair', 'Strong'];
            const level  = levels[score] || 'weak';

            strengthFill.className  = 'strength-fill ' + level;
            strengthLabel.className = 'strength-label ' + level;
            strengthLabel.textContent = labels[score] || 'Weak';
        });

        // --- Live confirm match check ---
        fields.confirm.addEventListener('input', function () {
            if (this.value && this.value !== fields.password.value) {
                showError('confirm', 'Passwords do not match.');
            } else {
                clearError('confirm');
            }
        });

        // --- Submit validation ---
        form.addEventListener('submit', function (e) {
            let valid = true;

            if (fields.name.value.trim().length < 2) {
                showError('name', 'Please enter your full name.'); valid = false;
            }
            if (!/^[a-zA-Z0-9_]{3,30}$/.test(fields.username.value.trim())) {
                showError('username', '3–30 characters, letters, numbers and underscores only.'); valid = false;
            }
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(fields.email.value.trim())) {
                showError('email', 'Please enter a valid email address.'); valid = false;
            }
            if (fields.password.value.length < 8) {
                showError('password', 'Password must be at least 8 characters.'); valid = false;
            }
            if (fields.confirm.value !== fields.password.value) {
                showError('confirm', 'Passwords do not match.'); valid = false;
            }

            if (!valid) e.preventDefault();
        });

        // --- Password show/hide toggle ---
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
