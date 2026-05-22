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

// Só ativa os filtros se a página tiver filter-form
if (filterForm) {
    // Impede o formulário de recarregar a página se alguém carregar Enter
    filterForm.addEventListener('submit', function (event) {
        event.preventDefault();
    });

    // Atualiza automaticamente quando mudares um select
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

    // Abrir/fechar formulário de edição do schedule
    if (event.target.classList.contains('edit-schedule-button')) {
        const targetId = event.target.dataset.target;
        const editBox = document.getElementById(targetId);

        if (editBox) {
            editBox.classList.toggle('is-visible');
        }
    }

    // Fechar formulário de edição sem guardar
    if (event.target.classList.contains('cancel-edit-schedule-button')) {
        const targetId = event.target.dataset.target;
        const editBox = document.getElementById(targetId);

        if (editBox) {
            editBox.classList.remove('is-visible');
        }
    }
});