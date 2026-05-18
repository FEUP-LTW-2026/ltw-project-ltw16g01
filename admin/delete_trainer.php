<?php
session_start();

require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/trainers.php';

requireAuth('admin');

$pdo = getDatabaseConnection();
$id = (int)($_GET['id'] ?? 0);

if ($id > 0 && deleteTrainer($pdo, $id)) {
    header('Location: trainers.php?msg=deleted');
    exit;
}

header('Location: trainers.php?error=delete_failed');
exit;