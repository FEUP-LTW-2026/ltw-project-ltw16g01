<?php
// db/classes.php

/**
 * Retorna todos os horários (class_schedules) com os dados da aula e do treinador.
 * Aplica filtros opcionais e ordena por dia da semana (usando CASE) e hora.
 */
function getAllClassSchedules($pdo, $filters = []) {
    $sql = "
        SELECT 
            cs.id AS schedule_id,
            cs.day_of_week,
            cs.start_time,
            cs.location,
            cs.capacity,
            cs.duration_min,
            c.id AS class_id,
            c.name AS class_name,
            c.description AS class_description,
            ct.id AS class_type_id,
            ct.name AS class_type_name,
            ct.label AS class_type_label,
            u.id AS trainer_id,
            u.name AS trainer_name,
            (SELECT COUNT(*) FROM enrollments e 
             JOIN class_sessions sess ON e.session_id = sess.id 
             WHERE sess.schedule_id = cs.id AND e.status = 'enrolled') AS enrolled_count
        FROM class_schedules cs
        JOIN classes c ON cs.class_id = c.id
        JOIN class_types ct ON c.class_type_id = ct.id
        LEFT JOIN users u ON cs.trainer_id = u.id
        WHERE c.active = 1
    ";
    $params = [];

    // Filtro por tipo de aula (class_type_id ou name)
    if (!empty($filters['type'])) {
        $sql .= " AND ct.name = ?";
        $params[] = $filters['type'];
    }

    // Filtro por treinador (ID)
    if (!empty($filters['trainer'])) {
        $sql .= " AND cs.trainer_id = ?";
        $params[] = $filters['trainer'];
    }

    // Filtro por dia da semana (exact match)
    if (!empty($filters['day'])) {
        $day = ucfirst(strtolower($filters['day']));
        $sql .= " AND cs.day_of_week = ?";
        $params[] = $day;
    }

    // Ordenação: por dia da semana (usando CASE) e depois por hora
    $sql .= "
        ORDER BY 
            CASE cs.day_of_week
                WHEN 'Monday' THEN 1
                WHEN 'Tuesday' THEN 2
                WHEN 'Wednesday' THEN 3
                WHEN 'Thursday' THEN 4
                WHEN 'Friday' THEN 5
                WHEN 'Saturday' THEN 6
                WHEN 'Sunday' THEN 7
                ELSE 8
            END,
            cs.start_time
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Retorna uma sessão específica (class_sessions) com os dados do horário, aula e treinador.
 */
function getClassSessionById($pdo, $sessionId) {
    $sql = "
        SELECT 
            sess.id AS session_id,
            sess.session_date,
            sess.status AS session_status,
            cs.id AS schedule_id,
            cs.day_of_week,
            cs.start_time,
            cs.location,
            cs.capacity,
            cs.duration_min,
            c.id AS class_id,
            c.name AS class_name,
            c.description AS class_description,
            ct.name AS class_type_name,
            ct.label AS class_type_label,
            u.id AS trainer_id,
            u.name AS trainer_name,
            (SELECT COUNT(*) FROM enrollments e WHERE e.session_id = sess.id AND e.status = 'enrolled') AS enrolled_count
        FROM class_sessions sess
        JOIN class_schedules cs ON sess.schedule_id = cs.id
        JOIN classes c ON cs.class_id = c.id
        JOIN class_types ct ON c.class_type_id = ct.id
        LEFT JOIN users u ON cs.trainer_id = u.id
        WHERE sess.id = ?
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$sessionId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Retorna as sessões futuras para um determinado horário (schedule_id).
 */
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

/**
 * Verifica se um membro já está inscrito numa dada sessão.
 */
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