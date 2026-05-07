<?php
session_start();

require_once 'db/connection.php';
require_once 'includes/header.php';

$pdo = getDatabaseConnection();
$equipment = $pdo->query("SELECT * FROM equipment ORDER BY category, name")->fetchAll();

$categories = array_unique(array_column($equipment, 'category'));
sort($categories);

output_header("equipment.css");
?>

<link rel="stylesheet" href="css/pages/equipment.css">

<main class="main">
    <div class="container">

        <header class="page-header">
            <nav>
                <ol class="breadcrumb">
                    <li><a href="index.php">Home</a></li>
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

        <?php foreach ($categories as $cat): ?>
            <h3 class="category-title"><?= htmlspecialchars($cat) ?></h3>
            <div class="equip-grid" id="cat-<?= htmlspecialchars(strtolower(str_replace(' ', '-', $cat))) ?>">
                <?php foreach ($equipment as $item):
                    if ($item['category'] !== $cat) continue;

                    // --------------- FIX: force 'unavailable' when available == 0 ---------------
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
                                <div class="avail-fill <?= $barLevel ?>"
                                     style="width: <?= $percentage ?>%"></div>
                            </div>
                            <span class="text-muted avail-text" data-avail>
                                <?= $item['available'] ?> / <?= $item['quantity'] ?>
                            </span>
                        </div>

                        <?php if (!empty($item['notes'])): ?>
                            <p class="text-muted equip-notes"><?= htmlspecialchars($item['notes']) ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endforeach; ?>

    </div>
</main>

<?php require_once 'includes/footer.php'; output_footer(); ?>

<script>
// Live update every 30 seconds – client-side also applies the same override
function refreshEquipment() {
    fetch('ajax/equipment-status.php')
        .then(r => r.json())
        .then(data => {
            data.forEach(eq => {
                const card = document.querySelector(`[data-eq-id="${eq.id}"]`);
                if (!card) return;

                // Client-side: force unavailable if available == 0
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

                // Update card border class
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