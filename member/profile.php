<?php
/**
 * profile.php – Member profile page.
 * Allows editing personal info, changing password, and uploading a profile photo.
 */

require_once '../includes/session.php';

require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../db/connection.php';
require_once '../db/profile.php';
require_once '../template/profile.php';

// Only logged‑in members
requireAuth('member');

$pdo = getDatabaseConnection();
$userId = $_SESSION['user_id'];
$message = '';
$error = '';

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    if (!isset($_POST['csrf']) || $_SESSION['csrf'] !== $_POST['csrf']) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }

    $action = $_POST['action'] ?? '';

    // Update basic profile (name, username, email, photo)
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

    // Update password
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
}

// Get fresh user data
$user = getUserProfileById($pdo, $userId);

if (!$user) {
    session_destroy();
    header('Location: ../pages/login.php');
    exit;
}

// Update session with latest data
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_photo'] = $user['photo'] ?? null;

output_header('profile.css');
?>

<main class="main profile-page">
    <div class="container">
        <?php output_profile_alert($message, 'success'); ?>
        <?php output_profile_alert($error, 'error'); ?>
        <?php output_profile_hero($user, 'Update your profile details and password.'); ?>
        <?php output_basic_profile_form($user); ?>
        <?php output_password_form(); ?>

        <div class="logout-box">
            <a href="../pages/logout.php" id="logout-button" class="secondary-button btn-sm logout-button">Logout</a>
        </div>
    </div>
</main>

<script src="../js/profile.js"></script>

<?php output_footer('../member/'); ?>