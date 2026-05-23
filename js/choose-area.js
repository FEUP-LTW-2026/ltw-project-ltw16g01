document.querySelectorAll('.role-btn').forEach(button => {
    button.addEventListener('click', function (event) {
        event.preventDefault();

        const role = this.dataset.role;
        const targetUrl = this.href;

        fetch('../ajax/set_active_role.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded'
            },
            body: 'role=' + encodeURIComponent(role)
        })
            .then(() => {
                window.location.href = targetUrl;
            })
            .catch(error => {
                console.error('Role switch error:', error);
                window.location.href = targetUrl;
            });
    });
});