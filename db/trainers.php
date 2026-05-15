<?php
// db/trainers.php

/**
 * Retorna todos os treinadores (role='trainer') com os seus perfis, aplicando filtros opcionais.
 */
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
 * Calcula uma classificação média do treinador (exemplo simples baseado em reviews das aulas que ele lecciona).
 * Podes adaptar conforme a tua base de dados.
 */
function getTrainerRating($pdo, $trainerId) {
    // Placeholder – retorna um valor fictício para demonstração
    static $ratings = [4.2, 4.8, 4.5, 4.0, 4.9];
    static $idx = 0;
    $val = $ratings[$idx % count($ratings)];
    $idx++;
    return $val;
}