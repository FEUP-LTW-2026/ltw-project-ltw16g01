<?php
/**
 * class-form-fields.php – Reusable form fields for creating/editing class schedules.
 * Renders a set of input fields (name, type, description, trainer, day, time, capacity, duration).
 * Used by admin class creation/editing pages.
 */
function classFormFields(array $trainers, array $days, array $types, array $cls = []): void {
    // Helper to escape HTML safely
    $h = fn($s) => htmlspecialchars($s ?? '', ENT_QUOTES);
    ?>
    <div class="form-row">
        <div class="form-group">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-input" value="<?= $h($cls['name'] ?? '') ?>" required>
        </div>
        <div class="form-group">
            <label class="form-label">Type</label>
            <select name="type" class="form-select" required>
                <?php foreach ($types as $t): ?>
                    <option value="<?= $t ?>" <?= ($cls['type'] ?? '') === $t ? 'selected' : '' ?>>
                        <?= ucfirst($t) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-input" rows="2"><?= $h($cls['description'] ?? '') ?></textarea>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label class="form-label">Trainer</label>
            <select name="trainer_id" class="form-select">
                <option value="">No trainer</option>
                <?php foreach ($trainers as $t): ?>
                    <option value="<?= $t['id'] ?>" <?= ($cls['trainer_id'] ?? '') == $t['id'] ? 'selected' : '' ?>>
                        <?= $h($t['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Location</label>
            <input type="text" name="location" class="form-input" value="<?= $h($cls['location'] ?? 'Studio A') ?>">
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label class="form-label">Day of Week</label>
            <select name="day_of_week" class="form-select" required>
                <?php foreach ($days as $d): ?>
                    <option value="<?= $d ?>" <?= ($cls['day_of_week'] ?? '') === $d ? 'selected' : '' ?>>
                        <?= $d ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label class="form-label">Start Time</label>
            <input type="time" name="start_time" class="form-input" value="<?= $h($cls['start_time'] ?? '09:00') ?>" required>
        </div>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label class="form-label">Capacity</label>
            <input type="number" name="capacity" class="form-input" value="<?= $cls['capacity'] ?? 20 ?>" min="1" max="200" required>
        </div>
        <div class="form-group">
            <label class="form-label">Duration (min)</label>
            <input type="number" name="duration_min" class="form-input" value="<?= $cls['duration_min'] ?? 60 ?>" min="15" max="300" required>
        </div>
    </div>
    <?php
}