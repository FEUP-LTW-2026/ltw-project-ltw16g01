<?php
require_once '../includes/session.php';

require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userRole = $_SESSION['role'] ?? '';
$userIsAdmin = (int)($_SESSION['is_admin'] ?? 0) == 1;
$userName = $_SESSION['user_name'] ?? 'User';
$firstName = explode(' ', trim($userName))[0] ?? $userName;

if (!$userIsAdmin || !in_array($userRole, ['member', 'trainer'])) {
    if ($userRole === 'member') {
        header('Location: ../member/dashboard.php');
    } elseif ($userRole === 'trainer') {
        header('Location: ../trainer/dashboard.php');
    } else {
        header('Location: ../index.php');
    }
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
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf']) ?>">
    <link rel="stylesheet" href="../css/pages/choose-area.css">
</head>

<body>
    <main class="choose-role-main">
        <div class="choose-role-card">
            <h1>Welcome, <?= htmlspecialchars($firstName) ?></h1>

            <p>You have administrative privileges. Please choose how you want to proceed:</p>

            <div class="role-buttons">
                <a 
                    href="<?= htmlspecialchars($normalAreaLink) ?>" 
                    class="role-btn" 
                    data-role="<?= htmlspecialchars($userRole) ?>"
                >
                    <?= htmlspecialchars($normalAreaLabel) ?>
                </a>

                <a 
                    href="<?= htmlspecialchars($adminAreaLink) ?>" 
                    class="role-btn" 
                    data-role="admin"
                >
                    Admin Area
                </a>
            </div>

            <div class="logout-link">
                <a href="logout.php">Logout</a>
            </div>
        </div>
    </main>

    <script src="../js/choose-area.js"></script>
</body>
</html>