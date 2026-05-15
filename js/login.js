const form = document.getElementById('loginForm');
const identifier = document.getElementById('identifier');
const password = document.getElementById('password');

function showError(input, errorId) {
    input.classList.add('input-error');
    document.getElementById(errorId).classList.add('visible');
}

function clearError(input, errorId) {
    input.classList.remove('input-error');
    document.getElementById(errorId).classList.remove('visible');
}

identifier.addEventListener('input', function () {
    clearError(identifier, 'err-identifier');
});

password.addEventListener('input', function () {
    clearError(password, 'err-password');
});

form.addEventListener('submit', function (event) {
    let valid = true;

    if (identifier.value.trim() === '') {
        showError(identifier, 'err-identifier');
        valid = false;
    }

    if (password.value === '') {
        showError(password, 'err-password');
        valid = false;
    }

    if (!valid) {
        event.preventDefault();
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