<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/footer.php';

require_once '../db/connection.php';
require_once '../db/trainers.php';        // funções para treinadores
require_once '../template/trainers.php';   // funções de output

$pdo = getDatabaseConnection();
$trainers = getAllTrainers($pdo);

output_header('trainers.css');  // CSS específico
?>

<main class="main">
    <div class="container">
        <header class="page-header">
            <nav>
                <ol class="breadcrumb">
                    <li><a href="index.php">Home</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><span>Trainers</span></li>
                </ol>
            </nav>
            <div class="page-header-row">
                <h1>Our Trainers</h1>
                <p>Meet our certified coaches and book a personal session</p>
            </div>
        </header>

        <!-- Filtros -->
        <section>
            <form class="filter-bar" method="get" action="trainers.php">
                <div class="filter-group">
                    <label class="form-label" for="f-specialization">Specialization</label>
                    <select id="f-specialization" name="specialization" class="form-select">
                        <option value="">All specializations</option>
                        <option value="Yoga">Yoga</option>
                        <option value="Pilates">Pilates</option>
                        <option value="HIIT">HIIT</option>
                        <option value="Strength">Strength</option>
                        <option value="Cycling">Cycling</option>
                        <option value="Functional">Functional</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="form-label" for="f-name">Trainer name</label>
                    <input type="text" id="f-name" name="name" class="form-select" placeholder="e.g. Ana">
                </div>

                <div class="filter-group filter-group-end">
                    <button id="button" type="submit" class="main-button full-width">Filter</button>
                </div>
            </form>
        </section>

        <!-- Barra de resultados -->
        <div class="results-bar">
            <p class="results-count">
                Showing <strong><?= count($trainers) ?> trainers</strong>
            </p>
        </div>

        <!-- Lista de treinadores (usando função do template) -->
        <?php output_trainer_list($trainers); ?>

    </div>
</main>

<?php output_footer(); ?>