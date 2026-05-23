<?php
session_start();
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/auth.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userRole = $_SESSION['role'] ?? '';
$userIsAdmin = ($_SESSION['is_admin'] ?? 0) == 1;
$userName = $_SESSION['user_name'] ?? 'User';
$firstName = explode(' ', trim($userName))[0] ?? $userName;

if (!$userIsAdmin || !in_array($userRole, ['member', 'trainer'])) {
    if ($userRole === 'member') header('Location: ../member/dashboard.php');
    elseif ($userRole === 'trainer') header('Location: ../trainer/dashboard.php');
    else header('Location: ../index.php');
    exit;
}

$normalAreaLabel = ($userRole === 'member') ? 'Member Area' : 'Trainer Area';
$normalAreaLink = ($userRole === 'member') ? '../member/dashboard.php' : '../trainer/dashboard.php';
$adminAreaLink = '../admin/dashboard.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Choose Area - FitPeak</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/pages/choose-area.css">
</head>
<body>
<main class="choose-role-main">
    <div class="choose-role-card">
        <h1>Welcome, <?= htmlspecialchars($firstName) ?></h1>
        <p>You have administrative privileges. Please choose how you want to proceed:</p>
        <div class="role-buttons">
            <a href="<?= htmlspecialchars($normalAreaLink) ?>" class="role-btn" data-role="<?= $userRole ?>">
                <?= htmlspecialchars($normalAreaLabel) ?>
            </a>
            <a href="<?= htmlspecialchars($adminAreaLink) ?>" class="role-btn" data-role="admin">
                Admin Area
            </a>
        </div>
        <div class="logout-link">
            <a href="logout.php">Logout</a>
        </div>
    </div>
</main>

<script>
    document.querySelectorAll('.role-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const role = this.dataset.role;
            fetch('../ajax/set_active_role.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'role=' + encodeURIComponent(role)
            }).then(() => {
                window.location.href = this.href;
            });
        });
    });
</script>
</body>
</html>