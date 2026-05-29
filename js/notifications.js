// Gets the CSRF token from the page meta tag.
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

document.addEventListener('DOMContentLoaded', function () {
    const typeFilter = document.getElementById('type-filter');

    // Redirects when the notification type filter changes.
    if (typeFilter) {
        typeFilter.addEventListener('change', function () {
            const filter = typeFilter.value;
            window.location.href = 'notifications.php?filter=' + encodeURIComponent(filter);
        });
    }

    // Creates a hidden input for dynamic POST forms.
    function createHiddenInput(name, value) {
        const input = document.createElement('input');

        input.type = 'hidden';
        input.name = name;
        input.value = value;

        return input;
    }

    // Submits notification actions using a generated POST form.
    function submitNotificationForm(action, notificationId) {
        const form = document.createElement('form');

        form.method = 'POST';
        form.appendChild(createHiddenInput('action', action));
        form.appendChild(createHiddenInput('notification_id', notificationId));
        form.appendChild(createHiddenInput('csrf', getCsrfToken()));

        document.body.appendChild(form);
        form.submit();
    }

    // Marks one notification as read.
    document.querySelectorAll('.mark-read-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const notificationId = button.dataset.id;

            if (!notificationId) {
                return;
            }

            submitNotificationForm('mark_read', notificationId);
        });
    });

    // Deletes one notification 
    document.querySelectorAll('.delete-notif-btn').forEach(function (button) {
        button.addEventListener('click', function () {
            const notificationId = button.dataset.id;

            if (!notificationId) {
                return;
            }

            submitNotificationForm('delete', notificationId);
        });
    });
});