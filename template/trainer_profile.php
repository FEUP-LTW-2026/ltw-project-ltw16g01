<?php
// template/trainer_profile.php

function output_trainer_profile_error($title, $message) { ?>
    <main class="main">
        <section class="trainer-profile-section">
            <div class="container">
                <div class="profile-error-card">
                    <h1><?= htmlspecialchars($title) ?></h1>
                    <p><?= htmlspecialchars($message) ?></p>
                    <a href="pt.php" class="secondary-button">Back to Personal Trainers</a>
                </div>
            </div>
        </section>
    </main>
<?php }

function output_trainer_profile_hero($trainer, $rating) {
    $name = htmlspecialchars($trainer['name']);
    $bio = htmlspecialchars($trainer['bio'] ?? 'No bio provided yet.');
    $ptDescription = htmlspecialchars($trainer['pt_description'] ?? '');
    $photo = !empty($trainer['photo']) ? htmlspecialchars($trainer['photo']) : null;
    $initials = htmlspecialchars(strtoupper(substr($trainer['name'], 0, 2)));
    $isPt = !empty($trainer['is_pt']);
    $ptAvailable = !empty($trainer['pt_available']);
    $ratingValue = $rating['rating'] ?? 0;
    $reviewCount = $rating['count'] ?? 0;
    $specializations = array_map('trim', explode(',', $trainer['specializations'] ?? ''));
    ?>

    <section class="trainer-profile-hero">
        <div class="container">
            <div class="trainer-profile-layout">

                <div class="trainer-profile-avatar">
                    <?php if ($photo): ?>
                        <img src="<?= $photo ?>" alt="<?= $name ?>">
                    <?php else: ?>
                        <div class="profile-avatar-placeholder"><?= $initials ?></div>
                    <?php endif; ?>
                </div>

                <div class="trainer-profile-content">
                    <p class="small-text"><?= ($isPt && $ptAvailable) ? 'Personal Trainer' : 'Class Instructor' ?></p>

                    <h1><?= $name ?></h1>

                    <div class="profile-rating">
                        <span class="stars">★</span>
                        <span><?= number_format($ratingValue, 1) ?> / 5</span>
                        <span class="rating-muted">(<?= $reviewCount ?> reviews)</span>
                    </div>

                    <div class="profile-specializations">
                        <?php foreach ($specializations as $spec): ?>
                            <?php if ($spec !== ''): ?>
                                <span class="spec-badge"><?= htmlspecialchars($spec) ?></span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <?php if ($isPt && $ptAvailable && $ptDescription !== ''): ?>
                        <p class="profile-description"><?= nl2br($ptDescription) ?></p>
                    <?php else: ?>
                        <p class="profile-description"><?= nl2br($bio) ?></p>
                    <?php endif; ?>

                    <div class="profile-actions">
                        <?php if ($isPt && $ptAvailable): ?>
                            <a href="register.php" class="main-button">Create Account to Book</a>
                        <?php endif; ?>

                        <a href="trainers.php" class="secondary-button">Back to Trainers</a>
                    </div>
                </div>

            </div>
        </div>
    </section>

<?php }

function output_trainer_profile_details($trainer, $schedules) {
    $bio = htmlspecialchars($trainer['bio'] ?? 'No bio provided yet.');
    $certifications = htmlspecialchars($trainer['certifications'] ?? 'No certifications added yet.');
    $email = htmlspecialchars($trainer['email']);
    $isPt = !empty($trainer['is_pt']);
    $ptAvailable = !empty($trainer['pt_available']);
    ?>

    <section class="trainer-profile-section">
        <div class="container">
            <div class="profile-details-grid">

                <article class="profile-card">
                    <h2>About</h2>
                    <p><?= nl2br($bio) ?></p>
                </article>

                <article class="profile-card">
                    <h2>Certifications</h2>
                    <p><?= nl2br($certifications) ?></p>
                </article>

                <article class="profile-card">
                    <h2>Status</h2>

                    <?php if ($isPt && $ptAvailable): ?>
                        <p>This trainer is currently available for personal training sessions.</p>
                    <?php elseif ($isPt && !$ptAvailable): ?>
                        <p>This trainer is currently not available for personal training sessions.</p>
                    <?php else: ?>
                        <p>This trainer is currently assigned as a class instructor.</p>
                    <?php endif; ?>

                    <p class="profile-contact"><?= $email ?></p>
                </article>

            </div>

            <div class="profile-schedule-card">
                <div class="profile-schedule-head">
                    <h2>Classes & Schedule</h2>

                    <?php if (!empty($schedules)): ?>
                        <p>Regular classes assigned to this trainer.</p>
                    <?php endif; ?>
                </div>

                <?php if (empty($schedules)): ?>
                    <p class="empty-message">No regular classes assigned to this trainer.</p>
                <?php else: ?>
                    <div class="schedule-list">
                        <?php foreach ($schedules as $schedule): ?>
                            <div class="schedule-item">
                                <div>
                                    <h3><?= htmlspecialchars($schedule['class_name']) ?></h3>
                                    <p>
                                        <?= htmlspecialchars($schedule['day_of_week']) ?> ·
                                        <?= htmlspecialchars($schedule['start_time']) ?> ·
                                        <?= htmlspecialchars($schedule['duration_min']) ?> min
                                    </p>
                                </div>

                                <span class="schedule-location"><?= htmlspecialchars($schedule['location']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </section>

<?php }