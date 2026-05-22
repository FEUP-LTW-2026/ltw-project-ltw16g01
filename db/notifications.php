<?php
// db/notifications.php
function createNotification($pdo, $userId, $message, $type = 'info') {
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, type, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
    return $stmt->execute([$userId, $message, $type]);
}

function getNotificationsForUser($pdo, $userId, $limit = 50, $offset = 0) {
    $stmt = $pdo->prepare("
        SELECT n.*, nt.label as type_label, nt.css_class
        FROM notifications n
        JOIN notification_types nt ON n.notification_type_id = nt.id
        WHERE n.user_id = ?
        ORDER BY n.created_at DESC
        LIMIT ? OFFSET ?
    ");
    $stmt->execute([$userId, $limit, $offset]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUnreadCountForUser($pdo, $userId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL");
    $stmt->execute([$userId]);
    return (int)$stmt->fetchColumn();
}

function markNotificationAsRead($pdo, $notificationId, $userId) {
    $stmt = $pdo->prepare("UPDATE notifications SET read_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ?");
    return $stmt->execute([$notificationId, $userId]);
}

function markAllNotificationsAsRead($pdo, $userId) {
    $stmt = $pdo->prepare("UPDATE notifications SET read_at = CURRENT_TIMESTAMP WHERE user_id = ? AND read_at IS NULL");
    return $stmt->execute([$userId]);
}

function deleteNotification($pdo, $notificationId, $userId) {
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    return $stmt->execute([$notificationId, $userId]);
}