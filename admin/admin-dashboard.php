<?php

session_start();

require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../db/admin.php';
require_once '../template/admin.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$stats = getAdminStats();
$members = getMembers();
$trainers = getTrainers();
$classes = getClasses();
$equipment = getEquipment();

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

                <div class="dash-top-right">
                    <a href="#" class="main-button btn-sm">+ New User</a>
                </div>
            </div>
        </div>
    </header>

    <div class="dashboard-content">

        <section>
            <div class="kpi-grid">
                <div class="kpi-card">
                    <p class="kpi-num"><?= htmlspecialchars($stats['members']) ?></p>
                    <p class="kpi-lbl">👥 Active Members</p>
                </div>

                <div class="kpi-card kpi-card-warning">
                    <p class="kpi-num kpi-num-warning"><?= htmlspecialchars($stats['trainers']) ?></p>
                    <p class="kpi-lbl">🏋️ Trainers</p>
                </div>

                <div class="kpi-card kpi-card-success">
                    <p class="kpi-num kpi-num-success"><?= htmlspecialchars($stats['classes']) ?></p>
                    <p class="kpi-lbl">📅 Active Classes / Week</p>
                </div>

                <div class="kpi-card kpi-card-info">
                    <p class="kpi-num kpi-num-info">94%</p>
                    <p class="kpi-lbl">⭐ Satisfaction</p>
                </div>

                <div class="kpi-card">
                    <p class="kpi-num kpi-num-large">1 284</p>
                    <p class="kpi-lbl">📈 Enrollments / Month</p>
                </div>

                <div class="kpi-card kpi-card-danger">
                    <p class="kpi-num kpi-num-danger"><?= htmlspecialchars($stats['equipment_issues']) ?></p>
                    <p class="kpi-lbl">⚠️ Equipment Issues</p>
                </div>
            </div>
        </section>

        <nav>
            <div class="quick-grid">
                <a href="#" class="quick-item">
                    <span class="quick-icon">👤</span>
                    Manage Members
                </a>

                <a href="#" class="quick-item">
                    <span class="quick-icon">🏋️</span>
                    Manage Trainers
                </a>

                <a href="classes.php" class="quick-item">
                    <span class="quick-icon">📅</span>
                    Manage Classes
                </a>

                <a href="#" class="quick-item">
                    <span class="quick-icon">⚙️</span>
                    Manage Equipment
                </a>

                <a href="#" class="quick-item">
                    <span class="quick-icon">📊</span>
                    Reports
                </a>

                <a href="#" class="quick-item">
                    <span class="quick-icon">🔔</span>
                    Notifications
                </a>
            </div>
        </nav>

        <div class="dash-layout">

            <div class="dash-col">

                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Weekly Enrollments</h2>
                        <span class="badge badge-accent">+18% vs last month</span>
                    </div>

                    <div class="card-body card-body-no-bottom">
                        <div class="bar-chart-outer">
                            <div class="bar-chart">
                                <div class="bar-col">
                                    <div class="bar-val">142</div>
                                    <div class="bar bar-h-52"></div>
                                </div>

                                <div class="bar-col">
                                    <div class="bar-val">168</div>
                                    <div class="bar bar-h-61"></div>
                                </div>

                                <div class="bar-col">
                                    <div class="bar-val">155</div>
                                    <div class="bar bar-h-56"></div>
                                </div>

                                <div class="bar-col">
                                    <div class="bar-val">189</div>
                                    <div class="bar bar-h-69"></div>
                                </div>

                                <div class="bar-col">
                                    <div class="bar-val">204</div>
                                    <div class="bar bar-h-74"></div>
                                </div>

                                <div class="bar-col">
                                    <div class="bar-val">231</div>
                                    <div class="bar bar-h-84"></div>
                                </div>

                                <div class="bar-col">
                                    <div class="bar-val bar-val-warning">247</div>
                                    <div class="bar current bar-h-90"></div>
                                </div>
                            </div>

                            <div class="bar-labels">
                                <span class="bar-lbl">W1<br>Mar</span>
                                <span class="bar-lbl">W2<br>Mar</span>
                                <span class="bar-lbl">W3<br>Mar</span>
                                <span class="bar-lbl">W4<br>Mar</span>
                                <span class="bar-lbl">W1<br>Apr</span>
                                <span class="bar-lbl">W2<br>Apr</span>
                                <span class="bar-lbl bar-lbl-warning">W3<br>Apr</span>
                            </div>
                        </div>
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
                                <tr>
                                    <th>#</th>
                                    <th>Class</th>
                                    <th>Trainer</th>
                                    <th>Schedule</th>
                                    <th>Location</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($classes as $index => $class) {
                                    output_admin_class_row($class, $index);
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">All Members</h2>

                        <div class="card-head-actions">
                            <span class="badge badge-muted"><?= htmlspecialchars($stats['members']) ?> total</span>
                            <a href="#" class="main-button btn-sm">+ Add Member</a>
                        </div>
                    </div>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Member</th>
                                    <th>Username</th>
                                    <th>Joined</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($members as $member) {
                                    output_admin_member_row($member);
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

            <div class="dash-col">

                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Recent Activity</h2>

                        <span class="live-badge">
                            <span class="live-dot"></span>
                            Live
                        </span>
                    </div>

                    <div class="card-body">
                        <div class="activity-feed">
                            <div class="activity-item">
                                <div class="activity-dot green"></div>
                                <div>
                                    <div class="activity-text">New member registered</div>
                                    <div class="activity-time">Recently</div>
                                </div>
                            </div>

                            <div class="activity-item">
                                <div class="activity-dot blue"></div>
                                <div>
                                    <div class="activity-text">Classes updated in the system</div>
                                    <div class="activity-time">Today</div>
                                </div>
                            </div>

                            <div class="activity-item">
                                <div class="activity-dot red"></div>
                                <div>
                                    <div class="activity-text">Equipment status checked</div>
                                    <div class="activity-time">Today</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Equipment Availability</h2>
                        <a href="#" class="secondary-button btn-sm">Manage</a>
                    </div>

                    <div class="card-body">
                        <div class="equip-list">
                            <?php foreach ($equipment as $item) {
                                output_admin_equipment_item($item);
                            } ?>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Trainers</h2>
                        <a href="#" class="main-button btn-sm">+ Add Trainer</a>
                    </div>

                    <div class="table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Trainer</th>
                                    <th>Username</th>
                                    <th>Email</th>
                                </tr>
                            </thead>

                            <tbody>
                                <?php foreach ($trainers as $trainer) {
                                    output_admin_trainer_row($trainer);
                                } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>

        </div>

    </div>

</main>

<?php output_footer(); ?>