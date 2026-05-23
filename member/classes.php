<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/classes.php';
require_once '../template/classes.php';

requireAuth('member');

$pdo = getDatabaseConnection();
$GLOBALS['pdo'] = $pdo;

$types = $pdo->query("SELECT name, label FROM class_types WHERE active = 1 ORDER BY label")->fetchAll();
$trainers = $pdo->query("SELECT id, name FROM users WHERE role = 'trainer' AND active = 1 ORDER BY name")->fetchAll();
$times = $pdo->query("
    SELECT DISTINCT start_time 
    FROM class_schedules 
    ORDER BY start_time
")->fetchAll(PDO::FETCH_COLUMN);

$schedules = getAllClassSchedules($pdo, []);

output_header('classes.css');
?>

<main class="main">
    <div class="container">
        <header class="page-header">
            <div class="page-header-row" style="display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h1>Classes</h1>
                    <p>Browse our schedule and enroll in upcoming sessions</p>
                </div>
            </div>
        </header>

        <section>
            <form id="filter-form" class="filter-bar" method="get" action="classes.php">
                <div class="filter-group">
                    <label class="form-label" for="f-type">Class type</label>
                    <select id="f-type" name="type" class="form-select">
                        <option value="">All types</option>
                        <?php foreach ($types as $t): ?>
                            <option value="<?= htmlspecialchars($t['name']) ?>"><?= htmlspecialchars($t['label']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="form-label" for="f-trainer">Trainer</label>
                    <select id="f-trainer" name="trainer" class="form-select">
                        <option value="">All trainers</option>
                        <?php foreach ($trainers as $tr): ?>
                            <option value="<?= $tr['id'] ?>"><?= htmlspecialchars($tr['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="form-label" for="f-day">Day</label>
                    <select id="f-day" name="day" class="form-select">
                        <option value="">Any Day</option>
                        <option value="monday">Monday</option>
                        <option value="tuesday">Tuesday</option>
                        <option value="wednesday">Wednesday</option>
                        <option value="thursday">Thursday</option>
                        <option value="friday">Friday</option>
                        <option value="saturday">Saturday</option>
                        <option value="sunday">Sunday</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="form-label" for="f-time">Time</label>
                    <select id="f-time" name="time" class="form-select">
                        <option value="">Any Time</option>
                        <?php foreach ($times as $time): ?>
                            <option value="<?= htmlspecialchars($time) ?>">
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

<?php output_footer('../member/'); ?>