<?php
// File: admin/delete_trainer.php
// Description: Handles deletion of a trainer by ID. Redirects after action.

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/trainers.php';           // Contains deleteTrainer() function
require_once '../db/notifications.php'; // createNotification()

// Restrict access to admin users only
requireAuth('admin');

// Get database connection
$pdo = getDatabaseConnection();

// Get trainer ID from URL query string
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: trainers.php?error=invalid_id');
    exit;
}

// Fetch trainer details before deletion (name, username, email, specializations, certifications)
$stmt = $pdo->prepare("
    SELECT 
        u.id, 
        u.name, 
        u.username, 
        u.email,
        tp.specializations,
        tp.certifications
    FROM users u
    LEFT JOIN trainer_profiles tp ON u.id = tp.user_id
    WHERE u.id = ? AND u.role = 'trainer'
");
$stmt->execute([$id]);
$trainer = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$trainer) {
    // Trainer not found
    header('Location: trainers.php?error=not_found');
    exit;
}

$trainerName = $trainer['name'];
$trainerUsername = $trainer['username'];
$trainerEmail = $trainer['email'];
$specializations = $trainer['specializations'] ?? 'none specified';
$certifications = $trainer['certifications'] ?? 'none specified';

// Attempt to delete the trainer
if (deleteTrainer($pdo, $id)) {
    // Success: notify all administrators (role-based notification)
    $adminMessage = "Trainer deleted: {$trainerName} (username: {$trainerUsername}, email: {$trainerEmail}, specializations: {$specializations}, certifications: {$certifications}).";
    
    createNotification(
        $pdo,
        $adminMessage,   // message
        'warning',       // type (deletion is a warning)
        null,            // no specific user
        'admin',         // target_role = 'admin'
        'role',          // scope = 'role'
        'Trainer removed' // optional title
    );
    
    header('Location: trainers.php?msg=deleted');
    exit;
}

// Failure: redirect with error message
header('Location: trainers.php?error=delete_failed');
exit;
?>