<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: ../pages/login.php');
    exit;
}

if (($_SESSION['role'] ?? '') !== 'member') {
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
        $photoPath = $_POST['current_photo'] ?? null;

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload = uploadProfilePhoto($_FILES['photo']);

            if ($upload['success'] === false) {
                $error = $upload['error'];
            } else {
                $photoPath = $upload['path'];
            }
        }

        if ($error === '') {
            $result = updateBasicProfile(
                $pdo,
                $userId,
                $_POST['name'] ?? '',
                $_POST['username'] ?? '',
                $_POST['email'] ?? '',
                $photoPath
            );

            if ($result === true) {
                $_SESSION['user_name'] = trim($_POST['name'] ?? '');
                $_SESSION['user_email'] = trim($_POST['email'] ?? '');
                $_SESSION['user_photo'] = $photoPath;

                $message = 'Profile updated successfully.';
            } else {
                $error = $result;
            }
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
}

$user = getUserProfileById($pdo, $userId);

if (!$user) {
    session_destroy();
    header('Location: ../pages/login.php');
    exit;
}

output_header('profile.css');
?>

<main class="main profile-page">
    <div class="container">
        <header class="page-header">
            <nav>
                <ol class="breadcrumb">
                    <li><a href="../index.php">Home</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><a href="member-dashboard.php">Member Dashboard</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><span>Profile</span></li>
                </ol>
            </nav>
        </header>

        <?php output_profile_alert($message, 'success'); ?>
        <?php output_profile_alert($error, 'error'); ?>

        <?php output_profile_hero($user, 'Update your profile details and password.'); ?>

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