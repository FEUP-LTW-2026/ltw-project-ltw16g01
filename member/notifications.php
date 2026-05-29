<?php
// File: member/notifications.php
// Description: Displays and manages system notifications for members.

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/notifications.php';
require_once '../template/notifications.php';
require_once '../db/personal_bookings.php';

// Restrict access to member users only
requireAuth('member');

// Get database connection
$pdo = getDatabaseConnection();

// Get filter parameter from URL (all, info, success, warning, error)
$filter = $_GET['filter'] ?? 'all';

// Fetch notifications based on filter (limit 100, offset 0)
$userId = $_SESSION['user_id'];
$role = $_SESSION['active_role'];

$notifications = getNotifications(
    $pdo,
    $userId,
    $role,
    100,
    0,
    $filter
);

// Get total count for current filter and unread count for badge
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

// Handle POST actions (mark as read, mark all read, delete)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    if (!isset($_POST['csrf']) || $_SESSION['csrf'] !== $_POST['csrf']) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }

    $action = $_POST['action'] ?? '';
    $notifId = (int)($_POST['notification_id'] ?? 0);

    // Mark single notification as read
    if ($action === 'mark_read' && $notifId) {
        markNotificationAsRead(
            $pdo,
            $notifId,
            $userId
        );
        header('Location: notifications.php?filter=' . urlencode($filter));
        exit;
    }
    // Mark all notifications as read
    if ($action === 'mark_all_read') {
        markAllNotificationsAsRead(
            $pdo,
            $userId,
            $role
        );
        header('Location: notifications.php?filter=' . urlencode($filter));
        exit;
    }
    // Delete a notification
    if ($action === 'delete' && $notifId) {
        deleteNotification($pdo, $notifId);
        header('Location: notifications.php?filter=' . urlencode($filter));
        exit;
    }
}

// Output page header with notifications-specific CSS
output_header('notifications.css');
?>

<!-- Additional CSS for form elements (filter bar) -->
<link rel="stylesheet" href="../css/pages/form.css">

<main class="main">
    <div class="container">
        <!-- Page header with title and "Mark all as read" button -->
        <header class="page-header">
            <div class="page-header-row">
                <div>
                    <h1>Notifications</h1>
                    <p>System notifications for members</p>
                </div>
                <div class="notification-header-actions">
                    <form method="post">
                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                        <input type="hidden" name="action" value="mark_all_read">
                        <button type="submit" class="secondary-button btn-sm">Mark all as read</button>
                    </form>
                </div>
            </div>
        </header>

        <!-- Filter bar for notification types (uses onchange to reload page) -->
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

        <!-- Summary bar showing total and unread counts -->
        <div class="results-bar" id = "summary-bar">
            <p class="results-count">
                Total: <?= (int)$totalCount ?> notifications (<?= (int)$unreadCount ?> unread)
            </p>
        </div>

        <!-- Notifications list rendered by template function -->
        <?php output_notifications_list($notifications); ?>
    </div>
</main>

<!-- External JavaScript for notification interactions (mark read, delete via AJAX) -->
<script src="../js/notifications.js"></script>

<?php output_footer(); ?>