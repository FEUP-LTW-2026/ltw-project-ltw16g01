<?php
session_start();

require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/trainers.php';
require_once '../template/trainers.php';

requireAuth('admin');

$pdo = getDatabaseConnection();
$specializations = getDistinctSpecializations($pdo);
$selectedSpec = $_GET['specialization'] ?? '';
$selectedName = $_GET['name'] ?? '';

$filters = ['specialization' => $selectedSpec, 'name' => $selectedName];
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

        <form method="get" class="filter-bar trainers-filter">
            <div class="filter-group">
                <label class="form-label">Name</label>
                <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($filters['name']) ?>">
            </div>

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

            <div class="filter-group filter-group-end">
                <button type="submit" class="main-button full-width">Filter</button>
            </div>
        </form>

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

                <tbody>
                    <?php output_trainers_admin_table($trainers); ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<script src="../js/trainers-admin.js"></script>

<?php output_footer('../admin/'); ?>