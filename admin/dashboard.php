<?php
session_start();

require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/admin.php';
require_once '../template/admin.php';

requireAuth('admin');

$pdo = getDatabaseConnection();
$stats = getAdminStats($pdo);
$members = getMembers($pdo);
$trainers = getTrainers($pdo);
$classes = getClasses($pdo);
$equipment = getEquipment($pdo);
$notifications = getRecentNotifications($pdo, 5);
$monthlyGrowth = getMonthlyGrowth($pdo);
$monthlyEnrollments = getMonthlyEnrollments($pdo);

output_header('admin-dashboard.css');
?>

<main class="main">
    <header class="dash-header">
        <div class="dashboard-header">
            <div class="dash-top">
                <div>
                    <p class="dash-date"><?= date('l, d F Y') ?></p>
                    <h1 class="dash-h1">Admin <span>Dashboard</span></h1>
                </div>
            </div>
        </div>
    </header>

    <div class="dashboard-content">
        <section>
            <div class="kpi-grid">
                <div class="kpi-card"><p class="kpi-num"><?= $stats['members'] ?></p><p class="kpi-lbl">👥 Active Members</p></div>
                <div class="kpi-card kpi-card-warning"><p class="kpi-num kpi-num-warning"><?= $stats['trainers'] ?></p><p class="kpi-lbl">🏋️ Trainers</p></div>
                <div class="kpi-card kpi-card-success"><p class="kpi-num kpi-num-success"><?= $stats['classes'] ?></p><p class="kpi-lbl">📅 Active Classes / Week</p></div>
                <div class="kpi-card kpi-card-danger"><p class="kpi-num kpi-num-danger"><?= $stats['equipment_issues'] ?></p><p class="kpi-lbl">⚠️ Equipment Issues</p></div>

                <div class="kpi-card kpi-card-info">
                    <p class="kpi-num kpi-num-info"><?= $monthlyGrowth ?>%</p>
                    <p class="kpi-lbl">📈 Monthly Growth</p>
                </div>

                <div class="kpi-card">
                    <p class="kpi-num kpi-num-large"><?= number_format($monthlyEnrollments) ?></p>
                    <p class="kpi-lbl">✅ Monthly Enrollments</p>
                </div>
            </div>
        </section>

        <nav>
            <div class="quick-grid">
                <a href="members.php" class="quick-item"><span class="quick-icon">👤</span>Manage Members</a>
                <a href="trainers.php" class="quick-item"><span class="quick-icon">🏋️</span>Manage Trainers</a>
                <a href="classes.php" class="quick-item"><span class="quick-icon">📅</span>Manage Classes</a>
                <a href="equipment.php" class="quick-item"><span class="quick-icon">⚙️</span>Manage Equipment</a>
                <a href="reports.php" class="quick-item"><span class="quick-icon">📊</span>Reports</a>
                <a href="notifications.php" class="quick-item"><span class="quick-icon">🔔</span>Notifications</a>
            </div>
        </nav>

        <div class="dash-layout">
            <div class="dash-col">

                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Recent Activity</h2>
                        <a href="notifications.php" class="secondary-button btn-sm">View all</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($notifications)): ?>
                            <p class="text-muted center-text">✨ No recent notifications</p>
                        <?php else: ?>
                            <div class="activity-feed">
                                <?php foreach ($notifications as $notif): ?>
                                    <?php
                                        $icon = match($notif['css_class']) {
                                            'warning' => '⚠️',
                                            'success' => '✅',
                                            'danger', 'error' => '❌',
                                            default => '📢'
                                        };
                                        $typeClass = $notif['css_class'] ?? 'info';
                                        $message = htmlspecialchars($notif['message']);
                                        if (strlen($message) > 80) $message = substr($message, 0, 80) . '…';
                                    ?>
                                    <div class="activity-item">
                                        <div class="activity-icon <?= $typeClass ?>">
                                            <?= $icon ?>
                                        </div>
                                        <div class="activity-content">
                                            <div class="activity-text"><?= $message ?></div>
                                            <div class="activity-time">
                                                <?= date('d/m/Y H:i', strtotime($notif['created_at'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Most Popular Classes</h2>
                        <a href="classes.php" class="secondary-button btn-sm">View all</a>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr><th>#</th><th>Class</th><th>Trainer</th><th>Schedule</th><th>Location</th></tr>
                            </thead>
                            <tbody>
                                <?php 
                                $topClasses = array_slice($classes, 0, 5);
                                foreach ($topClasses as $i => $c) output_admin_class_row($c, $i); 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Recent Members</h2>
                        <div class="card-head-actions">
                            <span class="badge badge-muted"><?= $stats['members'] ?> total</span>
                            <a href="members.php" class="main-button btn-sm">Manage Members</a>
                        </div>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr><th>Member</th><th>Username</th><th>Joined</th><th>Status</th><th>Actions</th></tr>
                            </thead>
                            <tbody>
                                <?php 
                                $recentMembers = array_slice($members, 0, 5);
                                foreach ($recentMembers as $m) output_admin_member_row($m); 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="dash-col">

                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Equipment Availability</h2>
                        <a href="equipment.php" class="secondary-button btn-sm">Manage</a>
                    </div>
                    <div class="card-body">
                        <div class="equip-list">
                            <?php 
                            $limitedEquipment = array_slice($equipment, 0, 10);
                            foreach ($limitedEquipment as $e) output_admin_equipment_item($e); 
                            ?>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Recent Trainers</h2>
                        <a href="trainers.php" class="main-button btn-sm">Manage Trainers</a>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Trainer</th><th>Username</th><th>Email</th></tr></thead>
                            <tbody>
                                <?php 
                                $recentTrainers = array_slice($trainers, 0, 10);
                                foreach ($recentTrainers as $t) output_admin_trainer_row($t); 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php output_footer('../admin/'); ?>