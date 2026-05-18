<?php
// template/trainers.php

function output_trainer_list($trainers) {
    if (empty($trainers)) {
        echo '<div class="card"><div class="card-body center-text"><p>No trainers found matching your filters.</p></div></div>';
        return;
    }

    echo '<div class="trainer-grid">';
    foreach ($trainers as $trainer) output_trainer_card($trainer);
    echo '</div>';
}

function output_trainer_card($trainer) {
    $id = (int)$trainer['id'];
    $name = htmlspecialchars($trainer['name']);
    $bio = htmlspecialchars($trainer['bio'] ?? 'No bio provided yet.');
    $ptDescription = htmlspecialchars($trainer['pt_description'] ?? '');
    $ptRate = $trainer['pt_hourly_rate'] ?? null;
    $specializations = array_map('trim', explode(',', $trainer['specializations'] ?? ''));
    $photo = !empty($trainer['photo']) ? htmlspecialchars($trainer['photo']) : null;
    $initials = htmlspecialchars(strtoupper(substr($trainer['name'], 0, 2)));
    $rating = (float)($trainer['rating'] ?? 0);
    $reviewCount = (int)($trainer['review_count'] ?? 0);
    $fullStars = floor($rating);
    $halfStar = ($rating - $fullStars) >= 0.5;
    ?>

    <article class="trainer-card">
        <div class="trainer-avatar">
            <?php if ($photo): ?>
                <img src="<?= $photo ?>" alt="<?= $name ?>">
            <?php else: ?>
                <div class="avatar-placeholder"><?= $initials ?></div>
            <?php endif; ?>
        </div>

        <div class="trainer-body">
            <h3 class="trainer-name"><?= $name ?></h3>

            <div class="trainer-specializations">
                <?php foreach ($specializations as $spec): ?>
                    <?php if ($spec !== ''): ?>
                        <span class="spec-badge"><?= htmlspecialchars($spec) ?></span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>

            <p class="trainer-bio"><?= nl2br($ptDescription !== '' ? $ptDescription : $bio) ?></p>

            <?php if (!empty($ptRate)): ?>
                <p class="trainer-price">€<?= number_format($ptRate, 2) ?>/session</p>
            <?php endif; ?>
        </div>

        <div class="trainer-foot">
            <div class="trainer-rating">
                <div class="stars">
                    <?php for ($i = 1; $i <= 5; $i++): ?>
                        <?php if ($i <= $fullStars): ?>★<?php elseif ($halfStar && $i == $fullStars + 1): ?>½<?php else: ?>☆<?php endif; ?>
                    <?php endfor; ?>
                </div>
                <span class="rating-text"><?= number_format($rating, 1) ?> (<?= $reviewCount ?>)</span>
            </div>

            <a href="trainer_profile.php?id=<?= $id ?>" class="secondary-button btn-sm">View Profile</a>
        </div>
    </article>

    <?php
}

function output_trainers_admin_table($trainers) {
    if (empty($trainers)) {
        echo '<tr><td colspan="6" class="center-text">No trainers found.</td></tr>';
        return;
    }

    foreach ($trainers as $t) {
        $id = (int)$t['id'];
        ?>
        <tr>
            <td><?= htmlspecialchars($t['name']) ?></td>
            <td><?= htmlspecialchars($t['username']) ?></td>
            <td><?= htmlspecialchars($t['email']) ?></td>
            <td><?= htmlspecialchars(substr($t['specializations'] ?? '', 0, 50)) ?></td>
            <td><?= $t['active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>' ?></td>
            <td>
                <a href="edit_trainer.php?id=<?= $id ?>" class="action-link">Edit</a>
                <a href="delete_trainer.php?id=<?= $id ?>" class="action-link danger delete-trainer-link">Delete</a>
            </td>
        </tr>
        <?php
    }
}

function output_trainer_form($trainer = null, $errors = [], $success = '', $old = []) {
    $isEdit = !is_null($trainer);
    $action = $isEdit ? 'edit_trainer.php?id=' . (int)$trainer['id'] : 'create_trainer.php';

    $name = htmlspecialchars($old['name'] ?? ($trainer['name'] ?? ''));
    $username = htmlspecialchars($old['username'] ?? ($trainer['username'] ?? ''));
    $email = htmlspecialchars($old['email'] ?? ($trainer['email'] ?? ''));
    $bio = htmlspecialchars($old['bio'] ?? ($trainer['bio'] ?? ''));
    $specializations = htmlspecialchars($old['specializations'] ?? ($trainer['specializations'] ?? ''));
    $certifications = htmlspecialchars($old['certifications'] ?? ($trainer['certifications'] ?? ''));
    $active = $old['active'] ?? ($trainer['active'] ?? 1);
    ?>

    <div class="container form-container">
        <header class="page-header">
            <nav>
                <ol class="breadcrumb">
                    <li><a href="trainers.php">Trainers</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><span><?= $isEdit ? 'Edit Trainer' : 'Add Trainer' ?></span></li>
                </ol>
            </nav>

            <div class="page-header-row">
                <h1><?= $isEdit ? 'Edit Trainer' : 'Add New Trainer' ?></h1>
                <p><?= $isEdit ? 'Update the information of an existing fitness instructor.' : 'Register a new fitness instructor.' ?></p>
            </div>
        </header>

        <?php if ($errors): ?>
            <?php foreach ($errors as $e): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" action="<?= $action ?>" class="auth-form card form-panel">
            <div class="form-group">
                <label class="form-label">Full Name *</label>
                <input type="text" name="name" class="form-control" required value="<?= $name ?>">
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Username *</label>
                    <input type="text" name="username" class="form-control" required value="<?= $username ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Email *</label>
                    <input type="email" name="email" class="form-control" required value="<?= $email ?>">
                </div>
            </div>

            <div class="form-group">
                <label class="form-label">Bio</label>
                <textarea name="bio" class="form-control" rows="3"><?= $bio ?></textarea>
            </div>

            <div class="form-group">
                <label class="form-label">Specializations</label>
                <input type="text" name="specializations" class="form-control" value="<?= $specializations ?>" placeholder="e.g. Yoga, HIIT, Strength">
            </div>

            <div class="form-group">
                <label class="form-label">Certifications</label>
                <input type="text" name="certifications" class="form-control" value="<?= $certifications ?>">
            </div>

            <?php if (!$isEdit): ?>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password *</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Confirm Password *</label>
                        <input type="password" name="confirm_password" class="form-control" required>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($isEdit): ?>
                <div class="form-group">
                    <label class="form-label">Status</label>
                    <select name="active" class="form-select">
                        <option value="1" <?= (int)$active === 1 ? 'selected' : '' ?>>Active</option>
                        <option value="0" <?= (int)$active === 0 ? 'selected' : '' ?>>Inactive</option>
                    </select>
                </div>
            <?php endif; ?>

            <div class="form-actions">
                <button type="submit" class="main-button"><?= $isEdit ? 'Update Trainer' : 'Create Trainer' ?></button>
                <a href="trainers.php" class="secondary-button">Cancel</a>
            </div>
        </form>
    </div>

    <?php
}