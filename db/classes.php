<?php

function getAllClasses($pdo) {
    $stmt = $pdo->prepare("
        SELECT classes.*, users.name AS trainer_name
        FROM classes
        LEFT JOIN users ON classes.trainer_id = users.id
        WHERE classes.active = 1
    ");

    $stmt->execute();

    return $stmt->fetchAll();
}