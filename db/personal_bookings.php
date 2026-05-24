<?php
// db/personal_bookings.php

function getAvailableTrainersForPT($pdo) {
    $stmt = $pdo->prepare("
        SELECT 
            u.id, 
            u.name, 
            u.username, 
            tp.pt_hourly_rate, 
            tp.pt_description
        FROM users u
        JOIN trainer_profiles tp ON u.id = tp.user_id
        WHERE u.role = 'trainer'
          AND u.active = 1
          AND tp.is_pt = 1
          AND tp.pt_available = 1
        ORDER BY u.name
    ");

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMemberPTMonthlyCount($pdo, $memberId, $bookingDate) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM personal_bookings
        WHERE member_id = ?
          AND status IN ('pending', 'confirmed')
          AND strftime('%Y-%m', booking_date) = strftime('%Y-%m', ?)
    ");

    $stmt->execute([$memberId, $bookingDate]);
    return (int)$stmt->fetchColumn();
}

function getMemberPTDailyCount($pdo, $memberId, $bookingDate) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM personal_bookings
        WHERE member_id = ?
          AND booking_date = ?
          AND status IN ('pending', 'confirmed')
    ");

    $stmt->execute([$memberId, $bookingDate]);
    return (int)$stmt->fetchColumn();
}

function getMemberPTRequestValidation($pdo, $memberId, $bookingDate = null) {
    $stmt = $pdo->prepare("
        SELECT membership_plan
        FROM member_profiles
        WHERE user_id = ?
        LIMIT 1
    ");

    $stmt->execute([$memberId]);
    $plan = $stmt->fetchColumn() ?: 'basic';

    if ($plan === 'basic') {
        return [
            'allowed' => false,
            'message' => 'Your Basic plan does not include personal training sessions.'
        ];
    }

    if ($bookingDate === null || trim($bookingDate) === '') {
        return [
            'allowed' => false,
            'message' => 'Please choose a booking date.'
        ];
    }

    if ($plan === 'premium') {
        $monthlyCount = getMemberPTMonthlyCount($pdo, $memberId, $bookingDate);

        if ($monthlyCount >= 2) {
            return [
                'allowed' => false,
                'message' => 'Your Premium plan only allows 2 PT sessions per month. Upgrade to VIP for daily PT sessions.'
            ];
        }

        return [
            'allowed' => true,
            'message' => ''
        ];
    }

    if ($plan === 'vip') {
        $dailyCount = getMemberPTDailyCount($pdo, $memberId, $bookingDate);

        if ($dailyCount >= 1) {
            return [
                'allowed' => false,
                'message' => 'Your VIP plan allows a maximum of 1 PT session per day.'
            ];
        }

        return [
            'allowed' => true,
            'message' => ''
        ];
    }

    return [
        'allowed' => false,
        'message' => 'Invalid membership plan.'
    ];
}

function canMemberRequestPT($pdo, $memberId, $bookingDate = null) {
    $validation = getMemberPTRequestValidation($pdo, $memberId, $bookingDate);
    return $validation['allowed'];
}

function createPTRequest($pdo, $memberId, $trainerId, $bookingDate, $startTime, $duration, $notes = '') {
    $validation = getMemberPTRequestValidation($pdo, $memberId, $bookingDate);

    if (!$validation['allowed']) {
        return false;
    }

    $stmt = $pdo->prepare("
        INSERT INTO personal_bookings
            (member_id, trainer_id, booking_date, start_time, duration_min, notes, status, created_at)
        VALUES
            (?, ?, ?, ?, ?, ?, 'pending', CURRENT_TIMESTAMP)
    ");

    return $stmt->execute([
        $memberId,
        $trainerId,
        $bookingDate,
        $startTime,
        $duration,
        $notes
    ]);
}

function getTrainerPendingRequests($pdo, $trainerId) {
    $stmt = $pdo->prepare("
        SELECT 
            pb.*, 
            u.name AS member_name, 
            u.email AS member_email
        FROM personal_bookings pb
        JOIN users u ON pb.member_id = u.id
        WHERE pb.trainer_id = ?
          AND pb.status = 'pending'
        ORDER BY pb.booking_date ASC, pb.start_time ASC
    ");

    $stmt->execute([$trainerId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTrainerPastRequests($pdo, $trainerId, $limit = 10) {
    $stmt = $pdo->prepare("
        SELECT 
            pb.*, 
            u.name AS member_name
        FROM personal_bookings pb
        JOIN users u ON pb.member_id = u.id
        WHERE pb.trainer_id = ?
          AND pb.status IN ('confirmed', 'cancelled')
        ORDER BY pb.booking_date DESC, pb.start_time DESC
        LIMIT ?
    ");

    $stmt->execute([$trainerId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function updatePTRequestStatus($pdo, $requestId, $trainerId, $newStatus) {
    $allowed = ['confirmed', 'cancelled'];

    if (!in_array($newStatus, $allowed, true)) {
        return false;
    }

    $stmt = $pdo->prepare("
        UPDATE personal_bookings
        SET status = ?
        WHERE id = ?
          AND trainer_id = ?
          AND status = 'pending'
    ");

    $stmt->execute([
        $newStatus,
        $requestId,
        $trainerId
    ]);

    return $stmt->rowCount() > 0;
}

function getMemberPTRequests($pdo, $memberId) {
    $stmt = $pdo->prepare("
        SELECT 
            pb.*, 
            u.name AS trainer_name
        FROM personal_bookings pb
        JOIN users u ON pb.trainer_id = u.id
        WHERE pb.member_id = ?
        ORDER BY pb.created_at DESC
    ");

    $stmt->execute([$memberId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getMemberConfirmedPT($pdo, $memberId) {
    $stmt = $pdo->prepare("
        SELECT 
            pb.*, 
            u.name AS trainer_name
        FROM personal_bookings pb
        JOIN users u ON pb.trainer_id = u.id
        WHERE pb.member_id = ?
          AND pb.status = 'confirmed'
        ORDER BY pb.booking_date ASC, pb.start_time ASC
    ");

    $stmt->execute([$memberId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}