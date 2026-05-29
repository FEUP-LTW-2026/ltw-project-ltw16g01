<?php

function createNotification($pdo, $userId, $message, $type = 'info') {
    $stmt = $pdo->prepare("SELECT id FROM notification_types WHERE name = ?");
    $stmt->execute([$type]);
    $typeId = $stmt->fetchColumn();
    if (!$typeId) $typeId = 1;
    $stmt = $pdo->prepare("INSERT INTO notifications (user_id, message, notification_type_id, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
    return $stmt->execute([$userId, $message, $typeId]);
}

function getNotifications($pdo, $limit = 100, $offset = 0, $typeFilter = 'all', $excludePersonal = false) {
    $adminId = $_SESSION['user_id'];
    $sql = "SELECT n.*, nt.label as type_label, nt.css_class,
                   CASE WHEN nr.id IS NOT NULL THEN 1 ELSE 0 END as is_read
            FROM notifications n
            JOIN notification_types nt ON n.notification_type_id = nt.id
            LEFT JOIN notification_reads nr ON n.id = nr.notification_id AND nr.admin_id = ?
            WHERE n.user_id = ?";
    $params = [$adminId, $adminId];
    if ($excludePersonal) {
        $sql .= " AND LOWER(n.message) NOT LIKE 'your%'";
    }
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

function getTotalCountForUser($pdo, $typeFilter = 'all', $excludePersonal = false) {
    $adminId = $_SESSION['user_id'];
    $sql = "SELECT COUNT(*) FROM notifications n
            JOIN notification_types nt ON n.notification_type_id = nt.id
            WHERE n.user_id = ?";
    $params = [$adminId];
    if ($excludePersonal) {
        $sql .= " AND LOWER(n.message) NOT LIKE 'your%'";
    }
    if ($typeFilter !== 'all') {
        $sql .= " AND nt.name = ?";
        $params[] = $typeFilter;
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return (int)$stmt->fetchColumn();
}

function getUnreadCount($pdo, $excludePersonal = false) {
    $adminId = $_SESSION['user_id'];
    $sql = "SELECT COUNT(*) FROM notifications n
            WHERE user_id = ? AND read_at IS NULL";
    if ($excludePersonal) {
        $sql .= " AND LOWER(message) NOT LIKE 'your%'";
    }
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

function getMemberNotifications($pdo, $memberId, $limit = 50) {
    $sql = "SELECT n.*, nt.label as type_label, nt.css_class
            FROM notifications n
            JOIN notification_types nt ON n.notification_type_id = nt.id
            WHERE n.user_id = ?
            ORDER BY n.created_at DESC
            LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$memberId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getUnreadMemberNotificationCount($pdo, $memberId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = ? AND read_at IS NULL");
    $stmt->execute([$memberId]);
    return (int)$stmt->fetchColumn();
}

function markMemberNotificationAsRead($pdo, $notificationId, $memberId) {
    $stmt = $pdo->prepare("UPDATE notifications SET read_at = CURRENT_TIMESTAMP WHERE id = ? AND user_id = ? AND read_at IS NULL");
    return $stmt->execute([$notificationId, $memberId]);
}

function markAllMemberNotificationsAsRead($pdo, $memberId) {
    $stmt = $pdo->prepare("UPDATE notifications SET read_at = CURRENT_TIMESTAMP WHERE user_id = ? AND read_at IS NULL");
    return $stmt->execute([$memberId]);
}

function deleteMemberNotification($pdo, $notificationId, $memberId) {
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    return $stmt->execute([$notificationId, $memberId]);
}