<?php
// db/admin.php

function getAdminStats($pdo) {
    // Total de membros ativos
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'member' AND active = 1");
    $members = $stmt->fetchColumn();
    
    // Total de treinadores ativos
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'trainer' AND active = 1");
    $trainers = $stmt->fetchColumn();
    
    // Total de horários de aulas ativos (cada schedule = uma oferta semanal)
    $stmt = $pdo->query("
        SELECT COUNT(DISTINCT cs.id) 
        FROM class_schedules cs 
        JOIN classes c ON cs.class_id = c.id 
        WHERE c.active = 1
    ");
    $classes = $stmt->fetchColumn();
    
    // Equipamentos com problemas (manutenção ou indisponível)
    $stmt = $pdo->query("SELECT COUNT(*) FROM equipment WHERE status IN ('maintenance', 'unavailable')");
    $equipment_issues = $stmt->fetchColumn();
    
    return [
        'members' => $members,
        'trainers' => $trainers,
        'classes' => $classes,
        'equipment_issues' => $equipment_issues
    ];
}

function getMembers($pdo) {
    $stmt = $pdo->query("
        SELECT id, name, username, email, created_at, active
        FROM users
        WHERE role = 'member'
        ORDER BY created_at DESC
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getTrainers($pdo) {
    $stmt = $pdo->query("
        SELECT id, name, username, email, active
        FROM users
        WHERE role = 'trainer'
        ORDER BY name
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getClasses($pdo) {
    // Devolve os horários mais populares (mais inscrições) para a tabela
    $sql = "
        SELECT 
            cs.id as schedule_id,
            c.name as class_name,
            ct.label as class_type_label,
            u.name as trainer_name,
            cs.day_of_week,
            cs.start_time,
            cs.location,
            (SELECT COUNT(*) FROM enrollments e 
             JOIN class_sessions sess ON e.session_id = sess.id 
             WHERE sess.schedule_id = cs.id AND e.status = 'enrolled') as enrolled_count
        FROM class_schedules cs
        JOIN classes c ON cs.class_id = c.id
        JOIN class_types ct ON c.class_type_id = ct.id
        LEFT JOIN users u ON cs.trainer_id = u.id
        WHERE c.active = 1
        ORDER BY enrolled_count DESC
        LIMIT 10
    ";
    $stmt = $pdo->query($sql);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Transformar para o formato esperado pela template (compatível com output_admin_class_row)
    $classes = [];
    foreach ($results as $row) {
        $classes[] = [
            'name' => $row['class_name'],
            'type' => $row['class_type_label'],
            'trainer_name' => $row['trainer_name'] ?? 'No trainer',
            'day_of_week' => $row['day_of_week'],
            'start_time' => $row['start_time'],
            'location' => $row['location']
        ];
    }
    return $classes;
}

function getEquipment($pdo) {
    $stmt = $pdo->query("
        SELECT e.id, e.name, e.quantity, e.available, e.status, e.notes,
               ec.label as category
        FROM equipment e
        JOIN equipment_categories ec ON e.category_id = ec.id
        ORDER BY ec.label, e.name
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}