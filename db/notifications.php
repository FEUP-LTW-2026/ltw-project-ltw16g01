<?php
/**
 * notifications.php – Functions for creating and managing notifications.
 * Handles storing, marking as read, and retrieving notifications for users.
 */

function createNotification($pdo, $userId, $message, $type = 'info') {
    $stmt = $pdo->prepare("SELECT id FROM notification_types WHERE name = ?");
    $stmt->execute([$type]);
    $typeId = $stmt->fetchColumn();
    if (!$typeId) $typeId = 1;
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, notification_type_id, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
    return $stmt->execute([$userId, $message, $typeId]);
}

function getNotifications($pdo, $limit = 100, $offset = 0, $typeFilter = 'all') {
    $adminId = $_SESSION['user_id'];
    $sql = "SELECT n.*, nt.label as type_label, nt.css_class,
                   CASE WHEN nr.id IS NOT NULL THEN 1 ELSE 0 END as is_read
            FROM notifications n
            JOIN notification_types nt ON n.notification_type_id = nt.id
            LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.admin_id = ?
            ";
    $params = [$adminId];
    if ($typeFilter !== 'all') {
        $sql .= " WHERE nt.name = ?";
        $params[] = $typeFilter;
    }
    $sql .= " ORDER BY n.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTotalCountForUser($pdo, $typeFilter = 'all') {
    $sql = "SELECT COUNT(*) FROM notifications n
            JOIN notification_types nt ON n.notification_type_id = nt.id";
    $params = [];
    if ($typeFilter !== 'all') {
        $sql .= " WHERE nt.name = ?";
        $params[] = $typeFilter;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

function getUnreadCount($pdo) {
    $adminId = $_SESSION['user_id'];
    $sql = "SELECT COUNT(*) FROM notifications n
            WHERE NOT EXISTS (SELECT 1 FROM notification_reads nr
                              WHERE nr.notification_id = n.id AND nr.admin_id = ?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$adminId]);
    return (int)$stmt->fetchColumn();
}

function markNotificationAsRead($pdo, $notificationId) {
    $adminId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO notification_reads (notification_id, admin_id) VALUES (?, ?)");
    return $stmt->execute([$notificationId, $adminId]);
}

function markAllNotificationsAsRead($pdo) {
    $adminId = $_SESSION['user_id'];
    $stmt = $pdo->prepare("INSERT OR IGNORE INTO notification_reads (notification_id, admin_id)
                           SELECT id, ? FROM notifications n
                           WHERE NOT EXISTS (SELECT 1 FROM notification_reads nr
                                             WHERE nr.notification_id = n.id AND nr.admin_id = ?)");
    return $stmt->execute([$adminId, $adminId]);
}

function deleteNotification($pdo, $notificationId) {
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ?");
    return $stmt->execute([$notificationId]);
}