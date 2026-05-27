document.addEventListener('DOMContentLoaded', function () {
    const filterSelect = document.getElementById('status-filter');
    const tableContainer = document.getElementById('requests-table-container');
    const countSpan = document.getElementById('request-count');

    if (!filterSelect || !tableContainer || !countSpan) {
        return;
    }

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

    filterSelect.addEventListener('change', loadRequests);
});