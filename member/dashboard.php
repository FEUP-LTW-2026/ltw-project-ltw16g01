<?php
session_start();

require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/members.php';

requireAuth('member');

$pdo = getDatabaseConnection();
$memberId = $_SESSION['user_id'];

$stats = getMemberStats($pdo, $memberId);
$upcoming = getMemberUpcomingEnrollments($pdo, $memberId);
$pastClasses = getMemberPastClasses($pdo, $memberId);

output_header('member-dashboard.css');
?>

<main class="main">
    <header class="dash-header">
        <div class="dashboard-header">
            <div class="dash-top">
                <div>
                    <p class="dash-date"><?= date('l, d F Y') ?></p>
                    <h1 class="dash-h1">Member <span>Dashboard</span></h1>
                </div>
            </div>
        </div>
    </header>

    <div class="dashboard-content">
        <div class="kpi-grid">
            <div class="kpi-card"><p class="kpi-num"><?= (int)$stats['upcoming'] ?></p><p class="kpi-lbl">📅 Upcoming Classes</p></div>
            <div class="kpi-card"><p class="kpi-num"><?= (int)$stats['past'] ?></p><p class="kpi-lbl">🏁 Classes Completed</p></div>
            <div class="kpi-card"><p class="kpi-num"><?= htmlspecialchars($stats['avg_rating']) ?></p><p class="kpi-lbl">⭐ Average Rating</p></div>
        </div>

        <div class="dash-layout">
            <div class="dash-col">
                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Your Upcoming Classes</h2>
                        <a href="classes.php" class="secondary-button btn-sm">Browse All Classes</a>
                    </div>

                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr><th>Class</th><th>Trainer</th><th>Date</th><th>Time</th><th>Location</th><th>Actions</th></tr>
                            </thead>

                            <tbody>
                                <?php if (empty($upcoming)): ?>
                                    <tr><td colspan="6" class="center-text">You have no upcoming classes. <a href="classes.php">Book one now!</a></td></tr>
                                <?php else: ?>
                                    <?php foreach ($upcoming as $class): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($class['class_name']) ?></td>
                                            <td><?= htmlspecialchars($class['trainer_name'] ?? 'TBD') ?></td>
                                            <td><?= htmlspecialchars($class['session_date']) ?></td>
                                            <td><?= htmlspecialchars($class['start_time']) ?></td>
                                            <td><?= htmlspecialchars($class['location']) ?></td>
                                            <td><button class="btn-cancel-enroll" data-session-id="<?= (int)$class['session_id'] ?>">Cancel</button></td>
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
                        <h2 class="card-title">Recent Classes</h2>
                    </div>

                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr><th>Class</th><th>Date</th><th>Rating</th></tr>
                            </thead>

                            <tbody>
                                <?php if (empty($pastClasses)): ?>
                                    <tr><td colspan="3" class="center-text">You have no completed classes yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($pastClasses as $class): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($class['class_name']) ?></td>
                                            <td><?= htmlspecialchars($class['session_date']) ?></td>
                                            <td>
                                                <?php if (!empty($class['rating'])): ?>
                                                    <?= str_repeat('★', (int)$class['rating']) . str_repeat('☆', 5 - (int)$class['rating']) ?>
                                                <?php else: ?>
                                                    <a href="#" class="rate-link" data-session-id="<?= (int)$class['session_id'] ?>">Rate</a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<script src="../js/member-dashboard.js"></script>

<?php output_footer('../'); ?>