<?php
// db/home.php

function getHomeStats($pdo) {
    $activeMembers = $pdo
        ->query("SELECT COUNT(*) FROM users WHERE role = 'member' AND active = 1")
        ->fetchColumn();

    $classesPerWeek = $pdo
        ->query("
            SELECT COUNT(DISTINCT cs.id)
            FROM class_schedules cs
            JOIN classes c ON cs.class_id = c.id
            WHERE c.active = 1
        ")
        ->fetchColumn();

    $trainers = $pdo
        ->query("SELECT COUNT(*) FROM users WHERE role = 'trainer' AND active = 1")
        ->fetchColumn();

    $satisfaction = $pdo
        ->query("SELECT ROUND(COALESCE(AVG(rating), 0) * 20) FROM reviews")
        ->fetchColumn();

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
    $stmt = $pdo->query("
        SELECT label
        FROM class_types
        WHERE active = 1
        ORDER BY label
    ");

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}