// Gets the CSRF token from the page meta tag.
function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');
    return meta ? meta.content : '';
}

// Handles enrollment cancellation from the member dashboard.
document.querySelectorAll('.btn-cancel-enroll').forEach(function (button) {
    button.addEventListener('click', function () {
        const sessionId = this.dataset.sessionId;

        fetch('../ajax/enroll.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                action: 'cancel',
                session_id: sessionId,
                csrf: getCsrfToken()
            })
        })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message);
                }
            })
            .catch(function (error) {
                console.error('Cancel enrollment error:', error);
                alert('Something went wrong.');
            });
    });
});

// Opens the review modal and prepares the selected class/session data.
document.querySelectorAll('.rate-link').forEach(function (button) {
    button.addEventListener('click', function () {
        const sessionId = button.dataset.sessionId;
        const className = button.dataset.className;

        document.getElementById('reviewSessionId').value = sessionId;
        document.getElementById('reviewClassName').textContent = className;
        document.getElementById('ratingInput').value = 0;

        document.querySelectorAll('#reviewModal .star').forEach(function (star) {
            star.classList.remove('filled');
        });

        document.getElementById('reviewModal').classList.add('open');
    });
});

// Handles review form submission.
document.getElementById('reviewForm')?.addEventListener('submit', function (event) {
    event.preventDefault();

    const formData = new FormData(event.target);
    const rating = parseInt(formData.get('rating'));

    if (rating < 1) {
        alert('Please select a rating.');
        return;
    }

    const data = {
        session_id: formData.get('session_id'),
        rating: rating,
        comment: formData.get('comment'),
        csrf: getCsrfToken()
    };

    fetch('../ajax/review.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
        .then(function (response) {
            return response.json();
        })
        .then(function (result) {
            if (result.success) {
                alert('Review saved!');
                location.reload();
            } else {
                alert(result.message || 'Error saving review.');
            }
        })
        .catch(function (error) {
            console.error('Review error:', error);
            alert('Network error. Please try again.');
        });
});

// Closes the modal when clicking the dark overlay.
const modalOverlay = document.getElementById('reviewModal');

if (modalOverlay) {
    modalOverlay.addEventListener('click', function (event) {
        if (event.target === modalOverlay) {
            modalOverlay.classList.remove('open');
        }
    });
}

// Closes the modal using the close button.
const modalCloseBtn = document.querySelector('.modal-close');

if (modalCloseBtn && modalOverlay) {
    modalCloseBtn.addEventListener('click', function () {
        modalOverlay.classList.remove('open');
    });
}

// Prevents clicks inside the modal content from closing the modal.
const modalContent = document.querySelector('.modal');

if (modalContent) {
    modalContent.addEventListener('click', function (event) {
        event.stopPropagation();
    });
}

// Handles star rating selection.
document.querySelectorAll('#reviewModal .star').forEach(function (star, index) {
    star.addEventListener('click', function () {
        const value = index + 1;

        document.getElementById('ratingInput').value = value;

        document.querySelectorAll('#reviewModal .star').forEach(function (item, itemIndex) {
            if (itemIndex < value) {
                item.classList.add('filled');
            } else {
                item.classList.remove('filled');
            }
        });
    });
});