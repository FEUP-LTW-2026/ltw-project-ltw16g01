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

            <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'member' && $_SESSION['is_admin'] === 0): ?>
                <?php
                $sessions = getUpcomingSessionsBySchedule($GLOBALS['pdo'], $schedule['schedule_id'], 1);
                if (!empty($sessions)):
                    $nextSession = $sessions[0];
                    $sessionEnrolled = $nextSession['enrolled_count'] ?? 0;
                    $available = $capacity - $sessionEnrolled;
                    $isEnrolled = isMemberEnrolled($GLOBALS['pdo'], $_SESSION['user_id'], $nextSession['id']);
                    if ($isEnrolled):
                ?>
                        <button class="enrolled-btn" data-schedule-id="<?= $schedule['schedule_id'] ?>" data-action="cancel">✅ Enrolled</button>
                <?php elseif ($available > 0): ?>
                        <button class="enroll-btn" data-schedule-id="<?= $schedule['schedule_id'] ?>" data-action="enroll">Enroll</button>
                <?php else: ?>
                        <button class="disabled-btn secondary-button btn-sm" disabled>Full</button>
                <?php endif; else: ?>
                    <button class="disabled-btn secondary-button btn-sm" disabled>No sessions</button>
            <?php endif; endif; ?>

            <?php if (isset($_SESSION['role'])): ?>
                <a href="/member/class_details.php?id=<?= $scheduleId ?>" class="secondary-button btn-sm">Details</a>
            <?php endif; ?>

            <?php if (isset($_SESSION['role']) && ($_SESSION['role'] === 'admin' || $_SESSION['is_admin'] === 1)): ?>
                <div class="admin-actions">
                    <a href="edit_class.php?id=<?= $scheduleId ?>" class="secondary-button btn-sm">Edit</a>
                    <a href="delete_class.php?id=<?= $scheduleId ?>" 
                       class="delete-btn btn-sm" 
                       onclick="return confirm('Are you sure you want to delete this class schedule? This action cannot be undone.');">
                        Delete
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </article>

    <script>
        document.addEventListener('click', function(e) {
        const btn = e.target.closest('.enroll-btn, .enrolled-btn');
        if (!btn) return;
        e.preventDefault();

        if (btn.disabled) return;

        const scheduleId = btn.dataset.scheduleId;
        const action = btn.dataset.action; // 'enroll' ou 'cancel'
        if (!scheduleId || !action) return;

        btn.disabled = true;
        const originalText = btn.textContent;
        btn.textContent = action === 'enroll' ? 'Processing...' : 'Canceling...';

        fetch('../ajax/enroll.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: action, schedule_id: scheduleId })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                if (action === 'enroll') {
                    // Mudar para botão de cancelar
                    btn.textContent = '✅ Enrolled';
                    btn.classList.remove('enroll-btn', 'secondary-button');
                    btn.classList.add('enrolled-btn');
                    btn.dataset.action = 'cancel';
                } else {
                    // Mudar para botão de inscrever
                    btn.textContent = 'Enroll';
                    btn.classList.remove('enrolled-btn');
                    btn.classList.add('enroll-btn', 'secondary-button');
                    btn.dataset.action = 'enroll';
                }
                showToast(data.message, 'success');
            } else {
                showToast(data.message, 'error');
                btn.textContent = originalText;
            }
            btn.disabled = false;
        })
        .catch(err => {
            console.error('Error:', err);
            showToast('Network error. Please try again.', 'error');
            btn.textContent = originalText;
            btn.disabled = false;
        });
    });

    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.textContent = message;
        toast.style.position = 'fixed';
        toast.style.bottom = '20px';
        toast.style.right = '20px';
        toast.style.backgroundColor = type === 'success' ? '#2ed573' : '#ff4757';
        toast.style.color = '#fff';
        toast.style.padding = '12px 20px';
        toast.style.borderRadius = '8px';
        toast.style.zIndex = '9999';
        toast.style.fontWeight = '500';
        toast.style.boxShadow = '0 2px 10px rgba(0,0,0,0.2)';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
    </script>

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