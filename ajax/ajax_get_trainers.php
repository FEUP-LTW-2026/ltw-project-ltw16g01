<?php
session_start();

require_once '../db/connection.php';
require_once '../db/trainers.php';
require_once '../template/trainers.php';

$pdo = getDatabaseConnection();

$filters = [
    'specialization' => $_GET['specialization'] ?? '',
    'trainer_id' => $_GET['trainer_id'] ?? '',
    'is_pt' => $_GET['is_pt'] ?? '',
    'active_only' => true
];

$trainers = getAllTrainers($pdo, $filters);

output_trainer_list($trainers);