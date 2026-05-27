<?php
require_once '../includes/session.php';

require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/classes.php';
require_once '../template/classes.php';

requireAuth('admin');

$pdo = getDatabaseConnection();
$errors = [];
$success = '';
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$classTypes = getClassTypes($pdo);
$trainers = getActiveTrainersForClasses($pdo);
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || $_SESSION['csrf'] !== $_POST['csrf']) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }

    $className = trim($_POST['class_name'] ?? '');
    $classDescription = trim($_POST['class_description'] ?? '');
    $classTypeId = (int)($_POST['class_type_id'] ?? 0);
    $trainerId = !empty($_POST['trainer_id']) ? (int)$_POST['trainer_id'] : null;
    $dayOfWeek = $_POST['day_of_week'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $location = trim($_POST['location'] ?? 'Studio A');
    $capacity = (int)($_POST['capacity'] ?? 20);
    $duration = (int)($_POST['duration_min'] ?? 60);

    $old = [
        'class_name' => $className,
        'class_description' => $classDescription,
        'class_type_id' => $classTypeId,
        'trainer_id' => $trainerId,
        'day_of_week' => $dayOfWeek,
        'start_time' => $startTime,
        'location' => $location,
        'capacity' => $capacity,
        'duration_min' => $duration
    ];

    if ($className === '' || $classTypeId <= 0 || $dayOfWeek === '' || $startTime === '') {
        $errors[] = 'Please fill in all required fields.';
    } elseif (!in_array($dayOfWeek, $days, true)) {
        $errors[] = 'Invalid day of week.';
    } elseif ($capacity < 1) {
        $errors[] = 'Capacity must be at least 1.';
    } elseif ($duration < 15) {
        $errors[] = 'Duration must be at least 15 minutes.';
    } else {
        $result = createClassWithSchedule(
            $pdo,
            $className,
            $classDescription,
            $classTypeId,
            $trainerId,
            $dayOfWeek,
            $startTime,
            $location,
            $capacity,
            $duration
        );

        if ($result) {
            $success = 'Class created successfully.';
            $old = [];
        } else {
            $errors[] = 'Could not create class.';
        }
    }
}

output_header('form.css');
?>

<main class="main">
    <?php output_class_form($classTypes, $trainers, $days, $old, $errors, $success, false); ?>
</main>

<?php output_footer('../admin/'); ?>