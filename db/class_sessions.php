<?php
/**
 * class_sessions.php – Functions for working with class sessions.
 */

function getUpcomingSessions($pdo, $scheduleId, $limit = 1) {
    $sql = "SELECT id, session_date, status FROM class_sessions WHERE schedule_id = ? AND session_date >= date('now') AND status = 'scheduled' ORDER BY session_date ASC LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$scheduleId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getSessionEnrollmentCount($pdo, $sessionId) {
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE session_id = ? AND status = 'enrolled'");
    $stmt->execute([$sessionId]);
    return (int)$stmt->fetchColumn();
}

function getNextSessionWithCapacity($pdo, $schedule) {
    $sessions = getUpcomingSessions($pdo, $schedule['schedule_id'], 1);

    if (empty($sessions)) return null;

    $session = $sessions[0];
    $enrolled = getSessionEnrollmentCount($pdo, $session['id']);
    $capacity = (int)$schedule['capacity'];
    $percent = $capacity > 0 ? round(($enrolled / $capacity) * 100) : 100;

    return [
        'session_id' => $session['id'],
        'session_date' => $session['session_date'],
        'enrolled' => $enrolled,
        'capacity' => $capacity,
        'percent_filled' => $percent,
        'status' => $session['status']
    ];
}

function getClassSessionBasicInfo($pdo, $sessionId) {
    $stmt = $pdo->prepare("SELECT schedule_id, session_date FROM class_sessions WHERE id = ?");
    $stmt->execute([$sessionId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}