<?php
require_once '../includes/session.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/notifications.php';
require_once '../template/notifications.php';

requireAuth('admin');

$pdo = getDatabaseConnection();
$filter = $_GET['filter'] ?? 'all';

$notifications = getNotifications($pdo, 100, 0, $filter);
$totalCount = getTotalCountForUser($pdo, $filter);
$unreadCount = getUnreadCount($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || $_SESSION['csrf'] !== $_POST['csrf']) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }

    $action = $_POST['action'] ?? '';
    $notifId = (int)($_POST['notification_id'] ?? 0);

    if ($action === 'mark_read' && $notifId) {
        markNotificationAsRead($pdo, $notifId);
        header('Location: notifications.php?filter=' . urlencode($filter));
        exit;
    }
    if ($action === 'mark_all_read') {
        markAllNotificationsAsRead($pdo);
        header('Location: notifications.php?filter=' . urlencode($filter));
        exit;
    }
    if ($action === 'delete' && $notifId) {
        deleteNotification($pdo, $notifId);
        header('Location: notifications.php?filter=' . urlencode($filter));
        exit;
    }
}

output_header('notifications.css');
?>

<link rel="stylesheet" href="../css/pages/form.css">

<main class="main">
    <div class="container">
        <header class="page-header">
            <div class="page-header-row">
                <div>
                    <h1>Notifications</h1>
                    <p>System notifications for administrators</p>
                </div>
                <div class="notification-header-actions">
                    <form method="post" style="display: inline;">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                        <input type="hidden" name="action" value="mark_all_read">
                        <button type="submit" class="secondary-button btn-sm">Mark all as read</button>
                    </form>
                </div>
            </div>
        </header>

        <div class="filter-bar">
            <div class="filter-group">
                <label class="form-label">Filter by type</label>
                <select id="type-filter" class="form-select" onchange="window.location.href='notifications.php?filter='+encodeURIComponent(this.value)">
                    <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
                    <option value="info" <?= $filter === 'info' ? 'selected' : '' ?>>Info</option>
                    <option value="success" <?= $filter === 'success' ? 'selected' : '' ?>>Success</option>
                    <option value="warning" <?= $filter === 'warning' ? 'selected' : '' ?>>Warning</option>
                    <option value="error" <?= $filter === 'error' ? 'selected' : '' ?>>Error</option>
                </select>
            </div>
        </div>

        <div class="results-bar" style="margin-bottom: 15px; margin-top: 15px;">
            <p class="results-count">
                Total: <?= (int)$totalCount ?> notifications (<?= (int)$unreadCount ?> unread)
            </p>
        </div>

        <?php output_notifications_list($notifications); ?>
    </div>
</main>

<script src="../js/notifications.js"></script>

<?php output_footer(); ?>