<?php

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/members.php';        // Database functions for members
require_once '../template/members.php';  // output_member_form()
require_once '../includes/mailer.php';
require_once '../db//notifications.php'; // createNotification()

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
    $plan = $_POST['plan'] ?? 'basic';
    $planEnd = trim($_POST['plan_end'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    // Save old values to repopulate form in case of error
    $old = [
        'name' => $name,
        'username' => $username,
        'email' => $email,
        'plan' => $plan,
        'plan_end' => $planEnd
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
    } elseif (!in_array($plan, ['basic', 'premium', 'vip'])) {
        $errors[] = 'Invalid membership plan.';
    } else {
        // Insert member using function from db/members.php
        $result = createMember($pdo, $name, $username, $email, $password, $plan, $planEnd);

        if ($result === false) {
            // Duplicate username or email
            $errors[] = 'Username or email already exists.';
        } else {
            $emailSent = sendAccountCredentialsEmail($email, $name, $username, $password, 'member');

            if ($emailSent) {
                $success = 'Member created successfully and credentials email sent.';
            } else {
                $success = 'Member created successfully, but the credentials email could not be sent.';
            }

            $old = [];

            // Notify all administrators (role-based notification)
            $planLabel = ucfirst($plan); // 'Basic', 'Premium', 'Vip'
            $adminMessage = "New member created: {$name} (username: {$username}, email: {$email}, plan: {$planLabel}).";
            
            createNotification(
                $pdo,
                $adminMessage,   // message
                'info',          // type
                null,            // no specific user
                'admin',         // target_role = 'admin'
                'role',          // scope = 'role'
                'New member'     // title (optional, may be ignored if table lacks column)
            );
        }
    }
}

// Output page header
output_header('form.css');
?>

<main class="main">
    <?php output_member_form(null, $errors, $success, $old); ?>
</main>

<?php output_footer('../admin/'); ?>