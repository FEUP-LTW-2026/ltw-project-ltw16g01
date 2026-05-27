document.querySelectorAll('.btn-cancel-enroll').forEach(button => {
    button.addEventListener('click', function () {
        const sessionId = this.dataset.sessionId;

        if (!confirm('Cancel your enrollment?')) return;

        fetch('../ajax/enroll.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'cancel', schedule_id: sessionId })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(error => {
                console.error('Cancel enrollment error:', error);
                alert('Something went wrong.');
            });
    });
});

// Review modal logic
document.querySelectorAll('.rate-link').forEach(btn => {
    btn.addEventListener('click', () => {
        const sessionId = btn.dataset.sessionId;
        const className = btn.dataset.className;

        document.getElementById('reviewSessionId').value = sessionId;
        document.getElementById('reviewClassName').textContent = className;
        document.getElementById('ratingInput').value = 0;

        document.querySelectorAll('#reviewModal .star').forEach(s => {
            s.classList.remove('filled');
        });

        document.getElementById('reviewModal').classList.add('open');
    });
});

document.getElementById('reviewForm')?.addEventListener('submit', function (e) {
    e.preventDefault();

    const fd = new FormData(e.target);
    const rating = parseInt(fd.get('rating'));

    if (rating < 1) {
        alert('Please select a rating.');
        return;
    }

    const data = {
        session_id: fd.get('session_id'),
        rating: rating,
        comment: fd.get('comment')
    };

    fetch('../ajax/review.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data)
    })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                alert('Review saved!');
                location.reload();
            } else {
                alert(result.message || 'Error saving review.');
            }
        })
        .catch(error => {
            console.error('Review error:', error);
            alert('Network error. Please try again.');
        });
});

// Fechar modal apenas clicando no overlay (fundo escuro), não no conteúdo
const modalOverlay = document.getElementById('reviewModal');

if (modalOverlay) {
    modalOverlay.addEventListener('click', function (e) {
        if (e.target === modalOverlay) {
            modalOverlay.classList.remove('open');
        }
    });
}

// Botão de fechar também fecha o modal
const modalCloseBtn = document.querySelector('.modal-close');

if (modalCloseBtn) {
    modalCloseBtn.addEventListener('click', function () {
        modalOverlay.classList.remove('open');
    });
}

// Impedir que cliques dentro do modal fechem o modal (propagação)
const modalContent = document.querySelector('.modal');

if (modalContent) {
    modalContent.addEventListener('click', function (e) {
        e.stopPropagation();
    });
}

// Seleção de estrelas
document.querySelectorAll('#reviewModal .star').forEach((star, idx) => {
    star.addEventListener('click', () => {
        const val = idx + 1;

        document.getElementById('ratingInput').value = val;

        document.querySelectorAll('#reviewModal .star').forEach((s, i) => {
            if (i < val) {
                s.classList.add('filled');
            } else {
                s.classList.remove('filled');
            }
        });
    });
});