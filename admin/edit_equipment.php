<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/equipment.php';

requireRole('admin');

$pdo = getDatabaseConnection();
$id = (int)($_GET['id'] ?? 0);

// Buscar equipamento
$stmt = $pdo->prepare("SELECT e.*, ec.label as category_label FROM equipment e JOIN equipment_categories ec ON e.category_id = ec.id WHERE e.id = ?");
$stmt->execute([$id]);
$equipment = $stmt->fetch();

if (!$equipment) {
    header('Location: equipment.php?error=notfound');
    exit;
}

$categories = getAllEquipmentCategories($pdo);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = (int)($_POST['category_id'] ?? 0);
    $quantity = (int)($_POST['quantity'] ?? 1);
    $available = (int)($_POST['available'] ?? $quantity);
    $status = $_POST['status'] ?? 'available';
    $notes = trim($_POST['notes'] ?? '');

    if (empty($name) || $category_id <= 0) {
        $error = 'Name and category are required.';
    } elseif ($available > $quantity) {
        $error = 'Available count cannot exceed total quantity.';
    } else {
        try {
            $stmt = $pdo->prepare("
                UPDATE equipment 
                SET name = ?, category_id = ?, quantity = ?, available = ?, status = ?, notes = ?, updated_at = CURRENT_TIMESTAMP
                WHERE id = ?
            ");
            $stmt->execute([$name, $category_id, $quantity, $available, $status, $notes, $id]);
            $success = 'Equipment updated successfully!';
            // Recarregar dados
            $stmt = $pdo->prepare("SELECT * FROM equipment WHERE id = ?");
            $stmt->execute([$id]);
            $equipment = $stmt->fetch();
        } catch (PDOException $e) {
            $error = 'Database error: ' . $e->getMessage();
        }
    }
}

output_header('form.css');
?>

<main class="main">
    <div class="container" style="max-width: 700px;">
        <header class="page-header">
            <h1>Edit Equipment</h1>
            <p>Update equipment details and availability.</p>
        </header>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" class="card" style="padding: var(--sp-lg);">
            <div class="form-group">
                <label class="form-label">Equipment Name *</label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($equipment['name']) ?>">
            </div>

            <div class="form-group">
                <label class="form-label">Category *</label>
                <select name="category_id" class="form-select" required>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $equipment['category_id'] == $cat['id'] ? 'selected' : '' ?>><?= htmlspecialchars($cat['label']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Total Quantity</label>
                    <input type="number" name="quantity" class="form-control" min="1" value="<?= $equipment['quantity'] ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Available Count</label>
                    <input type="number" name="available" class="form-control" min="0" value="<?= $equipment['available'] ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="available" <?= $equipment['status'] == 'available' ? 'selected' : '' ?>>Available</option>
                    <option value="maintenance" <?= $equipment['status'] == 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                    <option value="unavailable" <?= $equipment['status'] == 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($equipment['notes'] ?? '') ?></textarea>
            </div>

            <div class="form-actions" style="display: flex; gap: var(--sp-md); margin-top: var(--sp-lg);">
                <button type="submit" class="main-button">Update Equipment</button>
                <a href="equipment.php" class="secondary-button">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php output_footer(); ?>