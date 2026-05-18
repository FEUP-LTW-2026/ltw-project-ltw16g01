const filterForm = document.getElementById('filter-form');
const classListContainer = document.getElementById('class-list-container');
const classCount = document.getElementById('class-count');

filterForm.addEventListener('submit', function (event) {
    event.preventDefault();

    const params = new URLSearchParams(new FormData(filterForm));

    fetch('../ajax/ajax_get_classes.php?' + params.toString())
        .then(response => response.text())
        .then(html => {
            classListContainer.innerHTML = html;
            classCount.innerText = document.querySelectorAll('#class-list-container .class-card').length;
        })
        .catch(error => console.error('AJAX error:', error));
});

document.addEventListener('click', function (event) {
    if (event.target.classList.contains('delete-class-link')) {
        if (!confirm('Delete this class schedule?')) event.preventDefault();
    }
});