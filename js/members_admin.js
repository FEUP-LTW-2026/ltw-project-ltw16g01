const filterForm = document.getElementById('filter-form');
const memberTableBody = document.getElementById('member-table-body');
const memberCount = document.getElementById('member-count');

// Reloads the admin members table using the current filters.
function updateAdminMembers() {
    if (!filterForm || !memberTableBody || !memberCount) {
        return;
    }

    const params = new URLSearchParams(new FormData(filterForm));

    fetch('../ajax/ajax_get_admin_members.php?' + params.toString())
        .then(function (response) {
            return response.text();
        })
        .then(function (html) {
            memberTableBody.innerHTML = html;

            const rows = memberTableBody.querySelectorAll('tr');
            const emptyRow = memberTableBody.querySelector('td[colspan]');

            memberCount.innerText = emptyRow ? 0 : rows.length;
        })
        .catch(function (error) {
            console.error('AJAX error:', error);
        });
}

if (filterForm) {
    filterForm.addEventListener('submit', function (event) {
        event.preventDefault();
        updateAdminMembers();
    });

    filterForm.querySelectorAll('select').forEach(function (select) {
        select.addEventListener('change', updateAdminMembers);
    });

    filterForm.querySelectorAll('input').forEach(function (input) {
        input.addEventListener('input', function () {
            clearTimeout(input.dataset.timer);

            input.dataset.timer = setTimeout(function () {
                updateAdminMembers();
            }, 300);
        });
    });
}

