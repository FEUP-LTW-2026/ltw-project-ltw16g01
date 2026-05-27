<?php
require_once '../includes/session.php';

require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../db/connection.php';
require_once '../db/equipment.php';
require_once '../db/members.php';
require_once '../template/equipment.php';

requireAuth('member');

$pdo = getDatabaseConnection();
$memberId = (int)$_SESSION['user_id'];

$categories = getAllEquipmentCategories($pdo);
$equipment = getAllEquipment($pdo);
$memberPlan = getMemberPlan($pdo, $memberId);
$reservations = getMemberEquipmentReservations($pdo, $memberId);

output_header('equipment.css');
?>

<main class="main">
    <div class="container">
        <header class="page-header">
            <div class="page-header-row equipment-header-row">
                <div>
                    <h1>Equipment</h1>
                    <p>Real-time availability of the training floor</p>
                </div>

                <div class="live-indicator">
                    <span class="live-dot"></span>
                    Live
                    <span id="lastUpdate" class="text-muted live-update-text"></span>
                </div>
            </div>
        </header>

        <div class="equipment-legend">
            <span class="badge badge-success">● Available</span>
            <span class="badge badge-warning">● Maintenance</span>
            <span class="badge badge-danger">● Unavailable</span>

            <?php if ($memberPlan === 'vip'): ?>
                <span class="badge badge-accent">VIP Reservations Enabled</span>
            <?php else: ?>
                <span class="badge badge-warning">Equipment reservation is VIP only</span>
            <?php endif; ?>
        </div>

        <?php if ($memberPlan !== 'vip'): ?>
            <div class="alert alert-warning">
                Equipment reservation is only available for VIP members.
            </div>
        <?php endif; ?>

        <?php if ($memberPlan === 'vip'): ?>
            <section class="card full-width-card equipment-reservations-card">
                <div class="card-head">
                    <h2 class="card-title">Your Equipment Reservations</h2>
                </div>

                <div class="card-body">
                    <?php if (empty($reservations)): ?>
                        <p class="text-muted">You have no active equipment reservations.</p>
                    <?php else: ?>
                        <div class="table-wrap">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Equipment</th>
                                        <th>Date</th>
                                        <th>Time</th>
                                        <th>Quantity</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    <?php foreach ($reservations as $reservation): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($reservation['equipment_name']) ?></td>
                                            <td><?= htmlspecialchars($reservation['reservation_date']) ?></td>
                                            <td><?= htmlspecialchars($reservation['reservation_time']) ?></td>
                                            <td><?= (int)$reservation['quantity'] ?></td>
                                            <td>
                                                <span class="badge badge-success">
                                                    <?= htmlspecialchars($reservation['status']) ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

        <?php output_equipment_grid($equipment, $categories, $memberPlan); ?>
    </div>
</main>

<script src="../js/equipment.js"></script>

<?php output_footer('../member/'); ?>