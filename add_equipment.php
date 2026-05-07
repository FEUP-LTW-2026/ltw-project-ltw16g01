<?php
session_start();

require_once 'db/connection.php';
require_once 'includes/header.php';

$pdo = getDatabaseConnection();

$stmt = $pdo->prepare("SELECT DISTINCT category FROM equipment ORDER BY category");
$stmt->execute();
$categories = $stmt->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $category = trim($_POST['category']);
    $quantity = $_POST['quantity'];
    $status = $_POST['status'];
    $notes = trim($_POST['notes']);

    $stmt = $pdo->prepare("
        INSERT INTO equipment
        (name, category, quantity, available, status, notes, updated_at)
        VALUES
        (:name, :category, :quantity, :available, :status, :notes, datetime('now'))
    ");

    $stmt->execute([
        ':name' => $name,
        ':category' => $category,
        ':quantity' => $quantity,
        ':available' => $quantity,
        ':status' => $status,
        ':notes' => $notes
    ]);
}

output_header("add_equipment.css");
?>

<main class="main">
    <div class="container">

        <header class="page-header">
            <nav>
                <ol class="breadcrumb">
                    <li><a href="index.php">Home</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><a href="equipment.php">Equipment</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><span>Add Equipment</span></li>
                </ol>
            </nav>

            <div class="page-header-row">
                <div>
                    <h1>Add Equipment</h1>
                    <p>Register a new piece of equipment for the training floor</p>
                </div>
            </div>
        </header>

        <section class="add-equipment-layout">

            <form class="add-equipment-form card" method="post">

                <div class="card-head">
                    <h3>Equipment Details</h3>
                    <span class="badge badge-accent">New</span>
                </div>

                <div class="card-body">

                    <div class="form-group">
                        <label class="form-label" for="name">Equipment Name</label>
                        <input
                            type="text"
                            id="name"
                            name="name"
                            class="form-input"
                            placeholder="Treadmill X500"
                            required
                        >
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="category">Category</label>
                        <input
                            type="text"
                            id="category"
                            name="category"
                            class="form-input"
                            list="category-list"
                            placeholder="Cardio, Strength, Mobility..."
                            required
                        >

                        <datalist id="category-list">
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat['category']) ?>">
                            <?php endforeach; ?>
                        </datalist>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label" for="quantity">Quantity</label>
                            <input
                                type="number"
                                id="quantity"
                                name="quantity"
                                class="form-input"
                                min="1"
                                value="1"
                                required
                            >
                        </div>

                        <div class="form-group">
                            <label class="form-label" for="status">Status</label>
                            <select id="status" name="status" class="form-select">
                                <option value="available">Available</option>
                                <option value="maintenance">Maintenance</option>
                                <option value="unavailable">Unavailable</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="notes">Notes</label>
                        <textarea
                            id="notes"
                            name="notes"
                            class="form-input"
                            rows="4"
                            placeholder="Extra information about this equipment..."
                        ></textarea>
                    </div>

                    <button type="submit" class="main-button full-button">
                        Add Equipment
                    </button>

                </div>
            </form>

            <aside class="equip-card status-available preview-card">
                <div class="equip-card-header">
                    <h4 class="equip-name">Equipment Preview</h4>
                    <span class="badge badge-success">Available</span>
                </div>

                <div class="equip-availability">
                    <div class="avail-bar">
                        <div class="avail-fill" style="width: 100%"></div>
                    </div>
                    <span class="text-muted avail-text">New item</span>
                </div>

                <p class="text-muted equip-notes">
                    After adding the equipment, it will appear on the equipment page grouped by category.
                </p>

                <a href="equipment.php" class="secondary-button full-button btn-sm">
                    Back to Equipment
                </a>
            </aside>

        </section>

    </div>
</main>

<?php
require_once 'includes/footer.php';
output_footer();
?>