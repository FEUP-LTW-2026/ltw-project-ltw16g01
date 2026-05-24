function equipmentWidthClass(percentage) {
    const rounded = Math.round(Math.max(0, Math.min(100, percentage)) / 10) * 10;
    return 'avail-w-' + rounded;
}

function updateEquipmentWidth(fill, percentage) {
    fill.classList.forEach(className => {
        if (className.startsWith('avail-w-')) {
            fill.classList.remove(className);
        }
    });

    fill.classList.add(equipmentWidthClass(percentage));
}

function refreshEquipment() {
    fetch('../ajax/equipment-status.php')
        .then(response => response.json())
        .then(data => {
            data.forEach(eq => {
                const card = document.querySelector(`[data-eq-id="${eq.id}"]`);

                if (!card) {
                    return;
                }

                let effectiveStatus = eq.status;

                if (parseInt(eq.available) === 0) {
                    effectiveStatus = 'unavailable';
                }

                const availText = card.querySelector('[data-avail]');

                if (availText) {
                    availText.textContent = `${eq.available} / ${eq.quantity}`;
                }

                const fill = card.querySelector('.avail-fill');

                if (fill) {
                    const percentage = eq.quantity > 0 ? (eq.available / eq.quantity) * 100 : 0;

                    fill.className = 'avail-fill';

                    if (percentage <= 25) {
                        fill.classList.add('low');
                    } else if (percentage <= 60) {
                        fill.classList.add('medium');
                    }

                    updateEquipmentWidth(fill, percentage);
                }

                const badge = card.querySelector('[data-status-badge]');

                if (badge) {
                    const labels = {
                        available: 'Available',
                        maintenance: 'Maintenance',
                        unavailable: 'Unavailable'
                    };

                    badge.textContent = labels[effectiveStatus] || effectiveStatus;

                    badge.className = 'badge ' + (
                        effectiveStatus === 'available'
                            ? 'badge-success'
                            : effectiveStatus === 'maintenance'
                                ? 'badge-warning'
                                : 'badge-danger'
                    );
                }

                card.classList.remove('status-available', 'status-maintenance', 'status-unavailable');
                card.classList.add('status-' + effectiveStatus);
            });

            const lastUpdate = document.getElementById('lastUpdate');

            if (lastUpdate) {
                lastUpdate.textContent = 'Updated: ' + new Date().toLocaleTimeString('pt-PT');
            }
        })
        .catch(error => console.warn('Live update error:', error));
}

document.addEventListener('click', function (event) {
    if (event.target.classList.contains('delete-equipment-link')) {
        if (!confirm('Delete this equipment?')) {
            event.preventDefault();
        }
    }
});

document.addEventListener('submit', function (event) {
    const form = event.target.closest('.reserve-equipment-form');

    if (!form) {
        return;
    }

    event.preventDefault();

    const equipmentId = form.querySelector('[name="equipment_id"]').value;
    const reservationDate = form.querySelector('[name="reservation_date"]').value;
    const reservationTime = form.querySelector('[name="reservation_time"]').value;
    const quantity = form.querySelector('[name="quantity"]').value;

    const submitButton = form.querySelector('button[type="submit"]');

    if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = 'Reserving...';
    }

    fetch('../ajax/reserve_equipment.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            equipment_id: equipmentId,
            reservation_date: reservationDate,
            reservation_time: reservationTime,
            quantity: quantity
        })
    })
        .then(response => response.json())
        .then(data => {
            alert(data.message);

            if (data.success) {
                location.reload();
            }
        })
        .catch(error => {
            console.error('Equipment reservation error:', error);
            alert('Something went wrong.');
        })
        .finally(() => {
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.textContent = 'Reserve';
            }
        });
});

refreshEquipment();
setInterval(refreshEquipment, 30000);