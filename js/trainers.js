const filterForm = document.getElementById('filter-form');
const trainerListContainer = document.getElementById('trainer-list-container');
const trainerCount = document.getElementById('trainer-count');

filterForm.addEventListener('submit', function (event) {
    event.preventDefault();

    const params = new URLSearchParams(new FormData(filterForm));

    fetch('../ajax/ajax_get_trainers.php?' + params.toString())
        .then(response => response.text())
        .then(html => {
            trainerListContainer.innerHTML = html;
            trainerCount.innerText = document.querySelectorAll('#trainer-list-container .trainer-card').length;
        })
        .catch(error => console.error('AJAX error:', error));
});

document.addEventListener('click', function (event) {
    if (event.target.classList.contains('delete-trainer-link')) {
        if (!confirm('Delete this trainer?')) event.preventDefault();
    }
});