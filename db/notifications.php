<?php
// db/notifications.php

function createNotification($pdo, $userId, $message, $type = 'info') {
    $stmt = $pdo->prepare("SELECT id FROM notification_types WHERE name = ?");
    $stmt->execute([$type]);
    $typeId = $stmt->fetchColumn();
    if (!$typeId) $typeId = 1;
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, notification_type_id, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
    return $stmt->execute([$userId, $message, $typeId]);
}

function getNotificationsForUser($pdo, $userId, $limit = 100, $offset = 0, $typeFilter = 'all') {
    $sql = "SELECT n.*, nt.label as type_label, nt.css_class
            FROM notifications n
            JOIN notification_types nt ON n.notification_type_id = nt.id
            WHERE n.user_id = ?";
    $params = [$userId];
    if ($typeFilter !== 'all') {
        $sql .= " AND nt.name = ?";
        $params[] = $typeFilter;
    }
    $sql .= " ORDER BY n.created_at DESC LIMIT ? OFFSET ?";
    $params[] = $limit;
    $params[] = $offset;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTotalCountForUser($pdo, $userId, $typeFilter = 'all') {
    $sql = "SELECT COUNT(*) FROM notifications n
            JOIN notification_types nt ON n.notification_type_id = nt.id
            WHERE n.user_id = ?";
    $params = [$userId];
    if ($typeFilter !== 'all') {
        $sql .= " AND nt.name = ?";
        $params[] = $typeFilter;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
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

function getNotificationTypes($pdo) {
    $stmt = $pdo->query("SELECT id, name, label, css_class FROM notification_types WHERE active = 1 ORDER BY label");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}