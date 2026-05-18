<?php
session_start();

require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/classes.php';

requireAuth('admin');

$pdo = getDatabaseConnection();
$scheduleId = (int)($_GET['id'] ?? 0);

if ($scheduleId > 0 && deleteClassSchedule($pdo, $scheduleId)) {
    header('Location: classes.php?msg=deleted');
    exit;
}

header('Location: classes.php?error=delete_failed');
exit;