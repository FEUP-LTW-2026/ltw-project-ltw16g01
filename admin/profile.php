<?php
// File: admin/profile.php
// Description: Admin profile management page. Allows updating basic info, photo, and password.

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../db/connection.php';
require_once '../db/profile.php';
require_once '../template/profile.php';

// Restrict access to admin users only
requireAuth('admin');

// Get database connection
$pdo = getDatabaseConnection();

// Current logged-in user ID
$userId = $_SESSION['user_id'];

// Initialize feedback variables
$message = '';
$error = '';

// Handle form submissions
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

        // Handle photo upload if a new file was provided
        if (isset($_FILES['photo']) && $_FILES['photo']['error'] !== UPLOAD_ERR_NO_FILE) {
            $upload = uploadProfilePhoto($_FILES['photo']);

            if ($upload['success'] === false) {
                $error = $upload['error'];
            } else {
                // Delete old photo file if exists
                deleteOldProfilePhoto($_POST['current_photo'] ?? null);
                $photoPath = $upload['path'];
            }
        }

        // If no upload error, proceed with profile update
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
                // Update session variables with new values
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

        if ($result === true) {
            $message = 'Password updated successfully.';
        } else {
            $error = $result;
        }
    }
}

// Fetch current user profile data
$user = getUserProfileById($pdo, $userId);

// If user not found, destroy session and redirect to login
if (!$user) {
    session_destroy();
    header('Location: ../pages/login.php');
    exit;
}

// Update session data with latest profile info
$_SESSION['user_name'] = $user['name'];
$_SESSION['user_email'] = $user['email'];
$_SESSION['user_photo'] = $user['photo'] ?? null;

// Output page header with profile-specific CSS
output_header('profile.css');
?>

<main class="main profile-page">
    <div class="container">
        <!-- Display success/error messages -->
        <?php output_profile_alert($message, 'success'); ?>
        <?php output_profile_alert($error, 'error'); ?>

        <!-- Profile hero section -->
        <?php output_profile_hero($user, 'Update your profile details and password.'); ?>

        <!-- Basic profile information form (name, username, email, photo) -->
        <?php output_basic_profile_form($user); ?>

        <!-- Password change form -->
        <?php output_password_form(); ?>

        <!-- Logout button -->
        <div class="logout-box">
            <a href="../pages/logout.php" id="logout-button" class="secondary-button btn-sm logout-button">Logout</a>
        </div>
    </div>
</main>

<!-- External JavaScript for profile interactions (photo preview, etc.) -->
<script src="../js/profile.js"></script>

<?php output_footer('../admin/'); ?>