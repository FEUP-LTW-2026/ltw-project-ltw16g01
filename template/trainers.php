<?php
// template/trainers.php

function output_trainer_list($trainers) {
    if (empty($trainers)) {
        echo '<div class="card"><div class="card-body center-text"><p>No trainers found.</p></div></div>';
        return;
    }
    echo '<div class="trainer-grid">';
    foreach ($trainers as $trainer) {
        output_trainer_card($trainer);
    }
    echo '</div>';
}

function output_trainer_card($trainer) {
    $name = htmlspecialchars($trainer['name']);
    $bio = htmlspecialchars($trainer['bio'] ?? 'No bio provided yet.');
    $specializations = array_map('trim', explode(',', $trainer['specializations'] ?? ''));
    $photo = !empty($trainer['photo']) ? $trainer['photo'] : null;
    $initials = strtoupper(substr($trainer['name'], 0, 2));
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
                <!-- Exemplo de rating (podes adaptar se tiveres tabela de avaliações) -->
                <div class="stars">★★★★☆</div>
                <span class="rating-text">(12)</span>
            </div>
            <a href="trainer_profile.php?id=<?= $trainer['id'] ?>" class="secondary-button btn-sm">View Profile</a>
        </div>
    </div>
    <?php
}