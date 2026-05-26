document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.mark-read-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const notificationId = button.dataset.id;

            if (!notificationId) {
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';

            form.innerHTML =
                '<input type="hidden" name="action" value="mark_read">' +
                '<input type="hidden" name="notification_id" value="' + notificationId + '">';

            document.body.appendChild(form);
            form.submit();
        });
    });

    document.querySelectorAll('.delete-notif-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const notificationId = button.dataset.id;

            if (!notificationId) {
                return;
            }

            if (!confirm('Delete this notification?')) {
                return;
            }

            const form = document.createElement('form');
            form.method = 'POST';

            form.innerHTML =
                '<input type="hidden" name="action" value="delete">' +
                '<input type="hidden" name="notification_id" value="' + notificationId + '">';

            document.body.appendChild(form);
            form.submit();
        });
    });
});