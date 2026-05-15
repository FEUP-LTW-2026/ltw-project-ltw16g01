<?php

require_once __DIR__ . '/connection.php';

function getAdminStats() {
    $pdo = getDatabaseConnection();

    $members = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'member'")->fetchColumn();

    $trainers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'trainer'")->fetchColumn();

    $classes = $pdo->query("SELECT COUNT(*) FROM classes WHERE active = 1")->fetchColumn();

    $equipmentIssues = $pdo->query("SELECT COUNT(*) FROM equipment WHERE status != 'available'")->fetchColumn();

    return [
        'members' => $members,
        'trainers' => $trainers,
        'classes' => $classes,
        'equipment_issues' => $equipmentIssues
    ];
}

function getMembers() {
    $pdo = getDatabaseConnection();

    $query = "SELECT id, name, username, email, active, created_at FROM users WHERE role = 'member' ORDER BY created_at DESC LIMIT 5";

    $stmt = $pdo->query($query);

    return $stmt->fetchAll();
}

function getTrainers() {
    $pdo = getDatabaseConnection();

    $query = "SELECT id, name, username, email FROM users WHERE role = 'trainer' ORDER BY name";

    $stmt = $pdo->query($query);

    return $stmt->fetchAll();
}

function getClasses() {
    $pdo = getDatabaseConnection();

    $query = "SELECT classes.name, classes.type, classes.day_of_week, classes.start_time, classes.location, users.name AS trainer_name FROM classes LEFT JOIN users ON classes.trainer_id = users.id WHERE classes.active = 1 ORDER BY classes.day_of_week, classes.start_time LIMIT 5";

    $stmt = $pdo->query($query);

    return $stmt->fetchAll();
}

function getEquipment() {
    $pdo = getDatabaseConnection();

    $query = "SELECT name, quantity, available, status FROM equipment ORDER BY name LIMIT 6";

    $stmt = $pdo->query($query);

    return $stmt->fetchAll();
}