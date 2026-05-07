<?php
session_start();

require_once 'db/connection.php';
require_once 'includes/header.php';

$pdo = getDatabaseConnection();
$error   = '';
$success = '';

// CSRF token
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Fetch existing categories for the datalist
$catStmt = $pdo->query("SELECT DISTINCT category FROM equipment ORDER BY category");
$categories = $catStmt->fetchAll(PDO::FETCH_COLUMN);

// Process form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error = 'Invalid request. Please try again.';
    } else {
        $name     = trim($_POST['name'] ?? '');
        $category = trim($_POST['category'] ?? '');
        $quantity = filter_var($_POST['quantity'] ?? 0, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        $status   = $_POST['status'] ?? 'available';
        $notes    = trim($_POST['notes'] ?? '');

        $allowedStatuses = ['available', 'maintenance', 'unavailable'];
        if (!in_array($status, $allowedStatuses)) {
            $status = 'available';
        }

        if ($name === '' || $category === '' || $quantity === false) {
            $error = 'Please fill in all required fields correctly.';
        } elseif (strlen($name) > 120) {
            $error = 'Name must be at most 120 characters.';
        } elseif (strlen($category) > 50) {
            $error = 'Category must be at most 50 characters.';
        } else {
            try {
                $stmt = $pdo->prepare(
                    "INSERT INTO equipment (name, category, quantity, available, status, notes, updated_at)
                     VALUES (:name, :category, :quantity, :available, :status, :notes, datetime('now'))"
                );
                $stmt->execute([
                    ':name'      => $name,
                    ':category'  => $category,
                    ':quantity'  => $quantity,
                    ':available' => $quantity, // initially all units are available
                    ':status'    => $status,
                    ':notes'     => $notes,
                ]);

                $success = "Equipment \"$name\" added successfully.";
                $_POST = []; // reset form fields
            } catch (PDOException $e) {
                $error = 'Database error: Could not add equipment.';
                error_log('Add equipment error: ' . $e->getMessage());
            }
        }
    }
}
output_header("add_equipment.css");
?>

<main class="auth-main">
    <div class="auth-card add-equipment-card">

        <!-- Breadcrumb -->
        <nav class="breadcrumb">
            <a href="index.php">Home</a>
            <span class="breadcrumb-sep">›</span>
            <a href="admin-dashboard.php">Admin</a>
            <span class="breadcrumb-sep">›</span>
            <span>Add Equipment</span>
        </nav>

        <div class="auth-header">
            <a href="index.php" class="auth-logo">FIT<span>PEAK</span></a>
            <h1>Add New Equipment</h1>
            <p>Register a new piece of equipment for the training floor</p>
        </div>

        <?php if ($error !== ''): ?>
            <div class="auth-alert alert-error" role="alert">
                <span class="auth-alert-icon">✕</span>
                <span><?= htmlspecialchars($error) ?></span>
            </div>
        <?php endif; ?>

        <?php if ($success !== ''): ?>
            <div class="auth-alert alert-success" role="alert">
                <span class="auth-alert-icon">✓</span>
                <span><?= htmlspecialchars($success) ?></span>
            </div>
        <?php endif; ?>

        <form class="auth-form" method="post" action="add_equipment.php" novalidate>
            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token']) ?>">

            <!-- Name -->
            <div class="form-group">
                <label class="form-label" for="name">Equipment Name *</label>
                <input
                    type="text"
                    id="name"
                    name="name"
                    class="form-input"
                    placeholder="e.g. Treadmill X500"
                    value="<?= htmlspecialchars($_POST['name'] ?? '') ?>"
                    maxlength="120"
                    required
                >
            </div>

            <!-- Category with datalist -->
            <div class="form-group">
                <label class="form-label" for="category">Category *</label>
                <input
                    type="text"
                    id="category"
                    name="category"
                    class="form-input"
                    placeholder="e.g. Cardio, Strength"
                    list="category-list"
                    value="<?= htmlspecialchars($_POST['category'] ?? '') ?>"
                    maxlength="50"
                    required
                >
                <datalist id="category-list">
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= htmlspecialchars($cat) ?>">
                    <?php endforeach; ?>
                </datalist>
            </div>

            <!-- Two-column row: quantity + status -->
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="quantity">Total Quantity *</label>
                    <input
                        type="number"
                        id="quantity"
                        name="quantity"
                        class="form-input"
                        min="1"
                        value="<?= htmlspecialchars($_POST['quantity'] ?? '1') ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label class="form-label" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <option value="available" <?= (isset($_POST['status']) && $_POST['status'] === 'available') ? 'selected' : '' ?>>Available</option>
                        <option value="maintenance" <?= (isset($_POST['status']) && $_POST['status'] === 'maintenance') ? 'selected' : '' ?>>Maintenance</option>
                        <option value="unavailable" <?= (isset($_POST['status']) && $_POST['status'] === 'unavailable') ? 'selected' : '' ?>>Unavailable</option>
                    </select>
                </div>
            </div>

            <!-- Notes -->
            <div class="form-group">
                <label class="form-label" for="notes">Notes (optional)</label>
                <textarea
                    id="notes"
                    name="notes"
                    class="form-input"
                    rows="3"
                    placeholder="Any additional information..."
                ><?= htmlspecialchars($_POST['notes'] ?? '') ?></textarea>
            </div>

            <!-- Submit -->
            <button type="submit" class="main-button full-button auth-submit">Add Equipment</button>
        </form>

        <div class="auth-divider">or</div>

        <div class="auth-footer">
            <a href="equipment.php">← Back to equipment list</a>
        </div>

    </div>
</main>

<?php require_once 'includes/footer.php'; output_footer(); ?>