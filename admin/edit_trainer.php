<?php
// File: admin/edit_trainer.php
// Description: Form to edit existing trainer details.

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/trainers.php';
require_once '../template/trainers.php';
require_once '../db/notifications.php'; // createNotification()

// Restrict access to admin users only
requireAuth('admin');

// Get database connection
$pdo = getDatabaseConnection();

// Get trainer ID from URL
$id = (int)($_GET['id'] ?? 0);

// Fetch existing trainer data (including inactive ones)
$trainer = getTrainerById($pdo, $id, false);

// If trainer not found, redirect with error
if (!$trainer) {
    header('Location: trainers.php?error=notfound');
    exit;
}

// Initialize variables
$errors = [];
$success = '';
$old = []; // Stores old form input for repopulation on error

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    if (!isset($_POST['csrf']) || $_SESSION['csrf'] !== $_POST['csrf']) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }

    // Get and sanitize form inputs
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $specializations = trim($_POST['specializations'] ?? '');
    $certifications = trim($_POST['certifications'] ?? '');
    $active = (int)($_POST['active'] ?? 1);

    // Check if admin is editing their own account
    $isEditingSelf = ((int)$_SESSION['user_id'] === $id);

    // Prevent removing admin role from self if they are already admin
    if ($isEditingSelf && !empty($trainer['is_admin'])) {
        $isAdmin = 1; // Keep admin status for self
    } else {
        $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
    }

    // Save old values to repopulate form in case of error
    $old = [
        'name' => $name,
        'username' => $username,
        'email' => $email,
        'bio' => $bio,
        'specializations' => $specializations,
        'certifications' => $certifications,
        'active' => $active,
        'is_admin' => $isAdmin
    ];

    // Validation
    if ($name === '' || $username === '' || $email === '') {
        $errors[] = 'Name, username and email are required.';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must have at least 3 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        // Update trainer using function from db/trainers.php
        $result = updateTrainer(
            $pdo,
            $id,
            $name,
            $username,
            $email,
            $active,
            $bio,
            $specializations,
            $certifications,
            $isAdmin
        );

        if ($result === false) {
            // Duplicate username or email
            $errors[] = 'Username or email already in use.';
        } else {
            $success = 'Trainer updated successfully.';
            
            // Refresh trainer data to show updated values
            $trainer = getTrainerById($pdo, $id, false);
            
            // Notify all administrators (role-based notification)
            $activeLabel = $active ? 'Active' : 'Inactive';
            $specializationsText = $specializations ?: 'none specified';
            $certificationsText = $certifications ?: 'none specified';
            $adminMessage = "Trainer updated: {$name} (username: {$username}, email: {$email}, status: {$activeLabel}, specializations: {$specializationsText}, certifications: {$certificationsText}).";
            createNotification(
                $pdo,
                $adminMessage,   // message
                'info',          // type
                null,            // no specific user
                'admin',         // target_role = 'admin'
                'role',          // scope = 'role'
                'Trainer updated' // optional title
            );
            
            $old = []; // Clear old values on success
        }
    }
}

// Output page header
output_header('form.css');
?>

<main class="main">
    <?php output_trainer_form($trainer, $errors, $success, $old); ?>
</main>

<?php output_footer('../admin/'); ?>