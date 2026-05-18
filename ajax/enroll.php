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
$sessionId = (int)($input['session_id'] ?? 0);
$memberId = (int)$_SESSION['user_id'];

if (!$sessionId) {
    echo json_encode(['success' => false, 'message' => 'Invalid session.']);
    exit;
}

$stmt = $pdo->prepare("SELECT sess.id, sess.session_date, sess.status, sch.capacity, c.name AS class_name FROM class_sessions sess JOIN class_schedules sch ON sess.schedule_id = sch.id JOIN classes c ON sch.class_id = c.id WHERE sess.id = ? AND sess.session_date >= date('now') AND sess.status = 'scheduled'");
$stmt->execute([$sessionId]);
$session = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$session) {
    echo json_encode(['success' => false, 'message' => 'Session not found or already finished.']);
    exit;
}

if ($action === 'enroll') {
    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE session_id = ? AND status = 'enrolled'");
    $countStmt->execute([$sessionId]);
    $enrolled = (int)$countStmt->fetchColumn();

    if ($enrolled >= (int)$session['capacity']) {
        echo json_encode(['success' => false, 'message' => 'This class is full.']);
        exit;
    }

    $check = $pdo->prepare("SELECT id, status FROM enrollments WHERE member_id = ? AND session_id = ?");
    $check->execute([$memberId, $sessionId]);
    $record = $check->fetch(PDO::FETCH_ASSOC);

    if ($record) {
        if ($record['status'] === 'enrolled') {
            echo json_encode(['success' => false, 'message' => 'You are already enrolled in this class.']);
            exit;
        }

        $stmt = $pdo->prepare("UPDATE enrollments SET status = 'enrolled', enrolled_at = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$record['id']]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO enrollments (member_id, session_id, status) VALUES (?, ?, 'enrolled')");
        $stmt->execute([$memberId, $sessionId]);
    }

    echo json_encode(['success' => true, 'message' => 'Enrolled successfully.']);
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

echo json_encode(['success' => false, 'message' => 'Invalid action.']);