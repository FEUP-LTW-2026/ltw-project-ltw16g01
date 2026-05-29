<?php
require_once '../includes/session.php';

require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/equipment.php';

requireAuth(['member', 'admin']);

// Create the database connection.
$pdo = getDatabaseConnection();

// Load all equipment availability data.
// This request only reads data, so CSRF protection is not required.
$equipment = getAllEquipment($pdo);

$response = [];

// Build a compact JSON response for live equipment updates.
foreach ($equipment as $item) {
    $response[] = [
        'id' => (int)$item['id'],
        'available' => (int)$item['available'],
        'quantity' => (int)$item['quantity'],
        'status' => $item['status'],
        'notes' => $item['notes']
    ];
}

header('Content-Type: application/json');
echo json_encode($response);