<?php
require_once '../includes/session.php';

require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/trainers.php';
require_once '../template/trainers.php';

requireAuth('admin');

$pdo = getDatabaseConnection();

$specializations = getDistinctSpecializations($pdo);

$filters = [
    'specialization' => $_GET['specialization'] ?? '',
    'name' => $_GET['name'] ?? '',
    'is_pt' => $_GET['is_pt'] ?? ''
];

$selectedSpec = $filters['specialization'];
$selectedName = $filters['name'];
$selectedIsPt = $filters['is_pt'];

$trainers = getAllTrainers($pdo, $filters);

output_header('admin-trainers.css');
?>

<link rel="stylesheet" href="../css/pages/form.css">

<main class="main">
    <div class="container">
        <header class="page-header">
            <div class="page-header-row page-header-actions">
                <div>
                    <h1>Manage Trainers</h1>
                    <p>View, filter, add, edit or remove trainers.</p>
                </div>

                <a href="create_trainer.php" class="main-button">+ Add Trainer</a>
            </div>
        </header>

        <form id="filter-form" method="get" class="filter-bar trainers-filter">
            <div class="filter-group">
                <label class="form-label" for="f-name">Name</label>
                <input 
                    type="text" 
                    id="f-name"
                    name="name" 
                    class="form-control" 
                    value="<?= htmlspecialchars($selectedName) ?>"
                    placeholder="Search trainer name..."
                >
            </div>

            <div class="filter-group">
                <label class="form-label" for="f-specialization">Specialization</label>
                <select id="f-specialization" name="specialization" class="form-select">
                    <option value="">All specializations</option>

                    <?php foreach ($specializations as $spec): ?>
                        <option 
                            value="<?= htmlspecialchars($spec) ?>" 
                            <?= $selectedSpec === $spec ? 'selected' : '' ?>
                        >
                            <?= htmlspecialchars($spec) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="filter-group">
                <label class="form-label" for="f-is-pt">Personal Trainer</label>
                <select id="f-is-pt" name="is_pt" class="form-select">
                    <option value="" <?= $selectedIsPt === '' ? 'selected' : '' ?>>All</option>
                    <option value="1" <?= $selectedIsPt === '1' ? 'selected' : '' ?>>PT only</option>
                    <option value="0" <?= $selectedIsPt === '0' ? 'selected' : '' ?>>Not PT</option>
                </select>
            </div>
        </form>

        <div class="results-bar">
            <p class="results-count">
                Showing <strong id="trainer-count"><?= count($trainers) ?></strong> trainers
            </p>
        </div>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Username</th>
                        <th>Email</th>
                        <th>Specializations</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>

                <tbody id="trainer-table-body">
                    <?php output_trainers_admin_table($trainers); ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="../js/trainers-admin.js"></script>

<?php output_footer('../admin/'); ?>