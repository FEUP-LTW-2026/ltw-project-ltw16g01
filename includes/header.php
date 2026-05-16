<?php

// Se a função output_header ainda não foi definida, define-a
if (!function_exists('output_header')) {
    function output_header($pageCss) {
        // Garantir que a sessão está disponível
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $loggedIn = isset($_SESSION['user_id']);
        $userName = $_SESSION['user_name'] ?? '';
        $userRole = $_SESSION['role'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FitPeak — Gym management platform.">
    <meta name="theme-color" content="#0b0b0b">
    <title>FitPeak</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="../css/pages/<?= $pageCss ?>">
</head>
<body>

<input type="checkbox" id="nav-toggle" class="nav-toggle">

<header>
    <nav class="navbar" aria-label="Main navigation">
        <a href="../index.php" class="logo">FIT<span>PEAK</span></a>

        <label for="nav-toggle" class="burger">
            <span></span><span></span><span></span>
        </label>

        <ul class="nav-menu">
            <li><a href="../pages/index.php">Home</a></li>
            <li><a href="../pages/classes.php">Classes</a></li>
            <li><a href="../pages/trainers.php">Trainers</a></li>
            <li><a href="../pages/equipment.php">Equipment</a></li>

            <?php if ($loggedIn): ?>
                <?php if ($userRole === 'member'): ?>
                    <li><a href="../member/dashboard.php">Dashboard</a></li>
                    <li><a href="../member/profile.php">My Profile</a></li>
                <?php elseif ($userRole === 'admin'): ?>
                    <li><a href="../admin/admin-dashboard.php">Admin</a></li>
                <?php endif; ?>
                <li><a href="../pages/logout.php" class="nav-logout">Logout (<?= htmlspecialchars($userName) ?>)</a></li>
            <?php else: ?>
                <li><a href="../pages/login.php" class="nav-login">Login</a></li>
                <li><a href="../pages/register.php" class="nav-register">Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<?php
    }
}