<?php
// File: admin/delete_class.php
// Description: Handles deletion of a class schedule by ID. Redirects after action.
//              Sends notification to the assigned trainer if the class is deleted.

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/classes.php'; // Contains deleteClassSchedule() function
require_once '../db/notifications.php'; // Contains createNotification()

// Restrict access to admin users only
requireAuth('admin');

// Get database connection
$pdo = getDatabaseConnection();

// Only allow POST requests for delete actions
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

// CSRF protection
if (!isset($_POST['csrf'], $_SESSION['csrf']) || $_SESSION['csrf'] !== $_POST['csrf']) {
    http_response_code(403);
    exit('Invalid CSRF token.');
}

// Get class schedule ID from POST body
$scheduleId = (int)($_POST['id'] ?? 0);

if ($scheduleId <= 0) {
    header('Location: classes.php?error=invalid_id');
    exit;
}

// Fetch class schedule details before deletion (to get trainer info and class name)
$schedule = getClassScheduleDeleteDetails($pdo, $scheduleId);

if (!$schedule) {
    // Class schedule not found
    header('Location: classes.php?error=not_found');
    exit;
}

$trainerId = $schedule['trainer_id'] ? (int)$schedule['trainer_id'] : null;
$className = $schedule['class_name'] ?? 'Unknown class';
$dayOfWeek = $schedule['day_of_week'] ?? '';
$startTime = $schedule['start_time'] ?? '';

// Attempt to delete the schedule
if (deleteClassSchedule($pdo, $scheduleId)) {
    // Success: send notification to the trainer if assigned
    if ($trainerId !== null && $trainerId > 0) {
        $message = "The class '{$className}' on {$dayOfWeek} at {$startTime} has been removed.";
        createNotification(
            $pdo,
            $trainerId,
            $message,
            'warning',          // notification type (from notification_types table)
            'Class removed'     // optional title
        );
    }
    
    // Redirect with success message
    header('Location: classes.php?msg=deleted');
    exit;
}

// Failure: redirect with error message
header('Location: classes.php?error=delete_failed');
exit;
?>