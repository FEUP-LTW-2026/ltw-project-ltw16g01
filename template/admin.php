<?php
// template/admin.php

function output_admin_member_row($member) { ?>
    <tr>
        <td>
            <div class="name-cell">
                <div class="avatar"><?= htmlspecialchars(strtoupper(substr($member['name'], 0, 1))) ?></div>
                <div class="name-cell-text">
                    <span class="name-main"><?= htmlspecialchars($member['name']) ?></span>
                    <span class="name-sub"><?= htmlspecialchars($member['email']) ?></span>
                </div>
            </div>
        </td>
        <td class="table-muted"><?= htmlspecialchars($member['username']) ?></td>
        <td class="table-muted table-small"><?= htmlspecialchars($member['created_at']) ?></td>
        <td><?= $member['active'] ? '<span class="badge badge-success">Active</span>' : '<span class="badge badge-danger">Inactive</span>' ?></td>
        <td class="table-small"><a href="members.php" class="action-link">View</a></td>
    </tr>
<?php }

function output_admin_class_row($class, $index) { ?>
    <tr>
        <td><span class="rank-num"><?= $index + 1 ?></span></td>
        <td>
            <div class="name-cell-text">
                <span class="name-main"><?= htmlspecialchars($class['name']) ?></span>
                <span class="name-sub"><?= htmlspecialchars($class['type'] ?? '') ?></span>
            </div>
        </td>
        <td class="table-muted"><?= htmlspecialchars($class['trainer_name'] ?? 'No trainer') ?></td>
        <td class="table-muted"><?= htmlspecialchars($class['day_of_week'] ?? '') ?> · <?= htmlspecialchars($class['start_time'] ?? '') ?></td>
        <td class="table-muted"><?= htmlspecialchars($class['location'] ?? '') ?></td>
    </tr>
<?php }

function output_admin_trainer_row($trainer) { ?>
    <tr>
        <td>
            <div class="name-cell">
                <div class="avatar"><?= htmlspecialchars(strtoupper(substr($trainer['name'], 0, 1))) ?></div>
                <span class="name-main"><?= htmlspecialchars($trainer['name']) ?></span>
            </div>
        </td>
        <td class="table-muted"><?= htmlspecialchars($trainer['username']) ?></td>
        <td class="table-muted"><?= htmlspecialchars($trainer['email']) ?></td>
    </tr>
<?php }

function output_admin_equipment_item($item) {
    $percentage = $item['quantity'] > 0 ? round(($item['available'] / $item['quantity']) * 100) : 0;
    $percentage = max(0, min(100, $percentage));
    $widthClass = 'progress-w-' . (round($percentage / 10) * 10);
    $badgeClass = 'badge-success';
    $fillClass = '';

    if ($percentage < 50) {
        $badgeClass = 'badge-danger';
        $fillClass = 'low';
    } elseif ($percentage < 75) {
        $badgeClass = 'badge-warning';
        $fillClass = 'mid';
    }
    ?>
    <div class="equip-item">
        <div class="equip-row">
            <span class="equip-name"><?= htmlspecialchars($item['name']) ?></span>
            <span class="badge <?= $badgeClass ?>"><?= (int)$item['available'] ?> / <?= (int)$item['quantity'] ?> available</span>
        </div>

        <div class="equip-prog">
            <div class="progress-bar">
                <div class="progress-fill <?= $fillClass ?> <?= $widthClass ?>"></div>
            </div>
            <span class="equip-percentage"><?= $percentage ?>%</span>
        </div>
    </div>
<?php }