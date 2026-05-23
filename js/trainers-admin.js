const filterForm = document.getElementById('filter-form');
const trainerTableBody = document.getElementById('trainer-table-body');
const trainerCount = document.getElementById('trainer-count');

function updateAdminTrainers() {
    if (!filterForm || !trainerTableBody || !trainerCount) {
        return;
    }

    const params = new URLSearchParams(new FormData(filterForm));

    fetch('../ajax/ajax_get_admin_trainers.php?' + params.toString())
        .then(response => response.text())
        .then(html => {
            trainerTableBody.innerHTML = html;

            const rows = trainerTableBody.querySelectorAll('tr');
            const emptyRow = trainerTableBody.querySelector('td[colspan]');

            trainerCount.innerText = emptyRow ? 0 : rows.length;
        })
        .catch(error => console.error('AJAX error:', error));
}

if (filterForm) {
    filterForm.addEventListener('submit', function (event) {
        event.preventDefault();
        updateAdminTrainers();
    });

    filterForm.querySelectorAll('select').forEach(function (select) {
        select.addEventListener('change', updateAdminTrainers);
    });

    filterForm.querySelectorAll('input').forEach(function (input) {
        input.addEventListener('input', function () {
            clearTimeout(input.dataset.timer);

            input.dataset.timer = setTimeout(function () {
                updateAdminTrainers();
            }, 300);
        });
    });
}

document.addEventListener('click', function (event) {
    if (event.target.classList.contains('delete-trainer-link')) {
        if (!confirm('Delete this trainer?')) {
            event.preventDefault();
        }
    }
});