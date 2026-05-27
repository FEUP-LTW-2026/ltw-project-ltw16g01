<?php
require_once '../includes/session.php';

require_once '../db/connection.php';
require_once '../db/classes.php';
require_once '../template/classes.php';

$pdo = getDatabaseConnection();
$filters = [
    'type' => $_GET['type'] ?? '',
    'trainer' => $_GET['trainer'] ?? '',
    'day' => $_GET['day'] ?? '',
    'time' => $_GET['time'] ?? ''
];

$schedules = getAllClassSchedules($pdo, $filters);
output_class_schedule_list($schedules);