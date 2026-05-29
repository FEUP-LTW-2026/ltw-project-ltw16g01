<?php
// File: trainer/notifications.php
// Description: Displays and manages system notifications for trainers.

require_once '../includes/session.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/notifications.php';
require_once '../template/notifications.php';

requireAuth('trainer');

$pdo = getDatabaseConnection();

$userId = (int)($_SESSION['user_id'] ?? 0);
$role = $_SESSION['active_role'] ?? $_SESSION['role'] ?? 'trainer';

// Filter by notification type: all, info, success, warning, error
$filter = $_GET['filter'] ?? 'all';

// Fetch notifications visible to this trainer
$notifications = getNotifications(
    $pdo,
    $userId,
    $role,
    100,
    0,
    $filter
);

// Totals and unread count
$totalCount = getTotalCountForUser(
    $pdo,
    $userId,
    $role,
    $filter
);

$unreadCount = getUnreadCount(
    $pdo,
    $userId,
    $role,
    $filter
);

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || $_SESSION['csrf'] !== $_POST['csrf']) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }

    $action = $_POST['action'] ?? '';
    $notifId = (int)($_POST['notification_id'] ?? 0);

    if ($action === 'mark_read' && $notifId > 0) {
        markNotificationAsRead($pdo, $notifId, $userId);
        header('Location: notifications.php?filter=' . urlencode($filter));
        exit;
    }

    if ($action === 'mark_all_read') {
        markAllNotificationsAsRead($pdo, $userId, $role);
        header('Location: notifications.php?filter=' . urlencode($filter));
        exit;
    }

    if ($action === 'delete' && $notifId > 0) {
        // Trainers typically should not delete global/system notifications.
        // Keep this only if your template exposes a delete button and you want it.
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
                    <p>System notifications for trainers</p>
                </div>
                <div class="notification-header-actions">
                    <?php if ($unreadCount > 0): ?>
                        <form method="post">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                            <input type="hidden" name="action" value="mark_all_read">
                            <button type="submit" class="secondary-button btn-sm">Mark all as read</button>
                        </form>
                    <?php endif; ?>
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

        <div class="results-bar" id="summary-bar">
            <p class="results-count">
                Total: <?= (int)$totalCount ?> notifications (<?= (int)$unreadCount ?> unread)
            </p>
        </div>

        <?php output_notifications_list($notifications); ?>
    </div>
</main>

<script src="../js/notifications.js"></script>

<?php output_footer(); ?>