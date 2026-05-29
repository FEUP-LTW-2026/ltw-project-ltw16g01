<?php
// File: admin/edit_class.php
// Description: Form to edit an existing class schedule.

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/classes.php';   // Contains database functions for classes
require_once '../template/classes.php'; // Contains output_class_form()

// Restrict access to admin users only
requireAuth('admin');

// Get database connection
$pdo = getDatabaseConnection();

// Get schedule ID from URL
$scheduleId = (int)($_GET['id'] ?? 0);

// Fetch existing schedule data
$schedule = getClassScheduleById($pdo, $scheduleId);

// If schedule not found, redirect with error
if (!$schedule) {
    header('Location: classes.php?error=notfound');
    exit;
}

// Initialize variables
$errors = [];
$success = '';

// List of valid days of week
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

// Fetch form options from database 
$classTypes = getClassTypes($pdo);
$trainers = getActiveTrainersForClasses($pdo);

// Pre-populate form with existing data
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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    if (!isset($_POST['csrf']) || $_SESSION['csrf'] !== $_POST['csrf']) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }

    // Get and sanitize input
    $className = trim($_POST['class_name'] ?? '');
    $classDescription = trim($_POST['class_description'] ?? '');
    $classTypeId = (int)($_POST['class_type_id'] ?? 0);
    $trainerId = !empty($_POST['trainer_id']) ? (int)$_POST['trainer_id'] : null;
    $dayOfWeek = $_POST['day_of_week'] ?? '';
    $startTime = $_POST['start_time'] ?? '';
    $location = trim($_POST['location'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 20);
    $duration = (int)($_POST['duration_min'] ?? 60);

    // Update data array with new values (for form repopulation on error)
    $data = [
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

    // Validation
    if ($className === '' || $classTypeId <= 0 || $dayOfWeek === '' || $startTime === '') {
        $errors[] = 'Please fill in all required fields.';
    } elseif (!in_array($dayOfWeek, $days, true)) {
        $errors[] = 'Invalid day of week.';
    } elseif ($capacity < 1) {
        $errors[] = 'Capacity must be at least 1.';
    } elseif ($duration < 15) {
        $errors[] = 'Duration must be at least 15 minutes.';
    } else {
        // Update class using function from db/classes.php
        $result = updateClassWithSchedule(
            $pdo,
            $scheduleId,
            $schedule['class_id'],
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
            $success = 'Class updated successfully.';
            // Refresh schedule data to show updated values
            $schedule = getClassScheduleById($pdo, $scheduleId);
            // Also update $data with fresh values
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
        } else {
            $errors[] = 'Could not update class.';
        }
    }
}

// Output page header
output_header('form.css');
?>

<main class="main">
    <?php output_class_form($classTypes, $trainers, $days, $data, $errors, $success, true); ?>
</main>

<?php output_footer('../admin/'); ?>