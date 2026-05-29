<?php
// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/classes.php';       // Contains all database functions
require_once '../template/classes.php';  // Contains output_class_form()
require_once '../db/notifications.php';  // Contains createNotification()

// Restrict access to admin users only
requireAuth('admin');

// Get database connection
$pdo = getDatabaseConnection();

// Initialize variables
$errors = [];
$success = '';

// List of valid days of week
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

// Fetch data from database (no inline SQL – uses functions from db/classes.php)
$classTypes = getClassTypes($pdo);                // All active class types
$trainers = getActiveTrainersForClasses($pdo);   // All active trainers

// Store old form input for repopulation in case of error
$old = [];

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
    $location = trim($_POST['location'] ?? 'Studio A');
    $capacity = (int)($_POST['capacity'] ?? 20);
    $duration = (int)($_POST['duration_min'] ?? 60);

    // Store old values for form repopulation
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
        // Create class using function from db/classes.php 
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

            // 1. Notify the assigned trainer (if any)
            if ($trainerId !== null && $trainerId > 0) {
                $trainerMessage = "You have been assigned to the class '{$className}' on {$dayOfWeek} at {$startTime}.";
                createNotification(
                    $pdo,
                    $trainerId,          // recipient user_id (trainer)
                    $trainerMessage,
                    'info',              // notification type
                    'New class assigned' // optional title
                );
            }

            // 2. Notify all administrators (role-based notification)
            $adminMessage = "A new class '{$className}' has been created on {$dayOfWeek} at {$startTime}.";
            createNotification(
                $pdo,
                $adminMessage,   // message
                'info',          // type
                null,            // no specific user
                'admin',         // target_role = 'admin'
                'role',          // scope = 'role'
                'New class created' // title
            );
        } else {
            $errors[] = 'Could not create class.';
        }
    }
}

// Output the page header
output_header('form.css');
?>

<main class="main">
    <?php output_class_form($classTypes, $trainers, $days, $old, $errors, $success, false); ?>
</main>

<?php output_footer('../admin/'); ?>