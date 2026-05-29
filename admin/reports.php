<?php
// File: admin/reports.php
// Description: Displays analytics dashboard with KPIs, charts, and tables for gym management.

// Load core dependencies
require_once '../includes/session.php';
require_once '../includes/header.php';
require_once '../includes/footer.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/reports.php';
require_once '../template/reports.php';

// Restrict access to admin users only
requireAuth('admin');

// Get database connection
$pdo = getDatabaseConnection();

// Fetch all report data using functions from db/reports.php
$kpis = getKpis($pdo);                               // Key performance indicators
$enrollmentEvolution = getEnrollmentEvolution($pdo); // Enrollment trend over time
$topClasses = getTopClasses($pdo);                   // Most popular classes
$planDistribution = getMembershipPlanDistribution($pdo); // Member plan breakdown
$occupancyByDay = getOccupancyByDay($pdo);           // Average occupancy per weekday
$criticalClasses = getCriticalClasses($pdo);         // Classes near full capacity
$lowEquipment = getLowAvailabilityEquipment($pdo);   // Equipment with low availability

// Output page header with reports-specific CSS
output_header('reports.css');
?>

<main class="main">
    <div class="container">
        <!-- Page header with title and export buttons -->
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

        <!-- KPI cards (active members, trainers, classes, etc.) -->
        <?php renderKpiCards($kpis); ?>

        <!-- Two-column layout for charts and tables -->
        <div class="reports-grid">
            <div class="reports-col">
                <!-- Enrollment evolution bar chart -->
                <?php renderBarChart($enrollmentEvolution, 'month', 'total', 'Enrollment Evolution (Last 6 Months)', ''); ?>

                <!-- Top 5 classes table -->
                <?php renderSimpleTable($topClasses, ['class_name', 'enrollments'], 'Top 5 Most Popular Classes'); ?>

                <!-- Critical capacity classes table -->
                <?php renderSimpleTable($criticalClasses, ['class_name', 'day_of_week', 'start_time', 'free_spots'], 'Classes with Critical Capacity (<20% free)'); ?>
            </div>

            <div class="reports-col">
                <!-- Membership plan distribution bar chart -->
                <?php renderBarChart($planDistribution, 'membership_plan', 'percentage', 'Membership Plan Distribution', '%'); ?>

                <!-- Occupancy by day bar chart -->
                <?php renderBarChart($occupancyByDay, 'day_of_week', 'avg_occupancy', 'Average Occupancy by Day of Week', '%'); ?>

                <!-- Low availability equipment table -->
                <?php renderSimpleTable($lowEquipment, ['name', 'available', 'quantity', 'percentage'], 'Low Availability Equipment (<30%)'); ?>
            </div>
        </div>
    </div>
</main>

<?php output_footer('../admin/'); ?>