<?php
/**
 * profile.php – Template functions for user profile pages (member, trainer, admin).
 * Includes hero section, basic profile form, password change form, trainer public profile form,
 * schedule table, and a public trainer profile view.
 */

/**
 * Returns the display label for a user role.
 */
function profile_role_label($role) {
    return [
        'member' => 'Member',
        'trainer' => 'Trainer',
        'admin' => 'Admin'
    ][$role] ?? ucfirst($role);
}

/**
 * Outputs an alert message (success or error).
 */
function output_profile_alert($message, $type = 'success') {
    if (!$message) return;

    $class = $type === 'success' ? 'badge-success' : 'badge-danger';
    ?>
    <div class="profile-alert <?= $class ?>">
        <?= htmlspecialchars($message) ?>
    </div>
    <?php
}

/**
 * Renders the profile hero section: large avatar, name, role badge, meta info.
 */
function output_profile_hero($user, $subtitle = '') {
    $photo = $user['photo'] ?? null;
    $initials = strtoupper(substr($user['name'], 0, 2));
    ?>
    <section class="profile-hero">
        <div class="profile-avatar-large">
            <?php if (!empty($photo)): ?>
                <img src="<?= htmlspecialchars($photo) ?>" alt="<?= htmlspecialchars($user['name']) ?>">
            <?php else: ?>
                <span><?= htmlspecialchars($initials) ?></span>
            <?php endif; ?>
        </div>

        <div class="profile-hero-content">
            <span id="role-label" class="badge badge-accent">
                <?= htmlspecialchars(profile_role_label($user['role'])) ?>
            </span>

            <h1><?= htmlspecialchars($user['name']) ?></h1>

            <p><?= htmlspecialchars($subtitle !== '' ? $subtitle : $user['email']) ?></p>

            <div class="profile-meta-row">
                <span>@<?= htmlspecialchars($user['username']) ?></span>
                <span><?= htmlspecialchars($user['email']) ?></span>
                <?php if (!empty($user['created_at'])): ?>
                    <span>Joined <?= htmlspecialchars(substr($user['created_at'], 0, 10)) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </section>
    <?php
}

/**
 * Displays the form for updating basic account information (name, username, email, photo).
 */
function output_basic_profile_form($user) {
    ?>
    <section class="card profile-card">
        <div class="card-head">
            <h2>Account Details</h2>
            <span class="badge badge-muted">Editable</span>
        </div>

        <div class="card-body">
            <form method="post" class="profile-form" enctype="multipart/form-data">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                <input type="hidden" name="action" value="update_basic_profile">

                <div class="profile-form-grid">
                    <div class="form-group">
                        <label class="form-label" for="name">Name</label>
                        <input class="form-input" type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="username">Username</label>
                        <input class="form-input" type="text" id="username" name="username" value="<?= htmlspecialchars($user['username']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="email">Email</label>
                        <input class="form-input" type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="photo">Profile photo</label>

                        <input type="hidden" name="current_photo" value="<?= htmlspecialchars($user['photo'] ?? '') ?>">

                        <div class="custom-file-upload">
                            <input type="file" id="photo" name="photo" accept="image/png,image/jpeg,image/webp">
                            <label for="photo" class="custom-file-button">Choose image</label>
                            <span id="file-name" class="custom-file-name">No image selected</span>
                        </div>

                        <small class="text-muted">Accepted formats: PNG, JPG or WEBP.</small>
                    </div>
                </div>

                <button class="main-button" type="submit">Save Profile</button>
            </form>
        </div>
    </section>
    <?php
}

/**
 * Displays the form for changing the user's password.
 */
function output_password_form() {
    ?>
    <section class="card profile-card">
        <div class="card-head">
            <h2>Change Password</h2>
            <span class="badge badge-warning">Security</span>
        </div>

        <div class="card-body">
            <form method="post" class="profile-form" id="profilePasswordForm">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                <input type="hidden" name="action" value="update_password">

                <div class="profile-form-grid">
                    <div class="form-group">
                        <label class="form-label" for="current_password">Current password</label>
                        <input class="form-input" type="password" id="current_password" name="current_password" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="new_password">New password</label>
                        <input class="form-input" type="password" id="new_password" name="new_password" minlength="8" required>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm_password">Confirm password</label>
                        <input class="form-input" type="password" id="confirm_password" name="confirm_password" minlength="8" required>
                    </div>
                </div>

                <button class="secondary-button" type="submit">Update Password</button>
            </form>
        </div>
    </section>
    <?php
}

