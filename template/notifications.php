<?php
// template/notifications.php

function output_notifications_list($notifications) {
    if (empty($notifications)) {
        echo '<div class="card"><div class="card-body center-text"><p>No notifications found.</p></div></div>';
        return;
    }
    ?>
    <div class="notifications-list">
        <?php foreach ($notifications as $notif): ?>
            <div class="notification-item <?= $notif['read_at'] ? 'read' : 'unread' ?>" data-id="<?= $notif['id'] ?>">
                <div class="notification-icon">
                    <span class="badge badge-<?= htmlspecialchars($notif['css_class']) ?>">
                        <?= htmlspecialchars($notif['type_label']) ?>
                    </span>
                </div>
                <div class="notification-content">
                    <p><?= htmlspecialchars($notif['message']) ?></p>
                    <small class="notification-date"><?= date('d/m/Y H:i', strtotime($notif['created_at'])) ?></small>
                </div>
                <div class="notification-actions">
                    <?php if (!$notif['read_at']): ?>
                        <button class="mark-read-btn secondary-button btn-sm" data-id="<?= (int)$notif['id'] ?>">Mark read</button>
                    <?php endif; ?>
                    <button class="delete-notif-btn danger-button btn-sm" data-id="<?= (int)$notif['id'] ?>">Delete</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}