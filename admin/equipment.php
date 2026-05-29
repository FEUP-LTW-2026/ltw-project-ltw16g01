<?php
// File: admin/equipment.php
// Description: Listing page for equipment with filtering and management options.

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../db/connection.php';
require_once '../db/equipment.php';
require_once '../template/equipment.php';

// Restrict access to admin users only
requireAuth('admin');

// Get database connection
$pdo = getDatabaseConnection();

// Fetch equipment categories and all equipment data
$categories = getAllEquipmentCategories($pdo);
$equipment = getAllEquipment($pdo);

// Output page header with equipment-specific CSS
output_header('equipment.css');
?>

<main class="main">
    <div class="container">
        <!-- Page header with title and "New Equipment" button -->
        <header class="page-header">
            <div class="page-header-row page-header-actions">
                <div>
                    <h1>Manage Equipment</h1>
                    <p>View, filter, add, edit or remove equipment.</p>
                </div>

                <a href="create_equipment.php" class="main-button">+ New Equipment</a>
            </div>
        </header>

        <!-- Status legend for quick reference -->
        <div class="equipment-legend">
            <span class="badge badge-success">● Available</span>
            <span class="badge badge-warning">● Maintenance</span>
            <span class="badge badge-danger">● Unavailable</span>
        </div>

        <!-- Equipment grid rendered by template function -->
        <?php output_equipment_grid($equipment, $categories); ?>
    </div>
</main>

<!-- External JavaScript for equipment interactions (filters, AJAX) -->
<script src="../js/equipment.js"></script>

<?php output_footer('../admin/'); ?>