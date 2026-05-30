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
    setTimeout(() => toast.remove(), 3000);
}

// Reloads class cards using the selected filters.
function updateClasses() {
    if (!filterForm || !classListContainer || !classCount) return;
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
    filterForm.addEventListener('submit', event => {
        event.preventDefault();
        updateClasses();
    });
    filterForm.querySelectorAll('select').forEach(select => {
        select.addEventListener('change', updateClasses);
    });
}

// Handles class enrollment/cancellation actions.
document.addEventListener('click', function (event) {
    const enrollButton = event.target.closest('.enroll-btn, .enrolled-btn');
    if (!enrollButton) return;

    event.preventDefault();
    if (enrollButton.disabled) return;

    const scheduleId = enrollButton.dataset.scheduleId;
    const sessionId = enrollButton.dataset.sessionId; // only for enrolled-btn
    const action = enrollButton.dataset.action;

    if (!scheduleId || !action) return;

    enrollButton.disabled = true;
    const originalText = enrollButton.textContent;
    enrollButton.textContent = action === 'enroll' ? 'Processing...' : 'Canceling...';

    const payload = { action, schedule_id: scheduleId, csrf: getCsrfToken() };
    if (action === 'cancel' && sessionId) payload.session_id = sessionId;

    fetch('../ajax/enroll.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showToast(data.message, 'success');
                // Reload the whole class list to refresh spot counts and button states
                updateClasses();
            } else {
                showToast(data.message || 'Something went wrong.', 'error');
                // Restore button only if it's still the same element (not replaced by reload)
                // Since we don't reload on error, we restore manually.
                enrollButton.textContent = originalText;
                enrollButton.disabled = false;
            }
        })
        .catch(error => {
            console.error('Enrollment AJAX error:', error);
            showToast('Network error. Please try again.', 'error');
            enrollButton.textContent = originalText;
            enrollButton.disabled = false;
        });
});

// Handles opening and closing the trainer schedule edit form.
document.addEventListener('click', function (event) {
    const editButton = event.target.closest('.edit-schedule-button');

    if (editButton) {
        const targetId = editButton.dataset.target;
        const editBox = document.getElementById(targetId);

        if (!editBox) {
            return;
        }

        editBox.classList.toggle('is-hidden');
        return;
    }

    const cancelButton = event.target.closest('.cancel-edit-schedule-button');

    if (cancelButton) {
        const targetId = cancelButton.dataset.target;
        const editBox = document.getElementById(targetId);

        if (!editBox) {
            return;
        }

        editBox.classList.add('is-hidden');
    }
});