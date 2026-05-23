<?php
session_start();

require_once '../includes/auth.php';

if (!isLoggedIn()) {
    header('Location: login.php');
    exit;
}

$userRole = $_SESSION['role'] ?? '';
$userName = $_SESSION['user_name'] ?? 'User';
$userIsAdmin = !empty($_SESSION['is_admin']) || $userRole === 'admin';

if (!$userIsAdmin) {
    if ($userRole === 'member') {
        header('Location: ../member/dashboard.php');
    } elseif ($userRole === 'trainer') {
        header('Location: ../trainer/dashboard.php');
    } elseif ($userRole === 'admin') {
        header('Location: ../admin/dashboard.php');
    } else {
        header('Location: ../index.php');
    }
    exit;
}

if ($userRole === 'member') {
    $primaryAreaLabel = 'Member Area';
    $primaryAreaLink = '../member/dashboard.php';
} elseif ($userRole === 'trainer') {
    $primaryAreaLabel = 'Trainer Area';
    $primaryAreaLink = '../trainer/dashboard.php';
} elseif ($userRole === 'admin') {
    header('Location: ../admin/dashboard.php');
    exit;
} else {
    $primaryAreaLabel = 'User Area';
    $primaryAreaLink = '../index.php';
}

$firstName = explode(' ', trim($userName))[0] ?? $userName;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Choose Area - FitPeak</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="../css/pages/choose-area.css">
</head>
<body>

<main class="choose-area-main">
    <section class="choose-area-card">
        <h1>Welcome, <?= htmlspecialchars($firstName) ?></h1>

        <p>You have multiple roles. Please choose how you want to proceed:</p>

        <div class="choose-area-actions">
            <a href="<?= htmlspecialchars($primaryAreaLink) ?>" class="choose-area-option">
                <?= htmlspecialchars($primaryAreaLabel) ?>
            </a>

            <a href="../admin/dashboard.php" class="choose-area-option">
                Admin Area
            </a>
        </div>

        <div class="choose-area-logout">
            <span>Not now?</span>
            <a href="logout.php">Logout</a>
        </div>
    </section>
</main>

</body>
</html>