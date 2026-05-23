<?php
// db/enrollments.php

function getNextScheduledSessionByScheduleId($pdo, $scheduleId) {
    $stmt = $pdo->prepare("
        SELECT id, session_date, status
        FROM class_sessions
        WHERE schedule_id = ?
          AND session_date >= date('now')
          AND status = 'scheduled'
        ORDER BY session_date ASC
        LIMIT 1
    ");

    $stmt->execute([$scheduleId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


function getEnrollmentCountForSession($pdo, $sessionId) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM enrollments
        WHERE session_id = ?
          AND status = 'enrolled'
    ");

    $stmt->execute([$sessionId]);
    return (int)$stmt->fetchColumn();
}


function getSessionCapacityForEnrollment($pdo, $sessionId) {
    $stmt = $pdo->prepare("
        SELECT sch.capacity
        FROM class_sessions sess
        JOIN class_schedules sch ON sess.schedule_id = sch.id
        WHERE sess.id = ?
    ");

    $stmt->execute([$sessionId]);
    return (int)$stmt->fetchColumn();
}


function getMemberEnrollmentForSession($pdo, $memberId, $sessionId) {
    $stmt = $pdo->prepare("
        SELECT id, status
        FROM enrollments
        WHERE member_id = ?
          AND session_id = ?
        LIMIT 1
    ");

    $stmt->execute([$memberId, $sessionId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}


function enrollMemberInSession($pdo, $memberId, $sessionId) {
    $capacity = getSessionCapacityForEnrollment($pdo, $sessionId);
    $enrolled = getEnrollmentCountForSession($pdo, $sessionId);

    if ($enrolled >= $capacity) {
        return [
            'success' => false,
            'message' => 'This class is full.'
        ];
    }

    $record = getMemberEnrollmentForSession($pdo, $memberId, $sessionId);

    if ($record) {
        if ($record['status'] === 'enrolled') {
            return [
                'success' => false,
                'message' => 'You are already enrolled.'
            ];
        }

        $stmt = $pdo->prepare("
            UPDATE enrollments
            SET status = 'enrolled',
                enrolled_at = CURRENT_TIMESTAMP
            WHERE id = ?
        ");

        $stmt->execute([$record['id']]);

        return [
            'success' => true,
            'message' => 'Enrolled successfully!'
        ];
    }

    $stmt = $pdo->prepare("
        INSERT INTO enrollments (member_id, session_id, status)
        VALUES (?, ?, 'enrolled')
    ");

    $stmt->execute([$memberId, $sessionId]);

    return [
        'success' => true,
        'message' => 'Enrolled successfully!'
    ];
}


function cancelMemberEnrollment($pdo, $memberId, $sessionId) {
    $stmt = $pdo->prepare("
        UPDATE enrollments
        SET status = 'cancelled'
        WHERE member_id = ?
          AND session_id = ?
          AND status = 'enrolled'
    ");

    $stmt->execute([$memberId, $sessionId]);

    if ($stmt->rowCount() > 0) {
        return [
            'success' => true,
            'message' => 'Enrollment cancelled.'
        ];
    }

    return [
        'success' => false,
        'message' => 'You were not enrolled in this class.'
    ];
}