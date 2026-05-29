<?php
/**
 * request_pt.php – Member page to request a personal training session.
 * Only available for Premium and VIP members.
 */

require_once '../includes/session.php';
require_once '../includes/session.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/personal_bookings.php';
require_once '../db/members.php';
require_once '../db/notifications.php'; // For trainer notification

// Only logged‑in members
requireAuth('member');

$pdo = getDatabaseConnection();
$memberId = $_SESSION['user_id'];
$memberPlan = getMemberPlan($pdo, $memberId);

// Block Basic members
if ($memberPlan === 'basic') {
    output_header('request_pt.css');
    ?>

    <main class="main">
        <div class="container">
            <header class="page-header">
                <h1>Personal Training Not Available</h1>
                <p>Your current membership plan does not include personal training sessions.</p>
            </header>

            <div class="auth-form card form-card">
                <div class="alert alert-danger">
                    Your Basic plan does not include access to Personal Training sessions.
                </div>
                <p>Upgrade to Premium or VIP to request personal training sessions with our trainers.</p>
                <div class="form-actions">
                    <a href="dashboard.php" class="secondary-button">Back to Dashboard</a>
                </div>
            </div>
        </div>
    </main>

    <?php
    output_footer();
    exit;
}

$error = '';
$success = '';
$trainers = getAvailableTrainersForPT($pdo);

// Fetch member name for the notification (to avoid extra DB call later, do it once)
$stmt = $pdo->prepare("SELECT name FROM users WHERE id = ?");
$stmt->execute([$memberId]);
$memberName = $stmt->fetchColumn();

// Process the request form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CSRF protection
    if (!isset($_POST['csrf']) || $_SESSION['csrf'] !== $_POST['csrf']) {
        http_response_code(403);
        exit('Invalid CSRF token.');
    }

    $trainerId = (int)($_POST['trainer_id'] ?? 0);
    $bookingDate = trim($_POST['booking_date'] ?? '');
    $startTime = trim($_POST['start_time'] ?? '');
    $duration = (int)($_POST['duration_min'] ?? 60);
    $notes = trim($_POST['notes'] ?? '');
    $bookingDateTime = DateTime::createFromFormat('Y-m-d H:i', $bookingDate . ' ' . $startTime);
    $minimumDateTime = new DateTime('+3 hours');

    if ($trainerId <= 0 || empty($bookingDate) || empty($startTime)) {
        $error = 'Please select a trainer, date and time.';
    } elseif (!$bookingDateTime) {
        $error = 'Please choose a valid date and time.';
    } elseif ($bookingDateTime <= new DateTime()) {
        $error = 'You cannot request a PT session for a date or time that has already passed.';
    } elseif ($bookingDateTime < $minimumDateTime) {
        $error = 'PT sessions must be requested at least 3 hours in advance.';
    } elseif ($duration < 30 || $duration > 120) {
        $error = 'Duration must be between 30 and 120 minutes.';
    } elseif (memberHasBookingConflict($pdo, $memberId, $bookingDate, $startTime, $duration)) {
        $error = 'You already have a PT session (pending or confirmed) at this time.';
    } elseif (trainerHasBookingConflict($pdo, $trainerId, $bookingDate, $startTime, $duration)) {
        $error = 'The selected trainer is not available at this time (already booked for another PT).';
    } elseif (trainerHasClassConflict($pdo, $trainerId, $bookingDate, $startTime, $duration)) {
        $error = 'The selected trainer is teaching a group class at this time.';
    } elseif (createPTRequest($pdo, $memberId, $trainerId, $bookingDate, $startTime, $duration, $notes)) {
        $success = 'Your personal training request has been sent to the trainer.';

        // Notify the selected trainer about the request
        $formattedDateTime = $bookingDateTime->format('l, F j, Y \a\t g:i A');
        $message = "{$memberName} has requested a personal training session with you on {$formattedDateTime} (duration: {$duration} min).";
        createNotification(
            $pdo,
            $trainerId,          // recipient – the trainer
            $message,
            'info',              // type
            'PT Request'         // optional title (will be ignored if table lacks column)
        );
    } else {
        $error = 'Failed to submit request. Please try again.';
    }
}

output_header('request_pt.css');
?>

<link rel="stylesheet" href="../css/pages/form.css">

<main class="main">
    <div class="container">
        <header class="page-header">
            <h1>Request Personal Training</h1>
            <p>Choose a certified personal trainer and propose a session time.</p>
        </header>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" class="auth-form card form-card">
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">

            <div class="form-group">
                <label class="form-label">Select Trainer *</label>
                <select name="trainer_id" class="form-select" required>
                    <option value="">-- Choose a trainer --</option>
                    <?php foreach ($trainers as $t): ?>
                        <option value="<?= (int)$t['id'] ?>">
                            <?= htmlspecialchars($t['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Date *</label>
                    <input type="date" name="booking_date" class="form-control" required min="<?= date('Y-m-d') ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Start Time *</label>
                    <input type="time" name="start_time" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Duration (minutes)</label>
                <select name="duration_min" class="form-select">
                    <option value="30">30 minutes</option>
                    <option value="45">45 minutes</option>
                    <option value="60" selected>60 minutes</option>
                    <option value="90">90 minutes</option>
                    <option value="120">120 minutes</option>
                </select>
            </div>

            <div class="form-group">
                <label class="form-label">Notes (optional)</label>
                <textarea name="notes" class="form-control" rows="3" placeholder="e.g., Focus on back strength, injury history, etc."></textarea>
            </div>

            <div class="form-actions">
                <button type="submit" class="main-button">Send Request</button>
                <a href="dashboard.php" class="secondary-button">Cancel</a>
            </div>
        </form>
    </div>
</main>

<?php output_footer('../member/'); ?>