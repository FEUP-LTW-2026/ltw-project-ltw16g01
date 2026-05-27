function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

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
        form.appendChild(createHiddenInput('csrf', getCsrfToken()));

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
    document.querySelectorAll('.delete-notif-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Delete this notification?')) return;
            const id = this.dataset.id;
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `<input type="hidden" name="action" value="delete"><input type="hidden" name="notification_id" value="${id}">`;
            document.body.appendChild(form);
            form.submit();
        });
    });
});