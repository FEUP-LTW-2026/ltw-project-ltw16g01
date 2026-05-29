<?php
require_once '../includes/session.php';

require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/equipment.php';

header('Content-Type: application/json');

// Only logged-in members can reserve equipment.
if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'member') {
    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Not authorized.'
    ]);

    exit;
}

// This endpoint changes state, so only POST requests are allowed.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid method.'
    ]);

    exit;
}

// Create the database connection.
$pdo = getDatabaseConnection();

// Decode the JSON request body.
$input = json_decode(file_get_contents('php://input'), true) ?? [];

// Validate the CSRF token because this request changes state.
if (!isset($input['csrf']) || $_SESSION['csrf'] !== $input['csrf']) {
    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid CSRF token.'
    ]);

    exit;
}

$memberId = (int)$_SESSION['user_id'];
$equipmentId = (int)($input['equipment_id'] ?? 0);
$reservationDate = trim($input['reservation_date'] ?? '');
$reservationTime = trim($input['reservation_time'] ?? '');
$quantity = (int)($input['quantity'] ?? 1);

// Create the equipment reservation through the database helper.
$result = reserveEquipment(
    $pdo,
    $memberId,
    $equipmentId,
    $reservationDate,
    $reservationTime,
    $quantity
);

echo json_encode($result);
exit;