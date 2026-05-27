    document.querySelectorAll('.mark-read-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            const id = this.dataset.id;
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `<input type="hidden" name="action" value="mark_read"><input type="hidden" name="notification_id" value="${id}">`;
            document.body.appendChild(form);
            form.submit();
        });
    });
    document.querySelectorAll('.delete-notif-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Delete this notification?')) return;
            const id = this.dataset.id;
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `<input type="hidden" name="action" value="delete"><input type="hidden" name="notification_id" value="${id}">`;
            document.body.appendChild(form);
            form.submit();
        });
    });