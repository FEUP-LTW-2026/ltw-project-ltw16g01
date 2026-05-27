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

$filter = $_GET['filter'] ?? 'all';
$notifications = getNotificationsForUser($pdo, $adminId, 100, 0, $filter);
$totalCount = getTotalCountForUser($pdo, $adminId, $filter);
$unreadCount = getUnreadCountForUser($pdo, $adminId);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $notifId = (int)($_POST['notification_id'] ?? 0);

    if ($action === 'mark_read' && $notifId) {
        markNotificationAsRead($pdo, $notifId, $adminId);
        header('Location: notifications.php?filter=' . urlencode($filter));
        exit;
    }
    if ($action === 'mark_all_read') {
        markAllNotificationsAsRead($pdo, $adminId);
        header('Location: notifications.php?filter=' . urlencode($filter));
        exit;
    }
    if ($action === 'delete' && $notifId) {
        deleteNotification($pdo, $notifId, $adminId);
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
                    <?php if ($unreadCount > 0): ?>
                        <form method="post" style="display: inline;">
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
                <select id="type-filter" class="form-select">
                    <option value="all" <?= $filter === 'all' ? 'selected' : '' ?>>All</option>
                    <option value="info" <?= $filter === 'info' ? 'selected' : '' ?>>Info</option>
                    <option value="success" <?= $filter === 'success' ? 'selected' : '' ?>>Success</option>
                    <option value="warning" <?= $filter === 'warning' ? 'selected' : '' ?>>Warning</option>
                    <option value="error" <?= $filter === 'error' ? 'selected' : '' ?>>Error</option>
                </select>
            </div>
        </div>

        <div class="results-bar" style="margin-bottom: 15px; margin-top: 15px;">
            <p class="results-count">Total: <?= $totalCount ?> notifications (<?= $unreadCount ?> unread)</p>
        </div>

        <?php output_notifications_list($notifications); ?>
    </div>
</main>

<script>
    document.getElementById('type-filter').addEventListener('change', function() {
        const filter = this.value;
        window.location.href = 'notifications.php?filter=' + encodeURIComponent(filter);
    });

    document.querySelectorAll('.mark-read-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.dataset.id;
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `<input type="hidden" name="action" value="mark_read"><input type="hidden" name="notification_id" value="${id}">`;
            document.body.appendChild(form);
            form.submit();
        });
    });
    document.querySelectorAll('.delete-notif-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (!confirm('Delete this notification?')) return;
            const id = this.dataset.id;
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `<input type="hidden" name="action" value="delete"><input type="hidden" name="notification_id" value="${id}">`;
            document.body.appendChild(form);
            form.submit();
        });
    });
</script>

<?php output_footer(); ?>