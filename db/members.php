<?php
// db/members.php

function getMemberUpcomingEnrollments($pdo, $memberId, $limit = 5) {
    $sql = "SELECT cs.id AS schedule_id, c.name AS class_name, cs.day_of_week, cs.start_time, cs.location, cs.duration_min, u.name AS trainer_name, sess.session_date, sess.id AS session_id, e.status, e.enrolled_at FROM enrollments e JOIN class_sessions sess ON e.session_id = sess.id JOIN class_schedules cs ON sess.schedule_id = cs.id JOIN classes c ON cs.class_id = c.id LEFT JOIN users u ON cs.trainer_id = u.id WHERE e.member_id = ? AND e.status = 'enrolled' AND sess.session_date >= date('now') ORDER BY sess.session_date ASC, cs.start_time ASC LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$memberId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMemberPastClasses($pdo, $memberId, $limit = 5) {
    $sql = "SELECT c.name AS class_name, cs.day_of_week, cs.start_time, sess.session_date, sess.id AS session_id, r.rating, r.comment FROM enrollments e JOIN class_sessions sess ON e.session_id = sess.id JOIN class_schedules cs ON sess.schedule_id = cs.id JOIN classes c ON cs.class_id = c.id LEFT JOIN reviews r ON r.session_id = sess.id AND r.member_id = e.member_id WHERE e.member_id = ? AND e.status = 'enrolled' AND sess.session_date < date('now') ORDER BY sess.session_date DESC LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$memberId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMemberStats($pdo, $memberId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments e JOIN class_sessions sess ON e.session_id = sess.id WHERE e.member_id = ? AND e.status = 'enrolled' AND sess.session_date >= date('now')");
    $stmt->execute([$memberId]);
    $upcoming = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments e JOIN class_sessions sess ON e.session_id = sess.id WHERE e.member_id = ? AND e.status = 'enrolled' AND sess.session_date < date('now')");
    $stmt->execute([$memberId]);
    $past = (int)$stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT AVG(rating) FROM reviews WHERE member_id = ?");
    $stmt->execute([$memberId]);
    $avgRating = round($stmt->fetchColumn() ?: 0, 1);

    return ['upcoming' => $upcoming, 'past' => $past, 'avg_rating' => $avgRating];
}

function getAllMembersAdmin($pdo, $filters = []) {
    $sql = "
        SELECT 
            u.id,
            u.name,
            u.username,
            u.email,
            u.active,
            u.is_admin,
            u.created_at,
            mp.membership_plan,
            mp.plan_start,
            mp.plan_end
        FROM users u
        LEFT JOIN member_profiles mp ON u.id = mp.user_id
        WHERE u.role = 'member'
    ";

    $params = [];

    if (!empty($filters['name'])) {
        $sql .= " AND u.name LIKE ?";
        $params[] = '%' . $filters['name'] . '%';
    }

    if (!empty($filters['plan'])) {
        $sql .= " AND mp.membership_plan = ?";
        $params[] = $filters['plan'];
    }

    $sql .= " ORDER BY u.created_at DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMemberById($pdo, $id) {
    $sql = "
        SELECT 
            u.id,
            u.name,
            u.username,
            u.email,
            u.active,
            u.is_admin,
            u.created_at,
            mp.membership_plan,
            mp.plan_start,
            mp.plan_end
        FROM users u
        LEFT JOIN member_profiles mp ON u.id = mp.user_id
        WHERE u.id = ?
          AND u.role = 'member'
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function memberUsernameOrEmailExists($pdo, $username, $email, $excludeId = null) {
    if ($excludeId === null) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $email]);
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id <> ? LIMIT 1");
        $stmt->execute([$username, $email, $excludeId]);
    }

    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

function createMember($pdo, $name, $username, $email, $password, $plan, $planEnd) {
    if (memberUsernameOrEmailExists($pdo, $username, $email)) return false;

    $hash = password_hash($password, PASSWORD_DEFAULT);
    $planStart = date('Y-m-d');

    if ($planEnd === '') {
        $planEnd = date('Y-m-d', strtotime('+1 year'));
    }

    $stmt = $pdo->prepare("INSERT INTO users (name, username, email, password, role, active) VALUES (?, ?, ?, ?, 'member', 1)");
    if (!$stmt->execute([$name, $username, $email, $hash])) return false;

    $userId = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO member_profiles (user_id, membership_plan, plan_start, plan_end) VALUES (?, ?, ?, ?)");
    if (!$stmt->execute([$userId, $plan, $planStart, $planEnd])) return false;

    return $userId;
}

