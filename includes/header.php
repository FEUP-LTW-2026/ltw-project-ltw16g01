<?php
// includes/header.php

require_once __DIR__ . '/session.php';

if (!function_exists('output_header')) {
    function output_header($pageCss) {
        $loggedIn = isset($_SESSION['user_id']);
        $userName = $_SESSION['user_name'] ?? '';
        $userRole = $_SESSION['active_role'] ?? ($_SESSION['role'] ?? '');
        $userPhoto = $_SESSION['user_photo'] ?? null;
        $userIsAdmin = !empty($_SESSION['is_admin']) || $userRole === 'admin';

        $avatarHtml = '';

        if ($loggedIn) {
            $initial = strtoupper(substr($userName, 0, 1));

            if (!empty($userPhoto)) {
                $avatarHtml = '<img src="' . htmlspecialchars($userPhoto) . '" alt="' . htmlspecialchars($userName) . '" class="avatar-img">';
            } else {
                $avatarHtml = '<span class="avatar-initial">' . htmlspecialchars($initial) . '</span>';
            }
        }

        if ($loggedIn && $isAdminArea && $userIsAdmin) {
            $headerArea = 'admin';
        } elseif ($loggedIn && $userRole === 'member') {
            $headerArea = 'member';
        } elseif ($loggedIn && $userRole === 'trainer') {
            $headerArea = 'trainer';
        } elseif ($loggedIn && $userRole === 'admin') {
            $headerArea = 'admin';
        } else {
            $headerArea = 'public';
        }

        if ($headerArea === 'member') {
            $logoLink = '../member/dashboard.php';
            $profileLink = '../member/profile.php';
        } elseif ($headerArea === 'trainer') {
            $logoLink = '../trainer/dashboard.php';
            $profileLink = '../trainer/profile.php';
        } elseif ($headerArea === 'admin') {
            $logoLink = '../admin/dashboard.php';
            $profileLink = '../admin/profile.php';
        } else {
            $logoLink = '../index.php';
            $profileLink = '../pages/login.php';
        }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="<?= htmlspecialchars($_SESSION['csrf']) ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="FitPeak — Gym management platform.">
    <meta name="theme-color" content="#0b0b0b">
    <title>FitPeak</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="../css/pages/<?= htmlspecialchars($pageCss) ?>">
</head>
<body>

<input type="checkbox" id="nav-toggle" class="nav-toggle">

<header>
    <nav class="navbar" aria-label="Main navigation">
        <a href="<?= htmlspecialchars($logoLink) ?>" class="logo">FIT<span>PEAK</span></a>

        <label for="nav-toggle" class="burger">
            <span></span>
            <span></span>
            <span></span>
        </label>

        <ul class="nav-menu">

            <?php if ($headerArea === 'member'): ?>

                <li><a href="../member/dashboard.php">Home</a></li>
                <li><a href="../member/classes.php">Classes</a></li>
                <li><a href="../member/trainers.php">Trainers</a></li>
                <li><a href="../member/equipment.php">Equipment</a></li>

                <?php if ($userIsAdmin): ?>
                    <li><a href="../pages/choose_area.php" class="nav-login">Switch Area</a></li>
                <?php endif; ?>

                <li class="nav-avatar">
                    <a href="../member/profile.php" class="avatar-link" title="My Profile">
                        <div class="avatar">
                            <?= $avatarHtml ?>
                        </div>
                    </a>
                </li>

            <?php elseif ($headerArea === 'trainer'): ?>

                <li><a href="../trainer/dashboard.php">Home</a></li>
                <li><a href="../trainer/classes.php">Classes</a></li>
                <li><a href="../trainer/trainers.php">Trainers</a></li>
                <li><a href="../trainer/equipment.php">Equipment</a></li>

                <?php if ($userIsAdmin): ?>
                    <li><a href="../pages/choose_area.php" class="nav-login">Switch Area</a></li>
                <?php endif; ?>

                <li class="nav-avatar">
                    <a href="../trainer/profile.php" class="avatar-link" title="My Profile">
                        <div class="avatar">
                            <?= $avatarHtml ?>
                        </div>
                    </a>
                </li>

            <?php elseif ($headerArea === 'admin'): ?>

                <li><a href="../admin/dashboard.php">Home</a></li>
                <li><a href="../admin/classes.php">Classes</a></li>
                <li><a href="../admin/members.php">Members</a></li>
                <li><a href="../admin/trainers.php">Trainers</a></li>
                <li><a href="../admin/pt_requests.php">PT Requests</a></li>
                <li><a href="../admin/equipment.php">Equipment</a></li>

                <?php if ($userRole === 'member' || $userRole === 'trainer'): ?>
                    <li><a href="../pages/choose_area.php" class="nav-login">Switch Area</a></li>
                <?php endif; ?>

                <li class="nav-avatar">
                    <a href="<?= htmlspecialchars($profileLink) ?>" class="avatar-link" title="My Profile">
                        <div class="avatar">
                            <?= $avatarHtml ?>
                        </div>
                    </a>
                </li>

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