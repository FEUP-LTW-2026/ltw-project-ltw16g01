<?php
require_once '../includes/session.php';

require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../db/connection.php';
require_once '../db/profile.php';
require_once '../template/profile.php';

requireAuth('trainer');

// Create the database connection and get the logged-in user id.
$pdo = getDatabaseConnection();
$userId = $_SESSION['user_id'];

$message = '';
$error = '';

// Handle profile form submissions while keeping the existing CSRF protection.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || $_SESSION['csrf'] !== $_POST['csrf']) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }

    $action = $_POST['action'] ?? '';

    if ($action === 'update_basic_profile') {
        $photoPath = $_POST['current_photo'] ?? null;

        if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload = uploadProfilePhoto($_FILES['photo']);

            if ($upload['success'] === false) {
                $error = $upload['error'];
            } else {
                deleteOldProfilePhoto($_POST['current_photo'] ?? null);
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

        $result === true ? $message = 'Password updated successfully.' : $error = $result;
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

// Reload the user profile after any update.
$user = getUserProfileById($pdo, $userId);

if (!$user) {
    session_destroy();
    header('Location: ../public/login.php');
    exit;
}

// Keep session data synchronized with the database profile.
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_photo'] = $user['photo'] ?? null;

ensureTrainerProfileExists($pdo, $userId);
$trainerProfile = getTrainerPublicProfile($pdo, $userId);

output_header('profile.css');
?>

<main class="main profile-page">
    <div class="container">
        <header class="page-header">
        </header>

        <?php output_profile_alert($message, 'success'); ?>
        <?php output_profile_alert($error, 'error'); ?>
        <?php output_profile_hero($user, 'Update your account details and public trainer profile.'); ?>
        <?php output_trainer_edit_form($trainerProfile); ?>
        <?php output_basic_profile_form($user); ?>
        <?php output_password_form(); ?>

        <div class="logout-box">
            <a href="../public/logout.php" id="logout-button" class="secondary-button btn-sm logout-button">Logout</a>
        </div>
    </div>
</main>

<script src="../js/profile.js"></script>

<?php output_footer('../trainer/'); ?>