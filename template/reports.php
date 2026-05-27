<?php
// template/reports.php

function renderKpiCards($kpis) {
    ?>
    <div class="kpi-grid">
        <div class="kpi-card"><div class="kpi-value"><?= $kpis['activeMembers'] ?></div><div class="kpi-label">👥 Active Members</div></div>
        <div class="kpi-card"><div class="kpi-value"><?= $kpis['activeTrainers'] ?></div><div class="kpi-label">🏋️ Trainers</div></div>
        <div class="kpi-card"><div class="kpi-value"><?= $kpis['activeClassesPerWeek'] ?></div><div class="kpi-label">📅 Classes / Week</div></div>
        <div class="kpi-card"><div class="kpi-value"><?= $kpis['avgOccupancy'] ?>%</div><div class="kpi-label">📊 Avg Occupancy (30d)</div></div>
        <div class="kpi-card"><div class="kpi-value"><?= $kpis['equipmentIssues'] ?></div><div class="kpi-label">⚠️ Equipment Issues</div></div>
    </div>
    <?php
}

function renderBarChart($data, $labelKey, $valueKey, $title, $valueSuffix = '') {
    if (empty($data)) {
        echo '<div class="chart-card"><h3>' . htmlspecialchars($title) . '</h3><p>No data available.</p></div>';
        return;
    }
    $maxValue = max(array_column($data, $valueKey));
    if ($maxValue == 0) $maxValue = 1; // evitar divisão por zero
    ?>
    <div class="chart-card">
        <h3><?= htmlspecialchars($title) ?></h3>
        <div class="bar-chart">
            <?php foreach ($data as $item): ?>
                <?php 
                    $value = round($item[$valueKey], 1);
                    $percentage = ($value / $maxValue) * 100;
                ?>
                <div class="bar-row">
                    <div class="bar-label"><?= htmlspecialchars($item[$labelKey]) ?></div>
                    <div class="bar-container">
                        <div class="bar-fill" style="width: <?= $percentage ?>%"></div>
                    </div>
                    <div class="bar-value"><?= $value . $valueSuffix ?></div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php
}

function renderSimpleTable($data, $columns, $title) {
    if (empty($data)) {
        echo '<div class="table-card"><h3>' . htmlspecialchars($title) . '</h3><p>No classes with critical capacity issues.</p></div>';
        return;
    }
    ?>
    <div class="table-card">
        <h3><?= htmlspecialchars($title) ?></h3>
        <div class="table-wrap">
            <table>
                <thead>
                    <tr><?php foreach ($columns as $col): ?><th><?= htmlspecialchars($col) ?></th><?php endforeach; ?></tr>
                </thead>
                <tbody>
                    <?php foreach ($data as $row): ?>
                        <tr>
                            <?php foreach ($columns as $col): ?>
                                <td><?= htmlspecialchars($row[$col] ?? '-') ?></td>
                            <?php endforeach; ?>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php
}