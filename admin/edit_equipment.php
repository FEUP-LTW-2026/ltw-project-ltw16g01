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
$id = (int)($_GET['id'] ?? 0);
$equipment = getEquipmentById($pdo, $id);

if (!$equipment) {
    header('Location: equipment.php?error=notfound');
    exit;
}

$categories = getAllEquipmentCategories($pdo);
$errors = [];
$success = '';
$old = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $categoryId = (int)($_POST['category_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    $available = (int)($_POST['available'] ?? $quantity);
    $status = $_POST['status'] ?? 'available';
    $notes = trim($_POST['notes'] ?? '');

    $old = ['name' => $name, 'category_id' => $categoryId, 'quantity' => $quantity, 'available' => $available, 'status' => $status, 'notes' => $notes];

    if ($name === '' || $categoryId <= 0) {
        $errors[] = 'Name and category are required.';
    } elseif ($quantity < 1) {
        $errors[] = 'Total quantity must be at least 1.';
    } elseif ($available < 0) {
        $errors[] = 'Available count cannot be negative.';
    } elseif ($available > $quantity) {
        $errors[] = 'Available count cannot exceed total quantity.';
    } elseif (!in_array($status, ['available', 'maintenance', 'unavailable'])) {
        $errors[] = 'Invalid status.';
    } else {
        if (updateEquipment($pdo, $id, $name, $categoryId, $quantity, $available, $status, $notes)) {
            $success = 'Equipment updated successfully.';
            $equipment = getEquipmentById($pdo, $id);
            $old = [];
        } else {
            $errors[] = 'Could not update equipment.';
        }
    }
}

output_header('form.css');
?>

<main class="main">
    <?php output_equipment_form($categories, $equipment, $errors, $success, $old); ?>
</main>

<?php output_footer('../admin/'); ?>