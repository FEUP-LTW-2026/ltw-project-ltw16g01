<?php
session_start();

require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/trainers.php';
require_once '../template/trainers.php';

requireAuth('admin');

$pdo = getDatabaseConnection();
$errors = [];
$success = '';
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $specializations = trim($_POST['specializations'] ?? '');
    $certifications = trim($_POST['certifications'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    $old = ['name' => $name, 'username' => $username, 'email' => $email, 'bio' => $bio, 'specializations' => $specializations, 'certifications' => $certifications];

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
        $result = createTrainer($pdo, $name, $username, $email, $bio, $specializations, $certifications, $password);

        if ($result === false) {
            $errors[] = 'Username or email already exists.';
        } else {
            $success = 'Trainer created successfully.';
            $old = [];
        }
    }
}

output_header('form.css');
?>

<main class="main">
    <?php output_trainer_form(null, $errors, $success, $old); ?>
</main>

<?php output_footer('../'); ?>