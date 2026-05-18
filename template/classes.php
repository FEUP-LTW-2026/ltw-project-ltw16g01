<?php
// template/classes.php

function output_class_schedule_list($schedules) {
    if (empty($schedules)) {
        echo '<div class="card"><div class="card-body center-text"><p>No classes found matching your filters.</p></div></div>';
        return;
    }

    echo '<div class="class-grid">';
    foreach ($schedules as $schedule) output_class_schedule_card($schedule);
    echo '</div>';
}

function output_class_schedule_card($schedule) {
    $scheduleId = (int)$schedule['schedule_id'];
    $className = htmlspecialchars($schedule['class_name']);
    $trainerName = htmlspecialchars($schedule['trainer_name'] ?? 'TBD');
    $day = htmlspecialchars($schedule['day_of_week']);
    $startTime = htmlspecialchars($schedule['start_time']);
    $location = htmlspecialchars($schedule['location']);
    $duration = (int)$schedule['duration_min'];
    $capacity = (int)$schedule['capacity'];
    $enrolled = (int)($schedule['enrolled_count'] ?? 0);
    $freeSpots = max(0, $capacity - $enrolled);
    $typeLabel = htmlspecialchars($schedule['class_type_label'] ?? ucfirst($schedule['class_type_name']));
    $description = trim($schedule['class_description'] ?? '');
    $shortDescription = htmlspecialchars(substr($description, 0, 100)) . (strlen($description) > 100 ? '…' : '');

    if ($freeSpots <= 0) $fillClass = 'danger';
    elseif ($freeSpots <= ceil($capacity * 0.2)) $fillClass = 'warning';
    else $fillClass = 'success';
    ?>

    <article class="class-card" data-schedule-id="<?= $scheduleId ?>">
        <div class="class-head">
            <div class="class-time-big"><?= $startTime ?></div>
            <div class="class-ratings">
                <div class="stars">★★★★☆</div>
                <span class="rating-text">(4.2)</span>
            </div>
        </div>

        <div class="class-body">
            <h3 class="class-name"><?= $className ?></h3>
            <p><?= $shortDescription ?></p>

            <ul class="class-meta">
                <li>🧘 <?= $typeLabel ?></li>
                <li>👤 <?= $trainerName ?></li>
                <li>⏱️ <?= $duration ?> min</li>
                <li>📍 <?= $location ?></li>
                <li>📅 <?= $day ?> at <?= $startTime ?></li>
            </ul>
        </div>

        <div class="class-foot">
            <div class="cap-wrap">
                <span class="badge badge-<?= $fillClass ?>"><?= $freeSpots ?> spots left</span>
            </div>

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                <div class="admin-actions">
                    <a href="../admin/edit_class.php?id=<?= $scheduleId ?>" class="secondary-button btn-sm">Edit</a>
                    <a href="../admin/delete_class.php?id=<?= $scheduleId ?>" class="delete-btn btn-sm delete-class-link">Delete</a>
                </div>
            <?php endif; ?>
        </div>
    </article>

    <?php
}

function output_class_form($classTypes, $trainers, $days, $data = [], $errors = [], $success = '', $isEdit = false) {
    $className = htmlspecialchars($data['class_name'] ?? '');
    $classDescription = htmlspecialchars($data['class_description'] ?? '');
    $classTypeId = $data['class_type_id'] ?? '';
    $trainerId = $data['trainer_id'] ?? '';
    $dayOfWeek = $data['day_of_week'] ?? '';
    $startTime = htmlspecialchars($data['start_time'] ?? '');
    $location = htmlspecialchars($data['location'] ?? 'Studio A');
    $capacity = (int)($data['capacity'] ?? 20);
    $duration = (int)($data['duration_min'] ?? 60);
    ?>

    <div class="container form-container-large">
        <header class="page-header">
            <nav>
                <ol class="breadcrumb">
                    <li><a href="classes.php">Classes</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><span><?= $isEdit ? 'Edit Class' : 'New Class' ?></span></li>
                </ol>
            </nav>

            <div class="page-header-row">
                <h1><?= $isEdit ? 'Edit Class' : 'Create New Class' ?></h1>
                <p><?= $isEdit ? 'Update class details and schedule.' : 'Add a new class schedule to the system.' ?></p>
            </div>
        </header>

        <?php if ($errors): ?>
            <?php foreach ($errors as $e): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($e) ?></div>
            <?php endforeach; ?>
        <?php elseif ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <form method="post" class="auth-form card form-card">
            <div class="form-row form-spaced">
                <div class="form-group">
                    <label class="form-label">Class Name *</label>
                    <input type="text" name="class_name" class="form-control" required value="<?= $className ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Class Type *</label>
                    <select name="class_type_id" class="form-select" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($classTypes as $type): ?>
                            <option value="<?= (int)$type['id'] ?>" <?= (string)$classTypeId === (string)$type['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($type['label']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="form-group form-spaced">
                <label class="form-label">Description</label>
                <textarea name="class_description" class="form-control" rows="3"><?= $classDescription ?></textarea>
            </div>

            <div class="form-row form-spaced">
                <div class="form-group">
                    <label class="form-label">Day of Week *</label>
                    <select name="day_of_week" class="form-select" required>
                        <option value="">-- Select --</option>
                        <?php foreach ($days as $day): ?>
                            <option value="<?= $day ?>" <?= $dayOfWeek === $day ? 'selected' : '' ?>><?= $day ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Start Time *</label>
                    <input type="time" name="start_time" class="form-control" required value="<?= $startTime ?>">
                </div>
            </div>

            <div class="form-row form-spaced">
                <div class="form-group">
                    <label class="form-label">Trainer</label>
                    <select name="trainer_id" class="form-select">
                        <option value="">-- Not assigned --</option>
                        <?php foreach ($trainers as $trainer): ?>
                            <option value="<?= (int)$trainer['id'] ?>" <?= (string)$trainerId === (string)$trainer['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($trainer['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-control" value="<?= $location ?>">
                </div>
            </div>

            <div class="form-row form-spaced">
                <div class="form-group">
                    <label class="form-label">Capacity</label>
                    <input type="number" name="capacity" class="form-control" min="1" value="<?= $capacity ?>">
                </div>

                <div class="form-group">
                    <label class="form-label">Duration (minutes)</label>
                    <input type="number" name="duration_min" class="form-control" min="15" step="15" value="<?= $duration ?>">
                </div>
            </div>

            <div class="form-actions">
                <button type="submit" class="main-button"><?= $isEdit ? 'Update Class' : 'Create Class' ?></button>
                <a href="classes.php" class="secondary-button">Cancel</a>
            </div>
        </form>
    </div>

    <?php
}