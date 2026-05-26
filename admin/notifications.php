<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/notifications.php';
require_once '../template/notifications.php';

requireAuth('admin');

$pdo = getDatabaseConnection();
$adminId = $_SESSION['user_id'];

// Processar ações POST (AJAX pode ser usado, mas aqui faremos via POST simples)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $notifId = (int)($_POST['notification_id'] ?? 0);

    if ($action === 'mark_read' && $notifId) {
        markNotificationAsRead($pdo, $notifId, $adminId);
        header('Location: notifications.php');
        exit;
    }
    if ($action === 'mark_all_read') {
        markAllNotificationsAsRead($pdo, $adminId);
        header('Location: notifications.php');
        exit;
    }
    if ($action === 'delete' && $notifId) {
        deleteNotification($pdo, $notifId, $adminId);
        header('Location: notifications.php');
        exit;
    }
}

$notifications = getNotificationsForUser($pdo, $adminId);
$unreadCount = getUnreadCountForUser($pdo, $adminId);

output_header('notifications.css');
?>

<main class="main">
    <div class="container">
        <header class="page-header">
            <div class="page-header-row">
                <div>
                    <h1>Notifications</h1>
                    <p>System notifications for administrators</p>
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
            <p class="results-count">Total: <?= count($notifications) ?> notifications (<?= $unreadCount ?> unread)</p>
        </div>

        <?php output_notifications_list($notifications); ?>
    </div>
</main>

<script src="../js/notifications.js"></script>

<?php output_footer('../admin/'); ?>