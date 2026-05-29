<?php
// File: trainer_profile.php (public/member view)
// Description: Displays public profile information for a specific trainer.

// Load core dependencies
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../db/connection.php';
require_once '../db/trainers.php';
require_once '../template/trainer_profile.php';

// Get database connection
$pdo = getDatabaseConnection();

// Validate and sanitize trainer ID from URL
$trainerId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

// If no valid ID, show error page
if (!$trainerId) {
    output_header('trainer_profile.css');
    output_trainer_profile_error('Invalid Trainer', 'The trainer profile you are trying to access is not valid.');
    output_footer('../');
    exit;
}

// Fetch trainer data (active only by default from getTrainerById)
$trainer = getTrainerById($pdo, $trainerId);

// If trainer not found, show 404-like error
if (!$trainer) {
    output_header('trainer_profile.css');
    output_trainer_profile_error('Trainer Not Found', 'This trainer does not exist or is no longer available.');
    output_footer('../');
    exit;
}

// Get average rating for this trainer
$rating = getTrainerRating($pdo, $trainerId);

// Get upcoming class schedules for this trainer (limit 5)
$schedules = getUpcomingSchedulesForTrainer($pdo, $trainerId, 5);

// Output page header with trainer-specific CSS
output_header('trainer_profile.css');
?>

<main class="main">
    <!-- Trainer hero section (photo, name, specializations, rating) -->
    <?php output_trainer_profile_hero($trainer, $rating); ?>

    <!-- Trainer details (bio, certifications, upcoming schedules) -->
    <?php output_trainer_profile_details($trainer, $schedules); ?>
</main>

<?php output_footer('../pages/'); ?>