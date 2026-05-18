document.addEventListener('click', function (event) {
    if (event.target.classList.contains('delete-trainer-link')) {
        if (!confirm('Delete this trainer?')) event.preventDefault();
    }
});