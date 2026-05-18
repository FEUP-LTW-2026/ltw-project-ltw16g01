<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../db/connection.php';
require_once '../db/trainers.php';
require_once '../template/trainers.php';

requireAuth('trainer');

$pdo = getDatabaseConnection();
$GLOBALS['pdo'] = $pdo;

// Carregar treinadores iniciais (sem filtros)
$trainers = getAllTrainers($pdo, []);

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

        <!-- Formulário de filtros com AJAX -->
        <section>
            <form id="filter-form" class="filter-bar" method="get" action="trainers.php">
                <div class="filter-group">
                    <label class="form-label" for="f-specialization">Specialization</label>
                    <input type="text" id="f-specialization" name="specialization" class="form-select" 
                           placeholder="e.g. Yoga, HIIT, Strength" value="<?= htmlspecialchars($_GET['specialization'] ?? '') ?>">
                </div>

                <div class="filter-group">
                    <label class="form-label" for="f-name">Trainer name</label>
                    <input type="text" id="f-name" name="name" class="form-select" 
                           placeholder="e.g. Ana" value="<?= htmlspecialchars($_GET['name'] ?? '') ?>">
                </div>

                <div class="filter-group filter-group-end">
                    <button type="submit" class="main-button full-width">Filter</button>
                </div>
            </form>
        </section>

        <div class="results-bar">
            <p class="results-count" id="results-count">
                Showing <strong id="trainer-count"><?= count($trainers) ?></strong> trainers
            </p>
        </div>

        <div id="trainer-list-container">
            <?php output_trainer_list($trainers); ?>
        </div>
    </div>
</main>

<?php output_footer('../'); ?>

<script>
    document.getElementById('filter-form').addEventListener('submit', function(e) {
        e.preventDefault();
        const formData = new FormData(this);
        const params = new URLSearchParams(formData);
        fetch('../ajax/ajax_get_trainers.php?' + params.toString())
            .then(response => response.text())
            .then(html => {
                document.getElementById('trainer-list-container').innerHTML = html;
                const cardCount = document.querySelectorAll('#trainer-list-container .trainer-card').length;
                document.getElementById('trainer-count').innerText = cardCount;
            })
            .catch(err => console.error('AJAX error:', err));
    });
</script>