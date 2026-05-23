<?php
session_start();

require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/enrollments.php';

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

$action = $input['action'] ?? '';
$scheduleId = (int)($input['schedule_id'] ?? 0);
$memberId = (int)$_SESSION['user_id'];

if (!in_array($action, ['enroll', 'cancel'], true)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action.'
    ]);
    exit;
}

if ($scheduleId <= 0) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid schedule ID.'
    ]);
    exit;
}

$session = getNextScheduledSessionByScheduleId($pdo, $scheduleId);

if (!$session) {
    echo json_encode([
        'success' => false,
        'message' => 'No upcoming sessions available for this class.'
    ]);
    exit;
}

$sessionId = (int)$session['id'];

if ($action === 'enroll') {
    $result = enrollMemberInSession($pdo, $memberId, $sessionId);
    echo json_encode($result);
    exit;
}

if ($action === 'cancel') {
    $result = cancelMemberEnrollment($pdo, $memberId, $sessionId);
    echo json_encode($result);
    exit;
}