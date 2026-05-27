<?php
require_once '../includes/session.php';

header('Content-Type: application/json');

$allowedRoles = ['member', 'trainer', 'admin'];

$role = $_POST['role'] ?? '';

if (!isset($_POST['csrf']) || $_SESSION['csrf'] !== $_POST['csrf']) {
    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid CSRF token.'
    ]);

    exit;
}

if (!in_array($role, $allowedRoles, true)) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid role.'
    ]);

    exit;
}

$_SESSION['active_role'] = $role;

echo json_encode([
    'success' => true,
    'message' => 'Active role updated.'
]);

exit;