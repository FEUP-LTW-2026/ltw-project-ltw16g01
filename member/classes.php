<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/classes.php';
require_once '../db/class_sessions.php';

requireRole('member');

$pdo = getDatabaseConnection();
$memberId = $_SESSION['user_id'];

// Filters
$type = $_GET['type'] ?? '';
$trainer = $_GET['trainer'] ?? '';
$day = $_GET['day'] ?? '';

$filters = [];
if ($type) $filters['type'] = $type;
if ($trainer) $filters['trainer'] = $trainer;
if ($day) $filters['day'] = $day;

$schedules = getAllClassSchedules($pdo, $filters);

// Enrolled session IDs for this member
$stmt = $pdo->prepare("SELECT session_id FROM enrollments WHERE member_id = ? AND status = 'enrolled'");
$stmt->execute([$memberId]);
$enrolledSessionIds = $stmt->fetchAll(PDO::FETCH_COLUMN);

output_header('member-dashboard.css');
?>

<main class="main">
    <div class="container">
        <header class="page-header">
            <h1>Classes</h1>
            <p>Browse and enroll in upcoming sessions</p>
        </header>

        <form id="filter-form" method="get" class="filter-bar">
            <div class="filter-group">
                <label class="form-label">Class Type</label>
                <select name="type" class="form-select">
                    <option value="">All types</option>
                    <?php
                    $types = $pdo->query("SELECT name, label FROM class_types WHERE active = 1")->fetchAll();
                    foreach ($types as $t): ?>
                        <option value="<?= $t['name'] ?>" <?= $type == $t['name'] ? 'selected' : '' ?>><?= $t['label'] ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label class="form-label">Trainer</label>
                <select name="trainer" class="form-select">
                    <option value="">All trainers</option>
                    <?php
                    $trainers = $pdo->query("SELECT id, name FROM users WHERE role='trainer' AND active=1 ORDER BY name")->fetchAll();
                    foreach ($trainers as $tr): ?>
                        <option value="<?= $tr['id'] ?>" <?= $trainer == $tr['id'] ? 'selected' : '' ?>><?= htmlspecialchars($tr['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group">
                <label class="form-label">Day</label>
                <select name="day" class="form-select">
                    <option value="">Any day</option>
                    <?php foreach (['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sunday'] as $d): ?>
                        <option value="<?= strtolower($d) ?>" <?= $day == strtolower($d) ? 'selected' : '' ?>><?= $d ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="filter-group filter-group-end">
                <button type="submit" class="main-button full-width">Filter</button>
            </div>
        </form>

        <div class="results-count">Showing <?= count($schedules) ?> classes</div>

        <div class="class-grid">
            <?php foreach ($schedules as $schedule):
                $nextSession = getUpcomingSessions($pdo, $schedule['schedule_id'], 1);
                $session = $nextSession ? $nextSession[0] : null;
                $isEnrolled = $session && in_array($session['id'], $enrolledSessionIds);
                $enrolledCount = $session ? getSessionEnrollmentCount($pdo, $session['id']) : 0;
                $spotsLeft = $schedule['capacity'] - $enrolledCount;
            ?>
                <div class="class-card" data-schedule-id="<?= $schedule['schedule_id'] ?>">
                    <div class="class-head">
                        <div class="class-time-big"><?= $schedule['start_time'] ?></div>
                    </div>
                    <div class="class-body">
                        <h3 class="class-name"><?= htmlspecialchars($schedule['class_name']) ?></h3>
                        <p><?= htmlspecialchars(substr($schedule['class_description'] ?? '', 0, 100)) ?>…</p>
                        <ul class="class-meta">
                            <li>🧘 <?= htmlspecialchars($schedule['class_type_label'] ?? '') ?></li>
                            <li>👤 <?= htmlspecialchars($schedule['trainer_name'] ?? 'TBD') ?></li>
                            <li>📍 <?= htmlspecialchars($schedule['location']) ?></li>
                            <li>📅 <?= $schedule['day_of_week'] ?> at <?= $schedule['start_time'] ?></li>
                        </ul>
                    </div>
                    <div class="class-foot">
                        <?php if (!$session): ?>
                            <span class="badge badge-muted">No upcoming session</span>
                        <?php elseif ($isEnrolled): ?>
                            <span class="badge badge-success">Enrolled</span>
                            <button class="btn-cancel secondary-button btn-sm" data-session-id="<?= $session['id'] ?>">Cancel</button>
                        <?php elseif ($spotsLeft <= 0): ?>
                            <span class="badge badge-danger">Full</span>
                        <?php else: ?>
                            <button class="btn-enroll main-button btn-sm" data-session-id="<?= $session['id'] ?>">Enroll (<?= $spotsLeft ?> left)</button>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<script>
async function enroll(sessionId) {
    const res = await fetch('../ajax/enroll.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'enroll', session_id: sessionId})
    });
    const data = await res.json();
    if (data.success) location.reload();
    else alert(data.message);
}
async function cancel(sessionId) {
    if (!confirm('Cancel enrollment?')) return;
    const res = await fetch('../ajax/enroll.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({action: 'cancel', session_id: sessionId})
    });
    const data = await res.json();
    if (data.success) location.reload();
    else alert(data.message);
}
document.querySelectorAll('.btn-enroll').forEach(btn => btn.addEventListener('click', () => enroll(btn.dataset.sessionId)));
document.querySelectorAll('.btn-cancel').forEach(btn => btn.addEventListener('click', () => cancel(btn.dataset.sessionId)));
</script>

<?php output_footer(); ?>