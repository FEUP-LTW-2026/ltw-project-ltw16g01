<?php
session_start();

require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/classes.php';
require_once '../template/classes.php';

requireAuth('admin');

$pdo = getDatabaseConnection();
$scheduleId = (int)($_GET['id'] ?? 0);
$schedule = getClassScheduleById($pdo, $scheduleId);

if (!$schedule) {
    header('Location: classes.php?error=notfound');
    exit;
}

$errors = [];
$success = '';
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$classTypes = getClassTypes($pdo);
$trainers = getActiveTrainersForClasses($pdo);

$data = [
    'class_name' => $schedule['class_name'],
    'class_description' => $schedule['class_description'],
    'class_type_id' => $schedule['class_type_id'],
    'trainer_id' => $schedule['trainer_id'],
    'day_of_week' => $schedule['day_of_week'],
    'start_time' => $schedule['start_time'],
    'location' => $schedule['location'],
    'capacity' => $schedule['capacity'],
    'duration_min' => $schedule['duration_min']
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $className = trim($_POST['class_name'] ?? '');
    $classDescription = trim($_POST['class_description'] ?? '');
    $classTypeId = (int)($_POST['class_type_id'] ?? 0);
    $trainerId = !empty($_POST['trainer_id']) ? (int)$_POST['trainer_id'] : null;
    $dayOfWeek = $_POST['day_of_week'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 20);
    $duration = (int)($_POST['duration_min'] ?? 60);

    $data = ['class_name' => $className, 'class_description' => $classDescription, 'class_type_id' => $classTypeId, 'trainer_id' => $trainerId, 'day_of_week' => $dayOfWeek, 'start_time' => $startTime, 'location' => $location, 'capacity' => $capacity, 'duration_min' => $duration];

    if ($className === '' || $classTypeId <= 0 || $dayOfWeek === '' || $startTime === '') {
        $errors[] = 'Please fill in all required fields.';
    } elseif (!in_array($dayOfWeek, $days)) {
        $errors[] = 'Invalid day of week.';
    } elseif ($capacity < 1) {
        $errors[] = 'Capacity must be at least 1.';
    } elseif ($duration < 15) {
        $errors[] = 'Duration must be at least 15 minutes.';
    } else {
        $result = updateClassWithSchedule($pdo, $scheduleId, $schedule['class_id'], $className, $classDescription, $classTypeId, $trainerId, $dayOfWeek, $startTime, $location, $capacity, $duration);

        if ($result) {
            $success = 'Class updated successfully.';
            $schedule = getClassScheduleById($pdo, $scheduleId);
        } else {
            $errors[] = 'Could not update class.';
        }
    }
}

output_header('form.css');
?>

<main class="main">
    <?php output_class_form($classTypes, $trainers, $days, $data, $errors, $success, true); ?>
</main>

<?php output_footer('../'); ?>