<?php
// db/trainer.php

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