<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/footer.php';

require_once '../db/connection.php';
require_once '../db/classes.php';

require_once '../template/classes.php';

$pdo = getDatabaseConnection();
$classes = getAllClasses($pdo);

output_header('classes.css');
?>

<main class="main">
    <div class="container">
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

        <section>
            <form class="filter-bar" method="get" action="classes.php">
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
                        <option value="1">Ana Silva</option>
                        <option value="2">Bruno Costa</option>
                        <option value="3">Carla Mendes</option>
                        <option value="4">Miguel Rocha</option>
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
                <div class="filter-group filter-group-end">
                    <button type="submit" class="main-button full-width">Filter</button>
                </div>
            </form>
        </section>

        <div class="results-bar">
            <p class="results-count">
                Showing <strong><?= count($classes) ?> classes</strong> available today
            </p>
        </div>

        <?php output_class_list($classes); ?>

    </div>
</main>
    </div>
</main>

<?php output_footer(); ?>
<?php output_footer(); ?>