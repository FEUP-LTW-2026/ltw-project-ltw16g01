<?php
require_once '../includes/header.php';
require_once '../includes/footer.php';

require_once '../db/connection.php';
require_once '../db/trainers.php';

require_once '../template/trainer_profile.php';

$pdo = getDatabaseConnection();

$trainerId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$trainerId) {
    output_header('trainer_profile.css');
    ?>
    <main class="main">
        <section class="trainer-profile-section">
            <div class="container">
                <div class="profile-error-card">
                    <h1>Invalid Trainer</h1>
                    <p>The trainer profile you are trying to access is not valid.</p>
                    <a href="pt.php" class="secondary-button">Back to Personal Trainers</a>
                </div>
            </div>
        </section>
    </main>
    <?php
    output_footer('../');
    exit;
}

$trainer = getTrainerById($pdo, $trainerId);

if (!$trainer) {
    output_header('trainer_profile.css');
    ?>
    <main class="main">
        <section class="trainer-profile-section">
            <div class="container">
                <div class="profile-error-card">
                    <h1>Trainer Not Found</h1>
                    <p>This trainer does not exist or is no longer available.</p>
                    <a href="pt.php" class="secondary-button">Back to Personal Trainers</a>
                </div>
            </div>
        </section>
    </main>
    <?php
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

<?php output_footer('../'); ?>