<?php
require_once '../includes/session.php';

require_once '../db/connection.php';
require_once '../db/classes.php';
require_once '../template/classes.php';

// Create the database connection.
$pdo = getDatabaseConnection();

// Build filters from the GET query string.
// This request only reads class schedules, so CSRF protection is not required.
$filters = [
    'type' => $_GET['type'] ?? '',
    'trainer' => $_GET['trainer'] ?? '',
    'day' => $_GET['day'] ?? '',
    'time' => $_GET['time'] ?? ''
];

// Load class schedules using the selected filters.
$schedules = getAllClassSchedules($pdo, $filters);

// Render the class schedule list HTML.
output_class_schedule_list($schedules);