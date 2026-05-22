const filterForm = document.getElementById('filter-form');
const classListContainer = document.getElementById('class-list-container');
const classCount = document.getElementById('class-count');

function updateClasses() {
    if (!filterForm || !classListContainer || !classCount) {
        return;
    }
    const params = new URLSearchParams(new FormData(filterForm));
    fetch('../ajax/ajax_get_classes.php?' + params.toString())
        .then(response => response.text())
        .then(html => {
            classListContainer.innerHTML = html;
            classCount.innerText = classListContainer.querySelectorAll('.class-card').length;
        })
        .catch(error => console.error('AJAX error:', error));
}

if (filterForm) {
    filterForm.addEventListener('submit', function (event) {
        event.preventDefault();
    });
    filterForm.querySelectorAll('select').forEach(function (select) {
        select.addEventListener('change', updateClasses);
    });
}

document.addEventListener('click', function (event) {
    if (event.target.classList.contains('delete-class-link')) {
        if (!confirm('Delete this class schedule?')) {
            event.preventDefault();
        }
    }
    if (event.target.classList.contains('edit-schedule-button')) {
        const targetId = event.target.dataset.target;
        const editBox = document.getElementById(targetId);
        if (editBox) {
            editBox.style.display = 'block';
        }
    }
    if (event.target.classList.contains('cancel-edit-schedule-button')) {
        const targetId = event.target.dataset.target;
        const editBox = document.getElementById(targetId);
        if (editBox) {
            editBox.style.display = 'none';
        }
    }
    if (event.target.classList.contains('enroll-btn') || event.target.classList.contains('enrolled-btn')) {
        event.preventDefault();
        const button = event.target;
        const scheduleId = button.dataset.scheduleId;
        const action = button.dataset.action;
        if (!scheduleId || !action) {
            return;
        }
        button.disabled = true;
        fetch('../ajax/enroll.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                schedule_id: scheduleId,
                action: action
            })
        })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
                if (data.success) {
                    location.reload();
                }
            })
            .catch(error => {
                console.error('Enrollment AJAX error:', error);
                alert('Something went wrong.');
            })
            .finally(() => {
                button.disabled = false;
            });
    }
});