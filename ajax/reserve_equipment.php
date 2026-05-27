<?php
require_once '../includes/session.php';

require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/equipment.php';

header('Content-Type: application/json');

if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'member') {
    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Not authorized.'
    ]);

    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid method.'
    ]);

    exit;
}

$pdo = getDatabaseConnection();

$input = json_decode(file_get_contents('php://input'), true) ?? [];

$memberId = (int)$_SESSION['user_id'];
$equipmentId = (int)($input['equipment_id'] ?? 0);
$reservationDate = trim($input['reservation_date'] ?? '');
$reservationTime = trim($input['reservation_time'] ?? '');
$quantity = (int)($input['quantity'] ?? 1);

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