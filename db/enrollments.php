<?php
/**
 * enrollments.php – Functions for class enrolment and cancellation.
 */

function getNextScheduledSessionByScheduleId($pdo, $scheduleId) {
    $stmt = $pdo->prepare("
        SELECT cs.id, cs.session_date, cs.status
        FROM class_sessions cs
        JOIN class_schedules sch ON cs.schedule_id = sch.id
        WHERE cs.schedule_id = ?
          AND datetime(cs.session_date || ' ' || sch.start_time) > datetime('now')
          AND cs.status = 'scheduled'
        ORDER BY cs.session_date ASC, sch.start_time ASC
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

function getSessionTrainerInfo(PDO $pdo, int $sessionId): ?array
{
    $stmt = $pdo->prepare("
        SELECT
            cs.trainer_id,
            c.name AS class_name
        FROM class_sessions s
        JOIN class_schedules cs
            ON cs.id = s.schedule_id
        JOIN classes c
            ON c.id = cs.class_id
        WHERE s.id = ?
    ");

    $stmt->execute([$sessionId]);

    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    return $result ?: null;
}