function equipmentWidthClass(percentage) {
    const rounded = Math.round(Math.max(0, Math.min(100, percentage)) / 10) * 10;
    return 'avail-w-' + rounded;
}

function updateEquipmentWidth(fill, percentage) {
    fill.classList.forEach(className => {
        if (className.startsWith('avail-w-')) fill.classList.remove(className);
    });

    fill.classList.add(equipmentWidthClass(percentage));
}

function refreshEquipment() {
    fetch('../ajax/equipment-status.php')
        .then(response => response.json())
        .then(data => {
            data.forEach(eq => {
                const card = document.querySelector(`[data-eq-id="${eq.id}"]`);
                if (!card) return;

                let effectiveStatus = eq.status;
                if (parseInt(eq.available) === 0) effectiveStatus = 'unavailable';

                const availText = card.querySelector('[data-avail]');
                if (availText) availText.textContent = `${eq.available} / ${eq.quantity}`;

                const fill = card.querySelector('.avail-fill');
                if (fill) {
                    const percentage = eq.quantity > 0 ? (eq.available / eq.quantity) * 100 : 0;
                    fill.className = 'avail-fill';
                    if (percentage <= 25) fill.classList.add('low');
                    else if (percentage <= 60) fill.classList.add('medium');
                    updateEquipmentWidth(fill, percentage);
                }

                const badge = card.querySelector('[data-status-badge]');
                if (badge) {
                    const labels = { available: 'Available', maintenance: 'Maintenance', unavailable: 'Unavailable' };
                    badge.textContent = labels[effectiveStatus] || effectiveStatus;
                    badge.className = 'badge ' + (effectiveStatus === 'available' ? 'badge-success' : effectiveStatus === 'maintenance' ? 'badge-warning' : 'badge-danger');
                }

                card.classList.remove('status-available', 'status-maintenance', 'status-unavailable');
                card.classList.add('status-' + effectiveStatus);
            });

            const lastUpdate = document.getElementById('lastUpdate');
            if (lastUpdate) lastUpdate.textContent = 'Updated: ' + new Date().toLocaleTimeString('pt-PT');
        })
        .catch(error => console.warn('Live update error:', error));
}

document.addEventListener('click', function (event) {
    if (event.target.classList.contains('delete-equipment-link')) {
        if (!confirm('Delete this equipment?')) event.preventDefault();
    }
});

refreshEquipment();
setInterval(refreshEquipment, 30000);