<?php
// File: admin/class_details_queries.php
// Description: Contains all database queries for the class details page.

// Get trainer details by ID
function getTrainerById($pdo, $trainerId) {
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE id = ?");
    $stmt->execute([$trainerId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

// Get class type label by type ID
function getClassTypeLabelById($pdo, $typeId) {
    $stmt = $pdo->prepare("SELECT label FROM class_types WHERE id = ?");
    $stmt->execute([$typeId]);
    return $stmt->fetchColumn();
}

// Get all data needed for class details page
function getClassDetailsData($pdo, $scheduleId) {
    // Use existing function from db/classes.php
    require_once '../db/classes.php';
    $schedule = getClassScheduleById($pdo, $scheduleId);
    if (!$schedule) {
        return null;
    }

    // Trainer data
    $trainer = null;
    if (!empty($schedule['trainer_id'])) {
        $trainer = getTrainerById($pdo, $schedule['trainer_id']);
    }

    // Class type label
    $typeLabel = getClassTypeLabelById($pdo, $schedule['class_type_id']);
    if (!$typeLabel) {
        $typeLabel = 'General';
    }

    // Reviews and average rating
    $reviews = getClassReviews($pdo, $scheduleId);
    $reviewCount = count($reviews);
    $avgRating = 0;
    if ($reviewCount > 0) {
        $sum = array_sum(array_column($reviews, 'rating'));
        $avgRating = round($sum / $reviewCount, 1);
    }

    return [
        'schedule' => $schedule,
        'trainer' => $trainer,
        'typeLabel' => $typeLabel,
        'reviews' => $reviews,
        'avgRating' => $avgRating,
        'reviewCount' => $reviewCount,
    ];
}
?>