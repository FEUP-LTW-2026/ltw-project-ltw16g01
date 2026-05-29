<?php
require_once '../includes/session.php';

header('Content-Type: application/json');

// Allowed roles that can be selected as the active area.
$allowedRoles = ['member', 'trainer', 'admin'];

$role = $_POST['role'] ?? '';

// Validate the CSRF token because this request changes session state.
if (!isset($_POST['csrf']) || $_SESSION['csrf'] !== $_POST['csrf']) {
    http_response_code(403);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid CSRF token.'
    ]);

    exit;
}

// Validate the requested active role.
if (!in_array($role, $allowedRoles, true)) {
    http_response_code(400);

    echo json_encode([
        'success' => false,
        'message' => 'Invalid role.'
    ]);

    exit;
}

// Store the selected active role in the session.
$_SESSION['active_role'] = $role;

echo json_encode([
    'success' => true,
    'message' => 'Active role updated.'
]);

exit;