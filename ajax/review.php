<?php
session_start();

require_once '../includes/auth.php';
require_once '../db/connection.php';

header('Content-Type: application/json');

if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'member') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Unauthorized.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
if (!$input) {
    echo json_encode(['success' => false, 'message' => 'Invalid JSON.']);
    exit;
}

$sessionId = (int)($input['session_id'] ?? 0);
$rating = (int)($input['rating'] ?? 0);
$comment = trim($input['comment'] ?? '');

$memberId = (int)$_SESSION['user_id'];

if ($sessionId <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode(['success' => false, 'message' => 'Invalid session or rating.']);
    exit;
}

$pdo = getDatabaseConnection();

// Verificar se o membro participou nesta sessão (aula passada)
$check = $pdo->prepare("
    SELECT 1 
    FROM enrollments e
    JOIN class_sessions sess ON e.session_id = sess.id
    WHERE e.member_id = ? AND sess.id = ? 
      AND e.status = 'enrolled' AND sess.session_date < date('now')
    LIMIT 1
");
$check->execute([$memberId, $sessionId]);
if (!$check->fetch()) {
    echo json_encode(['success' => false, 'message' => 'You can only review classes you have attended.']);
    exit;
}

// Inserir ou atualizar avaliação
$stmt = $pdo->prepare("
    INSERT INTO reviews (member_id, session_id, rating, comment)
    VALUES (?, ?, ?, ?)
    ON CONFLICT(member_id, session_id) DO UPDATE SET
        rating = excluded.rating,
        comment = excluded.comment,
        created_at = CURRENT_TIMESTAMP
");
$stmt->execute([$memberId, $sessionId, $rating, $comment]);

echo json_encode(['success' => true, 'message' => 'Review saved successfully!']);