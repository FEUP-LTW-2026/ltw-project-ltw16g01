<?php
session_start();
require_once '../db/connection.php';
require_once '../includes/auth.php';

requireRole('admin');

$pdo = getDatabaseConnection();
$id = (int)($_GET['id'] ?? 0);

if ($id) {
    // Soft delete? Podemos simplesmente remover ou desativar. Vamos usar soft delete se houver coluna active, mas não tem. Então DELETE real.
    // Para evitar inconsistências, podemos apenas definir status = 'unavailable' ou remover. Vamos remover (cuidado com integridade referencial).
    // Como não há foreign keys para equipment, DELETE é seguro.
    $stmt = $pdo->prepare("DELETE FROM equipment WHERE id = ?");
    $stmt->execute([$id]);
}

header('Location: equipment.php?msg=deleted');
exit;
?>