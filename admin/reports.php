<?php
session_start();
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/reports.php';
require_once '../template/reports.php';

requireAuth('admin');

$pdo = getDatabaseConnection();

// Obter todos os dados
$kpis = getKpis($pdo);
$enrollmentEvolution = getEnrollmentEvolution($pdo);
$topClasses = getTopClasses($pdo);
$planDistribution = getMembershipPlanDistribution($pdo);
$occupancyByDay = getOccupancyByDay($pdo);
$criticalClasses = getCriticalClasses($pdo);
$lowEquipment = getLowAvailabilityEquipment($pdo);

output_header('reports.css');
?>

<main class="main">
    <div class="container">
        <header class="page-header">
            <div class="page-header-row">
                <div>
                    <h1>Analytics & Reports</h1>
                    <p>Dashboard with key metrics and insights for gym management.</p>
                </div>
                <div class="export-buttons">
                    <a href="export_reports.php?format=csv" class="secondary-button btn-sm">📊 Export CSV</a>
                    <a href="export_reports.php?format=pdf" class="secondary-button btn-sm" target="_blank">🖨️ Print / PDF</a>
                </div>
            </div>
        </header>

        <?php renderKpiCards($kpis); ?>

        <div class="reports-grid">
            <div class="reports-col">
                <?php renderBarChart($enrollmentEvolution, 'month', 'total', 'Enrollment Evolution (Last 6 Months)', ''); ?>
                <?php renderSimpleTable($topClasses, ['class_name', 'enrollments'], 'Top 5 Most Popular Classes'); ?>
                <?php renderSimpleTable($criticalClasses, ['class_name', 'day_of_week', 'start_time', 'free_spots'], 'Classes with Critical Capacity (<20% free)'); ?>
            </div>
            <div class="reports-col">
                <?php renderBarChart($planDistribution, 'membership_plan', 'percentage', 'Membership Plan Distribution', '%'); ?>
                <?php renderBarChart($occupancyByDay, 'day_of_week', 'avg_occupancy', 'Average Occupancy by Day of Week', '%'); ?>
                <?php renderSimpleTable($lowEquipment, ['name', 'available', 'quantity', 'percentage'], 'Low Availability Equipment (<30%)'); ?>
            </div>
        </div>
    </div>
</main>

<?php output_footer('../admin/'); ?>