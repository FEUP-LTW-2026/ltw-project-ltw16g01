<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_clean();
header('Content-Type: application/json');

require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/enrollments.php';
require_once '../db/members.php';
require_once '../db/notifications.php';
require_once '../db/classes.php';   // contém a função dinâmica getNextScheduledSessionByScheduleId

if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'member') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Not authorized.']);
    exit;
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Invalid method.']);
    exit;
}
$pdo = getDatabaseConnection();
$input = json_decode(file_get_contents('php://input'), true) ?? [];
if (!isset($input['csrf']) || $_SESSION['csrf'] !== $input['csrf']) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Invalid CSRF token.']);
    exit;
}
$action = $input['action'] ?? '';
$scheduleId = (int)($input['schedule_id'] ?? 0);
$memberId = (int)$_SESSION['user_id'];
if (!in_array($action, ['enroll', 'cancel'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit;
}

if ($action === 'enroll') {
    // 1. Try to get an existing future session
    $session = getNextScheduledSessionByScheduleId($pdo, $scheduleId);
    
    // 2. If none exists, create one now
    if (!$session) {
        $session = createNextSessionForSchedule($pdo, $scheduleId);
        if (!$session) {
            echo json_encode(['success' => false, 'message' => 'Could not create a session for this class.']);
            exit;
        }
    }
    
    $sessionId = (int)$session['id'];
    
    // 3. Check permissions (capacity, conflicts, etc.)
    $permission = canMemberEnrollInClass($pdo, $memberId, $sessionId);
    if (!$permission['allowed']) {
        echo json_encode(['success' => false, 'message' => $permission['message']]);
        exit;
    }
    
    // 4. Enroll the member
    $result = enrollMemberInSession($pdo, $memberId, $sessionId);
    
    // 5. Notify trainer if successful
    if (!empty($result['success'])) {
        $memberName = $_SESSION['user_name'] ?? 'Member';
        $sessionInfo = getSessionTrainerInfo($pdo, $sessionId);
        if ($sessionInfo && !empty($sessionInfo['trainer_id'])) {
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

if ($action === 'cancel') {
    $next = getNextScheduledSessionByScheduleId($pdo, $scheduleId);
    if (!$next) {
        echo json_encode(['success' => false, 'message' => 'No active session.']);
        exit;
    }
    $sessionId = (int)$next['id'];
    if ($sessionId <= 0 || !sessionExists($pdo, $sessionId)) {
        echo json_encode(['success' => false, 'message' => 'Session does not exist.']);
        exit;
    }
    $result = cancelMemberEnrollment($pdo, $memberId, $sessionId);
    echo json_encode($result);
    exit;
}