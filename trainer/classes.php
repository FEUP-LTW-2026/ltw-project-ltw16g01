<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/classes.php';
require_once '../db/class_sessions.php';
require_once '../db/trainer.php';

requireAuth('trainer');

$pdo = getDatabaseConnection();
$trainerId = $_SESSION['user_id'];

// Get only schedules for this trainer
$filters = ['trainer' => $trainerId];
$schedules = getAllClassSchedules($pdo, $filters);

output_header('classes.css');
?>

<main class="main">
    <div class="container">
        <header class="page-header">
            <h1>My Classes</h1>
            <p>Classes you are teaching – upcoming sessions and enrollments</p>
        </header>

        <div class="results-count">Showing <?= count($schedules) ?> class schedules</div>

        <div class="class-grid">
            <?php foreach ($schedules as $schedule):
                $nextSession = getUpcomingSessions($pdo, $schedule['schedule_id'], 1);
                $session = $nextSession ? $nextSession[0] : null;
                $enrolledCount = $session ? getSessionEnrollmentCount($pdo, $session['id']) : 0;
                $spotsLeft = $schedule['capacity'] - $enrolledCount;
            ?>
                <div class="class-card">
                    <div class="class-head">
                        <div class="class-time-big"><?= $schedule['start_time'] ?></div>
                    </div>
                    <div class="class-body">
                        <h3 class="class-name"><?= htmlspecialchars($schedule['class_name']) ?></h3>
                        <p><?= htmlspecialchars(substr($schedule['class_description'] ?? '', 0, 100)) ?>…</p>
                        <ul class="class-meta">
                            <li>🧘 <?= htmlspecialchars($schedule['class_type_label'] ?? '') ?></li>
                            <li>📍 <?= htmlspecialchars($schedule['location']) ?></li>
                            <li>📅 <?= $schedule['day_of_week'] ?> at <?= $schedule['start_time'] ?></li>
                        </ul>
                    </div>
                    <div class="class-foot">
                        <?php if (!$session): ?>
                            <span class="badge badge-muted">No upcoming session</span>
                        <?php else: ?>
                            <span class="badge badge-info"><?= $enrolledCount ?> / <?= $schedule['capacity'] ?> enrolled</span>
                            <?php if ($spotsLeft <= 0): ?>
                                <span class="badge badge-danger">Full</span>
                            <?php elseif ($spotsLeft <= ceil($schedule['capacity'] * 0.2)): ?>
                                <span class="badge badge-warning"><?= $spotsLeft ?> spots left</span>
                            <?php else: ?>
                                <span class="badge badge-success"><?= $spotsLeft ?> spots left</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<?php output_footer(); ?>