<?php
function output_notifications_list($notifications) {
    if (empty($notifications)) {
        echo '<div class="card"><div class="card-body center-text"><p>No notifications found.</p></div></div>';
        return;
    }
    ?>
    <div class="notifications-list">
        <?php foreach ($notifications as $notif): ?>
            <div class="notification-item <?= $notif['is_read'] ? 'read' : 'unread' ?>" data-id="<?= $notif['id'] ?>">
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
                    <?php if (!$notif['is_read']): ?>
                        <button class="mark-read-btn secondary-button btn-sm" data-id="<?= $notif['id'] ?>">Mark read</button>
                    <?php endif; ?>
                    <button class="delete-notif-btn danger-button btn-sm" data-id="<?= $notif['id'] ?>">Delete</button>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}

function output_member_notifications_list($notifications) {
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
                        <form method="post" class="inline-form">
                            <input type="hidden" name="action" value="mark_read">
                            <input type="hidden" name="notification_id" value="<?= $notif['id'] ?>">
                            <button type="submit" class="secondary-button btn-sm">Mark read</button>
                        </form>
                    <?php endif; ?>
                    <form method="post" class="inline-form">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="notification_id" value="<?= $notif['id'] ?>">
                        <button type="submit" class="delete-notif-btn btn-sm" onclick="return confirm('Delete this notification?');">Delete</button>
                    </form>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php
}