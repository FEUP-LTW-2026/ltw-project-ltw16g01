<?php
session_start();

require_once '../db/connection.php';
require_once '../db/auth.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../template/auth.php';

$error = '';
$postedIdentifier = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';
    $postedIdentifier = $identifier;

    if ($identifier === '' || $password === '') {
        $error = 'Please fill in all fields.';
    } else {
        $pdo = getDatabaseConnection();
        $user = authenticateUser($pdo, $identifier, $password);

        if ($user === false) {
            $error = 'Incorrect credentials. Please try again.';
        } else {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['is_admin'] = (int)($user['is_admin'] ?? 0);
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_photo'] = $user['photo'] ?? null;

            if ((int)$user['is_admin'] === 1 && in_array($user['role'], ['member', 'trainer'], true)) {
                header('Location: choose_area.php');
            } elseif ($user['role'] === 'admin') {
                $_SESSION['active_role'] = $user['role'];
                header('Location: ../admin/dashboard.php');
            } elseif ($user['role'] === 'member') {
                $_SESSION['active_role'] = $user['role'];
                header('Location: ../member/dashboard.php');
            } elseif ($user['role'] === 'trainer') {
                $_SESSION['active_role'] = $user['role'];
                header('Location: ../trainer/dashboard.php');
            } else {
                header('Location: ../index.php');
            }
            exit;
        }
    }
}

output_header('auth.css');
?>

<main class="auth-main">
    <?php output_login_form($error, $postedIdentifier); ?>
</main>

<script src="../js/login.js"></script>

<?php output_footer('../pages/'); ?>