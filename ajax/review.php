<?php
require_once '../includes/session.php';

require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/reviews.php';

header('Content-Type: application/json');

// Only logged-in members can submit class reviews.
if (!isLoggedIn() || ($_SESSION['role'] ?? '') !== 'member') {
    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized.'
    ]);

    exit;
}

// This endpoint changes state, so only POST requests are allowed.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);

    echo json_encode([
        'success' => false,
        'message' => 'Method not allowed.'
    ]);

    exit;
}

// Decode the JSON request body.
$input = json_decode(file_get_contents('php://input'), true);

if (!$input) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid JSON.'
    ]);

    exit;
}

// Validate the CSRF token because this request changes state.
if (!isset($input['csrf']) || $_SESSION['csrf'] !== $input['csrf']) {
    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid CSRF token.'
    ]);

    exit;
}

$sessionId = (int)($input['session_id'] ?? 0);
$rating = (int)($input['rating'] ?? 0);
$comment = trim($input['comment'] ?? '');
$memberId = (int)$_SESSION['user_id'];

// Validate review input.
if ($sessionId <= 0 || $rating < 1 || $rating > 5) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid session or rating.'
    ]);

    exit;
}

// Create the database connection.
$pdo = getDatabaseConnection();

// Check if the member is allowed to review this session.
if (!memberCanReviewSession($pdo, $memberId, $sessionId)) {
    echo json_encode([
        'success' => false,
        'message' => 'You can only review classes you have attended.'
    ]);

    exit;
}

// Save or update the review.
if (!saveClassReview($pdo, $memberId, $sessionId, $rating, $comment)) {
    echo json_encode([
        'success' => false,
        'message' => 'Could not save review.'
    ]);

    exit;
}

echo json_encode([
    'success' => true,
    'message' => 'Review saved successfully!'
]);

exit;