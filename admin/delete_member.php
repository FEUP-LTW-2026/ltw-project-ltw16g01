<?php
session_start();
require_once '../db/connection.php';
require_once '../db/members.php';
require_once '../includes/auth.php';

requireRole('admin');
$pdo = getDatabaseConnection();

$id = (int)($_GET['id'] ?? 0);
if ($id && deleteMember($pdo, $id)) {
    header('Location: members.php?msg=deleted');
} else {
    header('Location: members.php?error=delete_failed');
}
exit;
?>