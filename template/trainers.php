<?php
// template/trainers.php

require_once __DIR__ . '/../db/trainers.php'; // para funções auxiliares

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
    $ptDescription = htmlspecialchars($trainer['pt_description'] ?? '');
    $ptRate = $trainer['pt_hourly_rate'] ?? null;
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

            <?php if (!empty($ptDescription)): ?>
                <p class="trainer-bio"><?= nl2br($ptDescription) ?></p>
            <?php else: ?>
                <p class="trainer-bio"><?= nl2br($bio) ?></p>
            <?php endif; ?>

            <?php if (!empty($ptRate)): ?>
                <p class="trainer-price">
                    €<?= number_format($ptRate, 2) ?>/session
                </p>
            <?php endif; ?>
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

/**
 * Renderiza a tabela de treinadores com opções de editar/eliminar.
 */
function output_trainers_admin_table($trainers) {
    if (empty($trainers)) {
        echo '<tr><td colspan="6" class="center-text">No trainers found.</td></tr>';
        return;
    }
    foreach ($trainers as $t) {
        ?>
        <tr>
            <td><?= htmlspecialchars($t['name']) ?></td>
            <td><?= htmlspecialchars($t['username']) ?></td>
            <td><?= htmlspecialchars($t['email']) ?></td>
            <td><?= htmlspecialchars(substr($t['specializations'] ?? '', 0, 50)) ?></td>
            <td><?= $t['active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>' ?></td>
            <td>
                <a href="edit_trainer.php?id=<?= $t['id'] ?>" class="action-link">Edit</a>
                <a href="delete_trainer.php?id=<?= $t['id'] ?>" class="action-link danger" onclick="return confirm('Delete this trainer?')">Delete</a>
            </td>
        </tr>
        <?php
    }
}

/**
 * Renderiza o formulário de criação/edição de treinador.
 */
function output_trainer_form($trainer = null, $errors = [], $success = '') {
    $isEdit = !is_null($trainer);
    $action = $isEdit ? 'edit_trainer.php?id=' . $trainer['id'] : 'create_trainer.php';
    $name = $trainer['name'] ?? '';
    $username = $trainer['username'] ?? '';
    $email = $trainer['email'] ?? '';
    $bio = $trainer['bio'] ?? '';
    $specializations = $trainer['specializations'] ?? '';
    $certifications = $trainer['certifications'] ?? '';
    $active = $trainer['active'] ?? 1;
    ?>
    <div class="container" style="max-width: 700px;">
        <header class="page-header">
            <h1><?= $isEdit ? 'Edit Trainer' : 'Add New Trainer' ?></h1>
        </header>

        <?php if ($errors): ?>
            <?php foreach ($errors as $e): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="POST" class="auth-form card" style="padding: var(--sp-lg);">
            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" name="name" class="form-control" required value="<?= htmlspecialchars($name) ?>">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control" required value="<?= htmlspecialchars($username) ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required value="<?= htmlspecialchars($email) ?>">
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">Bio</label>
                <textarea name="bio" class="form-control" rows="3"><?= htmlspecialchars($bio) ?></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Specializations (comma separated)</label>
                <input type="text" name="specializations" class="form-control" value="<?= htmlspecialchars($specializations) ?>" placeholder="e.g. Yoga, HIIT, Strength">
            </div>
            <div class="form-group">
                <label class="form-label">Certifications</label>
                <input type="text" name="certifications" class="form-control" value="<?= htmlspecialchars($certifications) ?>">
            </div>
            <?php if ($isEdit): ?>
            <div class="form-group">
                <label class="form-label">Status</label>
                <select name="active" class="form-select">
                    <option value="1" <?= $active == 1 ? 'selected' : '' ?>>Active</option>
                    <option value="0" <?= $active == 0 ? 'selected' : '' ?>>Inactive</option>
                </select>
            </div>
            <?php endif; ?>
            <div style="display: flex; gap: var(--sp-md);">
                <button type="submit" class="main-button"><?= $isEdit ? 'Update Trainer' : 'Create Trainer' ?></button>
                <a href="admin_trainers.php" class="secondary-button">Cancel</a>
            </div>
        </form>
    </div>
    <?php
}