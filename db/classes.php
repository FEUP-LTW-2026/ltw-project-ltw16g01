<?php

function getAllClassSchedules($pdo, $filters = []) {
    $sql = "SELECT cs.id AS schedule_id, cs.day_of_week, cs.start_time, cs.location, cs.capacity, cs.duration_min, c.id AS class_id, c.name AS class_name, c.description AS class_description, ct.id AS class_type_id, ct.name AS class_type_name, ct.label AS class_type_label, u.id AS trainer_id, u.name AS trainer_name, (SELECT COUNT(*) FROM enrollments e JOIN class_sessions sess ON e.session_id = sess.id WHERE sess.schedule_id = cs.id AND e.status = 'enrolled') AS enrolled_count FROM class_schedules cs JOIN classes c ON cs.class_id = c.id JOIN class_types ct ON c.class_type_id = ct.id LEFT JOIN users u ON cs.trainer_id = u.id WHERE c.active = 1";
    $params = [];

    if (!empty($filters['type'])) {
        $sql .= " AND ct.name = ?";
        $params[] = $filters['type'];
    }
    if (!empty($filters['trainer'])) {
        $sql .= " AND cs.trainer_id = ?";
        $params[] = $filters['trainer'];
    }
    if (!empty($filters['day'])) {
        $sql .= " AND cs.day_of_week = ?";
        $params[] = ucfirst(strtolower($filters['day']));
    }
    if (!empty($filters['time'])) {
        $sql .= " AND cs.start_time = ?";
        $params[] = $filters['time'];
    }
    $sql .= " ORDER BY CASE cs.day_of_week WHEN 'Monday' THEN 1 WHEN 'Tuesday' THEN 2 WHEN 'Wednesday' THEN 3 WHEN 'Thursday' THEN 4 WHEN 'Friday' THEN 5 WHEN 'Saturday' THEN 6 WHEN 'Sunday' THEN 7 ELSE 8 END, cs.start_time";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTrainers($pdo) {
    $stmt = $pdo->query("SELECT id, name FROM users WHERE role = 'trainer' AND active = 1 ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getDistinctTimes($pdo) {
    $stmt = $pdo->query("SELECT DISTINCT start_time FROM class_schedules ORDER BY start_time");
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

function getClassTypes($pdo) {
    $stmt = $pdo->prepare("SELECT id, name, label FROM class_types WHERE active = 1 ORDER BY label");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getActiveTrainersForClasses($pdo) {
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE role = 'trainer' AND active = 1 ORDER BY name");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getClassScheduleById($pdo, $scheduleId) {
    $sql = "SELECT cs.*, c.name AS class_name, c.description AS class_description, c.class_type_id FROM class_schedules cs JOIN classes c ON cs.class_id = c.id WHERE cs.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$scheduleId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getClassScheduleRating($pdo, $scheduleId) {
    $sql = "SELECT ROUND(AVG(r.rating), 1) AS avg_rating, COUNT(r.id) AS review_count FROM reviews r JOIN class_sessions sess ON r.session_id = sess.id WHERE sess.schedule_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$scheduleId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return [
        'rating' => $result['avg_rating'] ?? 0,
        'count' => (int)($result['review_count'] ?? 0)
    ];
}

function getClassSessionById($pdo, $sessionId) {
    $sql = "SELECT sess.id AS session_id, sess.session_date, sess.status AS session_status, cs.id AS schedule_id, cs.day_of_week, cs.start_time, cs.location, cs.capacity, cs.duration_min, c.id AS class_id, c.name AS class_name, c.description AS class_description, ct.name AS class_type_name, ct.label AS class_type_label, u.id AS trainer_id, u.name AS trainer_name, (SELECT COUNT(*) FROM enrollments e WHERE e.session_id = sess.id AND e.status = 'enrolled') AS enrolled_count FROM class_sessions sess JOIN class_schedules cs ON sess.schedule_id = cs.id JOIN classes c ON cs.class_id = c.id JOIN class_types ct ON c.class_type_id = ct.id LEFT JOIN users u ON cs.trainer_id = u.id WHERE sess.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$sessionId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
function getUpcomingSessionsBySchedule(PDO $pdo, int $scheduleId, int $limit = 5): array
{
    // 1. Tentar obter sessões reais futuras (não canceladas)
    $stmt = $pdo->prepare("
        SELECT 
            sess.id,
            sess.session_date,
            sess.status,
            (SELECT COUNT(*) FROM enrollments WHERE session_id = sess.id AND status = 'enrolled') AS enrolled_count,
            cs.start_time,
            c.name AS class_name,
            u.name AS trainer_name,
            cs.capacity
        FROM class_sessions sess
        JOIN class_schedules cs ON sess.schedule_id = cs.id
        JOIN classes c ON cs.class_id = c.id
        LEFT JOIN users u ON cs.trainer_id = u.id
        WHERE sess.schedule_id = ? 
          AND datetime(sess.session_date || ' ' || cs.start_time) > datetime('now')
          AND sess.status = 'scheduled'
        ORDER BY sess.session_date ASC
        LIMIT ?
    ");
    $stmt->execute([$scheduleId, $limit]);
    $realSessions = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (!empty($realSessions)) {
        return $realSessions;
    }

    // 2. Fallback: calcular a(s) próxima(s) data(s) virtualmente
    $schedule = getClassScheduleById($pdo, $scheduleId);
    if (!$schedule) return [];

    $timezone = new DateTimeZone('Europe/Lisbon');
    $now = new DateTime('now', $timezone);
    $today = clone $now;
    $today->setTime(0, 0, 0);

    // A partir de amanhã
    $startDate = clone $today;
    $startDate->modify('+1 day');

    $dayMap = [
        'Monday'    => 'Mon',
        'Tuesday'   => 'Tue',
        'Wednesday' => 'Wed',
        'Thursday'  => 'Thu',
        'Friday'    => 'Fri',
        'Saturday'  => 'Sat',
        'Sunday'    => 'Sun'
    ];
    $targetDay = $dayMap[$schedule['day_of_week']] ?? null;
    if (!$targetDay) return [];

    $sessions = [];
    $date = clone $startDate;
    $found = 0;
    while ($found < $limit && $found < 52) { // máximo 52 semanas no futuro
        if ($date->format('D') === $targetDay) {
            $sessionDate = $date->format('Y-m-d');
            // Verifica se a data virtual já não foi cancelada (não há registo de cancelamento para datas virtuais, mas por segurança)
            // Podes também verificar se já existe uma sessão real cancelada nessa data e pular se quiseres.
            $sessions[] = [
                'id'            => -($found + 1), // ID virtual (negativo)
                'session_date'  => $sessionDate,
                'status'        => 'scheduled',
                'start_time'    => $schedule['start_time'],
                'class_name'    => $schedule['class_name'],
                'trainer_name'  => $schedule['trainer_name'] ?? 'TBD',
                'capacity'      => (int)$schedule['capacity'],
                'enrolled_count'=> 0,
                'is_virtual'    => true
            ];
            $found++;
        }
        $date->modify('+1 day');
    }
    return $sessions;
}

// Helper function needed above
function getTrainerNameById2(PDO $pdo, ?int $trainerId): ?string {
    if (!$trainerId) return null;
    $stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
    $stmt->execute([$trainerId]);
    return $stmt->fetchColumn() ?: null;
}

// -------------------------------------------------------------------
// NEW HELPER FUNCTIONS for dynamic session handling
// -------------------------------------------------------------------

/**
 * Check if a session record already exists for a given schedule and date.
 */
function getSessionIdByDateAndSchedule(PDO $pdo, int $scheduleId, string $date): ?int
{
    $stmt = $pdo->prepare("SELECT id FROM class_sessions WHERE schedule_id = ? AND session_date = ?");
    $stmt->execute([$scheduleId, $date]);
    return $stmt->fetchColumn() ?: null;
}

/**
 * Count how many members are enrolled (status='enrolled') in a given session.
 */
function countEnrollmentsForSession(PDO $pdo, int $sessionId): int
{
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM enrollments WHERE session_id = ? AND status = 'enrolled'");
    $stmt->execute([$sessionId]);
    return (int)$stmt->fetchColumn();
}

/**
 * Create a real session record for a schedule and date.
 * Returns the new session ID.
 */
function createSessionForSchedule(PDO $pdo, int $scheduleId, string $sessionDate): int
{
    $stmt = $pdo->prepare("INSERT INTO class_sessions (schedule_id, session_date, status) VALUES (?, ?, 'scheduled')");
    $stmt->execute([$scheduleId, $sessionDate]);
    return (int)$pdo->lastInsertId();
}

/**
 * Get or create a real session for the given schedule and date.
 * If it already exists, returns its ID; otherwise creates and returns the new ID.
 */
function getOrCreateSessionOnEnroll(PDO $pdo, int $scheduleId, string $sessionDate): int
{
    $existing = getSessionIdByDateAndSchedule($pdo, $scheduleId, $sessionDate);
    if ($existing) {
        return $existing;
    }
    return createSessionForSchedule($pdo, $scheduleId, $sessionDate);
}

/**
 * Check if a session ID exists and is a real record in class_sessions.
 */
function sessionExists(PDO $pdo, int $sessionId): bool
{
    $stmt = $pdo->prepare("SELECT 1 FROM class_sessions WHERE id = ?");
    $stmt->execute([$sessionId]);
    return (bool)$stmt->fetchColumn();
}


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

function createClassWithSchedule($pdo, $className, $classDescription, $classTypeId, $trainerId, $dayOfWeek, $startTime, $location, $capacity, $duration) {
    $classId = createClass($pdo, $className, $classDescription, $classTypeId);
    if (!$classId) return false;
    return createClassSchedule($pdo, $classId, $trainerId, $dayOfWeek, $startTime, $location, $capacity, $duration);
}

function updateClassWithSchedule($pdo, $scheduleId, $classId, $className, $classDescription, $classTypeId, $trainerId, $dayOfWeek, $startTime, $location, $capacity, $duration) {
    $stmt = $pdo->prepare("UPDATE classes SET name = ?, description = ?, class_type_id = ? WHERE id = ?");
    if (!$stmt->execute([$className, $classDescription, $classTypeId, $classId])) return false;
    $stmt = $pdo->prepare("UPDATE class_schedules SET trainer_id = ?, day_of_week = ?, start_time = ?, location = ?, capacity = ?, duration_min = ? WHERE id = ?");
    return $stmt->execute([$trainerId, $dayOfWeek, $startTime, $location, $capacity, $duration, $scheduleId]);
}

function deleteClassSchedule($pdo, $scheduleId) {
    $stmt = $pdo->prepare("SELECT class_id FROM class_schedules WHERE id = ?");
    $stmt->execute([$scheduleId]);
    $classId = $stmt->fetchColumn();
    if (!$classId) return false;
    $stmt = $pdo->prepare("UPDATE classes SET active = 0 WHERE id = ?");
    if (!$stmt->execute([$classId])) return false;
    $stmt = $pdo->prepare("UPDATE class_sessions SET status = 'cancelled' WHERE schedule_id = ? AND session_date >= date('now')");
    return $stmt->execute([$scheduleId]);
}

function getSessionEnrolledMembers($pdo, $sessionId, $trainerId) {
    $sql = "SELECT u.id, u.name, u.email, e.enrolled_at FROM enrollments e JOIN users u ON e.member_id = u.id JOIN class_sessions sess ON e.session_id = sess.id JOIN class_schedules cs ON sess.schedule_id = cs.id WHERE e.session_id = ? AND cs.trainer_id = ? AND e.status = 'enrolled' ORDER BY u.name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$sessionId, $trainerId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function cancelTrainerSession($pdo, $sessionId, $trainerId) {
    try {
        $pdo->beginTransaction();
        $sql = "UPDATE class_sessions SET status = 'cancelled' WHERE id = ? AND status = 'scheduled' AND schedule_id IN (SELECT id FROM class_schedules WHERE trainer_id = ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$sessionId, $trainerId]);
        if ($stmt->rowCount() === 0) {
            $pdo->rollBack();
            return false;
        }
        $stmt = $pdo->prepare("UPDATE enrollments SET status = 'cancelled' WHERE session_id = ? AND status = 'enrolled'");
        $stmt->execute([$sessionId]);
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        return false;
    }
}

function updateTrainerScheduleDetails($pdo, $scheduleId, $trainerId, $location, $capacity, $duration, $startTime) {
    $location = trim($location);
    $capacity = (int)$capacity;
    $duration = (int)$duration;
    $startTime = trim($startTime);
    if ($location === '' || $capacity <= 0 || $duration <= 0 || $startTime === '') {
        return 'Please fill in all schedule fields correctly.';
    }
    $stmt = $pdo->prepare("SELECT id, day_of_week, start_time, location, duration_min FROM class_schedules WHERE id = ? AND trainer_id = ?");
    $stmt->execute([$scheduleId, $trainerId]);
    $currentSchedule = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$currentSchedule) {
        return 'Schedule not found or you are not the trainer.';
    }
    $stmt = $pdo->prepare("SELECT COALESCE(MAX(enrolled_count), 0) FROM (SELECT COUNT(e.id) AS enrolled_count FROM class_sessions sess LEFT JOIN enrollments e ON e.session_id = sess.id AND e.status = 'enrolled' WHERE sess.schedule_id = ? AND sess.status = 'scheduled' AND sess.session_date >= date('now') GROUP BY sess.id)");
    $stmt->execute([$scheduleId]);
    $maxEnrolled = (int)$stmt->fetchColumn();
    if ($capacity < $maxEnrolled) {
        return 'Capacity cannot be lower than the number of enrolled members.';
    }
    $newStart = strtotime($startTime);
    $newEnd = strtotime("+{$duration} minutes", $newStart);
    if ($newStart === false || $newEnd === false) {
        return 'Invalid start time.';
    }
    $stmt = $pdo->prepare("SELECT cs.id, cs.start_time, cs.duration_min, c.name AS class_name FROM class_schedules cs JOIN classes c ON cs.class_id = c.id WHERE cs.id <> ? AND cs.day_of_week = ? AND LOWER(TRIM(cs.location)) = LOWER(TRIM(?)) AND c.active = 1");
    $stmt->execute([$scheduleId, $currentSchedule['day_of_week'], $location]);
    $possibleConflicts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($possibleConflicts as $conflict) {
        $existingStart = strtotime($conflict['start_time']);
        $existingEnd = strtotime("+{$conflict['duration_min']} minutes", $existingStart);
        if ($newStart < $existingEnd && $newEnd > $existingStart) {
            return "Schedule conflict: this time overlaps with '{$conflict['class_name']}' in the same location.";
        }
    }
    $stmt = $pdo->prepare("UPDATE class_schedules SET start_time = ?, location = ?, capacity = ?, duration_min = ? WHERE id = ? AND trainer_id = ?");
    $success = $stmt->execute([$startTime, $location, $capacity, $duration, $scheduleId, $trainerId]);
    return $success ? true : 'Could not update schedule details.';
}

function getClassReviews($pdo, $scheduleId) {
    $sql = "SELECT r.rating, r.comment, r.created_at, u.name AS member_name FROM reviews r JOIN class_sessions sess ON r.session_id = sess.id JOIN users u ON r.member_id = u.id WHERE sess.schedule_id = ? ORDER BY r.created_at DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$scheduleId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getScheduleTrainerBasicInfo(PDO $pdo, int $trainerId): ?array {
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE id = ?");
    $stmt->execute([$trainerId]);
    $trainer = $stmt->fetch(PDO::FETCH_ASSOC);
    return $trainer ?: null;
}

function getClassTypeLabelById(PDO $pdo, int $classTypeId): string {
    $stmt = $pdo->prepare("SELECT label FROM class_types WHERE id = ?");
    $stmt->execute([$classTypeId]);
    return (string)($stmt->fetchColumn() ?: '');
}

function getTrainerScheduleDetailsForUpdate(PDO $pdo, int $scheduleId, int $trainerId): ?array {
    $stmt = $pdo->prepare("SELECT location, capacity, duration_min, start_time FROM class_schedules WHERE id = ? AND trainer_id = ?");
    $stmt->execute([$scheduleId, $trainerId]);
    $schedule = $stmt->fetch(PDO::FETCH_ASSOC);
    return $schedule ?: null;
}

function createNextSessionForSchedule(PDO $pdo, int $scheduleId, ?string $afterDate = null): ?int
{
    // Get schedule details
    $schedule = getClassScheduleById($pdo, $scheduleId);
    if (!$schedule) {
        return null;
    }

    $dayOfWeek = $schedule['day_of_week']; // e.g., "Monday"
    $startTime = $schedule['start_time'];

    // Set the starting date for search
    $timezone = new DateTimeZone('Europe/Lisbon');
    $start = $afterDate ? new DateTime($afterDate, $timezone) : new DateTime('now', $timezone);
    // Move to the next day (we want a date strictly after $afterDate)
    $start->modify('+1 day');

    // Map day names to DateTime's "next X" format
    $targetDay = ucfirst($dayOfWeek);
    // Start searching from the start date
    $date = clone $start;
    // Limit to 52 weeks ahead
    for ($i = 0; $i < 52; $i++) {
        // If the current day matches the target day of week
        if ($date->format('l') === $targetDay) {
            $sessionDate = $date->format('Y-m-d');

            // Check if a session already exists for this date (any status)
            $stmt = $pdo->prepare("SELECT id FROM class_sessions WHERE schedule_id = ? AND session_date = ? LIMIT 1");
            $stmt->execute([$scheduleId, $sessionDate]);
            if (!$stmt->fetchColumn()) {
                // No session exists – create one
                $stmt = $pdo->prepare("INSERT INTO class_sessions (schedule_id, session_date, status) VALUES (?, ?, 'scheduled')");
                $stmt->execute([$scheduleId, $sessionDate]);
                return (int)$pdo->lastInsertId();
            }
            // If a session already exists (even cancelled), skip this date and continue searching
        }
        $date->modify('+1 day');
    }
    return null;
}

function getUpcomingEnrolledSessions(PDO $pdo, int $memberId): array
{
    $sql = "
        SELECT 
            e.id AS enrollment_id,
            sess.id AS session_id,
            sess.session_date,
            cs.start_time,
            cs.location,
            cs.duration_min,
            c.name AS class_name,
            c.description AS class_description,
            u.name AS trainer_name,
            (SELECT COUNT(*) FROM enrollments WHERE session_id = sess.id AND status = 'enrolled') AS enrolled_count,
            cs.capacity
        FROM enrollments e
        JOIN class_sessions sess ON e.session_id = sess.id
        JOIN class_schedules cs ON sess.schedule_id = cs.id
        JOIN classes c ON cs.class_id = c.id
        LEFT JOIN users u ON cs.trainer_id = u.id
        WHERE e.member_id = ?
          AND e.status = 'enrolled'
          AND datetime(sess.session_date || ' ' || cs.start_time) > datetime('now')
        ORDER BY sess.session_date ASC, cs.start_time ASC
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$memberId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getClassScheduleDeleteDetails($pdo, $scheduleId) {
    $stmt = $pdo->prepare("
        SELECT 
            cs.id, 
            cs.trainer_id, 
            cs.day_of_week, 
            cs.start_time,
            c.name AS class_name
        FROM class_schedules cs
        INNER JOIN classes c ON cs.class_id = c.id
        WHERE cs.id = ?
    ");

    $stmt->execute([$scheduleId]);

    return $stmt->fetch(PDO::FETCH_ASSOC);
}
?>