<?php
// File: admin/export_reports.php
// Description: Exports analytics reports in CSV or PDF format.

require_once '../includes/session.php';
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/reports.php';

requireAuth('admin');

// Clear any output buffers that might have been started
if (ob_get_level()) ob_end_clean();

$pdo = getDatabaseConnection();
$format = $_GET['format'] ?? 'csv';

// Fetch all required data
$kpis = getKpis($pdo);
$enrollmentEvolution = getEnrollmentEvolution($pdo);
$topClasses = getTopClasses($pdo);
$planDistribution = getMembershipPlanDistribution($pdo);
$occupancyByDay = getOccupancyByDay($pdo);
$criticalClasses = getCriticalClasses($pdo);
$lowEquipment = getLowAvailabilityEquipment($pdo);
$recentActivity = getRecentActivity($pdo);

if ($format === 'csv') {
    // CSV headers for download
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="fitpeak_reports_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');

    // Write UTF-8 BOM for Excel compatibility
    fwrite($output, "\xEF\xBB\xBF");
    
    // Parameters for fputcsv (compatible with PHP 8.2+)
    $sep = ',';
    $enclosure = '"';
    $escape = "\\";
    
    fputcsv($output, ['FitPeak Reports - ' . date('Y-m-d H:i:s')], $sep, $enclosure, $escape);
    fputcsv($output, [], $sep, $enclosure, $escape);
    
    // KPIs section
    fputcsv($output, ['=== KEY PERFORMANCE INDICATORS ==='], $sep, $enclosure, $escape);
    fputcsv($output, ['Metric', 'Value'], $sep, $enclosure, $escape);
    fputcsv($output, ['Active Members', $kpis['activeMembers']], $sep, $enclosure, $escape);
    fputcsv($output, ['Active Trainers', $kpis['activeTrainers']], $sep, $enclosure, $escape);
    fputcsv($output, ['Active Classes / Week', $kpis['activeClassesPerWeek']], $sep, $enclosure, $escape);
    fputcsv($output, ['Avg Occupancy (30d)', $kpis['avgOccupancy'] . '%'], $sep, $enclosure, $escape);
    fputcsv($output, ['Equipment Issues', $kpis['equipmentIssues']], $sep, $enclosure, $escape);
    fputcsv($output, [], $sep, $enclosure, $escape);
    
    // Enrollment evolution section
    fputcsv($output, ['=== ENROLLMENT EVOLUTION (Last 6 months) ==='], $sep, $enclosure, $escape);
    fputcsv($output, ['Month', 'Total Enrollments'], $sep, $enclosure, $escape);

    foreach ($enrollmentEvolution as $row) {
        fputcsv($output, [$row['month'], $row['total']], $sep, $enclosure, $escape);
    }

    fputcsv($output, [], $sep, $enclosure, $escape);
    
    // Top classes section
    fputcsv($output, ['=== TOP 5 CLASSES ==='], $sep, $enclosure, $escape);
    fputcsv($output, ['Class Name', 'Enrollments'], $sep, $enclosure, $escape);

    foreach ($topClasses as $row) {
        fputcsv($output, [$row['class_name'], $row['enrollments']], $sep, $enclosure, $escape);
    }

    fputcsv($output, [], $sep, $enclosure, $escape);
    
    // Membership plan distribution section
    fputcsv($output, ['=== MEMBERSHIP PLAN DISTRIBUTION ==='], $sep, $enclosure, $escape);
    fputcsv($output, ['Plan', 'Count', 'Percentage (%)'], $sep, $enclosure, $escape);

    foreach ($planDistribution as $row) {
        fputcsv($output, [$row['membership_plan'], $row['count'], $row['percentage']], $sep, $enclosure, $escape);
    }

    fputcsv($output, [], $sep, $enclosure, $escape);
    
    // Occupancy by day section
    fputcsv($output, ['=== AVERAGE OCCUPANCY BY DAY OF WEEK ==='], $sep, $enclosure, $escape);
    fputcsv($output, ['Day', 'Avg Occupancy (%)'], $sep, $enclosure, $escape);

    foreach ($occupancyByDay as $row) {
        fputcsv($output, [$row['day_of_week'], $row['avg_occupancy']], $sep, $enclosure, $escape);
    }

    fputcsv($output, [], $sep, $enclosure, $escape);
    
    // Critical capacity classes section
    fputcsv($output, ['=== CLASSES WITH CRITICAL CAPACITY (<20% free) ==='], $sep, $enclosure, $escape);
    fputcsv($output, ['Class', 'Day', 'Time', 'Capacity', 'Enrolled', 'Free Spots', 'Free %'], $sep, $enclosure, $escape);

    foreach ($criticalClasses as $row) {
        fputcsv($output, [
            $row['class_name'],
            $row['day_of_week'],
            $row['start_time'],
            $row['capacity'],
            $row['enrolled'],
            $row['free_spots'],
            $row['free_percent'] ?? '-'
        ], $sep, $enclosure, $escape);
    }

    fputcsv($output, [], $sep, $enclosure, $escape);
    
    // Low availability equipment section
    fputcsv($output, ['=== LOW AVAILABILITY EQUIPMENT (<30%) ==='], $sep, $enclosure, $escape);
    fputcsv($output, ['Equipment', 'Available', 'Quantity', 'Available %'], $sep, $enclosure, $escape);

    foreach ($lowEquipment as $row) {
        fputcsv($output, [
            $row['name'],
            $row['available'],
            $row['quantity'],
            $row['percentage'] . '%'
        ], $sep, $enclosure, $escape);
    }

    fputcsv($output, [], $sep, $enclosure, $escape);
    
    fclose($output);
    exit;
}

