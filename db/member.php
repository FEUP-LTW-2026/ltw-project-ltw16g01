<?php
// db/member.php

function getMemberUpcomingEnrollments($pdo, $memberId, $limit = 5) {
    $sql = "SELECT 
                cs.id as schedule_id,
                c.name as class_name,
                cs.day_of_week,
                cs.start_time,
                cs.location,
                cs.duration_min,
                u.name as trainer_name,
                sess.session_date,
                sess.id as session_id,
                e.status,
                e.enrolled_at
            FROM enrollments e
            JOIN class_sessions sess ON e.session_id = sess.id
            JOIN class_schedules cs ON sess.schedule_id = cs.id
            JOIN classes c ON cs.class_id = c.id
            LEFT JOIN users u ON cs.trainer_id = u.id
            WHERE e.member_id = ? AND e.status = 'enrolled' AND sess.session_date >= date('now')
            ORDER BY sess.session_date ASC, cs.start_time ASC
            LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$memberId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMemberPastClasses($pdo, $memberId, $limit = 5) {
    $sql = "SELECT 
                c.name as class_name,
                cs.day_of_week,
                cs.start_time,
                sess.session_date,
                sess.id as session_id,
                r.rating,
                r.comment
            FROM enrollments e
            JOIN class_sessions sess ON e.session_id = sess.id
            JOIN class_schedules cs ON sess.schedule_id = cs.id
            JOIN classes c ON cs.class_id = c.id
            LEFT JOIN reviews r ON r.session_id = sess.id AND r.member_id = e.member_id
            WHERE e.member_id = ? AND e.status = 'enrolled' AND sess.session_date < date('now')
            ORDER BY sess.session_date DESC
            LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$memberId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMemberStats($pdo, $memberId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments e JOIN class_sessions sess ON e.session_id = sess.id WHERE e.member_id = ? AND e.status = 'enrolled' AND sess.session_date >= date('now')");
    $stmt->execute([$memberId]);
    $upcoming = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments e JOIN class_sessions sess ON e.session_id = sess.id WHERE e.member_id = ? AND e.status = 'enrolled' AND sess.session_date < date('now')");
    $stmt->execute([$memberId]);
    $past = $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT AVG(rating) FROM reviews WHERE member_id = ?");
    $stmt->execute([$memberId]);
    $avgRating = round($stmt->fetchColumn() ?: 0, 1);

    return [
        'upcoming' => $upcoming,
        'past' => $past,
        'avg_rating' => $avgRating
    ];
}