<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../db/connection.php';
require_once '../db/classes.php';
require_once '../template/classes.php';

$pdo = getDatabaseConnection();

// Carregamento inicial (sem filtros)
$classes = getAllClasses($pdo, []);

output_header('classes.css');
?>

<main class="main">
    <div class="container">
        <header class="page-header">
            <nav>
                <ol class="breadcrumb">
                    <li><a href="index.php">Home</a></li>
                    <li><span class="breadcrumb-sep">›</span></li>
                    <li><span>Classes</span></li>
                </ol>
            </nav>
            <div class="page-header-row">
                <h1>Classes</h1>
                <p>Browse our schedule and enroll in upcoming sessions</p>
            </div>
        </header>

        <!-- Formulário de filtros com AJAX -->
        <section>
            <form id="filter-form" class="filter-bar" method="get" action="classes.php">
                <div class="filter-group">
                    <label class="form-label" for="f-type">Class type</label>
                    <select id="f-type" name="type" class="form-select">
                        <option value="">All types</option>
                        <option value="yoga">Yoga</option>
                        <option value="pilates">Pilates</option>
                        <option value="hiit">HIIT</option>
                        <option value="cycling">Cycling</option>
                        <option value="strength">Strength</option>
                        <option value="crossfit">CrossFit</option>
                        <option value="boxing">Boxing</option>
                        <option value="senior-fitness">Senior Fitness</option>
                    </select>
                </div>

                <div class="filter-group">
                    <label class="form-label" for="f-trainer">Trainer</label>
                    <select id="f-trainer" name="trainer" class="form-select">
                        <option value="">All trainers</option>
                        <?php
                        $trainers = $pdo->query("SELECT id, name FROM users WHERE role = 'trainer' AND active = 1 ORDER BY name")->fetchAll();
                        foreach ($trainers as $trainer): ?>
                            <option value="<?= $trainer['id'] ?>"><?= htmlspecialchars($trainer['name']) ?></option>
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
                    </select>
                </div>

                <div class="filter-group filter-group-end">
                    <button type="submit" class="main-button full-width">Filter</button>
                </div>
            </form>
        </section>

        <!-- Barra de resultados (contador dinâmico) -->
        <div class="results-bar">
            <p class="results-count" id="results-count">
                Showing <strong id="class-count"><?= count($classes) ?></strong> classes
            </p>
        </div>

        <!-- Container onde a lista será actualizada via AJAX -->
        <div id="class-list-container">
            <?php output_class_list($classes); ?>
        </div>
    </div>
</main>

<?php output_footer(); ?>

<script>
    document.getElementById('filter-form').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);
        const params = new URLSearchParams(formData);

        fetch('../ajax/ajax_get_classes.php?' + params.toString())
            .then(response => response.text())
            .then(html => {
                document.getElementById('class-list-container').innerHTML = html;
                // Actualizar contador
                const cardCount = document.querySelectorAll('#class-list-container .class-card').length;
                document.getElementById('class-count').innerText = cardCount;
            })
            .catch(err => console.error('AJAX error:', err));
    });
</script>