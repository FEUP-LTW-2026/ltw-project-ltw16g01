<?php
// File: admin/delete_equipment.php
// Description: Handles deletion of equipment by ID. Redirects after action.

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/equipment.php'; // Contains deleteEquipment() function

// Restrict access to admin users only
requireAuth('admin');

// Get database connection
$pdo = getDatabaseConnection();

// Get equipment ID from URL query string
$id = (int)($_GET['id'] ?? 0);

// Attempt to delete the equipment
if ($id > 0 && deleteEquipment($pdo, $id)) {
    // Success: redirect to equipment page with success message
    header('Location: equipment.php?msg=deleted');
    exit;
}

// Failure: redirect with error message
header('Location: equipment.php?error=delete_failed');
exit;
?>