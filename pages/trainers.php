<?php
session_start();

require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../db/connection.php';
require_once '../db/trainers.php';
require_once '../template/trainers.php';

$pdo = getDatabaseConnection();
$specializations = getDistinctSpecializations($pdo);
$selectedSpec = $_GET['specialization'] ?? '';
$selectedName = $_GET['name'] ?? '';

$filters = ['specialization' => $selectedSpec, 'name' => $selectedName];
$trainers = getAllTrainers($pdo, $filters);

output_header('trainers.css');
?>

<main class="main">
    <div class="container">
        <header class="page-header">
            <nav>
                <ol class="breadcrumb">
                    <li><a href="../index.php">Home</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><span>Trainers</span></li>
                </ol>
            </nav>

            <div class="page-header-row">
                <h1>Our Trainers</h1>
                <p>Meet our certified coaches and book a personal session</p>
            </div>
        </header>

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
                    <label class="form-label" for="f-name">Trainer name</label>
                    <input type="text" id="f-name" name="name" class="form-select" placeholder="e.g. Ana" value="<?= htmlspecialchars($filters['name']) ?>">
                </div>

                <div class="filter-group filter-group-end">
                    <button type="submit" class="main-button full-width">Filter</button>
                </div>
            </form>
        </section>

        <div class="results-bar">
            <p class="results-count" id="results-count">Showing <strong id="trainer-count"><?= count($trainers) ?></strong> trainers</p>
        </div>

        <div id="trainer-list-container">
            <?php output_trainer_list($trainers); ?>
        </div>
    </div>
</main>

<script src="../js/trainers.js"></script>

<?php output_footer('../'); ?>