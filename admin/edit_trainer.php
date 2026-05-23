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
$id = (int)($_GET['id'] ?? 0);
$trainer = getTrainerById($pdo, $id, false); // buscar mesmo que inativo

if (!$trainer) {
    header('Location: trainers.php?error=notfound');
    exit;
}

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
    $active = (int)($_POST['active'] ?? 1);

    $old = [
        'name' => $name,
        'username' => $username,
        'email' => $email,
        'bio' => $bio,
        'specializations' => $specializations,
        'certifications' => $certifications,
        'active' => $active
    ];

    if ($name === '' || $username === '' || $email === '') {
        $errors[] = 'Name, username and email are required.';
    } elseif (strlen($username) < 3) {
        $errors[] = 'Username must have at least 3 characters.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    } else {
        $result = updateTrainer($pdo, $id, $name, $username, $email, $active, $bio, $specializations, $certifications);
        if ($result === false) {
            $errors[] = 'Username or email already in use.';
        } else {
            $success = 'Trainer updated successfully.';
            $trainer = getTrainerById($pdo, $id, false);
            $old = [];
        }
    }
}

output_header('form.css');
?>

<main class="main">
    <?php output_trainer_form($trainer, $errors, $success, $old); ?>
</main>

<?php output_footer(); ?>