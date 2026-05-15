<?php
// template/trainers.php

require_once '../db/trainers.php'; // para funções auxiliares

function output_trainer_list($trainers) {
    if (empty($trainers)) {
        echo '<div class="card"><div class="card-body center-text"><p>No trainers found matching your filters.</p></div></div>';
        return;
    }
    echo '<div class="trainer-grid">';
    foreach ($trainers as $trainer) {
        output_trainer_card($trainer);
    }
    echo '</div>';
}

function output_trainer_card($trainer) {
    $pdo = $GLOBALS['pdo'] ?? null;
    $name = htmlspecialchars($trainer['name']);
    $bio = htmlspecialchars($trainer['bio'] ?? 'No bio provided yet.');
    $specializations = array_map('trim', explode(',', $trainer['specializations'] ?? ''));
    $photo = !empty($trainer['photo']) ? $trainer['photo'] : null;
    $initials = strtoupper(substr($trainer['name'], 0, 2));
    
    // Obter rating (opcional)
    $ratingData = $pdo ? getTrainerRating($pdo, $trainer['id']) : ['rating' => 0, 'count' => 0];
    $rating = $ratingData['rating'];
    $reviewCount = $ratingData['count'];
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    ?>
    <div class="trainer-card">
        <div class="trainer-avatar">
            <?php if ($photo): ?>
                <img src="<?= htmlspecialchars($photo) ?>" alt="<?= $name ?>">
            <?php else: ?>
                <div class="avatar-placeholder"><?= $initials ?></div>
            <?php endif; ?>
        </div>
        <div class="trainer-body">
            <h3 class="trainer-name"><?= $name ?></h3>
            <div class="trainer-specializations">
                <?php foreach ($specializations as $spec): ?>
                    <?php if (!empty($spec)): ?>
                        <span class="spec-badge"><?= htmlspecialchars($spec) ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
            <p class="trainer-bio"><?= nl2br($bio) ?></p>
        </div>
        <div class="trainer-foot">
            <div class="trainer-rating">
                <div class="stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?php if ($i <= $fullStars): ?>
                            ★
                        <?php elseif ($halfStar && $i == $fullStars + 1): ?>
                            ½
                        <?php else: ?>
                            ☆
                        <?php endif; ?>
                    <?php endfor; ?>
                </div>
                <span class="rating-text"><?= number_format($rating, 1) ?> (<?= $reviewCount ?>)</span>
            </div>
            <a href="trainer_profile.php?id=<?= $trainer['id'] ?>" class="secondary-button btn-sm">View Profile</a>
        </div>
    </div>
    <?php
}