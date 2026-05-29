<?php
/**
 * reports.php – Functions for generating admin reports and KPIs.
 */

function getKpis($pdo, $startDate = null, $endDate = null) {
    if (!$startDate) $startDate = date('Y-m-d', strtotime('-30 days'));
    if (!$endDate) $endDate = date('Y-m-d');

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM users
        WHERE role = 'member'
          AND active = 1
    ");
    $stmt->execute();
    $activeMembers = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM users
        WHERE role = 'trainer'
          AND active = 1
    ");
    $stmt->execute();
    $activeTrainers = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT cs.id)
        FROM class_schedules cs
        JOIN classes c ON cs.class_id = c.id
        WHERE c.active = 1
    ");
    $stmt->execute();
    $activeClassesPerWeek = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT AVG(occ.enrolled_count * 1.0 / cs.capacity) * 100 as avg_occupancy
        FROM class_sessions sess
        JOIN class_schedules cs ON sess.schedule_id = cs.id
        LEFT JOIN (
            SELECT session_id, COUNT(*) as enrolled_count
            FROM enrollments
            WHERE status = 'enrolled'
            GROUP BY session_id
        ) occ ON sess.id = occ.session_id
        WHERE sess.session_date BETWEEN ? AND ?
    ");
    $stmt->execute([$startDate, $endDate]);
    $avgOccupancy = round($stmt->fetchColumn() ?: 0, 1);

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM equipment
        WHERE status IN ('maintenance', 'unavailable')
    ");
    $stmt->execute();
    $equipmentIssues = $stmt->fetchColumn();

    return [
        'activeMembers' => $activeMembers,
        'activeTrainers' => $activeTrainers,
        'activeClassesPerWeek' => $activeClassesPerWeek,
        'avgOccupancy' => $avgOccupancy,
        'equipmentIssues' => $equipmentIssues
    ];
}

function getEnrollmentEvolution($pdo) {
    $stmt = $pdo->prepare("
        SELECT strftime('%Y-%m', enrolled_at) as month, COUNT(*) as total
        FROM enrollments
        WHERE enrolled_at >= date('now', '-6 months')
        GROUP BY strftime('%Y-%m', enrolled_at)
        ORDER BY month ASC
    ");

    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($data as &$row) {
        $timestamp = strtotime($row['month'] . '-01');
        $row['month'] = date('M Y', $timestamp);
        $row['total'] = (int)$row['total'];
    }

    return $data;
}

function getTopClasses($pdo) {
    $stmt = $pdo->prepare("
        SELECT c.name as class_name, COUNT(e.id) as enrollments
        FROM classes c
        JOIN class_schedules cs ON c.id = cs.class_id
        JOIN class_sessions sess ON cs.id = sess.schedule_id
        JOIN enrollments e ON sess.id = e.session_id
        WHERE e.status = 'enrolled'
        GROUP BY c.id
        ORDER BY enrollments DESC
        LIMIT 5
    ");

    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($data as &$row) {
        $row['enrollments'] = (int)$row['enrollments'];
    }

    return $data;
}

function getMembershipPlanDistribution($pdo) {
    $stmt = $pdo->prepare("
        SELECT membership_plan, COUNT(*) as count
        FROM member_profiles
        GROUP BY membership_plan
    ");

    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $total = array_sum(array_column($data, 'count'));

    foreach ($data as &$row) {
        $row['percentage'] = $total ? round($row['count'] / $total * 100, 1) : 0;
        $row['membership_plan'] = ucfirst($row['membership_plan']);
    }

    return $data;
}

function getOccupancyByDay($pdo) {
    $stmt = $pdo->prepare("
        SELECT 
            cs.day_of_week,
            AVG(occ.enrolled_count * 1.0 / cs.capacity) * 100 as avg_occupancy
        FROM class_schedules cs
        LEFT JOIN class_sessions sess ON cs.id = sess.schedule_id
        LEFT JOIN (
            SELECT session_id, COUNT(*) as enrolled_count
            FROM enrollments
            WHERE status = 'enrolled'
            GROUP BY session_id
        ) occ ON sess.id = occ.session_id
        WHERE sess.session_date >= date('now')
        GROUP BY cs.day_of_week
        ORDER BY 
            CASE cs.day_of_week
                WHEN 'Monday' THEN 1
                WHEN 'Tuesday' THEN 2
                WHEN 'Wednesday' THEN 3
                WHEN 'Thursday' THEN 4
                WHEN 'Friday' THEN 5
                WHEN 'Saturday' THEN 6
                WHEN 'Sunday' THEN 7
            END
    ");

    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($data as &$row) {
        $row['avg_occupancy'] = round($row['avg_occupancy'] ?? 0, 1);
    }

    return $data;
}

function getCriticalClasses($pdo) {
    $stmt = $pdo->prepare("
        SELECT 
            c.name as class_name,
            cs.day_of_week,
            cs.start_time,
            cs.capacity,
            COALESCE(occ.enrolled_count, 0) as enrolled,
            (cs.capacity - COALESCE(occ.enrolled_count, 0)) as free_spots,
            ROUND((cs.capacity - COALESCE(occ.enrolled_count, 0)) * 100.0 / cs.capacity, 1) as free_percent
        FROM class_schedules cs
        JOIN classes c ON cs.class_id = c.id
        LEFT JOIN (
            SELECT schedule_id, COUNT(e.id) as enrolled_count
            FROM class_sessions sess
            JOIN enrollments e ON sess.id = e.session_id
            WHERE e.status = 'enrolled'
              AND sess.session_date >= date('now')
            GROUP BY schedule_id
        ) occ ON cs.id = occ.schedule_id
        WHERE c.active = 1
          AND (cs.capacity - COALESCE(occ.enrolled_count, 0)) * 1.0 / cs.capacity < 0.2
        ORDER BY free_spots ASC
        LIMIT 5
    ");

    $stmt->execute();
    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($data as &$row) {
        $row['free_spots'] = (int)$row['free_spots'];
        $row['enrolled'] = (int)$row['enrolled'];
    }

    return $data;
}

function getLowAvailabilityEquipment($pdo) {
    $stmt = $pdo->prepare("
        SELECT name, available, quantity, ROUND((available * 1.0 / quantity) * 100, 1) as percentage
        FROM equipment
        WHERE quantity > 0
          AND (available * 1.0 / quantity) < 0.3
        ORDER BY percentage ASC
        LIMIT 5
    ");

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getRecentActivity($pdo) {
    $stmt = $pdo->prepare("
        SELECT 'new_member' as type, name, created_at as date
        FROM users
        WHERE role = 'member'
          AND created_at >= date('now', '-7 days')
        ORDER BY created_at DESC
        LIMIT 5
    ");

    $stmt->execute();
    $newMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $stmt = $pdo->prepare("
        SELECT 'new_enrollment' as type, u.name as member_name, c.name as class_name, e.enrolled_at as date
        FROM enrollments e
        JOIN users u ON e.member_id = u.id
        JOIN class_sessions sess ON e.session_id = sess.id
        JOIN class_schedules cs ON sess.schedule_id = cs.id
        JOIN classes c ON cs.class_id = c.id
        WHERE e.enrolled_at >= date('now', '-7 days')
        ORDER BY e.enrolled_at DESC
        LIMIT 5
    ");

    $stmt->execute();
    $newEnrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return array_merge($newMembers, $newEnrollments);
}
?>