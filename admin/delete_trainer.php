<?php
// File: admin/delete_trainer.php
// Description: Handles deletion of a trainer by ID. Redirects after action.

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/trainers.php'; // Contains deleteTrainer() function

// Restrict access to admin users only
requireAuth('admin');

// Get database connection
$pdo = getDatabaseConnection();

// Get trainer ID from URL query string
$id = (int)($_GET['id'] ?? 0);

// Attempt to delete the trainer
if ($id > 0 && deleteTrainer($pdo, $id)) {
    // Success: redirect to trainers page with success message
    header('Location: trainers.php?msg=deleted');
    exit;
}

// Failure: redirect with error message
header('Location: trainers.php?error=delete_failed');
exit;
?>