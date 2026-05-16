<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';

requireRole('admin');

$pdo = getDatabaseConnection();
$id = (int)($_GET['id'] ?? 0);

// Buscar dados atuais do treinador
$stmt = $pdo->prepare("
    SELECT u.id, u.name, u.username, u.email, u.active,
           tp.bio, tp.specializations, tp.certifications
    FROM users u
    LEFT JOIN trainer_profiles tp ON u.id = tp.user_id
    WHERE u.id = ? AND u.role = 'trainer'
");
$stmt->execute([$id]);
$trainer = $stmt->fetch();

if (!$trainer) {
    header('Location: trainers.php?error=notfound');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // ELIMINAR (soft delete)
    if ($action === 'delete') {
        $stmt = $pdo->prepare("UPDATE users SET active = 0 WHERE id = ?");
        if ($stmt->execute([$id])) {
            header('Location: trainers.php?msg=deleted');
            exit;
        } else {
            $error = 'Failed to delete trainer.';
        }
    }
    // ATUALIZAR
    else {
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $specializations = trim($_POST['specializations'] ?? '');
        $certifications = trim($_POST['certifications'] ?? '');
        $active = (int)($_POST['active'] ?? 1);
        $password = trim($_POST['password'] ?? '');
        $confirm = trim($_POST['confirm_password'] ?? '');

        // Validações
        if (empty($name) || empty($username) || empty($email)) {
            $error = 'Name, username and email are required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email address.';
        } elseif (!empty($password) && $password !== $confirm) {
            $error = 'Passwords do not match.';
        } elseif (!empty($password) && strlen($password) < 6) {
            $error = 'Password must be at least 6 characters.';
        } else {
            // Verificar duplicados (ignorando o próprio)
            $check = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id != ?");
            $check->execute([$username, $email, $id]);
            if ($check->fetch()) {
                $error = 'Username or email already in use.';
            } else {
                try {
                    $pdo->beginTransaction();

                    // Atualizar dados base
                    if (!empty($password)) {
                        $hash = password_hash($password, PASSWORD_DEFAULT);
                        $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, email = ?, active = ?, password = ? WHERE id = ?");
                        $stmt->execute([$name, $username, $email, $active, $hash, $id]);
                    } else {
                        $stmt = $pdo->prepare("UPDATE users SET name = ?, username = ?, email = ?, active = ? WHERE id = ?");
                        $stmt->execute([$name, $username, $email, $active, $id]);
                    }

                    // Atualizar perfil (trainer_profiles)
                    $stmt = $pdo->prepare("
                        INSERT INTO trainer_profiles (user_id, bio, specializations, certifications)
                        VALUES (?, ?, ?, ?)
                        ON CONFLICT(user_id) DO UPDATE SET
                            bio = excluded.bio,
                            specializations = excluded.specializations,
                            certifications = excluded.certifications
                    ");
                    $stmt->execute([$id, $bio, $specializations, $certifications]);

                    $pdo->commit();
                    $success = 'Trainer updated successfully!';

                    // Recarregar dados para exibição
                    $stmt = $pdo->prepare("
                        SELECT u.id, u.name, u.username, u.email, u.active,
                               tp.bio, tp.specializations, tp.certifications
                        FROM users u
                        LEFT JOIN trainer_profiles tp ON u.id = tp.user_id
                        WHERE u.id = ?
                    ");
                    $stmt->execute([$id]);
                    $trainer = $stmt->fetch();
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }
}

output_header('edit_trainer.css');
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
                <h1>Update Trainer</h1>
                <p>Update the information of an existing fitness instructor.</p>
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
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($trainer['name']) ?>">
            </div>

            <div class="form-row" style="margin-bottom: var(--sp-lg);">
                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($trainer['username']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($trainer['email']) ?>">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: var(--sp-lg);">
                <label class="form-label">Bio</label>
                <textarea name="bio" class="form-control" rows="3"><?= htmlspecialchars($trainer['bio'] ?? '') ?></textarea>
            </div>

            <div class="form-group" style="margin-bottom: var(--sp-lg);">
                <label class="form-label">Specializations (comma separated)</label>
                <input type="text" name="specializations" class="form-control" value="<?= htmlspecialchars($trainer['specializations'] ?? '') ?>" placeholder="e.g. Yoga, HIIT, Strength">
            </div>

            <div class="form-group" style="margin-bottom: var(--sp-lg);">
                <label class="form-label">Certifications</label>
                <input type="text" name="certifications" class="form-control" value="<?= htmlspecialchars($trainer['certifications'] ?? '') ?>">
            </div>

            <div class="form-group" style="margin-bottom: var(--sp-lg);">
                <label class="form-label">Status</label>
                <select name="active" class="form-select">
                    <option value="1" <?= $trainer['active'] == 1 ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= $trainer['active'] == 0 ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <div style="display: flex; gap: var(--sp-md); justify-content: space-between; margin-top: var(--sp-md);">
                <div style="display: flex; gap: var(--sp-md);">
                    <button type="submit" name="action" value="update" class="main-button">Update Trainer</button>
                    <a href="trainers.php" class="secondary-button">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</main>

<?php output_footer(); ?>