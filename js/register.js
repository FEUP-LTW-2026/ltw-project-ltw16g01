const form = document.getElementById('registerForm');

const nameInput = document.getElementById('name');
const usernameInput = document.getElementById('username');
const emailInput = document.getElementById('email');
const passwordInput = document.getElementById('password');
const confirmInput = document.getElementById('confirm');

const strengthWrap = document.getElementById('pwStrength');
const strengthFill = document.getElementById('strengthFill');
const strengthLabel = document.getElementById('strengthLabel');

function showError(input, errorId, message) {
    const error = document.getElementById(errorId);
    input.classList.add('input-error');
    error.textContent = message;
    error.classList.add('visible');
}

function clearError(input, errorId) {
    const error = document.getElementById(errorId);
    input.classList.remove('input-error');
    error.classList.remove('visible');
}

nameInput.addEventListener('input', function () {
    clearError(nameInput, 'err-name');
});

usernameInput.addEventListener('input', function () {
    clearError(usernameInput, 'err-username');
});

emailInput.addEventListener('input', function () {
    clearError(emailInput, 'err-email');
});

passwordInput.addEventListener('input', function () {
    clearError(passwordInput, 'err-password');

    const password = passwordInput.value;

    if (password.length === 0) {
        strengthWrap.classList.remove('visible');
        return;
    }

    strengthWrap.classList.add('visible');

    let score = 0;

    if (password.length >= 8) score++;
    if (/[A-Z]/.test(password)) score++;
    if (/[0-9]/.test(password)) score++;
    if (/[^A-Za-z0-9]/.test(password)) score++;

    if (score <= 1) {
        strengthFill.className = 'strength-fill weak';
        strengthLabel.className = 'strength-label weak';
        strengthLabel.textContent = 'Weak';
    } else if (score === 2 || score === 3) {
        strengthFill.className = 'strength-fill fair';
        strengthLabel.className = 'strength-label fair';
        strengthLabel.textContent = 'Fair';
    } else {
        strengthFill.className = 'strength-fill strong';
        strengthLabel.className = 'strength-label strong';
        strengthLabel.textContent = 'Strong';
    }
});

confirmInput.addEventListener('input', function () {
    if (confirmInput.value === '') {
        clearError(confirmInput, 'err-confirm');
    } else if (confirmInput.value !== passwordInput.value) {
        showError(confirmInput, 'err-confirm', 'Passwords do not match.');
    } else {
        clearError(confirmInput, 'err-confirm');
    }
});

document.querySelectorAll('.toggle-pw').forEach(function (button) {
    button.addEventListener('click', function () {
        const input = document.getElementById(button.dataset.target);

        if (input.type === 'password') {
            input.type = 'text';
            button.textContent = '🙈';
        } else {
            input.type = 'password';
            button.textContent = '👁';
        }
    });
});

form.addEventListener('submit', function (event) {
    let valid = true;

    if (nameInput.value.trim() === '') {
        showError(nameInput, 'err-name', 'Please enter your full name.');
        valid = false;
    }

    if (usernameInput.value.trim().length < 3) {
        showError(usernameInput, 'err-username', 'Username must have at least 3 characters.');
        valid = false;
    }

    if (!emailInput.validity.valid) {
        showError(emailInput, 'err-email', 'Please enter a valid email address.');
        valid = false;
    }

    if (passwordInput.value.length < 8) {
        showError(passwordInput, 'err-password', 'Password must be at least 8 characters.');
        valid = false;
    }

    if (confirmInput.value !== passwordInput.value) {
        showError(confirmInput, 'err-confirm', 'Passwords do not match.');
        valid = false;
    }

    if (!valid) {
        event.preventDefault();
    }
});