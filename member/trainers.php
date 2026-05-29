<?php
/**
 * trainers.php – Member page listing all trainers.
 * Includes filtering by specialization, name, and PT availability.
 */

require_once '../includes/session.php';

require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../db/connection.php';
require_once '../db/trainers.php';
require_once '../template/trainers.php';

$pdo = getDatabaseConnection();

// Read filter values from URL
$filters = [
    'specialization' => $_GET['specialization'] ?? '',
    'trainer_id' => $_GET['trainer_id'] ?? '',
    'is_pt' => $_GET['is_pt'] ?? '',
    'active_only' => true
];

$selectedSpec = $filters['specialization'];
$selectedTrainerId = $filters['trainer_id'];
$selectedIsPt = $filters['is_pt'];

// Fetch trainers based on filters
$trainers = getAllTrainers($pdo, $filters);
$specializations = getTrainerSpecializationOptions($pdo);
$trainerNames = getTrainerNameOptions($pdo);

output_header('trainers.css');
?>

<main class="main">
    <div class="container">
        <header class="page-header">
            <div class="page-header-row">
                <h1>Our Trainers</h1>
                <p>Meet our certified coaches and book a personal session</p>
            </div>
        </header>

        <!-- Filter form (submitted via AJAX) -->
        <section>
            <form id="filter-form" class="filter-bar" method="get" action="trainers.php">
                <div class="filter-group">
                    <label class="form-label" for="f-specialization">Specialization</label>
                    <select id="f-specialization" name="specialization" class="form-select">
                        <option value="">All specializations</option>
                        <?php foreach ($specializations as $spec): ?>
                            <option value="<?= htmlspecialchars($spec) ?>" <?= $selectedSpec === $spec ? 'selected' : '' ?>>
                                <?= htmlspecialchars($spec) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="form-label" for="f-trainer">Trainer name</label>
                    <select id="f-trainer" name="trainer_id" class="form-select">
                        <option value="">All trainers</option>
                        <?php foreach ($trainerNames as $trainer): ?>
                            <option value="<?= htmlspecialchars($trainer['id']) ?>"
                                <?= (string)$filters['trainer_id'] === (string)$trainer['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($trainer['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="form-label" for="f-is-pt">Personal Trainer</label>
                    <select id="f-is-pt" name="is_pt" class="form-select">
                        <option value="" <?= $filters['is_pt'] === '' ? 'selected' : '' ?>>All</option>
                        <option value="1" <?= $filters['is_pt'] === '1' ? 'selected' : '' ?>>PT only</option>
                        <option value="0" <?= $filters['is_pt'] === '0' ? 'selected' : '' ?>>Not PT</option>
                    </select>
                </div>
            </form>
        </section>

        <!-- Results count -->
        <div class="results-bar">
            <p class="results-count" id="results-count">
                Showing <strong id="trainer-count"><?= count($trainers) ?></strong> trainers
            </p>
        </div>

        <!-- Trainers list (updated via AJAX) -->
        <div id="trainer-list-container">
            <?php output_trainer_list($trainers); ?>
        </div>
    </div>
</main>

<script src="../js/trainers.js"></script>

<?php output_footer('../member/'); ?>