<?php
require_once '../includes/session.php';

require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/equipment.php';
require_once '../template/equipment.php';

requireAuth('admin');

$pdo = getDatabaseConnection();
$categories = getAllEquipmentCategories($pdo);
$errors = [];
$success = '';
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    $available = $_POST['available'] === '' ? $quantity : (int)$_POST['available'];
    $status = $_POST['status'] ?? 'available';
    $notes = trim($_POST['notes'] ?? '');

    $old = ['name' => $name, 'category_id' => $categoryId, 'quantity' => $quantity, 'available' => $available, 'status' => $status, 'notes' => $notes];

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
        if (createEquipment($pdo, $name, $categoryId, $quantity, $available, $status, $notes)) {
            $success = 'Equipment added successfully.';
            $old = [];
        } else {
            $errors[] = 'Could not add equipment.';
        }
    }
}

output_header('form.css');
?>

<main class="main">
    <?php output_equipment_form($categories, null, $errors, $success, $old); ?>
</main>

<?php output_footer('../admin/'); ?>