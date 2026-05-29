<?php
require_once '../includes/session.php';

require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/members.php';
require_once '../template/members.php';

requireAuth('admin');

// Create the database connection.
$pdo = getDatabaseConnection();

// Build filters from the GET query string.
// This request only reads data, so CSRF protection is not required.
$filters = [
    'name' => $_GET['name'] ?? '',
    'plan' => $_GET['plan'] ?? ''
];

// Load members using the selected filters.
$members = getAllMembersAdmin($pdo, $filters);

// Render the members table HTML.
output_members_admin_table($members);