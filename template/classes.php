<?php

function output_class_card($class) { ?>
    <article class="class-card">
        <div class="class-head">
            <span class="class-time-big"><?= $class['start_time'] ?></span>
            <span class="badge badge-info"><?= $class['type'] ?></span>
        </div>

        <div class="class-body">
            <div>
                <h2 class="class-name"><?= $class['name'] ?></h2>
                <p><?= $class['description'] ?></p>
            </div>

            <ul class="class-meta">
                <li>📅 <?= $class['day_of_week'] ?></li>
                <li>⏱ <?= $class['duration_min'] ?> min</li>
                <li>📍 <?= $class['location'] ?></li>
                <li>👤 <?= $class['trainer_name'] ?></li>
            </ul>
        </div>

        <div class="class-foot">
            <div class="cap-wrap">
                <span>Capacity: <?= $class['capacity'] ?></span>
            </div>

            <a href="register.php" class="main-button btn-sm">Enroll</a>
        </div>
    </article>
<?php }


function output_class_list($classes) { ?>
    <section>
        <div class="class-grid">
            <?php foreach ($classes as $class) {
                output_class_card($class);
            } ?>
        </div>
    </section>
<?php }