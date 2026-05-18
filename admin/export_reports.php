<?php
session_start();
require_once '../includes/auth.php';
require_once '../db/connection.php';
require_once '../db/reports.php';

requireAuth('admin');

// Limpar qualquer buffer de saída que possa ter sido iniciado
if (ob_get_level()) ob_end_clean();

$pdo = getDatabaseConnection();
$format = $_GET['format'] ?? 'csv';

// Recolher todos os dados
$kpis = getKpis($pdo);
$enrollmentEvolution = getEnrollmentEvolution($pdo);
$topClasses = getTopClasses($pdo);
$planDistribution = getMembershipPlanDistribution($pdo);
$occupancyByDay = getOccupancyByDay($pdo);
$criticalClasses = getCriticalClasses($pdo);
$lowEquipment = getLowAvailabilityEquipment($pdo);
$recentActivity = getRecentActivity($pdo);

if ($format === 'csv') {
    // Cabeçalhos para download CSV
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="fitpeak_reports_' . date('Y-m-d') . '.csv"');
    
    $output = fopen('php://output', 'w');
    // Escrever o BOM para UTF-8 (opcional, ajuda no Excel)
    fwrite($output, "\xEF\xBB\xBF");
    
    // Parâmetros para fputcsv compatíveis com PHP 8.2+ (separador, delimitador, escape)
    $sep = ',';
    $enclosure = '"';
    $escape = "\\";
    
    fputcsv($output, ['FitPeak Reports - ' . date('Y-m-d H:i:s')], $sep, $enclosure, $escape);
    fputcsv($output, [], $sep, $enclosure, $escape);
    
    // KPIs
    fputcsv($output, ['=== KEY PERFORMANCE INDICATORS ==='], $sep, $enclosure, $escape);
    fputcsv($output, ['Metric', 'Value'], $sep, $enclosure, $escape);
    fputcsv($output, ['Active Members', $kpis['activeMembers']], $sep, $enclosure, $escape);
    fputcsv($output, ['Active Trainers', $kpis['activeTrainers']], $sep, $enclosure, $escape);
    fputcsv($output, ['Active Classes / Week', $kpis['activeClassesPerWeek']], $sep, $enclosure, $escape);
    fputcsv($output, ['Avg Occupancy (30d)', $kpis['avgOccupancy'] . '%'], $sep, $enclosure, $escape);
    fputcsv($output, ['Equipment Issues', $kpis['equipmentIssues']], $sep, $enclosure, $escape);
    fputcsv($output, [], $sep, $enclosure, $escape);
    
    // Evolução de inscrições
    fputcsv($output, ['=== ENROLLMENT EVOLUTION (Last 6 months) ==='], $sep, $enclosure, $escape);
    fputcsv($output, ['Month', 'Total Enrollments'], $sep, $enclosure, $escape);
    foreach ($enrollmentEvolution as $row) {
        fputcsv($output, [$row['month'], $row['total']], $sep, $enclosure, $escape);
    }
    fputcsv($output, [], $sep, $enclosure, $escape);
    
    // Top aulas
    fputcsv($output, ['=== TOP 5 CLASSES ==='], $sep, $enclosure, $escape);
    fputcsv($output, ['Class Name', 'Enrollments'], $sep, $enclosure, $escape);
    foreach ($topClasses as $row) {
        fputcsv($output, [$row['class_name'], $row['enrollments']], $sep, $enclosure, $escape);
    }
    fputcsv($output, [], $sep, $enclosure, $escape);
    
    // Distribuição planos
    fputcsv($output, ['=== MEMBERSHIP PLAN DISTRIBUTION ==='], $sep, $enclosure, $escape);
    fputcsv($output, ['Plan', 'Count', 'Percentage (%)'], $sep, $enclosure, $escape);
    foreach ($planDistribution as $row) {
        fputcsv($output, [$row['membership_plan'], $row['count'], $row['percentage']], $sep, $enclosure, $escape);
    }
    fputcsv($output, [], $sep, $enclosure, $escape);
    
    // Ocupação por dia
    fputcsv($output, ['=== AVERAGE OCCUPANCY BY DAY OF WEEK ==='], $sep, $enclosure, $escape);
    fputcsv($output, ['Day', 'Avg Occupancy (%)'], $sep, $enclosure, $escape);
    foreach ($occupancyByDay as $row) {
        fputcsv($output, [$row['day_of_week'], $row['avg_occupancy']], $sep, $enclosure, $escape);
    }
    fputcsv($output, [], $sep, $enclosure, $escape);
    
    // Aulas com capacidade crítica
    fputcsv($output, ['=== CLASSES WITH CRITICAL CAPACITY (<20% free) ==='], $sep, $enclosure, $escape);
    fputcsv($output, ['Class', 'Day', 'Time', 'Capacity', 'Enrolled', 'Free Spots', 'Free %'], $sep, $enclosure, $escape);
    foreach ($criticalClasses as $row) {
        fputcsv($output, [
            $row['class_name'], $row['day_of_week'], $row['start_time'],
            $row['capacity'], $row['enrolled'], $row['free_spots'], $row['free_percent'] ?? '-'
        ], $sep, $enclosure, $escape);
    }
    fputcsv($output, [], $sep, $enclosure, $escape);
    
    // Equipamento baixa disponibilidade
    fputcsv($output, ['=== LOW AVAILABILITY EQUIPMENT (<30%) ==='], $sep, $enclosure, $escape);
    fputcsv($output, ['Equipment', 'Available', 'Quantity', 'Available %'], $sep, $enclosure, $escape);
    foreach ($lowEquipment as $row) {
        fputcsv($output, [$row['name'], $row['available'], $row['quantity'], $row['percentage'] . '%'], $sep, $enclosure, $escape);
    }
    fputcsv($output, [], $sep, $enclosure, $escape);
    
    fclose($output);
    exit;
}

