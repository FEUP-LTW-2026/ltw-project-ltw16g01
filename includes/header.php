<?php
// includes/header.php

if (!function_exists('output_header')) {
    function output_header($pageCss) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        $loggedIn = isset($_SESSION['user_id']);
        $userName = $_SESSION['user_name'] ?? '';
        $userRole = $_SESSION['role'] ?? '';
        $userPhoto = $_SESSION['user_photo'] ?? null; // Garantir que a foto é guardada no login

        // Função auxiliar para gerar o avatar (inicial ou imagem)
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
    <link rel="stylesheet" href="../css/pages/<?= $pageCss ?>">
</head>
<body>

<input type="checkbox" id="nav-toggle" class="nav-toggle">

<header>
    <nav class="navbar" aria-label="Main navigation">
        <?php if ($userRole === 'member' || $userRole === 'admin' || $userRole === 'trainer'): ?>
            <a href="dashboard.php" class="logo">FIT<span>PEAK</span></a>
        <?php else: ?>
            <a href="../index.php" class="logo">FIT<span>PEAK</span></a>
        <?php endif; ?>
        <label for="nav-toggle" class="burger">
            <span></span><span></span><span></span>
        </label>

        <ul class="nav-menu">
            <?php if ($loggedIn): ?>
                <?php if ($userRole === 'member'): ?>
                    <li><a href="../member/dashboard.php">Home</a></li>
                    <li><a href="../member/classes.php">Classes</a></li>
                    <li><a href="../member/trainers.php">Trainers</a></li>
                    <li><a href="../member/equipment.php">Equipment</a></li>
                    <li class="nav-avatar">
                    <a href="../member/profile.php" class="avatar-link" title="My Profile">
                        <div class="avatar">
                            <?= getAvatarHtml($userName, $userPhoto) ?>
                        </div>
                    </a>
                </li>
                <?php elseif ($userRole === 'admin'): ?>
                    <li><a href="../admin/dashboard.php">Home</a></li>
                    <li><a href="../admin/classes.php">Classes</a></li>
                    <li><a href="../admin/trainers.php">Trainers</a></li>
                    <li><a href="../admin/equipment.php">Equipment</a></li>
                    <li><a href="../admin/members.php">Members</a></li>
                    <li class="nav-avatar">
                    <a href="../admin/profile.php" class="avatar-link" title="My Profile">
                        <div class="avatar">
                            <?= getAvatarHtml($userName, $userPhoto) ?>
                        </div>
                    </a>
                </li>
                <?php elseif ($userRole === 'trainer'): ?>
                    <li><a href="../trainer/dashboard.php">Home</a></li>
                    <li><a href="../trainer/classes.php">Classes</a></li>
                    <li><a href="../trainer/trainers.php">Trainers</a></li>
                    <li><a href="../trainer/equipment.php">Equipment</a></li>
                    <li><a href="../trainer/members.php">Members</a></li>
                    <li class="nav-avatar">
                    <a href="../trainer/profile.php" class="avatar-link" title="My Profile">
                        <div class="avatar">
                            <?= getAvatarHtml($userName, $userPhoto) ?>
                        </div>
                    </a>
                </li>
                <?php endif; ?>
            <?php else: ?>
                <li><a href="../index.php">Home</a></li>
                <li><a href="../pages/classes.php">Classes</a></li>
                <li><a href="../pages/trainers.php">Trainers</a></li>
                <li><a href="../pages/equipment.php">Equipment</a></li>
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