<?php
// File: admin/delete_equipment.php
// Description: Handles deletion of equipment by ID. Redirects after action.

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/equipment.php';        // Contains getEquipmentById() and deleteEquipment()
require_once '../db/notifications.php'; // createNotification()

// Restrict access to admin users only
requireAuth('admin');

// Get database connection
$pdo = getDatabaseConnection();

// Get equipment ID from URL query string
$id = (int)($_GET['id'] ?? 0);

if ($id <= 0) {
    header('Location: equipment.php?error=invalid_id');
    exit;
}

// Fetch equipment details before deletion (to get name, category, quantity for the notification)
$stmt = $pdo->prepare("
    SELECT e.id, e.name, e.quantity, e.available, ec.label AS category_label
    FROM equipment e
    LEFT JOIN equipment_categories ec ON e.category_id = ec.id
    WHERE e.id = ?
");
$stmt->execute([$id]);
$equipment = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$equipment) {
    // Equipment not found
    header('Location: equipment.php?error=not_found');
    exit;
}

$equipmentName = $equipment['name'];
$categoryLabel = $equipment['category_label'] ?? 'Uncategorized';
$quantity = (int)$equipment['quantity'];
$available = (int)$equipment['available'];

// Attempt to delete the equipment
if (deleteEquipment($pdo, $id)) {
    // Success: notify all administrators (role-based notification)
    $adminMessage = "Equipment deleted: {$equipmentName} (Category: {$categoryLabel}, Total: {$quantity}, Available: {$available}).";
    
    createNotification(
        $pdo,
        $adminMessage,   // message
        'warning',       // type (deletion is a warning/info)
        null,            // no specific user
        'admin',         // target_role = 'admin'
        'role',          // scope = 'role'
        'Equipment removed' // optional title
    );
    
    header('Location: equipment.php?msg=deleted');
    exit;
}

// Failure: redirect with error message
header('Location: equipment.php?error=delete_failed');
exit;
?>