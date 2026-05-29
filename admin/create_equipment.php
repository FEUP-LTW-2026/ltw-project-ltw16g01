<?php
// File: admin/add_equipment.php
// Description: Form to add new gym equipment.

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/equipment.php';   // Contains database functions for equipment
require_once '../template/equipment.php'; // Contains output_equipment_form()

// Restrict access to admin users only
requireAuth('admin');

// Get database connection
$pdo = getDatabaseConnection();

// Fetch equipment categories 
$categories = getAllEquipmentCategories($pdo);

// Initialize variables
$errors = [];
$success = '';
$old = []; // Stores old form input for repopulation on error

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // CSRF protection
    if (!isset($_POST['csrf']) || $_SESSION['csrf'] !== $_POST['csrf']) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }

    // Get and sanitize form inputs
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    $available = $_POST['available'] === '' ? $quantity : (int)$_POST['available'];
    $status = $_POST['status'] ?? 'available';
    $notes = trim($_POST['notes'] ?? '');

    // Save old values to repopulate form in case of error
    $old = [
        'name' => $name,
        'category_id' => $categoryId,
        'quantity' => $quantity,
        'available' => $available,
        'status' => $status,
        'notes' => $notes
    ];

    // Validation
    if ($name === '' || $categoryId <= 0) {
        $errors[] = 'Please fill in all required fields.';
    } elseif ($quantity < 1) {
        $errors[] = 'Total quantity must be at least 1.';
    } elseif ($available < 0) {
        $errors[] = 'Available count cannot be negative.';
    } elseif ($available > $quantity) {
        $errors[] = 'Available count cannot exceed total quantity.';
    } elseif (!in_array($status, ['available', 'maintenance', 'unavailable'])) {
        $errors[] = 'Invalid status.';
    } else {
        // Insert equipment using function from db/equipment.php
        if (createEquipment($pdo, $name, $categoryId, $quantity, $available, $status, $notes)) {
            $success = 'Equipment added successfully.';
            $old = []; // Clear old values on success
        } else {
            $errors[] = 'Could not add equipment.';
        }
    }
}

// Output page header
output_header('form.css');
?>

<main class="main">
    <?php output_equipment_form($categories, null, $errors, $success, $old); ?>
</main>

<?php output_footer('../admin/'); ?>