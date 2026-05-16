const passwordForm = document.getElementById('profilePasswordForm');

if (passwordForm) {
    passwordForm.addEventListener('submit', function (event) {
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');

        if (!newPassword || !confirmPassword) return;

        if (newPassword.value.length < 8) {
            alert('Password must be at least 8 characters.');
            event.preventDefault();
            return;
        }

        if (newPassword.value !== confirmPassword.value) {
            alert('Passwords do not match.');
            event.preventDefault();
        }
    });
}