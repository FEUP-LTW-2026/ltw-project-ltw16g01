<?php
// template/equipment.php

/**
 * Renderiza o HTML de um card de equipamento.
 */
function output_equipment_card($item) {
    // Forçar 'unavailable' se available == 0
    $effectiveStatus = $item['status'];
    if ((int)$item['available'] === 0) {
        $effectiveStatus = 'unavailable';
    }

    $percentage = $item['quantity'] > 0
        ? round(((int)$item['available'] / (int)$item['quantity']) * 100)
        : 0;

    $barLevel = $percentage <= 25 ? 'low' : ($percentage <= 60 ? 'medium' : '');

    $statusLabel = [
        'available'   => 'Available',
        'maintenance' => 'Maintenance',
        'unavailable' => 'Unavailable'
    ][$effectiveStatus] ?? 'Unknown';

    $statusBadge = [
        'available'   => 'badge-success',
        'maintenance' => 'badge-warning',
        'unavailable' => 'badge-danger'
    ][$effectiveStatus] ?? 'badge-muted';
    ?>
    <article class="equip-card status-<?= htmlspecialchars($effectiveStatus) ?>" data-eq-id="<?= $item['id'] ?>">
        <div class="equip-card-header">
            <h4 class="equip-name"><?= htmlspecialchars($item['name']) ?></h4>
            <span class="badge <?= $statusBadge ?>"><?= $statusLabel ?></span>
        </div>

        <div class="equip-availability">
            <div class="avail-bar">
                <div class="avail-fill <?= $barLevel ?>" style="width: <?= $percentage ?>%"></div>
            </div>
            <span class="text-muted avail-text" data-avail>
                <?= $item['available'] ?> / <?= $item['quantity'] ?>
            </span>
        </div>

        <?php if (!empty($item['notes'])): ?>
            <p class="equip-notes"><?= htmlspecialchars($item['notes']) ?></p>
        <?php endif; ?>

        <!-- Botões admin sempre no final, alinhados à direita -->
        <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
            <div class="equip-actions">
                <a href="edit_equipment.php?id=<?= $item['id'] ?>" class="secondary-button btn-sm">Edit</a>
                <a href="delete_equipment.php?id=<?= $item['id'] ?>" class="delete-btn btn-sm" onclick="return confirm('Delete this equipment?')">Delete</a>
            </div>
        <?php endif; ?>
    </article>
    <?php
}

/**
 * Renderiza a grelha completa de equipamentos, agrupada por categoria.
 */
function output_equipment_grid($equipment, $categories) {
    foreach ($categories as $cat) {
        $catId = $cat['id'];
        $catLabel = htmlspecialchars($cat['label']);
        // Filtrar equipamentos desta categoria
        $catItems = array_filter($equipment, function($item) use ($catId) {
            return $item['category_id'] == $catId;
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