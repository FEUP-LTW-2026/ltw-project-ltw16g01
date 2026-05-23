<?php
// db/trainers.php

function getAllTrainers($pdo, $filters = []) {
    $sql = "
        SELECT 
            u.id,
            u.name,
            u.username,
            u.email,
            u.photo,
            u.active,
            u.is_admin,
            tp.bio,
            tp.specializations,
            tp.certifications,
            tp.is_pt,
            tp.pt_description,
            tp.pt_hourly_rate,
            tp.pt_available,
            COALESCE(ROUND(AVG(r.rating), 1), 0) AS rating,
            COUNT(r.id) AS review_count
        FROM users u
        LEFT JOIN trainer_profiles tp ON u.id = tp.user_id
        LEFT JOIN class_schedules cs ON cs.trainer_id = u.id
        LEFT JOIN class_sessions sess ON sess.schedule_id = cs.id
        LEFT JOIN reviews r ON r.session_id = sess.id
        WHERE u.role = 'trainer'
    ";

    $params = [];

    if (!empty($filters['active_only'])) {
        $sql .= " AND u.active = 1";
    }

    if (!empty($filters['name'])) {
        $sql .= " AND u.name LIKE ?";
        $params[] = '%' . $filters['name'] . '%';
    }

    if (!empty($filters['specialization'])) {
        $sql .= " AND tp.specializations LIKE ?";
        $params[] = '%' . $filters['specialization'] . '%';
    }

    if (!empty($filters['trainer_id'])) {
        $sql .= " AND u.id = ?";
        $params[] = $filters['trainer_id'];
    }

    if (isset($filters['is_pt']) && $filters['is_pt'] !== '') {
        if ($filters['is_pt'] === '1') {
            $sql .= " AND tp.is_pt = 1";
        } elseif ($filters['is_pt'] === '0') {
            $sql .= " AND COALESCE(tp.is_pt, 0) = 0";
        }
    }

    $sql .= "
        GROUP BY u.id
        ORDER BY u.name ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllPersonalTrainers($pdo, $filters = []) {
    $sql = "
        SELECT 
            u.id, u.name, u.username, u.email, u.photo, u.active, 
            tp.bio, tp.specializations, tp.certifications, 
            tp.is_pt, tp.pt_description, tp.pt_hourly_rate, tp.pt_available,
            COALESCE(ROUND(AVG(r.rating), 1), 0) AS rating,
            COUNT(r.id) AS review_count
        FROM users u
        LEFT JOIN trainer_profiles tp ON u.id = tp.user_id
        LEFT JOIN class_schedules cs ON cs.trainer_id = u.id
        LEFT JOIN class_sessions sess ON sess.schedule_id = cs.id
        LEFT JOIN reviews r ON r.session_id = sess.id
        WHERE u.role = 'trainer'
          AND u.active = 1
          AND tp.is_pt = 1
          AND tp.pt_available = 1
    ";
    $params = [];

    if (!empty($filters['specialization'])) {
        $sql .= " AND tp.specializations LIKE ?";
        $params[] = '%' . $filters['specialization'] . '%';
    }

    if (!empty($filters['name'])) {
        $sql .= " AND u.name LIKE ?";
        $params[] = '%' . $filters['name'] . '%';
    }

    $sql .= " GROUP BY u.id ORDER BY u.name ASC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTrainerById($pdo, $id, $activeOnly = true) {
    $sql = "
        SELECT 
            u.id,
            u.name,
            u.username,
            u.email,
            u.photo,
            u.active,
            u.is_admin,
            tp.bio,
            tp.specializations,
            tp.certifications,
            tp.is_pt,
            tp.pt_description,
            tp.pt_hourly_rate,
            tp.pt_available
        FROM users u
        LEFT JOIN trainer_profiles tp ON u.id = tp.user_id
        WHERE u.id = ?
          AND u.role = 'trainer'
    ";

    $params = [$id];

    if ($activeOnly) {
        $sql .= " AND u.active = 1";
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getTrainerRating($pdo, $trainerId) {
    $sql = "SELECT AVG(r.rating) AS avg_rating, COUNT(r.id) AS review_count FROM reviews r JOIN class_sessions sess ON r.session_id = sess.id JOIN class_schedules cs ON sess.schedule_id = cs.id WHERE cs.trainer_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$trainerId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return ['rating' => round($result['avg_rating'] ?? 0, 1), 'count' => (int)($result['review_count'] ?? 0)];
}

function getUpcomingSchedulesForTrainer($pdo, $trainerId, $limit = 5) {
    $sql = "SELECT cs.id, c.name AS class_name, cs.day_of_week, cs.start_time, cs.location, cs.duration_min FROM class_schedules cs JOIN classes c ON cs.class_id = c.id WHERE cs.trainer_id = ? AND c.active = 1 ORDER BY CASE cs.day_of_week WHEN 'Monday' THEN 1 WHEN 'Tuesday' THEN 2 WHEN 'Wednesday' THEN 3 WHEN 'Thursday' THEN 4 WHEN 'Friday' THEN 5 WHEN 'Saturday' THEN 6 WHEN 'Sunday' THEN 7 END, cs.start_time LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$trainerId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function trainerUsernameOrEmailExists($pdo, $username, $email, $excludeId = null) {
    if ($excludeId === null) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1");
        $stmt->execute([$username, $email]);
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE (username = ? OR email = ?) AND id <> ? LIMIT 1");
        $stmt->execute([$username, $email, $excludeId]);
    }

    return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
}

function createTrainer($pdo, $name, $username, $email, $bio, $specializations, $certifications, $password = 'changeme') {
    if (trainerUsernameOrEmailExists($pdo, $username, $email)) return false;

    $hash = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("INSERT INTO users (name, username, email, password, role, active) VALUES (?, ?, ?, ?, 'trainer', 1)");
    if (!$stmt->execute([$name, $username, $email, $hash])) return false;

    $userId = $pdo->lastInsertId();

    $stmt = $pdo->prepare("INSERT INTO trainer_profiles (user_id, bio, specializations, certifications) VALUES (?, ?, ?, ?)");
    if (!$stmt->execute([$userId, $bio, $specializations, $certifications])) return false;

    return $userId;
}

function updateTrainer($pdo, $id, $name, $username, $email, $active, $bio, $specializations, $certifications, $isAdmin = 0) {
    if (trainerUsernameOrEmailExists($pdo, $username, $email, $id)) return false;

    $stmt = $pdo->prepare("
        UPDATE users
        SET name = ?,
            username = ?,
            email = ?,
            active = ?,
            is_admin = ?
        WHERE id = ?
          AND role = 'trainer'
    ");

    if (!$stmt->execute([$name, $username, $email, $active, $isAdmin, $id])) {
        return false;
    }

    $stmt = $pdo->prepare("
        UPDATE trainer_profiles
        SET bio = ?,
            specializations = ?,
            certifications = ?
        WHERE user_id = ?
    ");

    return $stmt->execute([$bio, $specializations, $certifications, $id]);
}

function deleteTrainer($pdo, $id) {
    $stmt = $pdo->prepare("UPDATE users SET active = 0 WHERE id = ? AND role = 'trainer'");
    return $stmt->execute([$id]);
}

function getTrainerSpecializationOptions($pdo) {
    $stmt = $pdo->query("
        SELECT specializations
        FROM trainer_profiles
        WHERE specializations IS NOT NULL
          AND TRIM(specializations) <> ''
    ");

    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $specializations = [];

    foreach ($rows as $row) {
        $parts = explode(',', $row['specializations']);

        foreach ($parts as $part) {
            $spec = trim($part);

            if ($spec !== '') {
                $specializations[] = $spec;
            }
        }
    }

    $specializations = array_unique($specializations);
    sort($specializations);

    return $specializations;
}

function getTrainerNameOptions($pdo) {
    $stmt = $pdo->query("
        SELECT id, name
        FROM users
        WHERE role = 'trainer'
          AND active = 1
        ORDER BY name ASC
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDistinctSpecializations($pdo) {
    $stmt = $pdo->query("SELECT specializations FROM trainer_profiles WHERE specializations IS NOT NULL AND specializations != ''");
    $specs = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $parts = array_map('trim', explode(',', $row['specializations']));
        foreach ($parts as $part) {
            if (!empty($part)) $specs[$part] = $part;
        }
    }
    asort($specs);
    return array_keys($specs);
}

function getTrainerStats($pdo, $trainerId) {
    // Upcoming classes (sessions) for this trainer
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM class_sessions sess
        JOIN class_schedules cs ON sess.schedule_id = cs.id
        WHERE cs.trainer_id = ? AND sess.session_date >= date('now') AND sess.status = 'scheduled'
    ");
    $stmt->execute([$trainerId]);
    $upcoming = $stmt->fetchColumn();

    // Total students enrolled in trainer's upcoming classes
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT e.member_id)
        FROM enrollments e
        JOIN class_sessions sess ON e.session_id = sess.id
        JOIN class_schedules cs ON sess.schedule_id = cs.id
        WHERE cs.trainer_id = ? AND sess.session_date >= date('now') AND e.status = 'enrolled'
    ");
    $stmt->execute([$trainerId]);
    $students = $stmt->fetchColumn();

    // Total classes given (past)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM class_sessions sess
        JOIN class_schedules cs ON sess.schedule_id = cs.id
        WHERE cs.trainer_id = ? AND sess.session_date < date('now') AND sess.status = 'completed'
    ");
    $stmt->execute([$trainerId]);
    $past = $stmt->fetchColumn();

    return [
        'upcoming' => $upcoming,
        'students' => $students,
        'past' => $past
    ];
}

function getTrainerUpcomingClasses($pdo, $trainerId, $limit = 10) {
    $sql = "
        SELECT 
            cs.id as schedule_id,
            c.name as class_name,
            cs.day_of_week,
            cs.start_time,
            cs.location,
            cs.duration_min,
            sess.session_date,
            sess.id as session_id,
            (SELECT COUNT(*) FROM enrollments e WHERE e.session_id = sess.id AND e.status = 'enrolled') as enrolled_count,
            cs.capacity
        FROM class_schedules cs
        JOIN classes c ON cs.class_id = c.id
        JOIN class_sessions sess ON sess.schedule_id = cs.id
        WHERE cs.trainer_id = ? AND sess.session_date >= date('now') AND sess.status = 'scheduled'
        ORDER BY sess.session_date ASC, cs.start_time ASC
        LIMIT ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$trainerId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTrainerPastClasses($pdo, $trainerId, $limit = 10) {
    $sql = "
        SELECT 
            c.name as class_name,
            cs.day_of_week,
            cs.start_time,
            sess.session_date,
            sess.id as session_id,
            (SELECT COUNT(*) FROM enrollments e WHERE e.session_id = sess.id AND e.status = 'enrolled') as enrolled_count,
            cs.capacity
        FROM class_schedules cs
        JOIN classes c ON cs.class_id = c.id
        JOIN class_sessions sess ON sess.schedule_id = cs.id
        WHERE cs.trainer_id = ? AND sess.session_date < date('now')
        ORDER BY sess.session_date DESC
        LIMIT ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$trainerId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>