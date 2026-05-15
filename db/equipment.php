<?php
// db/equipment.php

/**
 * Retorna todas as categorias de equipamento activas.
 */
function getAllEquipmentCategories($pdo) {
    $stmt = $pdo->query("SELECT id, name, label FROM equipment_categories WHERE active = 1 ORDER BY label");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Retorna todos os equipamentos, com os dados da categoria.
 */
function getAllEquipment($pdo) {
    $sql = "SELECT e.*, ec.label as category_label, ec.name as category_name
            FROM equipment e
            JOIN equipment_categories ec ON e.category_id = ec.id
            ORDER BY ec.label, e.name";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Retorna equipamentos de uma determinada categoria.
 */
function getEquipmentByCategory($pdo, $categoryId) {
    $stmt = $pdo->prepare("SELECT * FROM equipment WHERE category_id = ? ORDER BY name");
    $stmt->execute([$categoryId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Actualiza o estado de um equipamento (para AJAX).
 * Retorna true em caso de sucesso.
 */
function updateEquipmentStatus($pdo, $id, $available, $status, $notes = null) {
    $sql = "UPDATE equipment SET available = ?, status = ?, updated_at = CURRENT_TIMESTAMP";
    $params = [$available, $status];
    if ($notes !== null) {
        $sql .= ", notes = ?";
        $params[] = $notes;
    }
    $sql .= " WHERE id = ?";
    $params[] = $id;
    $stmt = $pdo->prepare($sql);
    return $stmt->execute($params);
}