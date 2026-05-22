<?php
session_start();

require_once '../includes/auth.php';
require_once '../db/connection.php';

header('Content-Type: application/json');

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

$action = $input['action'] ?? '';
$scheduleId = (int)($input['schedule_id'] ?? 0);
$memberId = (int)$_SESSION['user_id'];

if (!in_array($action, ['enroll', 'cancel'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid action.']);
    exit;
}

if (!$scheduleId) {
    echo json_encode(['success' => false, 'message' => 'Invalid schedule ID.']);
    exit;
}

// Buscar a próxima sessão futura para este schedule
$stmt = $pdo->prepare("
    SELECT id, session_date, status
    FROM class_sessions
    WHERE schedule_id = ? AND session_date >= date('now') AND status = 'scheduled'
    ORDER BY session_date ASC
    LIMIT 1
");
$stmt->execute([$scheduleId]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    echo json_encode(['success' => false, 'message' => 'No upcoming sessions available for this class.']);
    exit;
}

$sessionId = $session['id'];

if ($action === 'enroll') {
    // Verificar lotação
    $capacityStmt = $pdo->prepare("
        SELECT sch.capacity
        FROM class_sessions sess
        JOIN class_schedules sch ON sess.schedule_id = sch.id
        WHERE sess.id = ?
    ");
    $capacityStmt->execute([$sessionId]);
    $capacity = (int)$capacityStmt->fetchColumn();

    $enrolledStmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE session_id = ? AND status = 'enrolled'");
    $enrolledStmt->execute([$sessionId]);
    $enrolled = (int)$enrolledStmt->fetchColumn();

    if ($enrolled >= $capacity) {
        echo json_encode(['success' => false, 'message' => 'This class is full.']);
        exit;
    }

    // Verificar se já está inscrito ou cancelado
    $checkStmt = $pdo->prepare("SELECT id, status FROM enrollments WHERE member_id = ? AND session_id = ?");
    $checkStmt->execute([$memberId, $sessionId]);
    $record = $checkStmt->fetch(PDO::FETCH_ASSOC);

    if ($record) {
        if ($record['status'] === 'enrolled') {
            echo json_encode(['success' => false, 'message' => 'You are already enrolled.']);
            exit;
        } else {
            $updateStmt = $pdo->prepare("UPDATE enrollments SET status = 'enrolled', enrolled_at = CURRENT_TIMESTAMP WHERE id = ?");
            $updateStmt->execute([$record['id']]);
        }
    } else {
        $insertStmt = $pdo->prepare("INSERT INTO enrollments (member_id, session_id, status) VALUES (?, ?, 'enrolled')");
        $insertStmt->execute([$memberId, $sessionId]);
    }

    echo json_encode(['success' => true, 'message' => 'Enrolled successfully!']);
    exit;
}

if ($action === 'cancel') {
    $stmt = $pdo->prepare("UPDATE enrollments SET status = 'cancelled' WHERE member_id = ? AND session_id = ? AND status = 'enrolled'");
    $stmt->execute([$memberId, $sessionId]);

    if ($stmt->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Enrollment cancelled.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'You were not enrolled in this class.']);
    }
    exit;
}