<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

if (($_SESSION['role'] ?? '') !== 'trainer') {
    header('Location: ../index.php');
    exit;
}

require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../db/connection.php';
require_once '../db/profile.php';
require_once '../template/profile.php';

$pdo = getDatabaseConnection();
$userId = $_SESSION['user_id'];

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'update_basic_profile') {
        $result = updateBasicProfile(
            $pdo,
            $userId,
            $_POST['name'] ?? '',
            $_POST['username'] ?? '',
            $_POST['email'] ?? '',
            $_POST['photo'] ?? null
        );

        if ($result === true) {
            $message = 'Profile updated successfully.';
        } else {
            $error = $result;
        }
    }

    if ($action === 'update_password') {
        $result = updateUserPassword(
            $pdo,
            $userId,
            $_POST['current_password'] ?? '',
            $_POST['new_password'] ?? '',
            $_POST['confirm_password'] ?? ''
        );

        if ($result === true) {
            $message = 'Password updated successfully.';
        } else {
            $error = $result;
        }
    }

    if ($action === 'update_trainer_profile') {
        updateTrainerPublicProfile(
            $pdo,
            $userId,
            $_POST['bio'] ?? '',
            $_POST['specializations'] ?? '',
            $_POST['certifications'] ?? ''
        );

        $message = 'Public trainer profile updated successfully.';
    }
}

$user = getUserProfileById($pdo, $userId);

if (!$user) {
    session_destroy();
    header('Location: ../pages/login.php');
    exit;
}

ensureTrainerProfileExists($pdo, $userId);
$trainerProfile = getTrainerPublicProfile($pdo, $userId);

output_header('profile.css');
?>

<main class="main profile-page">
    <div class="container">
        <header class="page-header">
            <nav>
                <ol class="breadcrumb">
                    <li><a href="../index.php">Home</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><a href="trainer-dashboard.php">Trainer Dashboard</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><span>Profile</span></li>
                </ol>
            </nav>
        </header>

        <?php output_profile_alert($message, 'success'); ?>
        <?php output_profile_alert($error, 'error'); ?>

        <?php output_profile_hero($user, 'Update your account details and public trainer profile.'); ?>

        <?php output_trainer_edit_form($trainerProfile); ?>

        <?php output_basic_profile_form($user); ?>

        <?php output_password_form(); ?>

        <div class="logout-box">
            <a href="../pages/logout.php" class="secondary-button btn-sm logout-button">
                Logout
            </a>
        </div>
    </div>
</main>

<script src="../js/profile.js"></script>

<?php output_footer(); ?>