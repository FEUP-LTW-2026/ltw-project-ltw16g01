<?php
// db/equipment.php

function getAllEquipmentCategories($pdo) {
    $stmt = $pdo->query("SELECT id, name, label FROM equipment_categories WHERE active = 1 ORDER BY label");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getAllEquipment($pdo) {
    $sql = "SELECT e.*, ec.label AS category_label, ec.name AS category_name FROM equipment e JOIN equipment_categories ec ON e.category_id = ec.id ORDER BY ec.label, e.name";
    $stmt = $pdo->query($sql);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getEquipmentById($pdo, $id) {
    $sql = "SELECT e.*, ec.label AS category_label FROM equipment e JOIN equipment_categories ec ON e.category_id = ec.id WHERE e.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$id]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getEquipmentByCategory($pdo, $categoryId) {
    $stmt = $pdo->prepare("SELECT * FROM equipment WHERE category_id = ? ORDER BY name");
    $stmt->execute([$categoryId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function createEquipment($pdo, $name, $categoryId, $quantity, $available, $status, $notes) {
    $stmt = $pdo->prepare("INSERT INTO equipment (name, category_id, quantity, available, status, notes) VALUES (?, ?, ?, ?, ?, ?)");
    return $stmt->execute([$name, $categoryId, $quantity, $available, $status, $notes]);
}

function updateEquipment($pdo, $id, $name, $categoryId, $quantity, $available, $status, $notes) {
    $stmt = $pdo->prepare("UPDATE equipment SET name = ?, category_id = ?, quantity = ?, available = ?, status = ?, notes = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ?");
    return $stmt->execute([$name, $categoryId, $quantity, $available, $status, $notes, $id]);
}

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

function deleteEquipment($pdo, $id) {
    $stmt = $pdo->prepare("DELETE FROM equipment WHERE id = ?");
    return $stmt->execute([$id]);
}