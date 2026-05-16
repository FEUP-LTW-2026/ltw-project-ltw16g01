<?php
session_start();
require_once '../db/connection.php';
require_once '../includes/auth.php';

requireRole('admin');

$pdo = getDatabaseConnection();
$schedule_id = (int)($_GET['id'] ?? 0);

// Buscar o class_id associado
$stmt = $pdo->prepare("SELECT class_id FROM class_schedules WHERE id = ?");
$stmt->execute([$schedule_id]);
$class_id = $stmt->fetchColumn();

if ($class_id) {
    // Soft delete da classe (active = 0)
    $stmt = $pdo->prepare("UPDATE classes SET active = 0 WHERE id = ?");
    $stmt->execute([$class_id]);
    // Opcional: também marcar sessões futuras como cancelled
    $stmt = $pdo->prepare("UPDATE class_sessions SET status = 'cancelled' WHERE schedule_id = ? AND session_date >= date('now')");
    $stmt->execute([$schedule_id]);
}

header('Location: classes.php?msg=deleted');
exit;
?>