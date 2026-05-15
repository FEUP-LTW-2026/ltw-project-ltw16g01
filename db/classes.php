<?php

function getAllClasses($pdo, $filters = []) {
    $sql = "SELECT c.*, u.name as trainer_name FROM classes c
            LEFT JOIN users u ON c.trainer_id = u.id
            WHERE c.active = 1";
    $params = [];
    if (!empty($filters['type'])) {
        $sql .= " AND c.type = ?";
        $params[] = $filters['type'];
    }
    if (!empty($filters['trainer'])) {
        $sql .= " AND c.trainer_id = ?";
        $params[] = $filters['trainer'];
    }
    if (!empty($filters['day'])) {
        $sql .= " AND c.day_of_week = ?";
        $params[] = ucfirst($filters['day']);
    }
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    return $stmt->fetchAll();
}