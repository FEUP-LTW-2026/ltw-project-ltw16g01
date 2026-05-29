<?php
// File: admin/delete_class.php
// Description: Handles deletion of a class schedule by ID. Redirects after action.
//              Sends notification to the assigned trainer if the class is deleted.

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/classes.php';           // Contains getClassScheduleById() and deleteClassSchedule()
require_once '../db/notifications.php'; // Contains createNotification()
require_once '../db/class_details.php';

// Restrict access to admin users only
requireAuth('admin');

// Get database connection
$pdo = getDatabaseConnection();

// Get class schedule ID from URL query string
$scheduleId = (int)($_GET['id'] ?? 0);

if ($scheduleId <= 0) {
    header('Location: classes.php?error=invalid_id');
    exit;
}

// Fetch class schedule details using existing function from db/classes.php
// This function should return an array containing: id, class_id, trainer_id, day_of_week, start_time, location, capacity, duration_min, and ideally class_name via JOIN
$schedule = getClassScheduleById($pdo, $scheduleId);

if (!$schedule) {
    header('Location: classes.php?error=not_found');
    exit;
}

// Extract trainer ID (if any)
$trainerId = !empty($schedule['trainer_id']) ? (int)$schedule['trainer_id'] : null;

// Get class name – assuming getClassScheduleById already includes 'class_name'
// If not, you can add a helper in class_details_queries.php (see note below)
$className = $schedule['class_name'] ?? 'Unknown class';
$dayOfWeek = $schedule['day_of_week'] ?? '';
$startTime = $schedule['start_time'] ?? '';

// Attempt to delete the schedule
if (deleteClassSchedule($pdo, $scheduleId)) {
    // Success: send notification to the trainer if assigned
    if ($trainerId !== null && $trainerId > 0) {
        $message = "The class '{$className}' on {$dayOfWeek} at {$startTime} has been removed (schedule ID: {$scheduleId}).";
        
        // Using createNotification – the title is optional and will be ignored if your table has no title column
        createNotification($pdo, $trainerId, $message, 'warning');
    }
    
    header('Location: classes.php?msg=deleted');
    exit;
}

// Failure
header('Location: classes.php?error=delete_failed');
exit;
?>