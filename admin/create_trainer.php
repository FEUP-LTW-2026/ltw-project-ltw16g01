<?php
// File: admin/add_trainer.php
// Description: Form to add a new trainer.

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/trainers.php';   // Contains database functions for trainers
require_once '../template/trainers.php';
require_once '../includes/mailer.php';

// Restrict access to admin users only
requireAuth('admin');

// Get database connection
$pdo = getDatabaseConnection();

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
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Save old values to repopulate form in case of error
    $old = [
        'name' => $name,
        'username' => $username,
        'email' => $email,
        'bio' => $bio,
        'specializations' => $specializations,
        'certifications' => $certifications
    ];

    // Validation
    if ($name === '' || $username === '' || $email === '' || $password === '' || $confirm === '') {
        $errors[] = 'Please fill in all required fields.';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must have at least 3 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (strlen($password) < 8) {
        $errors[] = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $errors[] = 'Passwords do not match.';
    } else {
        // Insert trainer using function from db/trainers.php
        $result = createTrainer($pdo, $name, $username, $email, $bio, $specializations, $certifications, $password);

        if ($result === false) {
            // Duplicate username or email
            $errors[] = 'Username or email already exists.';
        } else {
            $emailSent = sendAccountCredentialsEmail($email, $name, $username, $password, 'trainer');

            if ($emailSent) {
                $success = 'Trainer created successfully and credentials email sent.';
            } else {
                $success = 'Trainer created successfully, but the credentials email could not be sent.';
            }

            $old = [];
        }
    }
}

// Output page header
output_header('form.css');
?>

<main class="main">
    <?php output_trainer_form(null, $errors, $success, $old); ?>
</main>

<?php output_footer('../admin/'); ?>