/**
 * Displays the form for editing a trainer's public profile (bio, specializations, certifications).
 */
function output_trainer_edit_form($trainerProfile) {
    ?>
    <section class="card profile-card">
        <div class="card-head">
            <h2>Public Trainer Profile</h2>
            <span class="badge badge-accent">Editable</span>
        </div>

        <div class="card-body">
            <form method="post" class="profile-form">
                <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
                <input type="hidden" name="action" value="update_trainer_profile">

                <div class="form-group">
                    <label class="form-label" for="bio">Bio</label>
                    <textarea class="form-input profile-textarea" id="bio" name="bio" rows="5" placeholder="Tell members about your experience..."><?= htmlspecialchars($trainerProfile['bio'] ?? '') ?></textarea>
                </div>

                <div class="profile-form-grid">
                    <div class="form-group">
                        <label class="form-label" for="specializations">Specializations</label>
                        <input class="form-input" type="text" id="specializations" name="specializations" placeholder="Yoga, HIIT, Strength" value="<?= htmlspecialchars($trainerProfile['specializations'] ?? '') ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="certifications">Certifications</label>
                        <input class="form-input" type="text" id="certifications" name="certifications" placeholder="ACE CPT, RYT-200" value="<?= htmlspecialchars($trainerProfile['certifications'] ?? '') ?>">
                    </div>
                </div>

                <button class="main-button" type="submit">Save Public Profile</button>
            </form>
        </div>
    </section>
    <?php
}

/**
 * Outputs a table of class schedules (used in trainer profile and admin areas).
 */
function output_schedule_table($schedules) {
    if (empty($schedules)) {
        echo '<p class="text-muted">No classes assigned yet.</p>';
        return;
    }
    ?>
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>Class</th>
                    <th>Type</th>
                    <th>Day</th>
                    <th>Time</th>
                    <th>Location</th>
                    <th>Capacity</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($schedules as $schedule): ?>
                    <tr>
                        <td><?= htmlspecialchars($schedule['class_name']) ?></td>
                        <td><?= htmlspecialchars($schedule['class_type']) ?></td>
                        <td><?= htmlspecialchars($schedule['day_of_week']) ?></td>
                        <td><?= htmlspecialchars($schedule['start_time']) ?></td>
                        <td><?= htmlspecialchars($schedule['location']) ?></td>
                        <td><?= htmlspecialchars($schedule['enrolled_count']) ?> / <?= htmlspecialchars($schedule['capacity']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

/**
 * Renders a complete public trainer profile (hero + about + schedule).
 * Used by the public trainer profile page.
 */
function output_public_trainer_profile($trainer, $ratingData, $schedules) {
    $profile = [
        'role' => 'trainer',
        'name' => $trainer['name'],
        'username' => $trainer['username'],
        'email' => $trainer['email'],
        'photo' => $trainer['photo'] ?? null,
        'created_at' => ''
    ];

    output_profile_hero($profile, $trainer['specializations'] ?? 'Certified FitPeak trainer');
    ?>

    <section class="profile-grid-two">
        <div class="card profile-card">
            <div class="card-head">
                <h2>About</h2>
                <span class="badge badge-warning">
                    ★ <?= number_format($ratingData['rating'] ?? 0, 1) ?> 
                    (<?= (int)($ratingData['count'] ?? 0) ?>)
                </span>
            </div>

            <div class="card-body profile-info-list">
                <p><?= nl2br(htmlspecialchars($trainer['bio'] ?? 'No bio provided yet.')) ?></p>
                <p><strong>Specializations:</strong> <?= htmlspecialchars($trainer['specializations'] ?? 'Not defined') ?></p>
                <p><strong>Certifications:</strong> <?= htmlspecialchars($trainer['certifications'] ?? 'Not defined') ?></p>
            </div>
        </div>

        <div class="card profile-card">
            <div class="card-head">
                <h2>Classes</h2>
                <span class="badge badge-muted">Weekly schedule</span>
            </div>
            <div class="card-body">
                <?php output_schedule_table($schedules); ?>
            </div>
        </div>
    </section>

    <?php
}