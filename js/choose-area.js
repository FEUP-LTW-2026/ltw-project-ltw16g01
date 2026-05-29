// Gets the CSRF token from the page meta tag.
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

// Handles role/area switching.
document.querySelectorAll('.role-btn').forEach(function (button) {
    button.addEventListener('click', function (event) {
        event.preventDefault();

        const role = this.dataset.role;
        const targetUrl = this.href;

        fetch('../ajax/set_active_role.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'role=' + encodeURIComponent(role) +
                '&csrf=' + encodeURIComponent(getCsrfToken())
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data.success) {
                    window.location.href = targetUrl;
                } else {
                    alert(data.message || 'Could not switch area.');
                }
            })
            .catch(function (error) {
                console.error('Role switch error:', error);
                alert('Something went wrong.');
            });
    });
});