<?php
/**
 * File: member/my_classes.php
 * Description: Lists all upcoming classes the logged‑in member is enrolled in.
 *              Allows cancellation of enrollment and notifies the trainer.
 */

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/enrollments.php';
require_once '../db/notifications.php';
require_once '../db/classes.php';

// Restrict to members only
requireAuth('member');

$pdo = getDatabaseConnection();
$memberId = (int)$_SESSION['user_id'];
$memberName = $_SESSION['user_name'] ?? 'Member';

$message = '';
$error = '';

// Handle cancellation request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'cancel') {
    // CSRF protection
    if (!isset($_POST['csrf']) || $_SESSION['csrf'] !== $_POST['csrf']) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }

    $sessionId = (int)($_POST['session_id'] ?? 0);
    if ($sessionId <= 0) {
        $error = 'Invalid session.';
    } else {
        // Get session details before cancellation (for trainer notification)
        $sessionInfo = getSessionClassNotificationInfo($pdo, $sessionId);

        $result = cancelMemberEnrollment($pdo, $memberId, $sessionId);

        if ($result['success']) {
            $message = $result['message'];

            // Notify the trainer if the class has an assigned trainer
            if ($sessionInfo && !empty($sessionInfo['trainer_id'])) {
                notifyTrainerClassEnrollment(
                    $pdo,
                    (int)$sessionInfo['trainer_id'],
                    $memberName,
                    $sessionInfo['class_name'],
                    'cancel'
                );
            }
        } else {
            $error = $result['message'];
        }
    }
}

// Fetch all upcoming enrolled sessions
$sessions = getUpcomingEnrolledSessions($pdo, $memberId);

// Output page header with custom CSS (reuse admin_members.css styling)
output_header('admin_members.css');
?>

<!-- Additional form styles (same as admin members page) -->
<link rel="stylesheet" href="../css/pages/form.css">

<main class="main">
    <div class="container">
        <!-- Page header -->
        <header class="page-header">
            <div class="page-header-row page-header-actions">
                <div>
                    <h1>My Upcoming Classes</h1>
                    <p>Classes you have enrolled in that are scheduled for the future.</p>
                </div>
                <a href="classes.php" class="main-button">Browse More Classes</a>
            </div>
        </header>

        <!-- Display success / error messages -->
        <?php if ($message): ?>
            <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
        <?php endif; ?>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <!-- Results count -->
        <div class="results-bar">
            <p class="results-count">
                Showing <strong><?= count($sessions) ?></strong> upcoming class<?= count($sessions) !== 1 ? 'es' : '' ?>
            </p>
        </div>

        <!-- Table of upcoming classes -->
        <div class="table-wrap" id="enrolled-classes-table">
            <?php if (empty($sessions)): ?>
                <div class="card">
                    <div class="card-body center-text">
                        <p>You are not enrolled in any upcoming classes.</p>
                        <a href="classes.php" class="main-button">Find a Class</a>
                    </div>
                </div>
            <?php else: ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Class Name</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Trainer</th>
                            <th>Location</th>
                            <th>Duration</th>
                            <th>Spots Left</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($sessions as $session): ?>
                            <?php
                                $spotsLeft = (int)$session['capacity'] - (int)$session['enrolled_count'];
                                $sessionDate = new DateTime($session['session_date']);
                                $formattedDate = $sessionDate->format('l, F j, Y');
                                $trainerName = htmlspecialchars($session['trainer_name'] ?? 'TBD');
                            ?>
                            <tr>
                                <td><strong><?= htmlspecialchars($session['class_name']) ?></strong></td>
                                <td><?= htmlspecialchars($formattedDate) ?></td>
                                <td><?= htmlspecialchars($session['start_time']) ?></td>
                                <td><?= $trainerName ?></td>
                                <td><?= htmlspecialchars($session['location']) ?></td>
                                <td><?= (int)$session['duration_min'] ?> min</td>
                                <td>
                                    <?php if ($spotsLeft <= 0): ?>
                                        <span class="badge badge-danger">Full</span>
                                    <?php elseif ($spotsLeft <= 5): ?>
                                        <span class="badge badge-warning"><?= $spotsLeft ?></span>
                                    <?php else: ?>
                                        <span class="badge badge-success"><?= $spotsLeft ?></span>
                                    <?php endif; ?>
                                </td>
                                <td class="actions">
                                    <form method="post" class="inline-form" onsubmit="return confirm('Are you sure you want to cancel your enrollment? This action cannot be undone.');">
                                        <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="session_id" value="<?= (int)$session['session_id'] ?>">
                                        <button type="submit" class="action-link danger">Cancel</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>

        <!-- Back to dashboard link -->
        <div class="mt-4">
            <a href="dashboard.php" class="secondary-button">← Back to Dashboard</a>
        </div>
    </div>
</main>

<?php output_footer('../member/'); ?>