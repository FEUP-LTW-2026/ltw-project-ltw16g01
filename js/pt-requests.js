// Handles the PT requests filter and toast notification.
document.addEventListener('DOMContentLoaded', function () {
    const filterSelect = document.getElementById('status-filter');
    const tableContainer = document.getElementById('requests-table-container');
    const countSpan = document.getElementById('request-count');

    // Load filtered PT requests through AJAX.
    function loadRequests() {
        const filter = filterSelect.value;

        fetch('../ajax/ajax_get_pt_requests.php?filter=' + encodeURIComponent(filter))
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error ' + response.status);
                }

                return response.json();
            })
            .then(data => {
                if (data.success) {
                    tableContainer.innerHTML = data.html;
                    countSpan.innerText = data.count;
                }
            })
            .catch(error => {
                console.error('PT requests AJAX error:', error);
            });
    }

    if (filterSelect && tableContainer && countSpan) {
        filterSelect.addEventListener('change', loadRequests);
    }

    // Display toast notification after accepting or declining a request.
    const toast = document.getElementById('toast');

    if (!toast) {
        return;
    }

    const toastMessage = toast.dataset.message || '';
    const toastType = toast.dataset.type || 'info';

    if (!toastMessage) {
        return;
    }

    toast.textContent = toastMessage;
    toast.className = 'toast-notification toast-' + toastType;
    toast.style.display = 'block';

    setTimeout(() => {
        toast.style.display = 'none';
    }, 5000);
});