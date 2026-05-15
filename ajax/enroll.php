<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/csrf.php';

header('Content-Type: application/json');

if (!isLoggedIn() || currentUser()['role'] !== 'member') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Não autorizado.']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método inválido.']);
    exit;
}

$user = currentUser();
$pdo  = getDB();

$contentType = $_SERVER['CONTENT_TYPE'] ?? '';
if (strpos($contentType, 'application/json') !== false) {
    $raw   = file_get_contents('php://input');
    $input = json_decode($raw, true) ?? [];
    
    if (isset($input['csrf_token'])) {
        $_POST['csrf_token'] = $input['csrf_token'];
    }
} else {
    $input = $_POST;
}

verifyCsrf();

$action    = $input['action'] ?? '';
$sessionId = (int)($input['session_id'] ?? 0);

if (!$sessionId) {
    echo json_encode(['success' => false, 'message' => 'Sessão inválida.']);
    exit;
}

// Fetch session
$stmt = $pdo->prepare("
    SELECT cs.*, c.capacity, c.name AS class_name
    FROM class_sessions cs
    JOIN classes c ON c.id = cs.class_id
    WHERE cs.id = ? AND cs.session_date >= date('now') AND cs.status = 'scheduled'
");
$stmt->execute([$sessionId]);
$session = $stmt->fetch();

if (!$session) {
    echo json_encode(['success' => false, 'message' => 'Sessão não encontrada ou já terminou.']);
    exit;
}

if ($action === 'enroll') {
    // Count current enrollments
    $cntStmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE session_id = ? AND status = 'enrolled'");
    $cntStmt->execute([$sessionId]);
    $enrolled = (int)$cntStmt->fetchColumn();

    if ($enrolled >= (int)$session['capacity']) {
        echo json_encode(['success' => false, 'message' => 'A aula está lotada.']);
        exit;
    }

    // Check existing record
    $chk = $pdo->prepare("SELECT id, status FROM enrollments WHERE member_id=? AND session_id=?");
    $chk->execute([$user['id'], $sessionId]);
    $record = $chk->fetch();

    if ($record) {
        if ($record['status'] === 'enrolled') {
            echo json_encode(['success' => false, 'message' => 'Já estás inscrito nesta aula.']);
            exit;
        }
        $pdo->prepare("UPDATE enrollments SET status='enrolled', enrolled_at=CURRENT_TIMESTAMP WHERE id=?")
            ->execute([$record['id']]);
    } else {
        $pdo->prepare("INSERT INTO enrollments (member_id, session_id, status) VALUES (?,?,'enrolled')")
            ->execute([$user['id'], $sessionId]);
    }

    addNotification(
        $user['id'],
        'Inscrito na aula "' . $session['class_name'] . '" em ' . date('d/m/Y', strtotime($session['session_date'])) . '.',
        'success'
    );

    echo json_encode(['success' => true, 'message' => 'Inscrito com sucesso!']);

} elseif ($action === 'cancel') {
    $del = $pdo->prepare("UPDATE enrollments SET status='cancelled' WHERE member_id=? AND session_id=? AND status='enrolled'");
    $del->execute([$user['id'], $sessionId]);

    if ($del->rowCount() > 0) {
        echo json_encode(['success' => true, 'message' => 'Inscrição cancelada.']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Não estavas inscrito.']);
    }

} else {
    echo json_encode(['success' => false, 'message' => 'Ação inválida.']);
}
