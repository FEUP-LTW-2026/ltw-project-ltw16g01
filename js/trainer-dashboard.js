document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.decline-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            if (!confirm('Reject this personal training request?')) {
                e.preventDefault();
            }
        });
    });
});