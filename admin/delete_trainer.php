<?php
session_start();
require_once '../db/connection.php';
require_once '../db/trainers.php';
require_once '../includes/auth.php';

requireRole('admin');
$pdo = getDatabaseConnection();

$id = (int)($_GET['id'] ?? 0);
if ($id && deleteTrainer($pdo, $id)) {
    header('Location: trainers.php?msg=deleted');
} else {
    header('Location: trainers.php?error=delete_failed');
}
exit;
?>