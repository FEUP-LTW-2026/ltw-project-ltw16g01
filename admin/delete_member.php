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

// Get member ID from URL query string
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: members.php?error=invalid_id');
    exit;
}

// Fetch member details before deletion (name, username, email, membership plan)
$stmt = $pdo->prepare("
    SELECT 
        u.id, 
        u.name, 
        u.username, 
        u.email,
        mp.membership_plan
    FROM users u
    LEFT JOIN member_profiles mp ON u.id = mp.user_id
    WHERE u.id = ? AND u.role = 'member'
");
$stmt->execute([$id]);
$member = $stmt->fetch(PDO::FETCH_ASSOC);

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