// PDF format: generate HTML with print CSS
if ($format === 'pdf') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>FitPeak Reports - <?= date('Y-m-d') ?></title>

        <link rel="stylesheet" href="../css/pages/export_reports.css">
    </head>
    <body>
        <h1>FitPeak Analytics Reports</h1>
        <p>Generated on <?= date('d/m/Y H:i:s') ?></p>
        
        <!-- KPIs grid -->
        <div class="kpi-grid">
            <div class="kpi-card">
                <div class="kpi-value"><?= $kpis['activeMembers'] ?></div>
                <div>Active Members</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-value"><?= $kpis['activeTrainers'] ?></div>
                <div>Trainers</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-value"><?= $kpis['activeClassesPerWeek'] ?></div>
                <div>Classes / Week</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-value"><?= $kpis['avgOccupancy'] ?>%</div>
                <div>Avg Occupancy (30d)</div>
            </div>

            <div class="kpi-card">
                <div class="kpi-value"><?= $kpis['equipmentIssues'] ?></div>
                <div>Equipment Issues</div>
            </div>
        </div>

        <div class="reports-grid">
            <div class="reports-col">
                <!-- Enrollment evolution table -->
                <h2>Enrollment Evolution (Last 6 Months)</h2>

                <table>
                    <thead>
                        <tr>
                            <th>Month</th>
                            <th>Total Enrollments</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($enrollmentEvolution as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['month']) ?></td>
                                <td><?= $row['total'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Top classes table -->
                <h2>Top 5 Most Popular Classes</h2>

                <table>
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Enrollments</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($topClasses as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['class_name']) ?></td>
                                <td><?= $row['enrollments'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <!-- Critical classes table -->
                <h2>Classes with Critical Capacity (&lt;20% free)</h2>

                <table>
                    <thead>
                        <tr>
                            <th>Class</th>
                            <th>Day</th>
                            <th>Time</th>
                            <th>Free Spots</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($criticalClasses as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['class_name']) ?></td>
                                <td><?= htmlspecialchars($row['day_of_week']) ?></td>
                                <td><?= htmlspecialchars($row['start_time']) ?></td>
                                <td><?= $row['free_spots'] ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="reports-col">
                <!-- Plan distribution bar chart -->
                <h2>Membership Plan Distribution</h2>

                <div class="bar-chart">
                    <?php foreach ($planDistribution as $row): ?>
                        <div class="bar-row">
                            <div class="bar-label"><?= htmlspecialchars($row['membership_plan']) ?></div>

                            <div class="bar-container">
                                <div class="bar-fill" style="width: <?= $row['percentage'] ?>%"></div>
                            </div>

                            <div><?= $row['percentage'] ?>%</div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Occupancy by day bar chart -->
                <h2>Avg Occupancy by Day of Week</h2>

                <div class="bar-chart">
                    <?php foreach ($occupancyByDay as $row): ?>
                        <div class="bar-row">
                            <div class="bar-label"><?= htmlspecialchars($row['day_of_week']) ?></div>

                            <div class="bar-container">
                                <div class="bar-fill" style="width: <?= $row['avg_occupancy'] ?>%"></div>
                            </div>

                            <div><?= $row['avg_occupancy'] ?>%</div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Low equipment table -->
                <h2>Low Availability Equipment (&lt;30%)</h2>

                <table>
                    <thead>
                        <tr>
                            <th>Equipment</th>
                            <th>Available</th>
                            <th>Total</th>
                            <th>Availability %</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($lowEquipment as $row): ?>
                            <tr>
                                <td><?= htmlspecialchars($row['name']) ?></td>
                                <td><?= $row['available'] ?></td>
                                <td><?= $row['quantity'] ?></td>
                                <td><?= $row['percentage'] ?>%</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <script>
            window.print();
        </script>
    </body>
    </html>
    <?php
    exit;
}

// Fallback: redirect to reports page
header('Location: reports.php');
exit;
?>