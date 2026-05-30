<?php
/**
 * classes.php – Template functions for displaying class schedules.
 * Renders the list of class cards (member view) and the admin class form.
 */

/**
 * Outputs the whole list of class schedules inside a grid.
 */
function output_class_schedule_list($schedules) {
    if (empty($schedules)) {
        echo '<div class="card"><div class="card-body center-text"><p>No classes found matching your filters.</p></div></div>';
        return;
    }

    echo '<div class="class-grid">';
    foreach ($schedules as $schedule) {
        output_class_schedule_card($schedule);
    }
    echo '</div>';
}

/**
 * Outputs a single class card.
 * Includes class info, rating, capacity badge, enrolment button (for members), details link, and admin actions.
 */
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
    $ratingData = getClassScheduleRating($GLOBALS['pdo'], $scheduleId);
    $avgRating = $ratingData['rating'];
    $reviewCount = $ratingData['count'];

    if ($freeSpots <= 0) {
        $fillClass = 'danger';
    } elseif ($freeSpots <= ceil($capacity * 0.2)) {
        $fillClass = 'warning';
    } else {
        $fillClass = 'success';
    }

    $allowedAreas = ['member', 'trainer', 'admin'];
    $activeRole = $_SESSION['active_role'] ?? '';
    $showDetailsLink = in_array($activeRole, $allowedAreas, true);
    ?>
    <article class="class-card" data-schedule-id="<?= $scheduleId ?>">
        <div class="class-head">
            <div class="class-time-big"><?= $startTime ?></div>
            <div class="class-ratings">
                <?php if ($reviewCount > 0): ?>
                    <div class="stars">
                        <?php
                        $fullStars = floor($avgRating);
                        $halfStar = ($avgRating - $fullStars) >= 0.5;
                        for ($i = 1; $i <= 5; $i++) {
                            if ($i <= $fullStars) {
                                echo '★';
                            } elseif ($halfStar && $i == $fullStars + 1) {
                                echo '½';
                            } else {
                                echo '☆';
                            }
                        }
                        ?>
                    </div>
                <?php else: ?>
                    <span class="badge badge-muted">No reviews yet</span>
                <?php endif; ?>
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
                <?php
                // Get the next upcoming session (real or virtual)
                $sessions = getUpcomingSessionsBySchedule($GLOBALS['pdo'], $scheduleId, 1);
                if (!empty($sessions)):
                    $nextSession = $sessions[0];
                    $nextDate = $nextSession['session_date']; // Y-m-d
                    $dateObj = new DateTime($nextDate);
                    $formattedDate = $dateObj->format('l, F j, Y \a\t g:i A');
                ?>
                    <li>📅 <strong>Next:</strong> <?= htmlspecialchars($formattedDate) ?></li>
                <?php else: ?>
                    <li>📅 <?= htmlspecialchars($day) ?> at <?= $startTime ?></li>
                <?php endif; ?>
            </ul>
        </div>
        <div class="class-foot">
            <div class="cap-wrap">
                <span class="badge badge-<?= htmlspecialchars($fillClass) ?>">
                    <?= $freeSpots ?> spots left
                </span>
            </div>

            <!-- Enrolment buttons for members only -->
            <?php if (isset($_SESSION['active_role']) && $_SESSION['active_role'] === 'member'): ?>
                <?php
                $sessions = getUpcomingSessionsBySchedule($GLOBALS['pdo'], $scheduleId, 1);
                if (!empty($sessions)):
                    $nextSession = $sessions[0];
                    $sessionEnrolled = (int)($nextSession['enrolled_count'] ?? 0);
                    $available = $capacity - $sessionEnrolled;
                    $isEnrolled = isMemberEnrolled($GLOBALS['pdo'], $_SESSION['user_id'], $nextSession['id']);
                    if ($isEnrolled):
                ?>
                        <button class="enrolled-btn" 
                                data-schedule-id="<?= $scheduleId ?>" 
                                data-session-id="<?= $nextSession['id'] ?>"
                                data-action="cancel">
                            ✅ Enrolled
                        </button>
                    <?php elseif ($available > 0): ?>
                        <button class="enroll-btn" 
                                data-schedule-id="<?= $scheduleId ?>" 
                                data-action="enroll">
                            Enroll
                        </button>
                    <?php else: ?>
                        <button class="disabled-btn secondary-button btn-sm" disabled>Full</button>
                    <?php endif; ?>
                <?php else: ?>
                    <button class="disabled-btn secondary-button btn-sm" disabled>No sessions</button>
                <?php endif; ?>
            <?php endif; ?>

            <!-- Details link -->
            <?php if ($showDetailsLink): ?>
                <a href="/<?= htmlspecialchars($activeRole) ?>/class_details.php?id=<?= $scheduleId ?>" 
                   class="secondary-button btn-sm">Details</a>
            <?php endif; ?>

            <!-- Admin actions -->
            <?php if (isset($_SESSION['active_role']) && $_SESSION['active_role'] === 'admin'): ?>
                <div class="admin-actions">
                    <a href="edit_class.php?id=<?= $scheduleId ?>" class="secondary-button btn-sm">Edit</a>
                    <a href="delete_class.php?id=<?= $scheduleId ?>" class="delete-btn btn-sm delete-class-link">Delete</a>
                </div>
            <?php endif; ?>
        </div>
    </article>
    <?php
}

/**
 * Outputs the form for creating or editing a class schedule (admin area).
 */
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
            <input type="hidden" name="csrf" value="<?= htmlspecialchars($_SESSION['csrf']) ?>">
            
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
                            <option value="<?= htmlspecialchars($day) ?>" <?= $dayOfWeek === $day ? 'selected' : '' ?>>
                                <?= htmlspecialchars($day) ?>
                            </option>
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
                <button type="submit" class="main-button">
                    <?= $isEdit ? 'Update Class' : 'Create Class' ?>
                </button>

                <a href="classes.php" class="secondary-button">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    <?php
}