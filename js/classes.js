// Gets the CSRF token from the page meta tag.
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

const filterForm = document.getElementById('filter-form');
const classListContainer = document.getElementById('class-list-container');
const classCount = document.getElementById('class-count');

// Shows a temporary toast message.
function showToast(message, type) {
    const toast = document.createElement('div');

    toast.textContent = message;
    toast.className = 'toast-message';

    if (type === 'success') {
        toast.classList.add('toast-success');
    } else {
        toast.classList.add('toast-error');
    }

    document.body.appendChild(toast);

    setTimeout(function () {
        toast.remove();
    }, 3000);
}

// Reloads class cards using the selected filters.
function updateClasses() {
    if (!filterForm || !classListContainer || !classCount) {
        return;
    }

    const params = new URLSearchParams(new FormData(filterForm));

    fetch('../ajax/ajax_get_classes.php?' + params.toString())
        .then(function (response) {
            return response.text();
        })
        .then(function (html) {
            classListContainer.innerHTML = html;
            classCount.innerText = classListContainer.querySelectorAll('.class-card').length;
        })
        .catch(function (error) {
            console.error('AJAX error:', error);
        });
}

if (filterForm) {
    filterForm.addEventListener('submit', function (event) {
        event.preventDefault();
        updateClasses();
    });

    filterForm.querySelectorAll('select').forEach(function (select) {
        select.addEventListener('change', updateClasses);
    });
}

// Handles delete confirmation, schedule editing and class enrollment actions.
document.addEventListener('click', function (event) {
    const deleteLink = event.target.closest('.delete-class-link');

    const editButton = event.target.closest('.edit-schedule-button');

    if (editButton) {
        const targetId = editButton.dataset.target;
        const editBox = document.getElementById(targetId);

        if (editBox) {
            editBox.classList.remove('is-hidden');
        }

        return;
    }

    const cancelEditButton = event.target.closest('.cancel-edit-schedule-button');

    if (cancelEditButton) {
        const targetId = cancelEditButton.dataset.target;
        const editBox = document.getElementById(targetId);

        if (editBox) {
            editBox.classList.add('is-hidden');
        }

        return;
    }

    const enrollButton = event.target.closest('.enroll-btn, .enrolled-btn');

    if (!enrollButton) {
        return;
    }

    event.preventDefault();

    if (enrollButton.disabled) {
        return;
    }

    const scheduleId = enrollButton.dataset.scheduleId;
    const action = enrollButton.dataset.action;

    if (!scheduleId || !action) {
        return;
    }

    enrollButton.disabled = true;

    const originalText = enrollButton.textContent;
    enrollButton.textContent = action === 'enroll' ? 'Processing...' : 'Canceling...';

    fetch('../ajax/enroll.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            action: action,
            schedule_id: scheduleId,
            csrf: getCsrfToken()
        })
    })
        .then(function (response) {
            return response.json();
        })
        .then(function (data) {
            if (data.success) {
                if (action === 'enroll') {
                    enrollButton.textContent = '✅ Enrolled';
                    enrollButton.classList.remove('enroll-btn', 'secondary-button');
                    enrollButton.classList.add('enrolled-btn');
                    enrollButton.dataset.action = 'cancel';
                } else {
                    enrollButton.textContent = 'Enroll';
                    enrollButton.classList.remove('enrolled-btn');
                    enrollButton.classList.add('enroll-btn', 'secondary-button');
                    enrollButton.dataset.action = 'enroll';
                }

                showToast(data.message, 'success');
            } else {
                showToast(data.message || 'Something went wrong.', 'error');
                enrollButton.textContent = originalText;
            }

            enrollButton.disabled = false;
        })
        .catch(function (error) {
            console.error('Enrollment AJAX error:', error);
            showToast('Network error. Please try again.', 'error');

            enrollButton.textContent = originalText;
            enrollButton.disabled = false;
        });
});