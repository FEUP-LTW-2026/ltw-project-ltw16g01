document.addEventListener('DOMContentLoaded', function () {
    // Confirms before rejecting a personal training request.
    document.querySelectorAll('.decline-btn').forEach(function (button) {
        button.addEventListener('click', function (event) {
            if (!confirm('Reject this personal training request?')) {
                event.preventDefault();
            }
        });
    });
});