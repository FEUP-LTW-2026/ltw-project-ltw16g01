<?php
require_once '../db/connection.php';
require_once '../db/classes.php';
require_once '../template/classes.php';

$pdo = getDatabaseConnection();
$GLOBALS['pdo'] = $pdo;

$filters = [
    'type'    => $_GET['type'] ?? '',
    'trainer' => $_GET['trainer'] ?? '',
    'day'     => $_GET['day'] ?? ''
];

$schedules = getAllClassSchedules($pdo, $filters);
output_class_schedule_list($schedules);