// Formato PDF: gerar HTML com CSS de impressão
if ($format === 'pdf') {
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>FitPeak Reports - <?= date('Y-m-d') ?></title>
        <style>
            /* Estilos de impressão */
            body {
                font-family: 'Outfit', 'Segoe UI', Arial, sans-serif;
                margin: 2rem;
                color: #000;
                background: #fff;
            }
            h1, h2 {
                color: #e84118;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin-bottom: 1.5rem;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px 12px;
                text-align: left;
            }
            th {
                background: #f4f4f4;
            }
            .kpi-grid {
                display: flex;
                gap: 1rem;
                flex-wrap: wrap;
                margin-bottom: 2rem;
            }
            .kpi-card {
                border: 1px solid #ccc;
                padding: 1rem;
                border-radius: 8px;
                min-width: 150px;
                background: #fafafa;
            }
            .kpi-value {
                font-size: 2rem;
                font-weight: bold;
                color: #e84118;
            }
            .reports-grid {
                display: flex;
                flex-wrap: wrap;
                gap: 2rem;
            }
            .reports-col {
                flex: 1;
                min-width: 300px;
            }
            .bar-chart {
                margin-bottom: 1rem;
            }
            .bar-row {
                display: flex;
                align-items: center;
                gap: 0.5rem;
                margin-bottom: 0.5rem;
            }
            .bar-label {
                width: 100px;
            }
            .bar-container {
                flex: 1;
                height: 20px;
                background: #eee;
                border-radius: 4px;
                overflow: hidden;
            }
            .bar-fill {
                height: 100%;
                background: #e84118;
                width: 0%;
            }
            @media print {
                body { margin: 0; }
                .no-break { page-break-inside: avoid; }
                h2 { page-break-after: avoid; }
            }
        </style>
    </head>
    <body>
        <h1>FitPeak Analytics Reports</h1>
        <p>Generated on <?= date('d/m/Y H:i:s') ?></p>
        
        <!-- KPIs -->
        <div class="kpi-grid">
            <div class="kpi-card"><div class="kpi-value"><?= $kpis['activeMembers'] ?></div><div>Active Members</div></div>
            <div class="kpi-card"><div class="kpi-value"><?= $kpis['activeTrainers'] ?></div><div>Trainers</div></div>
            <div class="kpi-card"><div class="kpi-value"><?= $kpis['activeClassesPerWeek'] ?></div><div>Classes / Week</div></div>
            <div class="kpi-card"><div class="kpi-value"><?= $kpis['avgOccupancy'] ?>%</div><div>Avg Occupancy (30d)</div></div>
            <div class="kpi-card"><div class="kpi-value"><?= $kpis['equipmentIssues'] ?></div><div>Equipment Issues</div></div>
        </div>

        <div class="reports-grid">
            <div class="reports-col">
                <!-- Evolução inscrições -->
                <h2>Enrollment Evolution (Last 6 Months)</h2>
                <table>
                    <thead><tr><th>Month</th><th>Total Enrollments</th></tr></thead>
                    <tbody><?php foreach ($enrollmentEvolution as $row): ?>
                        <tr><td><?= htmlspecialchars($row['month']) ?></td><td><?= $row['total'] ?></td></tr>
                    <?php endforeach; ?></tbody>
                </table>

                <!-- Top Classes -->
                <h2>Top 5 Most Popular Classes</h2>
                <table>
                    <thead><tr><th>Class</th><th>Enrollments</th></tr></thead>
                    <tbody><?php foreach ($topClasses as $row): ?>
                        <tr><td><?= htmlspecialchars($row['class_name']) ?></td><td><?= $row['enrollments'] ?></td></tr>
                    <?php endforeach; ?></tbody>
                </table>

                <!-- Critical Classes -->
                <h2>Classes with Critical Capacity (<20% free)</h2>
                <table>
                    <thead><tr><th>Class</th><th>Day</th><th>Time</th><th>Free Spots</th></tr></thead>
                    <tbody><?php foreach ($criticalClasses as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['class_name']) ?></td>
                            <td><?= htmlspecialchars($row['day_of_week']) ?></td>
                            <td><?= htmlspecialchars($row['start_time']) ?></td>
                            <td><?= $row['free_spots'] ?></td>
                        </tr>
                    <?php endforeach; ?></tbody>
                </table>
            </div>

            <div class="reports-col">
                <!-- Plan distribution -->
                <h2>Membership Plan Distribution</h2>
                <div class="bar-chart">
                    <?php foreach ($planDistribution as $row): ?>
                        <div class="bar-row">
                            <div class="bar-label"><?= htmlspecialchars($row['membership_plan']) ?></div>
                            <div class="bar-container"><div class="bar-fill" style="width: <?= $row['percentage'] ?>%"></div></div>
                            <div><?= $row['percentage'] ?>%</div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Occupancy by day -->
                <h2>Avg Occupancy by Day of Week</h2>
                <div class="bar-chart">
                    <?php foreach ($occupancyByDay as $row): ?>
                        <div class="bar-row">
                            <div class="bar-label"><?= htmlspecialchars($row['day_of_week']) ?></div>
                            <div class="bar-container"><div class="bar-fill" style="width: <?= $row['avg_occupancy'] ?>%"></div></div>
                            <div><?= $row['avg_occupancy'] ?>%</div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Low equipment -->
                <h2>Low Availability Equipment (<30%)</h2>
                <table>
                    <thead><tr><th>Equipment</th><th>Available</th><th>Total</th><th>Availability %</th></tr></thead>
                    <tbody><?php foreach ($lowEquipment as $row): ?>
                        <tr>
                            <td><?= htmlspecialchars($row['name']) ?></td>
                            <td><?= $row['available'] ?></td>
                            <td><?= $row['quantity'] ?></td>
                            <td><?= $row['percentage'] ?>%</td>
                        </tr>
                    <?php endforeach; ?></tbody>
                </table>
            </div>
        </div>
        <script>window.print();</script>
    </body>
    </html>
    <?php
    exit;
}

// Fallback: redirecionar para reports
header('Location: reports.php');
exit;