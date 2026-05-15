<?php
require_once '../db/connection.php';
require_once '../db/trainers.php';
require_once '../template/trainers.php';

$pdo = getDatabaseConnection();

$filters = [
    'specialization' => $_GET['specialization'] ?? '',
    'name'           => $_GET['name'] ?? ''
];

$trainers = getAllTrainers($pdo, $filters);

// Devolve apenas o HTML da lista (sem header/footer)
output_trainer_list($trainers);