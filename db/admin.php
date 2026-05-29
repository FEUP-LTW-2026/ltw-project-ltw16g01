<?php
/**
 * admin.php – Functions for the admin dashboard.
 */

function getAdminStats($pdo) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'member' AND active = 1");
    $stmt->execute();
    $members = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE role = 'trainer' AND active = 1");
    $stmt->execute();
    $trainers = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT cs.id) FROM class_schedules cs JOIN classes c ON cs.class_id = c.id WHERE c.active = 1");
    $stmt->execute();
    $classes = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM equipment WHERE status IN ('maintenance', 'unavailable')");
    $stmt->execute();
    $equipmentIssues = $stmt->fetchColumn();

    return [
        'members' => $members,
        'trainers' => $trainers,
        'classes' => $classes,
        'equipment_issues' => $equipmentIssues
    ];
}
function getMembers($pdo) {
    $stmt = $pdo->prepare("SELECT id, name, username, email, created_at, active FROM users WHERE role = 'member' ORDER BY created_at DESC");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTrainers($pdo) {
    $stmt = $pdo->prepare("SELECT id, name, username, email, active FROM users WHERE role = 'trainer' ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getClasses($pdo) {
    $sql = "
        SELECT 
            cs.id AS schedule_id,
            c.name AS class_name,
            ct.label AS class_type_label,
            u.name AS trainer_name,
            cs.day_of_week,
            cs.start_time,
            cs.location,
            (
                SELECT COUNT(*)
                FROM enrollments e
                JOIN class_sessions sess ON e.session_id = sess.id
                WHERE sess.schedule_id = cs.id
                  AND e.status = 'enrolled'
            ) AS enrolled_count
        FROM class_schedules cs
        JOIN classes c ON cs.class_id = c.id
        JOIN class_types ct ON c.class_type_id = ct.id
        LEFT JOIN users u ON cs.trainer_id = u.id
        WHERE c.active = 1
        ORDER BY enrolled_count DESC
        LIMIT 10
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $classes = [];

    foreach ($results as $row) {
        $classes[] = [
            'name' => $row['class_name'],
            'type' => $row['class_type_label'],
            'trainer_name' => $row['trainer_name'] ?? 'No trainer',
            'day_of_week' => $row['day_of_week'],
            'start_time' => $row['start_time'],
            'location' => $row['location']
        ];
    }

    return $classes;
}

function getEquipment($pdo) {
    $stmt = $pdo->prepare("
        SELECT 
            e.id,
            e.name,
            e.quantity,
            e.available,
            e.status,
            e.notes,
            ec.label AS category
        FROM equipment e
        JOIN equipment_categories ec ON e.category_id = ec.id
        ORDER BY ec.label, e.name
    ");

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRecentNotifications(PDO $pdo, int $userId, string $role, int $limit = 5): array
{
    $sql = "
        SELECT
            n.*,
            COALESCE(nt.label, 'Info') AS type_label,
            COALESCE(nt.css_class, 'info') AS css_class,
            COALESCE(nt.icon, 'bell') AS icon,
            CASE WHEN nr.id IS NOT NULL THEN 1 ELSE 0 END AS is_read
        FROM notifications n
        LEFT JOIN notification_types nt
            ON nt.id = n.notification_type_id
        LEFT JOIN notification_reads nr
            ON nr.notification_id = n.id
            AND nr.user_id = ?
        WHERE " . getVisibleNotificationsWhereSql() . "
        ORDER BY n.created_at DESC
        LIMIT ?
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $userId,   // nr.user_id = ?
        $userId,   // n.user_id = ?
        $role,     // n.target_role = ?
        $limit
    ]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMonthlyGrowth($pdo) {
    $currentMonthStart = date('Y-m-01');
    $lastMonthStart = date('Y-m-01', strtotime('-1 month'));
    $lastMonthEnd = date('Y-m-t', strtotime('-1 month'));

    $stmt = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN created_at >= ? THEN 1 END) as current_count,
            COUNT(CASE WHEN created_at BETWEEN ? AND ? THEN 1 END) as last_count
        FROM users
        WHERE role = 'member'
    ");

    $stmt->execute([$currentMonthStart, $lastMonthStart, $lastMonthEnd]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    $current = (int)$result['current_count'];
    $last = (int)$result['last_count'];

    if ($last == 0) {
        return $current > 0 ? 100 : 0;
    }

    return round(($current - $last) / $last * 100);
}

function getMonthlyEnrollments($pdo) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*) as total
        FROM enrollments
        WHERE enrolled_at >= date('now', '-30 days')
    ");

    $stmt->execute();
    return (int)$stmt->fetchColumn();
}
?>