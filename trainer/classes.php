<?php
require_once '../includes/session.php';

require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/classes.php';
require_once '../db/class_sessions.php';
require_once '../db/trainers.php';
require_once '../db/notifications.php';

requireAuth('trainer');

// Create the database connection and get the logged-in trainer id.
$pdo = getDatabaseConnection();
$trainerId = $_SESSION['user_id'];

$message = '';
$error = '';

// Keep the existing CSRF protection for all POST requests.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || $_SESSION['csrf'] !== $_POST['csrf']) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }
}

// Handle class session cancellation.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'cancel_session') {
    $sessionId = (int)($_POST['session_id'] ?? 0);

    if ($sessionId <= 0) {
        $error = 'Invalid session.';
    } else {
        $cancelled = cancelTrainerSession($pdo, $sessionId, $trainerId);

        if ($cancelled) {
            // Notify affected members and admins through database helper functions.
            notifyUsersAboutCancelledTrainerSession($pdo, $sessionId);

            $message = 'Session cancelled successfully. Notifications sent.';
        } else {
            $error = 'Failed to cancel session. Please try again.';
        }
    }
}

// Handle class schedule updates.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update_schedule_details') {
    $scheduleId = (int)($_POST['schedule_id'] ?? 0);
    $location = trim($_POST['location'] ?? '');
    $capacity = (int)($_POST['capacity'] ?? 0);
    $duration = (int)($_POST['duration_min'] ?? 0);
    $startTime = trim($_POST['start_time'] ?? '');

    if ($scheduleId <= 0 || $location === '' || $capacity <= 0 || $duration <= 0 || $startTime === '') {
        $error = 'Please fill in all schedule fields correctly.';
    } else {
        // Load current schedule details through a database helper before updating.
        $old = getTrainerScheduleDetailsForUpdate($pdo, $scheduleId, $trainerId);

        if (!$old) {
            $error = 'Schedule not found or you are not the trainer.';
        } else {
            $updated = updateTrainerScheduleDetails($pdo, $scheduleId, $trainerId, $location, $capacity, $duration, $startTime);

            if ($updated === true) {
                $changes = [];

                if ($old['start_time'] !== $startTime) {
                    $changes[] = "time: '{$old['start_time']}' → '$startTime'";
                }

                if ($old['location'] !== $location) {
                    $changes[] = "location: '{$old['location']}' → '$location'";
                }

                if ((int)$old['capacity'] !== $capacity) {
                    $changes[] = "capacity: {$old['capacity']} → $capacity";
                }

                if ((int)$old['duration_min'] !== $duration) {
                    $changes[] = "duration: {$old['duration_min']} → $duration minutes";
                }

                if (!empty($changes)) {
                    // Notify affected members and admins through database helper functions.
                    notifyUsersAboutUpdatedClassSchedule($pdo, $scheduleId, $changes);
                }

                $message = 'Schedule details updated successfully. Notifications sent to affected members and admins.';
            } else {
                $error = $updated;
            }
        }
    }
}

