<?php
require_once '../includes/session.php';

require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/members.php';
require_once '../template/members.php';

requireAuth('admin');

$pdo = getDatabaseConnection();
$id = (int)($_GET['id'] ?? 0);
$member = getMemberById($pdo, $id);

if (!$member) {
    header('Location: members.php?error=notfound');
    exit;
}

$errors = [];
$success = '';
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $plan = $_POST['plan'] ?? 'basic';
    $planEnd = trim($_POST['plan_end'] ?? '');
    $active = (int)($_POST['active'] ?? 1);

    $isEditingSelf = ((int)$_SESSION['user_id'] === $id);

    if ($isEditingSelf && !empty($member['is_admin'])) {
        $isAdmin = 1;
    } else {
        $isAdmin = isset($_POST['is_admin']) ? 1 : 0;
    }

    $old = [
        'name' => $name,
        'username' => $username,
        'email' => $email,
        'plan' => $plan,
        'plan_end' => $planEnd,
        'active' => $active,
        'is_admin' => $isAdmin
    ];

    if ($name === '' || $username === '' || $email === '') {
        $errors[] = 'Name, username and email are required.';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must have at least 3 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } elseif (!in_array($plan, ['basic', 'premium', 'vip'])) {
        $errors[] = 'Invalid membership plan.';
    } else {
        $result = updateMember($pdo, $id, $name, $username, $email, $active, $plan, $planEnd, $isAdmin);

        if ($result === false) {
            $errors[] = 'Username or email already in use.';
        } else {
            $success = 'Member updated successfully.';
            $member = getMemberById($pdo, $id);
            $old = [];
        }
    }
}

output_header('form.css');
?>

<main class="main">
    <?php output_member_form($member, $errors, $success, $old); ?>
</main>

<?php output_footer('../admin/'); ?>