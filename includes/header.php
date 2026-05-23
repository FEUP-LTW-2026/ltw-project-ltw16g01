<?php
// includes/header.php

if (!function_exists('output_header')) {
    function output_header($pageCss) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $loggedIn = isset($_SESSION['user_id']);
        $activeRole = $_SESSION['active_role'] ?? $_SESSION['role'] ?? null;
        $userName = $_SESSION['user_name'] ?? '';
        $userPhoto = $_SESSION['user_photo'] ?? null;
        $userRoles = $_SESSION['user_roles'] ?? [];

        // Determina a página actual para destacar o menu
        $currentFile = basename($_SERVER['SCRIPT_FILENAME']);
        $currentPath = $_SERVER['REQUEST_URI'];

        // Função para gerar o avatar
        function getAvatarHtml($name, $photo = null) {
            $initial = strtoupper(substr($name, 0, 1));
            if (!empty($photo)) {
                return '<img src="' . htmlspecialchars($photo) . '" alt="' . htmlspecialchars($name) . '" class="avatar-img">';
            }
            return '<span class="avatar-initial">' . htmlspecialchars($initial) . '</span>';
        }
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
    <?php if ($pageCss): ?>
    <link rel="stylesheet" href="../css/pages/<?= htmlspecialchars($pageCss) ?>">
    <?php endif; ?>
</head>
<body>

<input type="checkbox" id="nav-toggle" class="nav-toggle">

<header>
    <nav class="navbar" aria-label="Main navigation">
        <?php if ($loggedIn && $activeRole): ?>
            <a href="../<?= $activeRole ?>/dashboard.php" class="logo">FIT<span>PEAK</span></a>
        <?php else: ?>
            <a href="../index.php" class="logo">FIT<span>PEAK</span></a>
        <?php endif; ?>

        <label for="nav-toggle" class="burger">
            <span></span><span></span><span></span>
        </label>

        <ul class="nav-menu">
            <?php if ($loggedIn && $activeRole): ?>
                <?php if ($activeRole === 'member'): ?>
                    <li><a href="../member/dashboard.php" class="<?= $currentFile === 'dashboard.php' ? 'active' : '' ?>">Home</a></li>
                    <li><a href="../member/classes.php" class="<?= $currentFile === 'classes.php' ? 'active' : '' ?>">Classes</a></li>
                    <li><a href="../member/trainers.php" class="<?= $currentFile === 'trainers.php' ? 'active' : '' ?>">Trainers</a></li>
                    <li><a href="../member/equipment.php" class="<?= $currentFile === 'equipment.php' ? 'active' : '' ?>">Equipment</a></li>
                <?php elseif ($activeRole === 'admin'): ?>
                    <li><a href="../admin/dashboard.php" class="<?= $currentFile === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
                    <li><a href="../admin/classes.php" class="<?= $currentFile === 'classes.php' ? 'active' : '' ?>">Classes</a></li>
                    <li><a href="../admin/trainers.php" class="<?= $currentFile === 'trainers.php' ? 'active' : '' ?>">Trainers</a></li>
                    <li><a href="../admin/equipment.php" class="<?= $currentFile === 'equipment.php' ? 'active' : '' ?>">Equipment</a></li>
                    <li><a href="../admin/members.php" class="<?= $currentFile === 'members.php' ? 'active' : '' ?>">Members</a></li>
                    <li><a href="../admin/reports.php" class="<?= $currentFile === 'reports.php' ? 'active' : '' ?>">Reports</a></li>
                <?php elseif ($activeRole === 'trainer'): ?>
                    <li><a href="../trainer/dashboard.php" class="<?= $currentFile === 'dashboard.php' ? 'active' : '' ?>">Dashboard</a></li>
                    <li><a href="../trainer/classes.php" class="<?= $currentFile === 'classes.php' ? 'active' : '' ?>">My Classes</a></li>
                    <li><a href="../trainer/equipment.php" class="<?= $currentFile === 'equipment.php' ? 'active' : '' ?>">Equipment</a></li>
                <?php endif; ?>

                <!-- Switch role if user has multiple roles -->
                <?php if (count($userRoles) > 1): ?>
                    <li><a href="../pages/choose_role.php" class="switch-role-link">🔄 Switch Role</a></li>
                <?php endif; ?>

                <!-- Avatar / Profile -->
                <li class="nav-avatar">
                    <a href="../<?= $activeRole ?>/profile.php" class="avatar-link" title="My Profile">
                        <div class="avatar">
                            <?= getAvatarHtml($userName, $userPhoto) ?>
                        </div>
                    </a>
                </li>
            <?php else: ?>
                <!-- Not logged in -->
                <li><a href="../index.php" class="<?= $currentFile === 'index.php' ? 'active' : '' ?>">Home</a></li>
                <li><a href="../pages/classes.php" class="<?= $currentFile === 'classes.php' ? 'active' : '' ?>">Classes</a></li>
                <li><a href="../pages/trainers.php" class="<?= $currentFile === 'trainers.php' ? 'active' : '' ?>">Trainers</a></li>
                <li><a href="../pages/equipment.php" class="<?= $currentFile === 'equipment.php' ? 'active' : '' ?>">Equipment</a></li>
                <li><a href="../pages/login.php" class="nav-login">Login</a></li>
                <li><a href="../pages/register.php" class="nav-register">Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<?php
    }
}
?>