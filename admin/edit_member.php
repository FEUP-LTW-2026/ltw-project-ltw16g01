<?php
// File: admin/edit_member.php
// Description: Form to edit existing member details.

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/members.php';
require_once '../template/members.php';

// Restrict access to admin users only
requireAuth('admin');

// Get database connection
$pdo = getDatabaseConnection();

// Get member ID from URL
$id = (int)($_GET['id'] ?? 0);

// Fetch existing member data
$member = getMemberById($pdo, $id);

// If member not found, redirect with error
if (!$member) {
    header('Location: members.php?error=notfound');
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
    $plan = $_POST['plan'] ?? 'basic';
    $planEnd = trim($_POST['plan_end'] ?? '');
    $active = (int)($_POST['active'] ?? 1);

    // Check if admin is editing their own account
    $isEditingSelf = ((int)$_SESSION['user_id'] === $id);

    // Prevent removing admin role from self if they are the only admin?
    if ($isEditingSelf && !empty($member['is_admin'])) {
        $isAdmin = 1; // Keep admin status for self
    } else {
        $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
    }

    // Save old values to repopulate form in case of error
    $old = [
        'name' => $name,
        'username' => $username,
        'email' => $email,
        'plan' => $plan,
        'plan_end' => $planEnd,
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
    } elseif (!in_array($plan, ['basic', 'premium', 'vip'])) {
        $errors[] = 'Invalid membership plan.';
    } else {
        // Update member using function from db/members.php
        $result = updateMember($pdo, $id, $name, $username, $email, $active, $plan, $planEnd, $isAdmin);

        if ($result === false) {
            $errors[] = 'Username or email already in use.';
        } else {
            $success = 'Member updated successfully.';
            // Refresh member data to show updated values
            $member = getMemberById($pdo, $id);
            $old = []; // Clear old values on success
        }
    }
}

// Output page header
output_header('form.css');
?>

<main class="main">
    <?php output_member_form($member, $errors, $success, $old); ?>
</main>

<?php output_footer('../admin/'); ?>