<?php

session_start();
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/equipment.php';
require_once '../template/equipment.php';

requireRole('admin');
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
                <div style="display: flex; gap: 1rem; align-items: center;">
                    <a href="create_equipment.php" class="main-button btn-sm">+ Add Equipment</a>
                    <div class="live-indicator">
                        <span class="live-dot"></span> Live
                        <span id="lastUpdate" class="text-muted" style="margin-left:0.5rem;font-size:0.7rem;"></span>
                    </div>
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

<?php require_once '../includes/footer.php'; output_footer(); ?>

<script>
function refreshEquipment() {
    fetch('../ajax/equipment-status.php')
        .then(r => r.json())
        .then(data => {
            data.forEach(eq => {
                const card = document.querySelector(`[data-eq-id="${eq.id}"]`);
                if (!card) return;

                let effectiveStatus = eq.status;
                if (parseInt(eq.available) === 0) {
                    effectiveStatus = 'unavailable';
                }

                const availText = card.querySelector('[data-avail]');
                if (availText) availText.textContent = `${eq.available} / ${eq.quantity}`;

                const fill = card.querySelector('.avail-fill');
                if (fill) {
                    const pct = eq.quantity > 0 ? (eq.available / eq.quantity) * 100 : 0;
                    fill.style.width = pct + '%';
                    fill.className = 'avail-fill' + (pct <= 25 ? ' low' : pct <= 60 ? ' medium' : '');
                }

                const badge = card.querySelector('.badge');
                if (badge) {
                    const labels = { available: 'Available', maintenance: 'Maintenance', unavailable: 'Unavailable' };
                    badge.textContent = labels[effectiveStatus] || effectiveStatus;
                    badge.className = `badge badge-${
                        effectiveStatus === 'available' ? 'success' :
                        effectiveStatus === 'maintenance' ? 'warning' : 'danger'
                    }`;
                }

                card.className = card.className.replace(/status-\w+/, `status-${effectiveStatus}`);
            });

            document.getElementById('lastUpdate').textContent =
                'Updated: ' + new Date().toLocaleTimeString('pt-PT');
        })
        .catch(err => console.warn('Live update error:', err));
}

refreshEquipment();
setInterval(refreshEquipment, 30000);
</script>