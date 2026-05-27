<?php
require_once '../includes/session.php';

require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/enrollments.php';
require_once '../db/members.php';

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
$sessionId = (int)($input['session_id'] ?? 0);
$memberId = (int)$_SESSION['user_id'];

if (!in_array($action, ['enroll', 'cancel'], true)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action.'
    ]);
    exit;
}

if ($sessionId <= 0) {
    if ($scheduleId <= 0) {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid class or session ID.'
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
}

if ($action === 'enroll') {
    $permission = canMemberEnrollInClass($pdo, $memberId, $sessionId);

    if (!$permission['allowed']) {
        echo json_encode([
            'success' => false,
            'message' => $permission['message']
        ]);
        exit;
    }

    $result = enrollMemberInSession($pdo, $memberId, $sessionId);
    echo json_encode($result);
    exit;
}

if ($action === 'cancel') {
    $result = cancelMemberEnrollment($pdo, $memberId, $sessionId);
    echo json_encode($result);
    exit;
}