const filterForm = document.getElementById('filter-form');
const trainerListContainer = document.getElementById('trainer-list-container');
const trainerCount = document.getElementById('trainer-count');

function updateTrainers() {
    const params = new URLSearchParams(new FormData(filterForm));

    fetch('../ajax/ajax_get_trainers.php?' + params.toString())
        .then(response => response.text())
        .then(html => {
            trainerListContainer.innerHTML = html;
            trainerCount.innerText = trainerListContainer.querySelectorAll('.trainer-card').length;
        })
        .catch(error => console.error('AJAX error:', error));
}

filterForm.querySelectorAll('select').forEach(function (select) {
    select.addEventListener('change', updateTrainers);
});