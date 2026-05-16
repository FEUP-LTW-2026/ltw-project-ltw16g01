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
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    $old = [
        'name' => $name,
        'username' => $username,
        'email' => $email
    ];

    if ($name === '' || $username === '' || $email === '' || $password === '' || $confirm === '') {
        $error = 'Please fill in all fields.';
    } elseif ($password !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $pdo = getDatabaseConnection();
        $userId = registerMember($pdo, $name, $username, $email, $password);

        if ($userId === false) {
            $error = 'That email or username is already registered.';
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = 'member';
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_photo'] = $user['photo'] ?? null;

            header('Location: ../index.php');
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

<?php
output_footer();
?>