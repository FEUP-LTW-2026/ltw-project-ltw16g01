<?php
require_once '../includes/session.php';

require_once '../db/connection.php';
require_once '../db/equipment.php';
require_once '../template/equipment.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';

$pdo = getDatabaseConnection();

// Obter categorias e equipamentos
$categories = getAllEquipmentCategories($pdo);
$equipment = getAllEquipment($pdo);

output_header("equipment.css");
?>

<main class="main">
    <div class="container">

        <header class="page-header">
            <nav>
                <ol class="breadcrumb">
                    <li><a href="../index.php">Home</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><span>Equipment</span></li>
                </ol>
            </nav>
            <div class="page-header-row flex-between" style="flex-wrap:wrap; gap:1rem;">
                <div>
                    <h1>Equipment</h1>
                    <p>Real‑time availability of the training floor</p>
                </div>
                <div class="live-indicator">
                    <span class="live-dot"></span> Live
                    <span id="lastUpdate" class="text-muted" style="margin-left:0.5rem;font-size:0.7rem;"></span>
                </div>
            </div>
        </header>

        <!-- Legend -->
        <div class="flex gap-md mb-lg" style="flex-wrap:wrap;">
            <span class="badge badge-success">● Available</span>
            <span class="badge badge-warning">● Maintenance</span>
            <span class="badge badge-danger">● Unavailable</span>
        </div>

        <?php output_equipment_grid($equipment, $categories); ?>

    </div>
</main>

<?php output_footer('../pages/'); ?>

<script src="../js/equipment.js"> </script>