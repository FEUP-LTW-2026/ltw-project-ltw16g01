<?php
// File: admin/dashboard.php
// Description: Admin dashboard showing KPIs, recent activity, and management shortcuts.

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/admin.php';      // Contains all database queries for dashboard
require_once '../template/admin.php'; // Contains helper functions for displaying data

// Restrict access to admin users only
requireAuth('admin');

// Get database connection
$pdo = getDatabaseConnection();

// Fetch dashboard data using functions from db/admin.php (no inline SQL)
$stats = getAdminStats($pdo);                 // KPI numbers (members, trainers, classes, equipment issues)
$members = getMembers($pdo);                  // List of members
$trainers = getTrainers($pdo);                // List of trainers
$classes = getClasses($pdo);                  // List of classes
$equipment = getEquipment($pdo);              // List of equipment
$notifications = getRecentNotifications($pdo, 5); // Recent notifications (first call, maybe old)
$monthlyGrowth = getMonthlyGrowth($pdo);      // Percentage growth of members
$monthlyEnrollments = getMonthlyEnrollments($pdo); // New enrollments this month

// Get admin ID from session for personalized notifications
$adminId = $_SESSION['user_id'];

// Overwrite notifications with admin-specific ones (limit 5)
$notifications = getRecentNotifications($pdo, $adminId, 5);

// Output page header (loads external CSS, no inline styles)
output_header('admin-dashboard.css');
?>

<main class="main">
    <!-- Dashboard header with date and title -->
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
        <!-- KPI Cards (Key Performance Indicators) -->
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

        <!-- Quick navigation cards -->
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

        <!-- Main dashboard layout (two columns) -->
        <div class="dash-layout">
            <!-- Left column -->
            <div class="dash-col">
                <!-- Recent Activity Card -->
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
                                        // Map notification type to an emoji icon
                                        $icon = match($notif['css_class']) {
                                            'warning' => '⚠️',
                                            'success' => '✅',
                                            'danger', 'error' => '❌',
                                            default => '📢'
                                        };
                                        $typeClass = $notif['css_class'] ?? 'info';
                                        $message = htmlspecialchars($notif['message']);
                                        // Trim long messages
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

                <!-- Most Popular Classes Card -->
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
                                // Display top 5 classes
                                $topClasses = array_slice($classes, 0, 5);
                                foreach ($topClasses as $i => $c) output_admin_class_row($c, $i); 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Members Card -->
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
                                <tr><th>Member</th><th>Username</th><th>Joined</th><th>Status</th></tr>
                            </thead>
                            <tbody>
                                <?php 
                                // Display last 5 members
                                $recentMembers = array_slice($members, 0, 5);
                                foreach ($recentMembers as $m) output_admin_member_row($m); 
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Right column -->
            <div class="dash-col">
                <!-- Equipment Availability Card -->
                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Equipment Availability</h2>
                        <a href="equipment.php" class="secondary-button btn-sm">Manage</a>
                    </div>
                    <div class="card-body">
                        <div class="equip-list">
                            <?php 
                            // Display first 10 equipment items
                            $limitedEquipment = array_slice($equipment, 0, 10);
                            foreach ($limitedEquipment as $e) output_admin_equipment_item($e); 
                            ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Trainers Card -->
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
                                // Display first 10 trainers
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