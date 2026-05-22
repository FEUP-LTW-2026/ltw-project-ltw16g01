<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/trainers.php';

requireAuth('trainer');

$pdo = getDatabaseConnection();
$trainerId = $_SESSION['user_id'];

$stats = getTrainerStats($pdo, $trainerId);
$upcoming = getTrainerUpcomingClasses($pdo, $trainerId);
$pastClasses = getTrainerPastClasses($pdo, $trainerId);

output_header('trainer-dashboard.css');
?>

<main class="main">
    <header class="dash-header">
        <div class="dashboard-header">
            <div class="dash-top">
                <div>
                    <p class="dash-date"><?= date('l, d F Y') ?></p>
                    <h1 class="dash-h1">Trainer <span>Dashboard</span></h1>
                </div>
            </div>
        </div>
    </header>

    <div class="dashboard-content">
        <div class="kpi-grid">
            <div class="kpi-card"><p class="kpi-num"><?= $stats['upcoming'] ?></p><p class="kpi-lbl">📅 Upcoming Classes</p></div>
            <div class="kpi-card"><p class="kpi-num"><?= $stats['students'] ?></p><p class="kpi-lbl">👥 Enrolled Students</p></div>
            <div class="kpi-card"><p class="kpi-num"><?= $stats['past'] ?></p><p class="kpi-lbl">🏁 Classes Completed</p></div>
        </div>

        <div class="dash-layout">
            <div class="dash-col">
                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Your Upcoming Classes</h2>
                        <a href="classes.php" class="secondary-button btn-sm">View Schedule</a>
                    </div>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr><th>Class</th><th>Date</th><th>Time</th><th>Location</th><th>Enrolled</th><th>Capacity</th></tr>
                            </thead>
                            <tbody>
                                <?php if (empty($upcoming)): ?>
                                    <tr><td colspan="6" class="center-text">No upcoming classes assigned.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($upcoming as $class): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($class['class_name']) ?></td>
                                            <td><?= htmlspecialchars($class['session_date']) ?></td>
                                            <td><?= htmlspecialchars($class['start_time']) ?></td>
                                            <td><?= htmlspecialchars($class['location']) ?></td>
                                            <td><?= $class['enrolled_count'] ?> / <?= $class['capacity'] ?></td>
                                            <td>
                                                <?php
                                                $percent = $class['capacity'] > 0 ? round(($class['enrolled_count'] / $class['capacity']) * 100) : 0;
                                                $badge = $percent >= 100 ? 'danger' : ($percent >= 80 ? 'warning' : 'success');
                                                ?>
                                                <span class="badge badge-<?= $badge ?>"><?= $percent ?>%</span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="dash-col">
                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Recent Classes (Past)</h2>
                    </div>
                    <div class="table-wrap">
                        <table class="table">
                            <thead><tr><th>Class</th><th>Date</th><th>Time</th><th>Enrolled</th></tr></thead>
                            <tbody>
                                <?php foreach ($pastClasses as $class): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($class['class_name']) ?></td>
                                        <td><?= htmlspecialchars($class['session_date']) ?></td>
                                        <td><?= htmlspecialchars($class['start_time']) ?></td>
                                        <td><?= $class['enrolled_count'] ?> / <?= $class['capacity'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php output_footer('../trainer/'); ?>