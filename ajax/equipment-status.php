<?php
require_once '../db/connection.php';
require_once '../db/equipment.php';

$pdo = getDatabaseConnection();
$equipment = getAllEquipment($pdo);

$response = [];
foreach ($equipment as $item) {
    $response[] = [
        'id' => $item['id'],
        'available' => (int)$item['available'],
        'quantity' => (int)$item['quantity'],
        'status' => $item['status'],
        'notes' => $item['notes']
    ];
}

header('Content-Type: application/json');
echo json_encode($response);