// Load all schedules assigned to this trainer.
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

        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="results-count">Showing <?= count($schedules) ?> class schedules</div>

        <div class="class-grid">
            <?php foreach ($schedules as $schedule): ?>
                <?php
                $nextSession = getUpcomingSessions($pdo, $schedule['schedule_id'], 1);
                $session = $nextSession ? $nextSession[0] : null;

                if ($session) {
                    $enrolledCount = getSessionEnrollmentCount($pdo, $session['id']);
                    $enrolledMembers = getSessionEnrolledMembers($pdo, $session['id'], $trainerId);
                } else {
                    $enrolledCount = 0;
                    $enrolledMembers = [];
                }

                $spotsLeft = (int)$schedule['capacity'] - $enrolledCount;
                $editBoxId = 'edit-schedule-' . (int)$schedule['schedule_id'];
                ?>

                <div class="class-card">
                    <div class="class-head">
                        <div class="class-time-big"><?= htmlspecialchars($schedule['start_time']) ?></div>
                    </div>

                    <div class="class-body">
                        <h3 class="class-name"><?= htmlspecialchars($schedule['class_name']) ?></h3>
                        <p><?= htmlspecialchars(substr($schedule['class_description'] ?? '', 0, 100)) ?>…</p>

                        <ul class="class-meta">
                            <li>🧘 <?= htmlspecialchars($schedule['class_type_label'] ?? '') ?></li>
                            <li>📍 <?= htmlspecialchars($schedule['location']) ?></li>
                            <li>📅 <?= htmlspecialchars($schedule['day_of_week']) ?> at <?= htmlspecialchars($schedule['start_time']) ?></li>
                            <li>👥 Capacity: <?= (int)$schedule['capacity'] ?></li>
                            <li>⏱️ Duration: <?= (int)$schedule['duration_min'] ?> minutes</li>
                        </ul>
                    </div>

                    <div class="class-actions">
                        <button type="button" class="secondary-button btn-sm edit-schedule-button" data-target="<?= $editBoxId ?>">
                            Edit Schedule
                        </button>

                        <a href="../trainer/class_details.php?id=<?= (int)$schedule['schedule_id'] ?>" class="secondary-button btn-sm">
                            Details
                        </a>

                        <?php if ($session): ?>
                            <form method="post" class="inline-form">
                                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                                <input type="hidden" name="action" value="cancel_session">
                                <input type="hidden" name="session_id" value="<?= (int)$session['id'] ?>">
                                <button type="submit" class="secondary-button btn-sm danger-button">Cancel Session</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div id="<?= $editBoxId ?>" class="schedule-edit-box is-hidden">
                        <form method="post" class="schedule-edit-form">
                            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                            <input type="hidden" name="action" value="update_schedule_details">
                            <input type="hidden" name="schedule_id" value="<?= (int)$schedule['schedule_id'] ?>">

                            <div class="schedule-edit-grid">
                                <div class="form-group">
                                    <label class="form-label">Start Time</label>
                                    <input type="time" name="start_time" class="form-control" value="<?= htmlspecialchars($schedule['start_time']) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Location</label>
                                    <input type="text" name="location" class="form-control" value="<?= htmlspecialchars($schedule['location']) ?>" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Capacity</label>
                                    <input type="number" name="capacity" class="form-control" min="1" value="<?= (int)$schedule['capacity'] ?>" required>
                                </div>

                                <div class="form-group">
                                    <label class="form-label">Duration (minutes)</label>
                                    <input type="number" name="duration_min" class="form-control" min="1" value="<?= (int)$schedule['duration_min'] ?>" required>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="main-button btn-sm">Save Changes</button>

                                <button type="button" class="secondary-button btn-sm cancel-edit-schedule-button" data-target="<?= $editBoxId ?>">
                                    Cancel Edit
                                </button>
                            </div>
                        </form>
                    </div>

                    <div class="class-foot">
                        <?php if (!$session): ?>
                            <span class="badge badge-muted">No upcoming session</span>
                        <?php else: ?>
                            <span class="badge badge-info"><?= $enrolledCount ?> / <?= (int)$schedule['capacity'] ?> enrolled</span>

                            <?php if ($spotsLeft <= 0): ?>
                                <span class="badge badge-danger">Full</span>
                            <?php elseif ($spotsLeft <= ceil((int)$schedule['capacity'] * 0.2)): ?>
                                <span class="badge badge-warning"><?= $spotsLeft ?> spots left</span>
                            <?php else: ?>
                                <span class="badge badge-success"><?= $spotsLeft ?> spots left</span>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>

                    <?php if ($session): ?>
                        <div class="roster-box">
                            <h4>Enrolled Members</h4>

                            <?php if (empty($enrolledMembers)): ?>
                                <p class="text-muted">No members enrolled yet.</p>
                            <?php else: ?>
                                <div class="table-wrap">
                                    <table class="roster-table">
                                        <thead>
                                            <tr>
                                                <th>Name</th>
                                                <th>Email</th>
                                                <th>Enrolled At</th>
                                            </tr>
                                        </thead>

                                        <tbody>
                                            <?php foreach ($enrolledMembers as $member): ?>
                                                <tr>
                                                    <td><?= htmlspecialchars($member['name']) ?></td>
                                                    <td><?= htmlspecialchars($member['email']) ?></td>
                                                    <td><?= htmlspecialchars($member['enrolled_at']) ?></td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</main>

<script src="../js/classes.js"></script>

<?php output_footer('../trainer/'); ?>