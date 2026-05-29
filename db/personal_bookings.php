<?php
/**
 * personal_bookings.php – Functions for personal training (PT) requests and management.
 */

function getAvailableTrainersForPT($pdo) {
    $stmt = $pdo->prepare("
        SELECT u.id, u.name, u.username, tp.pt_hourly_rate, tp.pt_description
        FROM users u
        JOIN trainer_profiles tp ON u.id = tp.user_id
        WHERE u.role = 'trainer' AND u.active = 1 AND tp.is_pt = 1 AND tp.pt_available = 1
        ORDER BY u.name
    ");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMemberPTMonthlyCount($pdo, $memberId, $bookingDate) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM personal_bookings
        WHERE member_id = ? AND status IN ('pending', 'confirmed') AND strftime('%Y-%m', booking_date) = strftime('%Y-%m', ?)
    ");
    $stmt->execute([$memberId, $bookingDate]);
    return (int)$stmt->fetchColumn();
}

function getMemberPTDailyCount($pdo, $memberId, $bookingDate) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM personal_bookings
        WHERE member_id = ? AND booking_date = ? AND status IN ('pending', 'confirmed')
    ");
    $stmt->execute([$memberId, $bookingDate]);
    return (int)$stmt->fetchColumn();
}

function getMemberPTRequestValidation($pdo, $memberId, $bookingDate = null) {
    $stmt = $pdo->prepare("SELECT membership_plan FROM member_profiles WHERE user_id = ?");
    $stmt->execute([$memberId]);
    $plan = $stmt->fetchColumn() ?: 'basic';

    if ($plan === 'basic') {
        return ['allowed' => false, 'message' => 'Your Basic plan does not include personal training sessions.'];
    }
    if (!$bookingDate) {
        return ['allowed' => false, 'message' => 'Please choose a booking date.'];
    }
    if ($plan === 'premium') {
        if (getMemberPTMonthlyCount($pdo, $memberId, $bookingDate) >= 2) {
            return ['allowed' => false, 'message' => 'Your Premium plan only allows 2 PT sessions per month. Upgrade to VIP for daily PT sessions.'];
        }
        return ['allowed' => true, 'message' => ''];
    }
    if ($plan === 'vip') {
        if (getMemberPTDailyCount($pdo, $memberId, $bookingDate) >= 1) {
            return ['allowed' => false, 'message' => 'Your VIP plan allows a maximum of 1 PT session per day.'];
        }
        return ['allowed' => true, 'message' => ''];
    }
    return ['allowed' => false, 'message' => 'Invalid membership plan.'];
}

function canMemberRequestPT($pdo, $memberId, $bookingDate = null) {
    return getMemberPTRequestValidation($pdo, $memberId, $bookingDate)['allowed'];
}

