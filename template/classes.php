<?php
// template/classes.php

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

function output_class_schedule_card($schedule) {
    $scheduleId = $schedule['schedule_id'];
    $className = htmlspecialchars($schedule['class_name']);
    $trainerName = htmlspecialchars($schedule['trainer_name'] ?? 'TBD');
    $day = $schedule['day_of_week'];
    $startTime = $schedule['start_time'];
    $location = htmlspecialchars($schedule['location']);
    $duration = $schedule['duration_min'];
    $capacity = $schedule['capacity'];
    $enrolled = $schedule['enrolled_count'] ?? 0;
    $freeSpots = $capacity - $enrolled;
    $fillPercent = ($enrolled / $capacity) * 100;
    
    // Determinar classe da barra de capacidade
    if ($freeSpots <= 0) $fillClass = 'danger';
    elseif ($freeSpots <= ceil($capacity * 0.2)) $fillClass = 'warning';
    else $fillClass = 'success';
    
    // Tipo de aula (com ícone se quiser)
    $typeLabel = htmlspecialchars($schedule['class_type_label'] ?? ucfirst($schedule['class_type_name']));
    ?>
    <div class="class-card" data-schedule-id="<?= $scheduleId ?>">
        <div class="class-head">
            <div class="class-time-big"><?= $startTime ?></div>
            <div class="class-ratings">
                <div class="stars">★★★★☆</div>
                <span class="rating-text">(4.2)</span>
            </div>
        </div>
        <div class="class-body">
            <h3 class="class-name"><?= $className ?></h3>
            <p><?= htmlspecialchars(substr($schedule['class_description'] ?? '', 0, 100)) ?>…</p>
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
            <?php
            if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
                echo '<div class="admin-actions" style="display: flex; gap: 8px;">';
                echo '<a href="edit_class.php?id=' . $schedule['schedule_id'] . '" class="secondary-button btn-sm">Edit</a>';
                echo '<a href="delete_class.php?id=' . $schedule['schedule_id'] . '" class="delete-btn btn-sm" onclick="return confirm(\'Delete this class schedule?\')">Delete</a>';
                echo '</div>';
            }
            ?>
        </div>
    </div>
    <?php
}