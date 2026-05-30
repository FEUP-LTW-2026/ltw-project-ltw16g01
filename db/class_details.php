<?php
/**
 * db/class_details.php
 * Database queries for the class details page.
 * 
 * All functions are read-only (SELECT queries) – no DELETE or UPDATE.
 */

/**
 * Get trainer details by ID.
 * 
 * @param PDO $pdo
 * @param int $trainerId
 * @return array|null Associative array with 'id' and 'name' or null if not found.
 */
function getTrainerById(PDO $pdo, int $trainerId): ?array
{
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE id = ? AND role = 'trainer'");
    $stmt->execute([$trainerId]);
    $trainer = $stmt->fetch(PDO::FETCH_ASSOC);
    return $trainer ?: null;
}

/**
 * Get class type label by type ID.
 * 
 * @param PDO $pdo
 * @param int $typeId
 * @return string Class type label (e.g., "Yoga"), or empty string if not found.
 */
function getClassTypeLabelById(PDO $pdo, int $typeId): string
{
    $stmt = $pdo->prepare("SELECT label FROM class_types WHERE id = ?");
    $stmt->execute([$typeId]);
    $label = $stmt->fetchColumn();
    return $label ? (string)$label : '';
}

/**
 * Get all reviews for a class schedule (by schedule_id).
 * Includes member name, rating, comment, and creation date.
 * 
 * @param PDO $pdo
 * @param int $scheduleId
 * @return array Array of reviews (empty if none).
 */
function getClassReviews(PDO $pdo, int $scheduleId): array
{
    $sql = "
        SELECT 
            r.rating, 
            r.comment, 
            r.created_at, 
            u.name AS member_name
        FROM reviews r
        JOIN class_sessions sess ON r.session_id = sess.id
        JOIN users u ON r.member_id = u.id
        WHERE sess.schedule_id = ?
        ORDER BY r.created_at DESC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$scheduleId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Get all data needed for the class details page.
 * Returns an array with keys: schedule, trainer, typeLabel, reviews, avgRating, reviewCount.
 * 
 * @param PDO $pdo
 * @param int $scheduleId
 * @return array|null Array of data or null if schedule not found.
 */
function getClassDetailsData(PDO $pdo, int $scheduleId): ?array
{
    // 1. Fetch the main schedule data, including class name, description, etc.
    $stmt = $pdo->prepare("
        SELECT 
            cs.id AS schedule_id,
            cs.day_of_week,
            cs.start_time,
            cs.location,
            cs.capacity,
            cs.duration_min,
            c.id AS class_id,
            c.name AS class_name,
            c.description,
            c.class_type_id,
            cs.trainer_id
        FROM class_schedules cs
        JOIN classes c ON cs.class_id = c.id
        WHERE cs.id = ?
    ");
    $stmt->execute([$scheduleId]);
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$schedule) {
        return null;
    }

    // 2. Get trainer details (if assigned)
    $trainer = null;
    if (!empty($schedule['trainer_id'])) {
        $trainer = getTrainerById($pdo, (int)$schedule['trainer_id']);
    }

    // 3. Get class type label
    $typeLabel = getClassTypeLabelById($pdo, (int)$schedule['class_type_id']);
    if ($typeLabel === '') {
        $typeLabel = 'General';
    }

    // 4. Get reviews and rating summary
    $reviews = getClassReviews($pdo, $scheduleId);
    $reviewCount = count($reviews);
    $avgRating = 0;
    if ($reviewCount > 0) {
        $sum = array_sum(array_column($reviews, 'rating'));
        $avgRating = round($sum / $reviewCount, 1);
    }

    // 5. Return assembled data
    return [
        'schedule'    => $schedule,
        'trainer'     => $trainer,
        'typeLabel'   => $typeLabel,
        'reviews'     => $reviews,
        'avgRating'   => $avgRating,
        'reviewCount' => $reviewCount,
    ];
}
?>