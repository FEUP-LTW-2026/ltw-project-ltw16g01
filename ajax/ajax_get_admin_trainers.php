<?php
require_once '../includes/session.php';

require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/trainers.php';
require_once '../template/trainers.php';

requireAuth('admin');

// Create the database connection.
$pdo = getDatabaseConnection();

// Build filters from the GET query string.
// This request only reads data, so CSRF protection is not required.
$filters = [
    'specialization' => $_GET['specialization'] ?? '',
    'name' => $_GET['name'] ?? '',
    'is_pt' => $_GET['is_pt'] ?? ''
];

// Load trainers using the selected filters.
$trainers = getAllTrainers($pdo, $filters);

// Render the admin trainers table HTML.
output_trainers_admin_table($trainers);