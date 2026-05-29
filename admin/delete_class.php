<?php
// File: admin/delete_class.php
// Description: Handles deletion of a class schedule by ID. Redirects after action.

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/classes.php'; // Contains deleteClassSchedule() function

// Restrict access to admin users only
requireAuth('admin');

// Get database connection
$pdo = getDatabaseConnection();

// Get class schedule ID from URL query string
$scheduleId = (int)($_GET['id'] ?? 0);

// Attempt to delete the schedule
if ($scheduleId > 0 && deleteClassSchedule($pdo, $scheduleId)) {
    // Success: redirect to classes page with success message
    header('Location: classes.php?msg=deleted');
    exit;
}

// Failure: redirect with error message
header('Location: classes.php?error=delete_failed');
exit;
?>