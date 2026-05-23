<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/personal_bookings.php';

requireAuth('member');
$pdo = getDatabaseConnection();
$memberId = $_SESSION['user_id'];

$error = '';
$success = '';
$trainers = getAvailableTrainersForPT($pdo);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trainerId = (int)($_POST['trainer_id'] ?? 0);
    $bookingDate = trim($_POST['booking_date'] ?? '');
    $startTime = trim($_POST['start_time'] ?? '');
    $duration = (int)($_POST['duration_min'] ?? 60);
    $notes = trim($_POST['notes'] ?? '');

    if ($trainerId <= 0 || empty($bookingDate) || empty($startTime)) {
        $error = 'Please select a trainer, date and time.';
    } elseif ($duration < 30 || $duration > 120) {
        $error = 'Duration must be between 30 and 120 minutes.';
    } else {
        if (createPTRequest($pdo, $memberId, $trainerId, $bookingDate, $startTime, $duration, $notes)) {
            $success = 'Your personal training request has been sent to the trainer.';
        } else {
            $error = 'Failed to submit request. Please try again.';
        }
    }
}

output_header('request_pt.css');
?>
<link rel="stylesheet" href="../css/pages/form.css">
<main class="main">
    <div class="container" style="max-width: 700px;">
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
            <div class="form-group">
                <label class="form-label">Select Trainer *</label>
                <select name="trainer_id" class="form-select" required>
                    <option value="">-- Choose a trainer --</option>
                    <?php foreach ($trainers as $t): ?>
                        <option value="<?= $t['id'] ?>">
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

<?php output_footer(); ?>