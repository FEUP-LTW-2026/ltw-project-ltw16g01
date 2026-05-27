document.addEventListener('DOMContentLoaded', function () {
    const typeFilter = document.getElementById('type-filter');

    if (typeFilter) {
        typeFilter.addEventListener('change', function () {
            const filter = typeFilter.value;
            window.location.href = 'notifications.php?filter=' + encodeURIComponent(filter);
        });
    }

    function createHiddenInput(name, value) {
        const input = document.createElement('input');

        input.type = 'hidden';
        input.name = name;
        input.value = value;

        return input;
    }

    function submitNotificationForm(action, notificationId) {
        const form = document.createElement('form');

        form.method = 'POST';
        form.appendChild(createHiddenInput('action', action));
        form.appendChild(createHiddenInput('notification_id', notificationId));

        document.body.appendChild(form);
        form.submit();
    }

    document.querySelectorAll('.mark-read-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const notificationId = button.dataset.id;

            if (!notificationId) {
                return;
            }

            submitNotificationForm('mark_read', notificationId);
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

            submitNotificationForm('delete', notificationId);
        });
    });
});