function createPTRequest($pdo, $memberId, $trainerId, $bookingDate, $startTime, $duration, $notes = '') {
    $stmt = $pdo->prepare("
        INSERT INTO personal_bookings (member_id, trainer_id, booking_date, start_time, duration_min, notes, status, created_at)
        VALUES (?, ?, ?, ?, ?, ?, 'pending', CURRENT_TIMESTAMP)
    ");
    if ($stmt->execute([$memberId, $trainerId, $bookingDate, $startTime, $duration, $notes])) {
        $memberName = $pdo->prepare("SELECT name FROM users WHERE id = ?");
        $memberName->execute([$memberId]);
        $member = $memberName->fetchColumn();
        $trainerNameStmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
        $trainerNameStmt->execute([$trainerId]);
        $trainer = $trainerNameStmt->fetchColumn();
        $message = "New PT request from {$member} to {$trainer} on {$bookingDate} at {$startTime}.";
        notifyAllAdmins($pdo, $message, 'info');
        return true;
    }
    return false;
}

function getTrainerPendingRequests($pdo, $trainerId) {
    $stmt = $pdo->prepare("
        SELECT pb.*, u.name AS member_name, u.email AS member_email
        FROM personal_bookings pb
        JOIN users u ON pb.member_id = u.id
        WHERE pb.trainer_id = ? AND pb.status = 'pending'
        ORDER BY pb.booking_date ASC, pb.start_time ASC
    ");
    $stmt->execute([$trainerId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTrainerPastRequests($pdo, $trainerId, $limit = 10) {
    $stmt = $pdo->prepare("
        SELECT pb.*, u.name AS member_name
        FROM personal_bookings pb
        JOIN users u ON pb.member_id = u.id
        WHERE pb.trainer_id = ? AND pb.status IN ('confirmed', 'cancelled')
        ORDER BY pb.booking_date DESC, pb.start_time DESC
        LIMIT ?
    ");
    $stmt->execute([$trainerId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function memberHasBookingConflict($pdo, $memberId, $bookingDate, $startTime, $duration, $excludeId = null) {
    $endTime = date('H:i:s', strtotime($startTime . ' + ' . $duration . ' minutes'));
    $sql = "
        SELECT id FROM personal_bookings
        WHERE member_id = ? AND booking_date = ? AND status IN ('pending', 'confirmed')
        AND ( (start_time < ? AND datetime(booking_date || ' ' || start_time, '+' || duration_min || ' minutes') > datetime(?))
              OR (start_time > ? AND start_time < datetime(?)) )
    ";
    if ($excludeId) $sql .= " AND id != ?";
    $stmt = $pdo->prepare($sql);
    $params = [$memberId, $bookingDate, $endTime, $bookingDate . ' ' . $startTime, $startTime, $bookingDate . ' ' . $endTime];
    if ($excludeId) $params[] = $excludeId;
    $stmt->execute($params);
    return $stmt->fetch() !== false;
}

function memberHasPendingRequest($pdo, $memberId) {
    $stmt = $pdo->prepare("SELECT id FROM personal_bookings WHERE member_id = ? AND status = 'pending'");
    $stmt->execute([$memberId]);
    return $stmt->fetch() !== false;
}

function trainerHasBookingConflict($pdo, $trainerId, $bookingDate, $startTime, $duration, $excludeId = null) {
    $endTime = date('H:i:s', strtotime($startTime . ' + ' . $duration . ' minutes'));
    $sql = "
        SELECT id FROM personal_bookings
        WHERE trainer_id = ? AND booking_date = ? AND status IN ('pending', 'confirmed')
        AND ( (start_time < ? AND datetime(booking_date || ' ' || start_time, '+' || duration_min || ' minutes') > datetime(?))
              OR (start_time > ? AND start_time < datetime(?)) )
    ";
    if ($excludeId) $sql .= " AND id != ?";
    $stmt = $pdo->prepare($sql);
    $params = [$trainerId, $bookingDate, $endTime, $bookingDate . ' ' . $startTime, $startTime, $bookingDate . ' ' . $endTime];
    if ($excludeId) $params[] = $excludeId;
    $stmt->execute($params);
    return $stmt->fetch() !== false;
}

function trainerHasClassConflict($pdo, $trainerId, $bookingDate, $startTime, $duration) {
    $dayOfWeek = date('l', strtotime($bookingDate));
    $sql = "
        SELECT cs.id
        FROM class_schedules cs
        WHERE cs.trainer_id = ?
          AND cs.day_of_week = ?
          AND time(cs.start_time) < time(?, '+' || ? || ' minutes')
          AND time(cs.start_time, '+' || cs.duration_min || ' minutes') > time(?)
        LIMIT 1
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $trainerId,
        $dayOfWeek,
        $startTime,
        $duration,
        $startTime
    ]);
    return $stmt->fetch() !== false;
}

function updatePTRequestStatus($pdo, $requestId, $trainerId, $newStatus) {
    if (!in_array($newStatus, ['confirmed', 'cancelled'], true)) return false;
    if ($newStatus === 'confirmed') {
        $stmt = $pdo->prepare("SELECT booking_date, start_time, duration_min, member_id FROM personal_bookings WHERE id = ? AND trainer_id = ? AND status = 'pending'");
        $stmt->execute([$requestId, $trainerId]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$request) return false;
        if (trainerHasBookingConflict($pdo, $trainerId, $request['booking_date'], $request['start_time'], $request['duration_min'], $requestId)) return false;
        if (trainerHasClassConflict($pdo, $trainerId, $request['booking_date'], $request['start_time'], $request['duration_min'])) return false;
    }
    $stmt = $pdo->prepare("UPDATE personal_bookings SET status = ? WHERE id = ? AND trainer_id = ? AND status = 'pending'");
    $stmt->execute([$newStatus, $requestId, $trainerId]);
    if ($stmt->rowCount() > 0) {
        $stmt = $pdo->prepare("SELECT member_id, trainer_id FROM personal_bookings WHERE id = ?");
        $stmt->execute([$requestId]);
        $req = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($req) {
            $memberName = $pdo->prepare("SELECT name FROM users WHERE id = ?");
            $memberName->execute([$req['member_id']]);
            $member = $memberName->fetchColumn();
            $trainerName = $pdo->prepare("SELECT name FROM users WHERE id = ?");
            $trainerName->execute([$trainerId]);
            $trainer = $trainerName->fetchColumn();
            $action = $newStatus === 'confirmed' ? 'accepted' : 'declined';
            $message = "PT request from {$member} to {$trainer} was {$action}.";
            notifyAllAdmins($pdo, $message, $newStatus === 'confirmed' ? 'success' : 'warning');
        }
        return true;
    }
    return false;
}

function getMemberPTRequests($pdo, $memberId) {
    $stmt = $pdo->prepare("
        SELECT pb.*, u.name AS trainer_name
        FROM personal_bookings pb
        JOIN users u ON pb.trainer_id = u.id
        WHERE pb.member_id = ?
        ORDER BY pb.created_at DESC
    ");
    $stmt->execute([$memberId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMemberConfirmedPT($pdo, $memberId) {
    // Returns only future confirmed PT sessions for the member dashboard.
    $stmt = $pdo->prepare("
        SELECT pb.*, u.name AS trainer_name
        FROM personal_bookings pb
        JOIN users u ON pb.trainer_id = u.id
        WHERE pb.member_id = ?
          AND pb.status = 'confirmed'
          AND (
                date(pb.booking_date) > date('now', 'localtime')
                OR (
                    date(pb.booking_date) = date('now', 'localtime')
                    AND time(pb.start_time) > time('now', 'localtime')
                )
          )
        ORDER BY pb.booking_date ASC, pb.start_time ASC
    ");

    $stmt->execute([$memberId]);

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllPTRequests($pdo, $status = null) {
    $sql = "
        SELECT pb.*, 
               m.name AS member_name, 
               t.name AS trainer_name
        FROM personal_bookings pb
        JOIN users m ON pb.member_id = m.id
        JOIN users t ON pb.trainer_id = t.id
    ";
    if ($status !== null && in_array($status, ['pending', 'confirmed', 'cancelled'])) {
        $sql .= " WHERE pb.status = ? ORDER BY pb.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$status]);
    } else {
        $sql .= " ORDER BY pb.created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
    }
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function notifyAllAdmins($pdo, $message, $type = 'info') {
    $stmt = $pdo->prepare("SELECT id FROM users WHERE role = 'admin'");
    $stmt->execute();
    $admins = $stmt->fetchAll(PDO::FETCH_COLUMN);
    if (empty($admins)) return;
    $stmt = $pdo->prepare("SELECT id FROM notification_types WHERE name = ?");
    $stmt->execute([$type]);
    $typeId = $stmt->fetchColumn();
    if (!$typeId) $typeId = 1;
    $insert = $pdo->prepare("INSERT INTO notifications (user_id, message, notification_type_id, created_at) VALUES (?, ?, ?, CURRENT_TIMESTAMP)");
    foreach ($admins as $adminId) {
        $insert->execute([$adminId, $message, $typeId]);
    }
}

function getPTRequestMemberId(PDO $pdo, int $requestId): ?int {
    // Get the member linked to a personal training request.
    $stmt = $pdo->prepare("
        SELECT member_id
        FROM personal_bookings
        WHERE id = ?
    ");

    $stmt->execute([$requestId]);
    $memberId = $stmt->fetchColumn();

    return $memberId ? (int)$memberId : null;
}