<?php
session_start();

require_once '../db/connection.php';
require_once '../db/auth.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../template/auth.php';

$error = '';
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $plan = $_POST['plan'] ?? 'basic';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    $allowedPlans = ['basic', 'premium', 'vip'];

    $old = [
        'name' => $name,
        'username' => $username,
        'email' => $email,
        'plan' => $plan
    ];

    if ($name === '' || $username === '' || $email === '' || $plan === '' || $password === '' || $confirm === '') {
        $error = 'Please fill in all fields.';
    } elseif (strlen($username) < 3) {
        $error = 'Username must have at least 3 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!in_array($plan, $allowedPlans, true)) {
        $error = 'Please select a valid membership plan.';
    } elseif (strlen($password) < 8) {
        $error = 'Password must be at least 8 characters.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $pdo = getDatabaseConnection();
        $userId = registerMember($pdo, $name, $username, $email, $password, $plan);

        if ($userId === false) {
            $error = 'That email or username is already registered.';
        } else {
            $_SESSION['user_id'] = $userId;
            $_SESSION['user_name'] = $name;
            $_SESSION['role'] = 'member';
            $_SESSION['user_email'] = $email;
            $_SESSION['user_photo'] = null;

            header('Location: ../member/dashboard.php');
            exit;
        }
    }
}

output_header('auth.css');
?>

<main class="auth-main">
    <?php output_register_form($error, $old); ?>
</main>

<script src="../js/register.js"></script>

<?php output_footer('../pages/'); ?>