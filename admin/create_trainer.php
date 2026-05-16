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
    $bio = trim($_POST['bio'] ?? '');
    $specializations = trim($_POST['specializations'] ?? '');
    $certifications = trim($_POST['certifications'] ?? '');
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
            try {
                $pdo->beginTransaction();
                $stmt = $pdo->prepare("
                    INSERT INTO users (name, username, email, password, role, active)
                    VALUES (?, ?, ?, ?, 'trainer', 1)
                ");
                $stmt->execute([$name, $username, $email, $hash]);
                $userId = $pdo->lastInsertId();

                $stmt = $pdo->prepare("
                    INSERT INTO trainer_profiles (user_id, bio, specializations, certifications)
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$userId, $bio, $specializations, $certifications]);
                $pdo->commit();

                $success = 'Trainer created successfully!';
                $_POST = []; // limpar formulário
            } catch (PDOException $e) {
                $pdo->rollBack();
                $error = 'Database error: ' . $e->getMessage();
            }
        }
    }
}

// Garantir que o CSS usado seja o específico (ou form.css)
output_header('create_trainer.css');
?>

<link rel="stylesheet" href="../css/pages/form.css">

<main class="main">
    <div class="container" style="max-width: 700px;">
        <header class="page-header">
            <nav>
                <ol class="breadcrumb">
                    <li><a href="trainers.php">Trainers</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><span>Add Trainer</span></li>
                </ol>
            </nav>
            <div class="page-header-row">
                <h1>Add New Trainer</h1>
                <p>Register a new fitness instructor.</p>
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

            <div class="form-group" style="margin-bottom: var(--sp-lg);">
                <label class="form-label">Bio</label>
                <textarea name="bio" class="form-control" rows="3"><?= htmlspecialchars($_POST['bio'] ?? '') ?></textarea>
            </div>

            <div class="form-group" style="margin-bottom: var(--sp-lg);">
                <label class="form-label">Specializations (comma separated)</label>
                <input type="text" name="specializations" class="form-control" value="<?= htmlspecialchars($_POST['specializations'] ?? '') ?>" placeholder="e.g. Yoga, HIIT, Strength">
            </div>

            <div class="form-group" style="margin-bottom: var(--sp-lg);">
                <label class="form-label">Certifications</label>
                <input type="text" name="certifications" class="form-control" value="<?= htmlspecialchars($_POST['certifications'] ?? '') ?>">
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
                <button type="submit" class="main-button">Add Trainer</button>
                <a href="trainers.php" class="secondary-button">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php output_footer(); ?>