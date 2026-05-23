<?php
session_start();

require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../db/connection.php';
require_once '../db/classes.php';
require_once '../template/classes.php';

$pdo = getDatabaseConnection();

$types = $pdo
    ->query("SELECT name, label FROM class_types WHERE active = 1 ORDER BY label")
    ->fetchAll(PDO::FETCH_ASSOC);

$trainers = $pdo
    ->query("SELECT id, name FROM users WHERE role = 'trainer' AND active = 1 ORDER BY name")
    ->fetchAll(PDO::FETCH_ASSOC);

$times = $pdo
    ->query("SELECT DISTINCT start_time FROM class_schedules ORDER BY start_time")
    ->fetchAll(PDO::FETCH_COLUMN);

$filters = [
    'type' => $_GET['type'] ?? '',
    'trainer' => $_GET['trainer'] ?? '',
    'day' => $_GET['day'] ?? '',
    'time' => $_GET['time'] ?? ''
];

$schedules = getAllClassSchedules($pdo, $filters);

output_header('classes.css');
?>

<main class="main">
    <div class="container">
        <header class="page-header">
            <nav>
                <ol class="breadcrumb">
                    <li><a href="../index.php">Home</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><span>Classes</span></li>
                </ol>
            </nav>

            <div class="page-header-row">
                <h1>Classes</h1>
                <p>Browse our schedule and enroll in upcoming sessions</p>
            </div>
        </header>

        <section>
            <form id="filter-form" class="filter-bar" method="get" action="classes.php">
                <div class="filter-group">
                    <label class="form-label" for="f-type">Class type</label>
                    <select id="f-type" name="type" class="form-select">
                        <option value="">All types</option>

                        <?php foreach ($types as $t): ?>
                            <option 
                                value="<?= htmlspecialchars($t['name']) ?>" 
                                <?= $filters['type'] === $t['name'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($t['label']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="form-label" for="f-trainer">Trainer</label>
                    <select id="f-trainer" name="trainer" class="form-select">
                        <option value="">All trainers</option>

                        <?php foreach ($trainers as $tr): ?>
                            <option 
                                value="<?= (int)$tr['id'] ?>" 
                                <?= (string)$filters['trainer'] === (string)$tr['id'] ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars($tr['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="form-label" for="f-day">Day</label>
                    <select id="f-day" name="day" class="form-select">
                        <option value="">Any Day</option>

                        <?php foreach (['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'] as $day): ?>
                            <option 
                                value="<?= $day ?>" 
                                <?= $filters['day'] === $day ? 'selected' : '' ?>
                            >
                                <?= ucfirst($day) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="form-label" for="f-time">Time</label>
                    <select id="f-time" name="time" class="form-select">
                        <option value="">Any Time</option>

                        <?php foreach ($times as $time): ?>
                            <option 
                                value="<?= htmlspecialchars($time) ?>" 
                                <?= $filters['time'] === $time ? 'selected' : '' ?>
                            >
                                <?= htmlspecialchars(substr($time, 0, 5)) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </section>

        <div class="results-bar">
            <p class="results-count" id="results-count">
                Showing <strong id="class-count"><?= count($schedules) ?></strong> classes
            </p>
        </div>

        <div id="class-list-container">
            <?php output_class_schedule_list($schedules); ?>
        </div>
    </div>
</main>

<script src="../js/classes.js"></script>

<?php output_footer('../pages/'); ?>