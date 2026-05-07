<?php

function getAllClasses($pdo) {
    $stmt = $pdo->prepare("
        SELECT 
            classes.id,
            classes.name,
            classes.description,
            classes.type,
            classes.capacity,
            classes.duration_min,
            classes.day_of_week,
            classes.start_time,
            classes.location,
            users.name AS trainer_name
        FROM classes
        LEFT JOIN users ON classes.trainer_id = users.id
        WHERE classes.active = 1
        ORDER BY classes.day_of_week, classes.start_time
    ");

    $stmt->execute();

    return $stmt->fetchAll();
}