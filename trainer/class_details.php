<?php
require_once '../includes/session.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/classes.php';

requireAuth('trainer');

$pdo = getDatabaseConnection();
$scheduleId = (int)($_GET['id'] ?? 0);
if ($scheduleId <= 0) {
    header('Location: classes.php');
    exit;
}

$schedule = getClassScheduleById($pdo, $scheduleId);
if (!$schedule) {
    header('Location: classes.php');
    exit;
}

$trainer = null;
if (!empty($schedule['trainer_id'])) {
    $stmt = $pdo->prepare("SELECT id, name FROM users WHERE id = ?");
    $stmt->execute([$schedule['trainer_id']]);
    $trainer = $stmt->fetch(PDO::FETCH_ASSOC);
}

$typeStmt = $pdo->prepare("SELECT label FROM class_types WHERE id = ?");
$typeStmt->execute([$schedule['class_type_id']]);
$typeLabel = $typeStmt->fetchColumn();

$reviews = getClassReviews($pdo, $scheduleId);
$avgRating = 0;
$reviewCount = count($reviews);
if ($reviewCount > 0) {
    $sum = array_sum(array_column($reviews, 'rating'));
    $avgRating = round($sum / $reviewCount, 1);
}

output_header('class-details.css');
?>

<main class="main">
    <div class="container">
        <div class="class-detail-header">
            <nav>
                <ol class="breadcrumb">
                    <li><a href="classes.php">Classes</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><span><?= htmlspecialchars($schedule['class_name']) ?></span></li>
                </ol>
            </nav>
        </div>

        <div class="card class-detail-card">
            <div class="card-body">
                <div class="class-detail-info">
                    <h1><?= htmlspecialchars($schedule['class_name']) ?></h1>
                    <div class="class-meta">
                        <span class="badge badge-accent"><?= htmlspecialchars($typeLabel) ?></span>
                        <span><?= htmlspecialchars($schedule['day_of_week']) ?> at <?= substr($schedule['start_time'], 0, 5) ?></span>
                        <span>📍 <?= htmlspecialchars($schedule['location']) ?></span>
                        <span>⏱ <?= $schedule['duration_min'] ?> min</span>
                        <span>👥 Capacity: <?= $schedule['capacity'] ?></span>
                    </div>
                    <?php if (!empty($schedule['description'])): ?>
                        <div class="class-description">
                            <h3>Description</h3>
                            <p><?= nl2br(htmlspecialchars($schedule['description'])) ?></p>
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($trainer): ?>
                    <div class="class-trainer-info">
                        <h3>Instructor</h3>
                        <div class="trainer-simple-card">
                            <div class="trainer-info">
                                <strong><?= htmlspecialchars($trainer['name']) ?></strong>
                                <a href="trainer_profile.php?id=<?= $trainer['id'] ?>" class="secondary-button btn-sm">View Profile</a>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card reviews-card">
            <div class="card-head">
                <h2>Member Reviews</h2>
                <?php if ($reviewCount > 0): ?>
                    <span class="badge badge-warning">★ <?= $avgRating ?> / 5 (<?= $reviewCount ?>)</span>
                <?php endif; ?>
            </div>
            <div class="card-body">
                <?php if (empty($reviews)): ?>
                    <p class="text-muted">No reviews yet for this class. Be the first to review after attending!</p>
                <?php else: ?>
                    <div class="reviews-list">
                        <?php foreach ($reviews as $rev): ?>
                            <div class="review-item">
                                <div class="review-header">
                                    <span class="review-author"><?= htmlspecialchars($rev['member_name']) ?></span>
                                    <div class="stars-display"><?= str_repeat('★', $rev['rating']) . str_repeat('☆', 5 - $rev['rating']) ?></div>
                                    <span class="review-date"><?= date('M d, Y', strtotime($rev['created_at'])) ?></span>
                                </div>
                                <?php if (!empty($rev['comment'])): ?>
                                    <p class="review-comment"><?= nl2br(htmlspecialchars($rev['comment'])) ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</main>

<?php output_footer('../trainer/'); ?>