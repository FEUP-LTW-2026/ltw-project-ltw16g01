<?php
session_start();

require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/equipment.php';

requireAuth('admin');

$pdo = getDatabaseConnection();
$id = (int)($_GET['id'] ?? 0);

if ($id > 0 && deleteEquipment($pdo, $id)) {
    header('Location: equipment.php?msg=deleted');
    exit;
}

header('Location: equipment.php?error=delete_failed');
exit;