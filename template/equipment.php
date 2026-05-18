<?php
// template/equipment.php

function equipment_width_class($percentage) {
    $percentage = max(0, min(100, (int)$percentage));
    return 'avail-w-' . (round($percentage / 10) * 10);
}

function output_equipment_card($item) {
    $id = (int)$item['id'];
    $name = htmlspecialchars($item['name']);
    $notes = htmlspecialchars($item['notes'] ?? '');
    $quantity = (int)$item['quantity'];
    $available = (int)$item['available'];
    $effectiveStatus = $available === 0 ? 'unavailable' : $item['status'];
    $percentage = $quantity > 0 ? round(($available / $quantity) * 100) : 0;
    $barLevel = $percentage <= 25 ? 'low' : ($percentage <= 60 ? 'medium' : '');
    $widthClass = equipment_width_class($percentage);

    $statusLabel = ['available' => 'Available', 'maintenance' => 'Maintenance', 'unavailable' => 'Unavailable'][$effectiveStatus] ?? 'Unknown';
    $statusBadge = ['available' => 'badge-success', 'maintenance' => 'badge-warning', 'unavailable' => 'badge-danger'][$effectiveStatus] ?? 'badge-muted';
    ?>

    <article class="equip-card status-<?= htmlspecialchars($effectiveStatus) ?>" data-eq-id="<?= $id ?>">
        <div class="equip-card-header">
            <h4 class="equip-name"><?= $name ?></h4>
            <span class="badge <?= $statusBadge ?>" data-status-badge><?= $statusLabel ?></span>
        </div>

        <div class="equip-availability">
            <div class="avail-bar">
                <div class="avail-fill <?= $barLevel ?> <?= $widthClass ?>"></div>
            </div>
            <span class="text-muted avail-text" data-avail><?= $available ?> / <?= $quantity ?></span>
        </div>

        <?php if ($notes !== ''): ?>
            <p class="equip-notes"><?= $notes ?></p>
        <?php endif; ?>

        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div class="equip-actions">
                <a href="edit_equipment.php?id=<?= $id ?>" class="secondary-button btn-sm">Edit</a>
                <a href="delete_equipment.php?id=<?= $id ?>" class="delete-btn btn-sm delete-equipment-link">Delete</a>
            </div>
        <?php endif; ?>
    </article>

    <?php
}

function output_equipment_grid($equipment, $categories) {
    foreach ($categories as $cat) {
        $catId = (int)$cat['id'];
        $catLabel = htmlspecialchars($cat['label']);

        $catItems = array_filter($equipment, function ($item) use ($catId) {
            return (int)$item['category_id'] === $catId;
        });

        if (empty($catItems)) continue;
        ?>

        <h3 class="category-title"><?= $catLabel ?></h3>

        <div class="equip-grid" data-category-id="<?= $catId ?>">
            <?php foreach ($catItems as $item): ?>
                <?php output_equipment_card($item); ?>
            <?php endforeach; ?>
        </div>

        <?php
    }
}

function output_equipment_form($categories, $equipment = null, $errors = [], $success = '', $old = []) {
    $isEdit = isset($equipment['id']);
    $name = htmlspecialchars($old['name'] ?? ($equipment['name'] ?? ''));
    $categoryId = $old['category_id'] ?? ($equipment['category_id'] ?? '');
    $quantity = (int)($old['quantity'] ?? ($equipment['quantity'] ?? 1));
    $available = $old['available'] ?? ($equipment['available'] ?? $quantity);
    $status = $old['status'] ?? ($equipment['status'] ?? 'available');
    $notes = htmlspecialchars($old['notes'] ?? ($equipment['notes'] ?? ''));
    ?>

    <div class="container form-container">
        <header class="page-header">
            <nav>
                <ol class="breadcrumb">
                    <li><a href="equipment.php">Equipment</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><span><?= $isEdit ? 'Edit Equipment' : 'Add Equipment' ?></span></li>
                </ol>
            </nav>

            <div class="page-header-row">
                <h1><?= $isEdit ? 'Edit Equipment' : 'Add New Equipment' ?></h1>
                <p><?= $isEdit ? 'Update equipment details and availability.' : 'Register a new piece of equipment in the gym.' ?></p>
            </div>
        </header>

        <?php if ($errors): ?>
            <?php foreach ($errors as $e): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" class="auth-form card form-card">
            <div class="form-group form-spaced">
                <label class="form-label">Equipment Name *</label>
                <input type="text" name="name" class="form-control" required value="<?= $name ?>">
            </div>

            <div class="form-group form-spaced">
                <label class="form-label">Category *</label>
                <select name="category_id" class="form-select" required>
                    <option value="">-- Select category --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= (int)$cat['id'] ?>" <?= (string)$categoryId === (string)$cat['id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($cat['label']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row form-spaced">
                <div class="form-group">
                    <label class="form-label">Total Quantity</label>
                    <input type="number" name="quantity" class="form-control" min="1" value="<?= $quantity ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Available Count</label>
                    <input type="number" name="available" class="form-control" min="0" value="<?= htmlspecialchars((string)$available) ?>">
                </div>
            </div>

            <div class="form-group form-spaced">
                <label class="form-label">Status</label>
                <select name="status" class="form-select">
                    <option value="available" <?= $status === 'available' ? 'selected' : '' ?>>Available</option>
                    <option value="maintenance" <?= $status === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                    <option value="unavailable" <?= $status === 'unavailable' ? 'selected' : '' ?>>Unavailable</option>
                </select>
            </div>

            <div class="form-group form-spaced">
                <label class="form-label">Notes</label>
                <textarea name="notes" class="form-control" rows="3"><?= $notes ?></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="main-button"><?= $isEdit ? 'Update Equipment' : 'Add Equipment' ?></button>
                <a href="equipment.php" class="secondary-button">Cancel</a>
            </div>
        </form>
    </div>

    <?php
}