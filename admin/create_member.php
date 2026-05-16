<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';

requireRole('admin');

$pdo = getDatabaseConnection();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $plan = $_POST['plan'] ?? 'basic';
    $planEnd = trim($_POST['plan_end'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm = trim($_POST['confirm_password'] ?? '');

    // Validações
    if (empty($name) || empty($username) || empty($email)) {
        $error = 'Name, username and email are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Invalid email address.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } else {
        // Verificar duplicados
        $check = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $check->execute([$username, $email]);
        if ($check->fetch()) {
            $error = 'Username or email already exists.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $planStart = date('Y-m-d');
            if (empty($planEnd)) {
                $planEnd = date('Y-m-d', strtotime('+1 year'));
            }

            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("
                    INSERT INTO users (name, username, email, password, role, active)
                    VALUES (?, ?, ?, ?, 'member', 1)
                ");
                $stmt->execute([$name, $username, $email, $hash]);
                $userId = $pdo->lastInsertId();

                $stmt = $pdo->prepare("
                    INSERT INTO member_profiles (user_id, membership_plan, plan_start, plan_end)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$userId, $plan, $planStart, $planEnd]);
                $pdo->commit();

                $success = 'Member created successfully!';
                $_POST = []; // limpar formulário
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

output_header('create_member.css');
?>

<link rel="stylesheet" href="../css/pages/form.css">

<main class="main">
    <div class="container" style="max-width: 700px;">
        <header class="page-header">
            <nav>
                <ol class="breadcrumb">
                    <li><a href="members.php">Members</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><span>Add Member</span></li>
                </ol>
            </nav>
            <div class="page-header-row">
                <h1>Add New Member</h1>
                <p>Register a new gym member.</p>
            </div>
        </header>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form card" style="padding: var(--sp-lg);">
            <div class="form-group" style="margin-bottom: var(--sp-lg);">
                <label class="form-label">Full Name *</label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>

            <div class="form-row" style="margin-bottom: var(--sp-lg);">
                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($_POST['username'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($_POST['email'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row" style="margin-bottom: var(--sp-lg);">
                <div class="form-group">
                    <label class="form-label">Membership Plan</label>
                    <select name="plan" class="form-select">
                        <option value="basic" <?= (($_POST['plan'] ?? 'basic') === 'basic') ? 'selected' : '' ?>>Basic</option>
                        <option value="premium" <?= (($_POST['plan'] ?? '') === 'premium') ? 'selected' : '' ?>>Premium</option>
                        <option value="vip" <?= (($_POST['plan'] ?? '') === 'vip') ? 'selected' : '' ?>>VIP</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Plan End Date</label>
                    <input type="date" name="plan_end" class="form-control" value="<?= htmlspecialchars($_POST['plan_end'] ?? date('Y-m-d', strtotime('+1 year'))) ?>">
                </div>
            </div>

            <div class="form-row" style="margin-bottom: var(--sp-lg);">
                <div class="form-group">
                    <label class="form-label">Password *</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Confirm Password *</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
            </div>

            <div style="display: flex; gap: var(--sp-md); margin-top: var(--sp-md);">
                <button type="submit" class="main-button">Add Member</button>
                <a href="members.php" class="secondary-button">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php output_footer(); ?>