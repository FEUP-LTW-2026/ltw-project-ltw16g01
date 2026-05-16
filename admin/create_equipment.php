<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';

requireRole('admin');

$pdo = getDatabaseConnection();
$error = '';
$success = '';

// Obter todas as categorias de equipamento
$categories = $pdo->query("SELECT id, name, label FROM equipment_categories WHERE active = 1 ORDER BY label")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $category_id = $_POST['category_id'] ?? '';
    $quantity = (int)($_POST['quantity'] ?? 1);
    $available = (int)($_POST['available'] ?? $quantity);
    $status = $_POST['status'] ?? 'available';
    $notes = trim($_POST['notes'] ?? '');

    if (empty($name) || empty($category_id)) {
        $error = 'Please fill in all required fields.';
    } elseif ($available > $quantity) {
        $error = 'Available count cannot exceed total quantity.';
    } else {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO equipment (name, category_id, quantity, available, status, notes)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$name, $category_id, $quantity, $available, $status, $notes]);
            $success = 'Equipment added successfully!';
            $_POST = []; // limpar formulário
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
            <nav>
                <ol class="breadcrumb">
                    <li><a href="equipment.php">Equipment</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><span>Add Equipment</span></li>
                </ol>
            </nav>
            <div class="page-header-row">
                <h1>Add New Equipment</h1>
                <p>Register a new piece of equipment in the gym.</p>
            </div>
        </header>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form card" style="padding: var(--sp-lg);">
            <div class="form-group" style="margin-bottom: var(--sp-lg);">
                <label class="form-label">Equipment Name *</label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($_POST['name'] ?? '') ?>">
            </div>

            <div class="form-group" style="margin-bottom: var(--sp-lg);">
                <label class="form-label">Category *</label>
                <select name="category_id" class="form-select" required>
                    <option value="">-- Select category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= (($_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row" style="margin-bottom: var(--sp-lg);">
                <div class="form-group">
                    <label class="form-label">Total Quantity</label>
                    <input type="number" name="quantity" class="form-control" min="1" value="<?= htmlspecialchars($_POST['quantity'] ?? 1) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Available Count</label>
                    <input type="number" name="available" class="form-control" min="0" value="<?= htmlspecialchars($_POST['available'] ?? '') ?>">
                    <small class="text-muted">Leave empty to set equal to total quantity.</small>
                </div>
            </div>

            <div class="form-group" style="margin-bottom: var(--sp-lg);">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="available" <?= (($_POST['status'] ?? '') == 'available') ? 'selected' : '' ?>>Available</option>
                    <option value="maintenance" <?= (($_POST['status'] ?? '') == 'maintenance') ? 'selected' : '' ?>>Maintenance</option>
                    <option value="unavailable" <?= (($_POST['status'] ?? '') == 'unavailable') ? 'selected' : '' ?>>Unavailable</option>
                </select>
            </div>

            <div class="form-group" style="margin-bottom: var(--sp-lg);">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3"><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
            </div>

            <div style="display: flex; gap: var(--sp-md); margin-top: var(--sp-md);">
                <button type="submit" class="main-button">Add Equipment</button>
                <a href="equipment.php" class="secondary-button">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php output_footer(); ?>