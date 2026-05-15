<?php
// db/trainers.php

function getAllTrainers($pdo, $filters = []) {
    $sql = "SELECT u.id, u.name, u.username, u.email, u.photo, u.active,
                   tp.bio, tp.specializations, tp.certifications
            FROM users u
            LEFT JOIN trainer_profiles tp ON u.id = tp.user_id
            WHERE u.role = 'trainer' AND u.active = 1";
    $params = [];

    if (!empty($filters['specialization'])) {
        $sql .= " AND tp.specializations LIKE ?";
        $params[] = '%' . $filters['specialization'] . '%';
    }
    if (!empty($filters['name'])) {
        $sql .= " AND u.name LIKE ?";
        $params[] = '%' . $filters['name'] . '%';
    }

    $sql .= " ORDER BY u.name ASC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Retorna um treinador pelo ID (incluindo perfil).
 */
function getTrainerById($pdo, $id) {
    $sql = "SELECT u.id, u.name, u.username, u.email, u.photo,
                   tp.bio, tp.specializations, tp.certifications
            FROM users u
            LEFT JOIN trainer_profiles tp ON u.id = tp.user_id
            WHERE u.id = ? AND u.role = 'trainer'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Calcula uma classificação média do treinador com base nas avaliações das sessões que ele lecciona.
 * (Opcional – pode ser usado para mostrar estrelas)
 */
function getTrainerRating($pdo, $trainerId) {
    $sql = "SELECT AVG(r.rating) as avg_rating, COUNT(r.id) as review_count
            FROM reviews r
            JOIN class_sessions sess ON r.session_id = sess.id
            JOIN class_schedules cs ON sess.schedule_id = cs.id
            WHERE cs.trainer_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$trainerId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    $avg = round($result['avg_rating'] ?? 0, 1);
    $count = $result['review_count'] ?? 0;
    return ['rating' => $avg, 'count' => $count];
}

/**
 * Retorna os próximos horários (aulas) que um treinador vai dar (úteis para perfil).
 */
function getUpcomingSchedulesForTrainer($pdo, $trainerId, $limit = 3) {
    $sql = "SELECT cs.id, c.name AS class_name, cs.day_of_week, cs.start_time, cs.location, cs.duration_min
            FROM class_schedules cs
            JOIN classes c ON cs.class_id = c.id
            WHERE cs.trainer_id = ? AND c.active = 1
            ORDER BY 
                CASE cs.day_of_week
                    WHEN 'Monday' THEN 1
                    WHEN 'Tuesday' THEN 2
                    WHEN 'Wednesday' THEN 3
                    WHEN 'Thursday' THEN 4
                    WHEN 'Friday' THEN 5
                    WHEN 'Saturday' THEN 6
                    WHEN 'Sunday' THEN 7
                END,
                cs.start_time
            LIMIT ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$trainerId, $limit]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}