<?php
require_once '../includes/session.php';

require_once '../db/connection.php';
require_once '../db/trainers.php';
require_once '../template/trainers.php';

// Create the database connection.
$pdo = getDatabaseConnection();

// Build filters from the GET query string.
// This request only reads trainer data, so CSRF protection is not required.
$filters = [
    'specialization' => $_GET['specialization'] ?? '',
    'trainer_id' => $_GET['trainer_id'] ?? '',
    'is_pt' => $_GET['is_pt'] ?? '',
    'active_only' => true
];

// Load trainers using the selected filters.
$trainers = getAllTrainers($pdo, $filters);

// Render the trainer list HTML.
output_trainer_list($trainers);