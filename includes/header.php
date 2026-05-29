<?php
/**
 * header.php – Reusable page header.
 * Determines the current area (member/trainer/admin/public) based on session data,
 * builds the navigation menu, and outputs the HTML head section.
 */

require_once __DIR__ . '/session.php';

if (!function_exists('output_header')) {
    function output_header($pageCss) {
        // Load session data
        $loggedIn = isset($_SESSION['user_id']);
        $userName = $_SESSION['user_name'] ?? '';
        $originalRole = $_SESSION['role'] ?? '';
        $userPhoto = $_SESSION['user_photo'] ?? null;
        $userIsAdmin = !empty($_SESSION['is_admin']) || $originalRole === 'admin';

        // Determine which area to display in the menu (active_role overrides original role)
        $activeRole = $_SESSION['active_role'] ?? '';
        if (!$loggedIn) {
            $headerArea = 'public';
        } elseif ($activeRole === 'member') {
            $headerArea = 'member';
        } elseif ($activeRole === 'trainer') {
            $headerArea = 'trainer';
        } elseif ($activeRole === 'admin') {
            $headerArea = 'admin';
        } else {
            if ($userIsAdmin) {
                $headerArea = 'admin';
            } elseif ($originalRole === 'member') {
                $headerArea = 'member';
            } elseif ($originalRole === 'trainer') {
                $headerArea = 'trainer';
            } else {
                $headerArea = 'public';
            }
        }

        // Build avatar HTML for the logged‑in user (photo or initial)
        $avatarHtml = '';
        if ($loggedIn) {
            $initial = strtoupper(substr($userName, 0, 1));
            if (!empty($userPhoto)) {
                $avatarHtml = '<img src="' . htmlspecialchars($userPhoto) . '" alt="' . htmlspecialchars($userName) . '" class="avatar-img">';
            } else {
                $avatarHtml = '<span class="avatar-initial">' . htmlspecialchars($initial) . '</span>';
            }
        }

        // Set appropriate links for logo and profile according to the current area
        switch ($headerArea) {
            case 'member':
                $logoLink = '../member/dashboard.php';
                $profileLink = '../member/profile.php';
                break;
            case 'trainer':
                $logoLink = '../trainer/dashboard.php';
                $profileLink = '../trainer/profile.php';
                break;
            case 'admin':
                $logoLink = '../admin/dashboard.php';
                $profileLink = '../admin/profile.php';
                break;
            default:
                $logoLink = '../index.php';
                $profileLink = '../public/login.php';
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

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Core CSS files -->
    <link rel="stylesheet" href="../css/base.css">
    <link rel="stylesheet" href="../css/components.css">
    <link rel="stylesheet" href="../css/pages/<?= htmlspecialchars($pageCss) ?>">
</head>
<body>

<!-- Hidden checkbox for mobile menu toggling -->
<input type="checkbox" id="nav-toggle" class="nav-toggle">

<header>
    <nav class="navbar" aria-label="Main navigation">
        <!-- Logo that links to the appropriate dashboard or homepage -->
        <a href="<?= htmlspecialchars($logoLink) ?>" class="logo">FIT<span>PEAK</span></a>

        <!-- Mobile burger menu icon -->
        <label for="nav-toggle" class="burger">
            <span></span><span></span><span></span>
        </label>

        <!-- Main navigation list (depends on headerArea) -->
        <ul class="nav-menu">
            <?php if ($headerArea === 'member'): ?>
                <li><a href="../member/dashboard.php">Home</a></li>
                <li><a href="../member/classes.php">Classes</a></li>
                <li><a href="../member/trainers.php">Trainers</a></li>
                <li><a href="../member/equipment.php">Equipment</a></li>
                <li><a href="../member/notifications.php">Notifications</a></li>
                <?php if ($userIsAdmin && $originalRole !== 'admin'): ?>
                    <li><a href="../public/choose_area.php" class="nav-login">Switch Area</a></li>
                <?php endif; ?>
                <li class="nav-avatar">
                    <a href="../member/profile.php" class="avatar-link" title="My Profile">
                        <div class="avatar"><?= $avatarHtml ?></div>
                    </a>
                </li>

            <?php elseif ($headerArea === 'trainer'): ?>
                <li><a href="../trainer/dashboard.php">Home</a></li>
                <li><a href="../trainer/classes.php">Classes</a></li>
                <li><a href="../trainer/trainers.php">Trainers</a></li>
                <li><a href="../trainer/equipment.php">Equipment</a></li>
                <li><a href="../trainer/notifications.php">Notifications</a></li>
                <?php if ($userIsAdmin && $originalRole !== 'admin'): ?>
                    <li><a href="../public/choose_area.php" class="nav-login">Switch Area</a></li>
                <?php endif; ?>
                <li class="nav-avatar">
                    <a href="../trainer/profile.php" class="avatar-link" title="My Profile">
                        <div class="avatar"><?= $avatarHtml ?></div>
                    </a>
                </li>

            <?php elseif ($headerArea === 'admin'): ?>
                <li><a href="../admin/dashboard.php">Home</a></li>
                <li><a href="../admin/classes.php">Classes</a></li>
                <li><a href="../admin/members.php">Members</a></li>
                <li><a href="../admin/trainers.php">Trainers</a></li>
                <li><a href="../admin/pt_requests.php">PT Requests</a></li>
                <li><a href="../admin/equipment.php">Equipment</a></li>
                <?php if ($userIsAdmin && $originalRole !== 'admin'): ?>
                    <li><a href="../public/choose_area.php" class="nav-login">Switch Area</a></li>
                <?php endif; ?>
                <li class="nav-avatar">
                    <a href="<?= htmlspecialchars($profileLink) ?>" class="avatar-link" title="My Profile">
                        <div class="avatar"><?= $avatarHtml ?></div>
                    </a>
                </li>

            <?php else: ?>
                <!-- Public area (not logged in) -->
                <li><a href="../index.php">Home</a></li>
                <li><a href="../public/classes.php">Classes</a></li>
                <li><a href="../public/trainers.php">Trainers</a></li>
                <li><a href="../public/equipment.php">Equipment</a></li>
                <li><a href="../public/login.php" class="nav-login">Login</a></li>
                <li><a href="../public/register.php" class="nav-register">Sign Up</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>
<?php
    }
}
?>