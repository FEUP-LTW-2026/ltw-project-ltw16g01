<?php
require_once '../includes/session.php';

require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/enrollments.php';
require_once '../db/members.php';
require_once '../db/notifications.php';

header('Content-Type: application/json');

// Only logged-in members can enroll or cancel enrollments.
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

$action = $input['action'] ?? '';
$scheduleId = (int)($input['schedule_id'] ?? 0);
$sessionId = (int)($input['session_id'] ?? 0);
$memberId = (int)$_SESSION['user_id'];

// Validate the requested action.
if (!in_array($action, ['enroll', 'cancel'], true)) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid action.'
    ]);

    exit;
}

// If a session id was not provided, find the next scheduled session for the selected schedule.
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

// Enroll the member in the selected session.
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

    if (!empty($result['success'])) {

        $memberName = $_SESSION['user_name'];

        $sessionInfo = getSessionTrainerInfo(
            $pdo,
            $sessionId
        );

        if (
            $sessionInfo &&
            !empty($sessionInfo['trainer_id'])
        ) {

            notifyTrainerClassEnrollment(
                $pdo,
                (int)$sessionInfo['trainer_id'],
                $memberName,
                $sessionInfo['class_name'],
                'enroll'
            );
        }
    }

    echo json_encode($result);
    exit;
}

// Cancel the member enrollment.
if ($action === 'cancel') {
    $result = cancelMemberEnrollment($pdo, $memberId, $sessionId);

    if (!empty($result['success'])) {

        $memberName = $_SESSION['user_name'];

        $sessionInfo = getSessionTrainerInfo(
            $pdo,
            $sessionId
        );

        if (
            $sessionInfo &&
            !empty($sessionInfo['trainer_id'])
        ) {

            notifyTrainerClassEnrollment(
                $pdo,
                (int)$sessionInfo['trainer_id'],
                $memberName,
                $sessionInfo['class_name'],
                'cancel'
            );
        }
    }

    echo json_encode($result);
    exit;
}