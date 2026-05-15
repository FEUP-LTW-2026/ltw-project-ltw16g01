<?php
// db/trainers.php

/**
 * Retorna todos os treinadores (users com role='trainer')
 * com os seus dados de perfil (trainer_profiles)
 */
function getAllTrainers($pdo, $filters = []) {
    $sql = "SELECT u.id, u.name, u.username, u.email, u.photo, 
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
 * Busca um treinador pelo ID
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