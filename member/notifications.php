<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/notifications.php';
require_once '../template/notifications.php';

// Only members can access
requireAuth('member');

$pdo = getDatabaseConnection();
$memberId = $_SESSION['user_id'];

// Handle POST actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $notifId = (int)($_POST['notification_id'] ?? 0);

    if ($action === 'mark_read' && $notifId) {
        markMemberNotificationAsRead($pdo, $notifId, $memberId);
        header('Location: notifications.php');
        exit;
    }
    if ($action === 'mark_all_read') {
        markAllMemberNotificationsAsRead($pdo, $memberId);
        header('Location: notifications.php');
        exit;
    }
    if ($action === 'delete' && $notifId) {
        deleteMemberNotification($pdo, $notifId, $memberId);
        header('Location: notifications.php');
        exit;
    }
}

$notifications = getMemberNotifications($pdo, $memberId);
$unreadCount = getUnreadMemberNotificationCount($pdo, $memberId);

output_header('member-notifications.css');
?>

<main class="main">
    <div class="container">
        <header class="page-header">
            <div class="page-header-row">
                <div>
                    <h1>Notifications</h1>
                    <p>Your personal notifications</p>
                </div>
                <div class="notification-header-actions">
                    <?php if ($unreadCount > 0): ?>
                        <form method="post" style="display: inline;">
                            <input type="hidden" name="action" value="mark_all_read">
                            <button type="submit" class="secondary-button btn-sm">Mark all as read</button>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </header>

        <div class="results-bar">
            <p class="results-count">
                Total: <?= count($notifications) ?> notifications (<?= $unreadCount ?> unread)
            </p>
        </div>

        <?php output_member_notifications_list($notifications); ?>
    </div>
</main>

<?php output_footer(); ?>