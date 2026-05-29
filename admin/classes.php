<?php
// File: admin/classes.php
// Description: Controller for listing, filtering, and managing class schedules.

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../db/connection.php';
require_once '../db/classes.php';
require_once '../template/classes.php';

// Restrict access to admin users only
requireAuth('admin');

// Get database connection
$pdo = getDatabaseConnection();

// Fetch all required data using separate query functions 
$types = getClassTypes($pdo);      // Class type filter options
$trainers = getTrainers($pdo);     // Trainer filter options
$times = getDistinctTimes($pdo);   // Time filter options (unique start times)

// Read and sanitize filter values from URL query string
$filters = [
    'type'    => $_GET['type'] ?? '',
    'trainer' => $_GET['trainer'] ?? '',
    'day'     => $_GET['day'] ?? '',
    'time'    => $_GET['time'] ?? ''
];

// Get filtered class schedules using the existing function from db/classes.php
$schedules = getAllClassSchedules($pdo, $filters);

// Output the page header
output_header('classes.css');
?>

<main class="main">
    <div class="container">
        <!-- Page header with title and "New Class" button -->
        <header class="page-header">
            <div class="page-header-row page-header-actions">
                <div>
                    <h1>Manage Classes</h1>
                    <p>View, filter, add, edit or remove classes.</p>
                </div>

                <a href="create_class.php" class="main-button">+ New Class</a>
            </div>
        </header>

        <!-- Filter form – uses GET to preserve filters in URL -->
        <section>
            <form id="filter-form" class="filter-bar" method="get" action="classes.php">
                <!-- Class type dropdown -->
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

                <!-- Trainer dropdown -->
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

                <!-- Day of week dropdown -->
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

                <!-- Time dropdown (distinct start times) -->
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

        <!-- Results count bar (updated by JavaScript when filters change) -->
        <div class="results-bar">
            <p class="results-count" id="results-count">
                Showing <strong id="class-count"><?= count($schedules) ?></strong> classes
            </p>
        </div>

        <!-- Container for the dynamic class list (reloaded via AJAX in classes.js) -->
        <div id="class-list-container">
            <?php output_class_schedule_list($schedules); ?>
        </div>
    </div>
</main>

<!-- External JavaScript for filter interactions and AJAX loading -->
<script src="../js/classes.js"></script>

<?php output_footer('../admin/'); ?>