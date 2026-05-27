<?php
require_once '../includes/session.php';

require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../db/connection.php';
require_once '../db/equipment.php';
require_once '../template/equipment.php';

requireAuth('admin');

$pdo = getDatabaseConnection();
$categories = getAllEquipmentCategories($pdo);
$equipment = getAllEquipment($pdo);

output_header('equipment.css');
?>

<main class="main">
    <div class="container">
        <header class="page-header">
            <div class="page-header-row page-header-actions">
                <div>
                    <h1>Manage Equipment</h1>
                    <p>View, filter, add, edit or remove equipment.</p>
                </div>

                <a href="create_equipment.php" class="main-button">+ New Equipment</a>
            </div>
        </header>

        <div class="equipment-legend">
            <span class="badge badge-success">● Available</span>
            <span class="badge badge-warning">● Maintenance</span>
            <span class="badge badge-danger">● Unavailable</span>
        </div>

        <?php output_equipment_grid($equipment, $categories); ?>
    </div>
</main>

<script src="../js/equipment.js"></script>

<?php output_footer('../admin/'); ?>