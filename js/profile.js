const passwordForm = document.getElementById('profilePasswordForm');

// Validates password changes on the profile page.
if (passwordForm) {
    passwordForm.addEventListener('submit', function (event) {
        const newPassword = document.getElementById('new_password');
        const confirmPassword = document.getElementById('confirm_password');

        if (!newPassword || !confirmPassword) {
            return;
        }

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

const photoInput = document.getElementById('photo');
const fileName = document.getElementById('file-name');

// Shows the selected profile image file name.
if (photoInput && fileName) {
    photoInput.addEventListener('change', function () {
        if (photoInput.files.length > 0) {
            fileName.textContent = photoInput.files[0].name;
        } else {
            fileName.textContent = 'No image selected';
        }
    });
}