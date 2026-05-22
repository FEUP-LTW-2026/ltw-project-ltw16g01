<?php
session_start();

require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../db/connection.php';
require_once '../db/trainers.php';
require_once '../template/pt.php';
require_once '../template/trainers.php';

$pdo = getDatabaseConnection();
$trainers = getAllPersonalTrainers($pdo);

output_header('pt.css');
?>

<main class="main">
    <?php output_pt_hero(); ?>

    <section class="pt-section" id="trainers">
        <div class="container">
            <header class="section-title">
                <h2>Meet Our Personal Trainers</h2>
                <p>
                    Find the right trainer for your goals, whether you want to build strength,
                    improve mobility, lose weight or train with more confidence.
                </p>
            </header>

            <?php output_trainer_list($trainers); ?>
        </div>
    </section>

    <?php output_pt_benefits(); ?>
    <?php output_pt_cta(); ?>
</main>

<?php output_footer('../pages/'); ?>