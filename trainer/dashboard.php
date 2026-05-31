<?php
/**
 * trainer/dashboard.php
 * Dashboard for trainers showing KPIs, pending PT requests, upcoming classes,
 * confirmed PT sessions, and past classes.
 *
 * All database queries are delegated to helper functions in db/trainers.php.
 */

require_once '../includes/session.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/trainers.php';
require_once '../db/personal_bookings.php';
require_once '../db/notifications.php';

requireAuth('trainer');

$pdo = getDatabaseConnection();
$trainerId = (int)$_SESSION['user_id'];

// Load all dashboard data via helper functions
$stats = getTrainerStats($pdo, $trainerId);
$upcomingClasses = getTrainerUpcomingClasses($pdo, $trainerId, 10);
$pastClasses = getTrainerPastClasses($pdo, $trainerId, 10);
$pendingPT = getTrainerPendingPTRequests($pdo, $trainerId);
$confirmedPT = getTrainerConfirmedPT($pdo, $trainerId, 10);

// Handle accept/reject actions for personal training requests.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf']) || $_SESSION['csrf'] !== $_POST['csrf']) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }

    $requestId = (int)($_POST['request_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if (($action === 'accept' || $action === 'reject') && $requestId) {
        $newStatus = $action === 'accept' ? 'confirmed' : 'cancelled';

        if (updatePTRequestStatus($pdo, $requestId, $trainerId, $newStatus)) {
            // Get the member ID affected by this PT request
            $memberId = getPTRequestMemberId($pdo, $requestId);
            if ($memberId) {
                $trainerName = $_SESSION['user_name'] ?? '';
                $message = $action === 'accept'
                    ? "Your personal training request has been confirmed by trainer " . $trainerName
                    : "Your personal training request has been declined by trainer " . $trainerName;
                createNotification($pdo, (int)$memberId, $message, $action === 'accept' ? 'success' : 'warning');
            }
            header('Location: dashboard.php');
            exit;
        }
    }
}

output_header('trainer_dashboard.css');
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
        <!-- KPI Cards -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <p class="kpi-num"><?= (int)$stats['upcoming'] ?></p>
                <p class="kpi-lbl">📅 This Week's Upcoming Classes</p>
            </div>

            <div class="kpi-card">
                <p class="kpi-num"><?= (int)$stats['students'] ?></p>
                <p class="kpi-lbl">👥 Enrolled Students</p>
            </div>

            <div class="kpi-card">
                <p class="kpi-num"><?= (int)$stats['past'] ?></p>
                <p class="kpi-lbl">🏁 Classes Completed</p>
            </div>
        </div>

        <div class="dash-layout">
            <div class="dash-col">

                <!-- Pending Personal Training Requests -->
                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Pending Personal Training Requests</h2>
                        <a href="pt_requests.php" class="secondary-button btn-sm">View All</a>
                    </div>

                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Member</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Duration</th>
                                    <th>Notes</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pendingPT)): ?>
                                    <tr>
                                        <td colspan="6" class="center-text">No pending requests.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($pendingPT as $req): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($req['member_name']) ?></td>
                                            <td><?= htmlspecialchars($req['booking_date']) ?></td>
                                            <td><?= htmlspecialchars($req['start_time']) ?></td>
                                            <td><?= (int)$req['duration_min'] ?> min</td>
                                            <td><?= htmlspecialchars($req['notes'] ?: '—') ?></td>
                                            <td>
                                                <form method="post" class="action-inline-form">
                                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                                                    <input type="hidden" name="request_id" value="<?= (int)$req['id'] ?>">
                                                    <button type="submit" name="action" value="accept" class="main-button btn-sm">Accept</button>
                                                </form>
                                                <form method="post" class="action-inline-form">
                                                    <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                                                    <input type="hidden" name="request_id" value="<?= (int)$req['id'] ?>">
                                                    <button type="submit" name="action" value="reject" class="decline-btn btn-sm">Decline</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Upcoming Classes -->
                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Your Upcoming Classes</h2>
                        <a href="classes.php" class="secondary-button btn-sm">View Schedule</a>
                    </div>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Class</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Location</th>
                                    <th>Enrolled</th>
                                    <th>Capacity</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($upcomingClasses)): ?>
                                    <tr><td colspan="6" class="center-text">No upcoming classes assigned.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($upcomingClasses as $class): ?>
                                        <?php
                                        $percent = $class['capacity'] > 0
                                            ? round(($class['enrolled_count'] / $class['capacity']) * 100)
                                            : 0;
                                        ?>
                                        <tr>
                                            <td><?= htmlspecialchars($class['class_name']) ?></td>
                                            <td><?= htmlspecialchars($class['session_date']) ?></td>
                                            <td><?= htmlspecialchars($class['start_time']) ?></td>
                                            <td><?= htmlspecialchars($class['location']) ?></td>
                                            <td><?= (int)$class['enrolled_count'] ?> / <?= (int)$class['capacity'] ?></td>
                                            <td>
                                                <span class="badge badge-<?= $percent >= 100 ? 'danger' : ($percent >= 80 ? 'warning' : 'success') ?>">
                                                    <?= $percent ?>%
                                                </span>
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

                <!-- Confirmed PT Sessions -->
                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Confirmed PT Sessions</h2>
                        <a href="pt_requests.php?filter=confirmed" class="secondary-button btn-sm">View All</a>
                    </div>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Member</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Duration</th>
                                    <th>Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($confirmedPT)): ?>
                                    <tr><td colspan="5" class="center-text">No confirmed PT sessions.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($confirmedPT as $pt): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($pt['member_name']) ?></td>
                                            <td><?= htmlspecialchars($pt['booking_date']) ?></td>
                                            <td><?= htmlspecialchars($pt['start_time']) ?></td>
                                            <td><?= (int)$pt['duration_min'] ?> min</td>
                                            <td><?= htmlspecialchars($pt['notes'] ?: '—') ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Recent Classes (Past) -->
                <div class="card">
                    <div class="card-head">
                        <h2 class="card-title">Recent Classes (Past)</h2>
                    </div>
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Class</th>
                                    <th>Date</th>
                                    <th>Time</th>
                                    <th>Enrolled</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($pastClasses)): ?>
                                    <tr><td colspan="4" class="center-text">No past classes yet.</td></tr>
                                <?php else: ?>
                                    <?php foreach ($pastClasses as $class): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($class['class_name']) ?></td>
                                            <td><?= htmlspecialchars($class['session_date']) ?></td>
                                            <td><?= htmlspecialchars($class['start_time']) ?></td>
                                            <td><?= (int)$class['enrolled_count'] ?> / <?= (int)$class['capacity'] ?></td>
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

<?php output_footer('../trainer/'); ?>