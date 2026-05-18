<?php
// db/classes.php

function getAllClassSchedules($pdo, $filters = []) {
    $sql = "SELECT cs.id AS schedule_id, cs.day_of_week, cs.start_time, cs.location, cs.capacity, cs.duration_min, c.id AS class_id, c.name AS class_name, c.description AS class_description, ct.id AS class_type_id, ct.name AS class_type_name, ct.label AS class_type_label, u.id AS trainer_id, u.name AS trainer_name, (SELECT COUNT(*) FROM enrollments e JOIN class_sessions sess ON e.session_id = sess.id WHERE sess.schedule_id = cs.id AND e.status = 'enrolled') AS enrolled_count FROM class_schedules cs JOIN classes c ON cs.class_id = c.id JOIN class_types ct ON c.class_type_id = ct.id LEFT JOIN users u ON cs.trainer_id = u.id WHERE c.active = 1";
    $params = [];

    if (!empty($filters['type'])) {
        $sql .= " AND ct.name = ?";
        $params[] = $filters['type'];
    }

    if (!empty($filters['trainer'])) {
        $sql .= " AND cs.trainer_id = ?";
        $params[] = $filters['trainer'];
    }

    if (!empty($filters['day'])) {
        $sql .= " AND cs.day_of_week = ?";
        $params[] = ucfirst(strtolower($filters['day']));
    }

    $sql .= " ORDER BY CASE cs.day_of_week WHEN 'Monday' THEN 1 WHEN 'Tuesday' THEN 2 WHEN 'Wednesday' THEN 3 WHEN 'Thursday' THEN 4 WHEN 'Friday' THEN 5 WHEN 'Saturday' THEN 6 WHEN 'Sunday' THEN 7 ELSE 8 END, cs.start_time";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getClassTypes($pdo) {
    $stmt = $pdo->query("SELECT id, name, label FROM class_types WHERE active = 1 ORDER BY label");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getActiveTrainersForClasses($pdo) {
    $stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'trainer' AND active = 1 ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getClassScheduleById($pdo, $scheduleId) {
    $sql = "SELECT cs.*, c.name AS class_name, c.description AS class_description, c.class_type_id FROM class_schedules cs JOIN classes c ON cs.class_id = c.id WHERE cs.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$scheduleId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getClassSessionById($pdo, $sessionId) {
    $sql = "SELECT sess.id AS session_id, sess.session_date, sess.status AS session_status, cs.id AS schedule_id, cs.day_of_week, cs.start_time, cs.location, cs.capacity, cs.duration_min, c.id AS class_id, c.name AS class_name, c.description AS class_description, ct.name AS class_type_name, ct.label AS class_type_label, u.id AS trainer_id, u.name AS trainer_name, (SELECT COUNT(*) FROM enrollments e WHERE e.session_id = sess.id AND e.status = 'enrolled') AS enrolled_count FROM class_sessions sess JOIN class_schedules cs ON sess.schedule_id = cs.id JOIN classes c ON cs.class_id = c.id JOIN class_types ct ON c.class_type_id = ct.id LEFT JOIN users u ON cs.trainer_id = u.id WHERE sess.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$sessionId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getUpcomingSessionsBySchedule($pdo, $scheduleId, $limit = 5) {
    $sql = "
        SELECT 
            sess.id,
            sess.session_date,
            sess.status,
            (SELECT COUNT(*) FROM enrollments e WHERE e.session_id = sess.id AND e.status = 'enrolled') AS enrolled_count
        FROM class_sessions sess
        WHERE sess.schedule_id = ? 
          AND sess.session_date >= date('now')
          AND sess.status = 'scheduled'
        ORDER BY sess.session_date ASC
        LIMIT ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$scheduleId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function isMemberEnrolled($pdo, $memberId, $sessionId) {
    $stmt = $pdo->prepare("SELECT 1 FROM enrollments WHERE member_id = ? AND session_id = ? AND status = 'enrolled'");
    $stmt->execute([$memberId, $sessionId]);
    return $stmt->fetch() !== false;
}

function createClass($pdo, $name, $description, $typeId) {
    $stmt = $pdo->prepare("INSERT INTO classes (name, description, class_type_id, active) VALUES (?, ?, ?, 1)");
    $stmt->execute([$name, $description, $typeId]);
    return $pdo->lastInsertId();
}

function createClassSchedule($pdo, $classId, $trainerId, $dayOfWeek, $startTime, $location, $capacity, $duration) {
    $stmt = $pdo->prepare("INSERT INTO class_schedules (class_id, trainer_id, day_of_week, start_time, location, capacity, duration_min) VALUES (?, ?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$classId, $trainerId, $dayOfWeek, $startTime, $location, $capacity, $duration]);
}

function createClassWithSchedule($pdo, $className, $classDescription, $classTypeId, $trainerId, $dayOfWeek, $startTime, $location, $capacity, $duration) {
    $classId = createClass($pdo, $className, $classDescription, $classTypeId);
    if (!$classId) return false;

    return createClassSchedule($pdo, $classId, $trainerId, $dayOfWeek, $startTime, $location, $capacity, $duration);
}

function updateClassWithSchedule($pdo, $scheduleId, $classId, $className, $classDescription, $classTypeId, $trainerId, $dayOfWeek, $startTime, $location, $capacity, $duration) {
    $stmt = $pdo->prepare("UPDATE classes SET name = ?, description = ?, class_type_id = ? WHERE id = ?");
    if (!$stmt->execute([$className, $classDescription, $classTypeId, $classId])) return false;

    $stmt = $pdo->prepare("UPDATE class_schedules SET trainer_id = ?, day_of_week = ?, start_time = ?, location = ?, capacity = ?, duration_min = ? WHERE id = ?");
    return $stmt->execute([$trainerId, $dayOfWeek, $startTime, $location, $capacity, $duration, $scheduleId]);
}

function deleteClassSchedule($pdo, $scheduleId) {
    $stmt = $pdo->prepare("SELECT class_id FROM class_schedules WHERE id = ?");
    $stmt->execute([$scheduleId]);
    $classId = $stmt->fetchColumn();

    if (!$classId) return false;

    $stmt = $pdo->prepare("UPDATE classes SET active = 0 WHERE id = ?");
    if (!$stmt->execute([$classId])) return false;

    $stmt = $pdo->prepare("UPDATE class_sessions SET status = 'cancelled' WHERE schedule_id = ? AND session_date >= date('now')");
    return $stmt->execute([$scheduleId]);
}