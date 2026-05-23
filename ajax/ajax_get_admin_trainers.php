<?php
session_start();

require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/trainers.php';
require_once '../template/trainers.php';

requireAuth('admin');

$pdo = getDatabaseConnection();

$filters = [
    'specialization' => $_GET['specialization'] ?? '',
    'name' => $_GET['name'] ?? '',
    'is_pt' => $_GET['is_pt'] ?? ''
];

$trainers = getAllTrainers($pdo, $filters);

output_trainers_admin_table($trainers);