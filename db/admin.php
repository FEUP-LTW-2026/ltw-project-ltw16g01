<?php
// db/admin.php

function getAdminStats($pdo) {
    $members = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'member' AND active = 1")->fetchColumn();
    $trainers = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'trainer' AND active = 1")->fetchColumn();
    $classes = $pdo->query("SELECT COUNT(DISTINCT cs.id) FROM class_schedules cs JOIN classes c ON cs.class_id = c.id WHERE c.active = 1")->fetchColumn();
    $equipmentIssues = $pdo->query("SELECT COUNT(*) FROM equipment WHERE status IN ('maintenance', 'unavailable')")->fetchColumn();

    return ['members' => $members, 'trainers' => $trainers, 'classes' => $classes, 'equipment_issues' => $equipmentIssues];
}

function getMembers($pdo) {
    $stmt = $pdo->query("SELECT id, name, username, email, created_at, active FROM users WHERE role = 'member' ORDER BY created_at DESC");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTrainers($pdo) {
    $stmt = $pdo->query("SELECT id, name, username, email, active FROM users WHERE role = 'trainer' ORDER BY name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getClasses($pdo) {
    $sql = "SELECT cs.id AS schedule_id, c.name AS class_name, ct.label AS class_type_label, u.name AS trainer_name, cs.day_of_week, cs.start_time, cs.location, (SELECT COUNT(*) FROM enrollments e JOIN class_sessions sess ON e.session_id = sess.id WHERE sess.schedule_id = cs.id AND e.status = 'enrolled') AS enrolled_count FROM class_schedules cs JOIN classes c ON cs.class_id = c.id JOIN class_types ct ON c.class_type_id = ct.id LEFT JOIN users u ON cs.trainer_id = u.id WHERE c.active = 1 ORDER BY enrolled_count DESC LIMIT 10";
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $classes = [];
    foreach ($results as $row) {
        $classes[] = ['name' => $row['class_name'], 'type' => $row['class_type_label'], 'trainer_name' => $row['trainer_name'] ?? 'No trainer', 'day_of_week' => $row['day_of_week'], 'start_time' => $row['start_time'], 'location' => $row['location']];
    }

    return $classes;
}

function getEquipment($pdo) {
    $stmt = $pdo->query("SELECT e.id, e.name, e.quantity, e.available, e.status, e.notes, ec.label AS category FROM equipment e JOIN equipment_categories ec ON e.category_id = ec.id ORDER BY ec.label, e.name");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}