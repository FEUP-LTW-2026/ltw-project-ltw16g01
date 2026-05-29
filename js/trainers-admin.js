const filterForm = document.getElementById('filter-form');
const trainerListContainer = document.getElementById('trainer-list-container');
const trainerCount = document.getElementById('trainer-count');

// Reloads the trainer list using the selected filters.
function updateTrainers() {
    if (!filterForm || !trainerListContainer || !trainerCount) {
        return;
    }

    const params = new URLSearchParams(new FormData(filterForm));

    fetch('../ajax/ajax_get_trainers.php?' + params.toString())
        .then(function (response) {
            return response.text();
        })
        .then(function (html) {
            trainerListContainer.innerHTML = html;
            trainerCount.innerText = trainerListContainer.querySelectorAll('.trainer-card').length;
        })
        .catch(function (error) {
            console.error('AJAX error:', error);
        });
}

if (filterForm) {
    filterForm.querySelectorAll('select').forEach(function (select) {
        select.addEventListener('change', updateTrainers);
    });
}