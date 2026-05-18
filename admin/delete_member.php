<?php
session_start();

require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/members.php';

requireAuth('admin');

$pdo = getDatabaseConnection();
$id = (int)($_GET['id'] ?? 0);

if ($id > 0 && deleteMember($pdo, $id)) {
    header('Location: members.php?msg=deleted');
    exit;
}

header('Location: members.php?error=delete_failed');
exit;