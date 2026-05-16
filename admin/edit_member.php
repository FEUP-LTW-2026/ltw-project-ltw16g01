<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';

requireRole('admin');

$pdo = getDatabaseConnection();
$id = (int)($_GET['id'] ?? 0);

// Buscar dados atuais do membro
$stmt = $pdo->prepare("
    SELECT u.id, u.name, u.username, u.email, u.active,
           mp.membership_plan, mp.plan_start, mp.plan_end
    FROM users u
    LEFT JOIN member_profiles mp ON u.id = mp.user_id
    WHERE u.id = ? AND u.role = 'member'
");
$stmt->execute([$id]);
$member = $stmt->fetch();

if (!$member) {
    header('Location: members.php?error=notfound');
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
            header('Location: members.php?msg=deleted');
            exit;
        } else {
            $error = 'Failed to delete member.';
        }
    }
    // ATUALIZAR
    else {
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $plan = $_POST['plan'] ?? 'basic';
        $planEnd = trim($_POST['plan_end'] ?? '');
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

                    // Atualizar perfil (member_profiles)
                    $stmt = $pdo->prepare("
                        UPDATE member_profiles 
                        SET membership_plan = ?, plan_end = ?
                        WHERE user_id = ?
                    ");
                    $stmt->execute([$plan, $planEnd, $id]);

                    $pdo->commit();
                    $success = 'Member updated successfully!';

                    // Recarregar dados para exibição
                    $stmt = $pdo->prepare("
                        SELECT u.id, u.name, u.username, u.email, u.active,
                               mp.membership_plan, mp.plan_start, mp.plan_end
                        FROM users u
                        LEFT JOIN member_profiles mp ON u.id = mp.user_id
                        WHERE u.id = ?
                    ");
                    $stmt->execute([$id]);
                    $member = $stmt->fetch();
                } catch (PDOException $e) {
                    $pdo->rollBack();
                    $error = 'Database error: ' . $e->getMessage();
                }
            }
        }
    }
}

output_header('edit_member.css');
?>

<link rel="stylesheet" href="../css/pages/form.css">

<main class="main">
    <div class="container" style="max-width: 700px;">
        <header class="page-header">
            <nav>
                <ol class="breadcrumb">
                    <li><a href="members.php">Members</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><span>Edit Member</span></li>
                </ol>
            </nav>
            <div class="page-header-row">
                <h1>Edit Member</h1>
                <p>Update member information and membership details.</p>
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
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($member['name']) ?>">
            </div>

            <div class="form-row" style="margin-bottom: var(--sp-lg);">
                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($member['username']) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($member['email']) ?>">
                </div>
            </div>

            <div class="form-row" style="margin-bottom: var(--sp-lg);">
                <div class="form-group">
                    <label class="form-label">Membership Plan</label>
                    <select name="plan" class="form-select">
                        <option value="basic" <?= ($member['membership_plan'] === 'basic') ? 'selected' : '' ?>>Basic</option>
                        <option value="premium" <?= ($member['membership_plan'] === 'premium') ? 'selected' : '' ?>>Premium</option>
                        <option value="vip" <?= ($member['membership_plan'] === 'vip') ? 'selected' : '' ?>>VIP</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Plan End Date</label>
                    <input type="date" name="plan_end" class="form-control" value="<?= htmlspecialchars($member['plan_end']) ?>">
                </div>
            </div>

            <div class="form-group" style="margin-bottom: var(--sp-lg);">
                <label class="form-label">Status</label>
                <select name="active" class="form-select">
                    <option value="1" <?= $member['active'] == 1 ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= $member['active'] == 0 ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>

            <div style="display: flex; gap: var(--sp-md); justify-content: space-between; margin-top: var(--sp-md);">
                <div style="display: flex; gap: var(--sp-md);">
                    <button type="submit" name="action" value="update" class="main-button">Update Member</button>
                    <a href="members.php" class="secondary-button">Cancel</a>
                </div>
            </div>
        </form>
    </div>
</main>

<?php output_footer(); ?>