<?php
/**
 * trainer_profile.php – Public profile page for a single trainer.
 * Used by visitors/members to view trainer details, specializations and upcoming classes.
 */

require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../db/connection.php';
require_once '../db/trainers.php';
require_once '../template/trainer_profile.php';

$pdo = getDatabaseConnection();
$trainerId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$trainerId) {
    output_header('trainer_profile.css');
    output_trainer_profile_error('Invalid Trainer', 'The trainer profile you are trying to access is not valid.');
    output_footer('../');
    exit;
}

$trainer = getTrainerById($pdo, $trainerId);

if (!$trainer) {
    output_header('trainer_profile.css');
    output_trainer_profile_error('Trainer Not Found', 'This trainer does not exist or is no longer available.');
    output_footer('../');
    exit;
}

$rating = getTrainerRating($pdo, $trainerId);
$schedules = getUpcomingSchedulesForTrainer($pdo, $trainerId, 5);

output_header('trainer_profile.css');
?>

<main class="main">
    <?php output_trainer_profile_hero($trainer, $rating); ?>
    <?php output_trainer_profile_details($trainer, $schedules); ?>
</main>

<?php output_footer('../pages/'); ?>