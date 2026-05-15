<?php
session_start();
require_once __DIR__ . '/../db/connection.php';

header('Content-Type: application/json');

$pdo = getDatabaseConnection();
$equipment = $pdo->query("SELECT id, name, category, quantity, available, status, notes, updated_at FROM equipment ORDER BY category, name")->fetchAll();

// Override: if no unit is available, force status to 'unavailable'
foreach ($equipment as &$item) {
    if ((int)$item['available'] === 0) {
        $item['status'] = 'unavailable';
    }
}

echo json_encode($equipment);