function updateMember($pdo, $id, $name, $username, $email, $active, $plan, $planEnd, $isAdmin = 0) {
    if (memberUsernameOrEmailExists($pdo, $username, $email, $id)) return false;

    $stmt = $pdo->prepare("
        UPDATE users
        SET name = ?,
            username = ?,
            email = ?,
            active = ?,
            is_admin = ?
        WHERE id = ?
          AND role = 'member'
    ");

    if (!$stmt->execute([$name, $username, $email, $active, $isAdmin, $id])) {
        return false;
    }

    $stmt = $pdo->prepare("
        UPDATE member_profiles
        SET membership_plan = ?,
            plan_end = ?
        WHERE user_id = ?
    ");

    return $stmt->execute([$plan, $planEnd, $id]);
}

function deleteMember($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE users SET active = 0 WHERE id = ? AND role = 'member'");
    return $stmt->execute([$id]);
}

/* =========================
   MEMBERSHIP PLAN LOGIC
   ========================= */

function getMemberPlan($pdo, $memberId) {
    $stmt = $pdo->prepare("SELECT membership_plan FROM member_profiles WHERE user_id = ?");
    $stmt->execute([$memberId]);
    $plan = $stmt->fetchColumn();
    return $plan ?: 'basic';
}

function getMemberWeeklyClassCount($pdo, $memberId, $sessionDate) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM enrollments e
        JOIN class_sessions sess ON e.session_id = sess.id
        WHERE e.member_id = ?
          AND e.status = 'enrolled'
          AND strftime('%Y-%W', sess.session_date) = strftime('%Y-%W', ?)
    ");

    $stmt->execute([$memberId, $sessionDate]);

    return (int)$stmt->fetchColumn();
}

function canMemberEnrollInClass($pdo, $memberId, $sessionId) {
    $plan = getMemberPlan($pdo, $memberId);

    if ($plan === 'premium' || $plan === 'vip') {
        return [
            'allowed' => true,
            'message' => ''
        ];
    }

    $stmt = $pdo->prepare("
        SELECT 
            sess.session_date,
            cs.start_time,
            cs.duration_min
        FROM class_sessions sess
        JOIN class_schedules cs ON sess.schedule_id = cs.id
        WHERE sess.id = ?
        LIMIT 1
    ");

    $stmt->execute([$sessionId]);
    $session = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$session) {
        return [
            'allowed' => false,
            'message' => 'Invalid session.'
        ];
    }

    $sessionDate = $session['session_date'];
    $startTime = $session['start_time'];
    $duration = (int)$session['duration_min'];

    $classStart = strtotime($sessionDate . ' ' . $startTime);
    $classEnd = strtotime("+{$duration} minutes", $classStart);
    $basicLimit = strtotime($sessionDate . ' 17:00:00');

    if ($classEnd > $basicLimit) {
        return [
            'allowed' => false,
            'message' => 'Your Basic plan only allows classes that finish by 17:00. Upgrade to Premium or VIP for full-day access.'
        ];
    }

    $weeklyCount = getMemberWeeklyClassCount($pdo, $memberId, $sessionDate);

    if ($weeklyCount >= 2) {
        return [
            'allowed' => false,
            'message' => 'Your Basic plan only allows 2 classes per week. Upgrade to Premium or VIP for unlimited classes.'
        ];
    }

    return [
        'allowed' => true,
        'message' => ''
    ];
}

function getMemberPlanFeatures($plan) {
    $features = [
        'basic' => [
            'label' => 'Basic',
            'classes' => '2 classes per week',
            'pt' => 'PT sessions not included',
            'equipment' => 'Equipment reservation not included'
        ],
        'premium' => [
            'label' => 'Premium',
            'classes' => 'Unlimited classes',
            'pt' => '2 PT sessions per month',
            'equipment' => 'Equipment reservation not included'
        ],
        'vip' => [
            'label' => 'VIP',
            'classes' => 'Unlimited classes',
            'pt' => 'Daily PT sessions',
            'equipment' => 'Equipment reservation included'
        ]
    ];

    return $features[$plan] ?? $features['basic'];
}

function upgradeMemberPlan($pdo, $memberId, $newPlan) {
    $allowedPlans = ['basic', 'premium', 'vip'];

    if (!in_array($newPlan, $allowedPlans, true)) {
        return false;
    }

    $stmt = $pdo->prepare("
        UPDATE member_profiles
        SET membership_plan = ?,
            plan_start = date('now'),
            plan_end = date('now', '+1 year')
        WHERE user_id = ?
    ");

    return $stmt->execute([
        $newPlan,
        $memberId
    ]);
}