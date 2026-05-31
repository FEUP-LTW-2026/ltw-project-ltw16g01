<?php
// File: admin/delete_member.php
// Description: Handles deletion of a member by ID. Redirects after action.

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/members.php';           // Contains deleteMember() function
require_once '../db/notifications.php'; // createNotification()

// Restrict access to admin users only
requireAuth('admin');

// Get database connection
$pdo = getDatabaseConnection();

// Only allow POST requests for delete actions
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed.');
}

// CSRF protection
if (!isset($_POST['csrf'], $_SESSION['csrf']) || $_SESSION['csrf'] !== $_POST['csrf']) {
    http_response_code(403);
    exit('Invalid CSRF token.');
}

// Get member ID from POST body
$id = (int)($_POST['id'] ?? 0);

if ($id <= 0) {
    header('Location: members.php?error=invalid_id');
    exit;
}

// Fetch member details before deletion (name, username, email, membership plan)
$member = getMemberDeleteDetails($pdo, $id);

if (!$member) {
    // Member not found
    header('Location: members.php?error=not_found');
    exit;
}

$memberName = $member['name'];
$memberUsername = $member['username'];
$memberEmail = $member['email'];
$memberPlan = $member['membership_plan'] ?? 'basic';
$planLabel = ucfirst($memberPlan);

// Attempt to delete the member
if (deleteMember($pdo, $id)) {
    // Success: notify all administrators (role-based notification)
    $adminMessage = "Member deleted: {$memberName} (username: {$memberUsername}, email: {$memberEmail}, plan: {$planLabel}).";
    
    createNotification(
        $pdo,
        $adminMessage,   // message
        'warning',       // type (deletion is a warning)
        null,            // no specific user
        'admin',         // target_role = 'admin'
        'role',          // scope = 'role'
        'Member removed' // optional title
    );
    
    header('Location: members.php?msg=deleted');
    exit;
}

// Failure: redirect with error message
header('Location: members.php?error=delete_failed');
exit;
?>