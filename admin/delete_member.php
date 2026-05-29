<?php
// File: admin/delete_member.php
// Description: Handles deletion of a member by ID. Redirects after action.

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/members.php'; // Contains deleteMember() function

// Restrict access to admin users only
requireAuth('admin');

// Get database connection
$pdo = getDatabaseConnection();

// Get member ID from URL query string
$id = (int)($_GET['id'] ?? 0);

// Attempt to delete the member
if ($id > 0 && deleteMember($pdo, $id)) {
    // Success: redirect to members page with success message
    header('Location: members.php?msg=deleted');
    exit;
}

// Failure: redirect with error message
header('Location: members.php?error=delete_failed');
exit;
?>