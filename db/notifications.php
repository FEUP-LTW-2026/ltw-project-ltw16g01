<?php
function getCurrentNotificationUserId(): ?int
{
    return isset($_SESSION['user_id']) ? (int) $_SESSION['user_id'] : null;
}

function getCurrentNotificationRole(): ?string
{
    return $_SESSION['active_role'] ?? $_SESSION['role'] ?? null;
}

function getVisibleNotificationsWhereSql(): string
{
    return "(n.user_id = ? OR n.target_role = ? OR n.target_role = 'all')";
}


function getNotificationTypeId(PDO $pdo, string $type = 'info'): int
{
    $stmt = $pdo->prepare("
        SELECT id
        FROM notification_types
        WHERE name = ? AND active = 1
        LIMIT 1
    ");
    $stmt->execute([$type]);
    $typeId = $stmt->fetchColumn();

    if ($typeId !== false) {
        return (int) $typeId;
    }

    $stmt = $pdo->prepare("
        SELECT id
        FROM notification_types
        WHERE name = 'info' AND active = 1
        LIMIT 1
    ");
    $stmt->execute();
    $typeId = $stmt->fetchColumn();

    if ($typeId !== false) {
        return (int) $typeId;
    }

    $stmt = $pdo->prepare("
        SELECT id
        FROM notification_types
        WHERE active = 1
        ORDER BY id ASC
        LIMIT 1
    ");
    $stmt->execute();
    $typeId = $stmt->fetchColumn();

    if ($typeId !== false) {
        return (int) $typeId;
    }

    throw new RuntimeException('No active notification types available.');
}

function createNotification(PDO $pdo, ...$args): bool
{
    if (empty($args)) {
        throw new InvalidArgumentException('createNotification requires arguments.');
    }

    $userId = null;
    $targetRole = null;
    $scope = 'personal';
    $title = null;

    if (is_numeric($args[0])) {
        $userId = (int) $args[0];
        $message = (string)($args[1] ?? '');
        $type = (string)($args[2] ?? 'info');
        $title = $args[3] ?? null;
    } else {
        $message = (string)($args[0] ?? '');
        $type = (string)($args[1] ?? 'info');
        $userId = $args[2] ?? null;
        $targetRole = $args[3] ?? null;
        $scope = $args[4] ?? ($targetRole ? 'role' : 'personal');
        $title = $args[5] ?? null;
    }

    if ($message === '') {
        throw new InvalidArgumentException('Notification message cannot be empty.');
    }

    $typeId = getNotificationTypeId($pdo, $type);

    $stmt = $pdo->prepare("
        INSERT INTO notifications (
            user_id,
            target_role,
            scope,
            title,
            message,
            notification_type_id,
            created_at
        )
        VALUES (?, ?, ?, ?, ?, ?, CURRENT_TIMESTAMP)
    ");

    return $stmt->execute([
        $userId,
        $targetRole,
        $scope,
        $title,
        $message,
        $typeId
    ]);
}

function getNotifications(PDO $pdo, ...$args): array
{
    $userId = getCurrentNotificationUserId();
    $role = getCurrentNotificationRole();
    $limit = 100;
    $offset = 0;
    $typeFilter = 'all';
    $excludePersonal = false;

    if (
        count($args) >= 2 &&
        is_numeric($args[0]) &&
        is_string($args[1]) &&
        in_array($args[1], ['admin', 'member', 'trainer', 'all'], true)
    ) {
        $userId = (int) $args[0];
        $role = $args[1];
        $limit = isset($args[2]) ? (int) $args[2] : 100;
        $offset = isset($args[3]) ? (int) $args[3] : 0;
        $typeFilter = $args[4] ?? 'all';
    } else {
        $limit = isset($args[0]) ? (int) $args[0] : 100;
        $offset = isset($args[1]) ? (int) $args[1] : 0;
        $typeFilter = $args[2] ?? 'all';
        $excludePersonal = (bool)($args[3] ?? false);
    }

    if ($userId === null || $role === null) {
        return [];
    }

    $sql = "
        SELECT
            n.*,
            COALESCE(nt.label, 'Info') AS type_label,
            COALESCE(nt.css_class, 'info') AS css_class,
            COALESCE(nt.icon, 'bell') AS icon,
            CASE
                WHEN nr.id IS NOT NULL THEN 1
                ELSE 0
            END AS is_read
        FROM notifications n
        LEFT JOIN notification_types nt
            ON nt.id = n.notification_type_id
        LEFT JOIN notification_reads nr
            ON nr.notification_id = n.id
            AND nr.user_id = ?
        WHERE
            (
                n.user_id = ?
                OR n.target_role = ?
                OR n.target_role = 'all'
            )
    ";

    $params = [
        $userId,
        $userId,
        $role
    ];

    if ($excludePersonal) {
        $sql .= " AND COALESCE(n.scope, 'personal') <> 'personal' ";
    }

    if ($typeFilter !== 'all') {
        $sql .= " AND nt.name = ? ";
        $params[] = $typeFilter;
    }

    $sql .= "
        ORDER BY n.created_at DESC
        LIMIT ?
        OFFSET ?
    ";

    $params[] = $limit;
    $params[] = $offset;

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTotalCountForUser(PDO $pdo, int $userId, string $role, string $typeFilter = 'all'): int
{
    $sql = "
        SELECT COUNT(DISTINCT n.id)
        FROM notifications n
        LEFT JOIN notification_types nt
            ON nt.id = n.notification_type_id
        WHERE
            (
                n.user_id = ?
                OR n.target_role = ?
                OR n.target_role = 'all'
            )
    ";

    $params = [
        $userId,
        $role
    ];

    if ($typeFilter !== 'all') {
        $sql .= " AND nt.name = ? ";
        $params[] = $typeFilter;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return (int) $stmt->fetchColumn();
}

function getUnreadCount(PDO $pdo, int $userId, string $role, string $typeFilter = 'all'): int
{
    $sql = "
        SELECT COUNT(DISTINCT n.id)
        FROM notifications n
        LEFT JOIN notification_reads nr
            ON nr.notification_id = n.id
            AND nr.user_id = ?
        LEFT JOIN notification_types nt
            ON nt.id = n.notification_type_id
        WHERE
            nr.id IS NULL
            AND (
                n.user_id = ?
                OR n.target_role = ?
                OR n.target_role = 'all'
            )
    ";

    $params = [
        $userId,
        $userId,
        $role
    ];

    if ($typeFilter !== 'all') {
        $sql .= " AND nt.name = ? ";
        $params[] = $typeFilter;
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return (int) $stmt->fetchColumn();
}

function markNotificationAsRead(PDO $pdo, ...$args): bool
{
    $notificationId = (int)($args[0] ?? 0);
    $userId = isset($args[1]) ? (int) $args[1] : getCurrentNotificationUserId();

    if ($notificationId <= 0 || $userId === null) {
        return false;
    }

    $stmt = $pdo->prepare("
        INSERT OR IGNORE INTO notification_reads (
            notification_id,
            user_id
        )
        VALUES (?, ?)
    ");

    return $stmt->execute([$notificationId, $userId]);
}

function markAllNotificationsAsRead(PDO $pdo, ...$args): bool
{
    $userId = isset($args[0]) ? (int) $args[0] : getCurrentNotificationUserId();
    $role = $args[1] ?? getCurrentNotificationRole();

    if ($userId === null || $role === null) {
        return false;
    }

    $stmt = $pdo->prepare("
        INSERT OR IGNORE INTO notification_reads (
            notification_id,
            user_id
        )
        SELECT
            n.id,
            ?
        FROM notifications n
        WHERE
            (
                n.user_id = ?
                OR n.target_role = ?
                OR n.target_role = 'all'
            )
    ");

    return $stmt->execute([
        $userId,
        $userId,
        $role
    ]);
}

function deleteNotification(PDO $pdo, int $notificationId): bool
{
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ?");
    return $stmt->execute([$notificationId]);
}

function getMemberNotifications(PDO $pdo, int $memberId, int $limit = 50): array
{
    return getNotifications($pdo, $memberId, 'member', $limit, 0, 'all');
}

function getUnreadMemberNotificationCount(PDO $pdo, int $memberId): int
{
    return getUnreadCount($pdo, $memberId, 'member');
}

function markMemberNotificationAsRead(PDO $pdo, int $notificationId, int $memberId): bool
{
    return markNotificationAsRead($pdo, $notificationId, $memberId);
}

function markAllMemberNotificationsAsRead(PDO $pdo, int $memberId): bool
{
    return markAllNotificationsAsRead($pdo, $memberId, 'member');
}

function deleteMemberNotification(PDO $pdo, int $notificationId, int $memberId): bool
{
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE id = ? AND user_id = ?");
    return $stmt->execute([$notificationId, $memberId]);
}

function notifyTrainerClassEnrollment(
    PDO $pdo,
    int $trainerId,
    string $memberName,
    string $className,
    string $action
): bool {

    $message = $action === 'enroll'
        ? "{$memberName} enrolled in your class '{$className}'."
        : "{$memberName} cancelled enrollment in your class '{$className}'.";

    return createNotification(
        $pdo,
        $trainerId,
        $message,
        'info',
        'Class Enrollment'
    );
}