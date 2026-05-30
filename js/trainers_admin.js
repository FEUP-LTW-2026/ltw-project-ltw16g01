/**
 * trainers_admin.js – Handles AJAX filtering for the admin trainers table.
 */

const filterForm = document.getElementById('filter-form');
const trainerTableBody = document.getElementById('trainer-table-body');
const trainerCount = document.getElementById('trainer-count');

// Reloads the trainer table using the selected filters.
function updateTrainers() {
    if (!filterForm || !trainerTableBody || !trainerCount) {
        return;
    }

    const params = new URLSearchParams(new FormData(filterForm));

    fetch('../ajax/ajax_get_admin_trainers.php?' + params.toString())
        .then(response => response.text())
        .then(html => {
            trainerTableBody.innerHTML = html;
            // Count rows inside the tbody (excluding an empty row with colspan)
            const rows = trainerTableBody.querySelectorAll('tr');
            const emptyRow = trainerTableBody.querySelector('td[colspan]');
            trainerCount.innerText = emptyRow ? 0 : rows.length;
        })
        .catch(error => console.error('AJAX error:', error));
}

if (filterForm) {
    // Prevent the form from reloading the page when submitted
    filterForm.addEventListener('submit', function (event) {
        event.preventDefault();
        updateTrainers();
    });

    // Trigger update on any select change
    filterForm.querySelectorAll('select').forEach(select => {
        select.addEventListener('change', updateTrainers);
    });

    // Trigger update on text input (with debounce to avoid too many requests)
    const nameInput = filterForm.querySelector('#f-name');
    if (nameInput) {
        let debounceTimer;
        nameInput.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(updateTrainers, 300);
        });
    }
}