// js/member-notifications.js
document.addEventListener('DOMContentLoaded', function() {
    // Handle mark as read buttons
    document.querySelectorAll('.mark-read-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            }).then(() => location.reload());
        });
    });

    // Handle delete buttons
    document.querySelectorAll('.delete-form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Delete this notification?')) e.preventDefault();
        });
    });
});