document.querySelectorAll('.btn-cancel-enroll').forEach(button => {
    button.addEventListener('click', async function () {
        const sessionId = this.dataset.sessionId;

        if (!confirm('Cancel your enrollment?')) return;

        const response = await fetch('../ajax/enroll.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({action: 'cancel', session_id: sessionId})
        });

        const data = await response.json();

        if (data.success) {
            location.reload();
        } else {
            alert(data.message);
        }
    });
});