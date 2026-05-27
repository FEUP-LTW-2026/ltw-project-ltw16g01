<?php
// db/home.php

function getHomeStats($pdo) {
    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM users
        WHERE role = 'member'
          AND active = 1
    ");
    $stmt->execute();
    $activeMembers = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT cs.id)
        FROM class_schedules cs
        JOIN classes c ON cs.class_id = c.id
        WHERE c.active = 1
    ");
    $stmt->execute();
    $classesPerWeek = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT COUNT(*)
        FROM users
        WHERE role = 'trainer'
          AND active = 1
    ");
    $stmt->execute();
    $trainers = $stmt->fetchColumn();

    $stmt = $pdo->prepare("
        SELECT ROUND(COALESCE(AVG(rating), 0) * 20)
        FROM reviews
    ");
    $stmt->execute();
    $satisfaction = $stmt->fetchColumn();

    return [
        [
            'number' => $activeMembers,
            'suffix' => '',
            'label' => 'Active Members'
        ],
        [
            'number' => $classesPerWeek,
            'suffix' => '',
            'label' => 'Classes / Week'
        ],
        [
            'number' => $trainers,
            'suffix' => '',
            'label' => 'Trainers'
        ],
        [
            'number' => $satisfaction,
            'suffix' => '%',
            'label' => 'Satisfaction'
        ]
    ];
}

function getHomeClassTypes($pdo) {
    $stmt = $pdo->prepare("
        SELECT label
        FROM class_types
        WHERE active = 1
        ORDER BY label
    ");

    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>