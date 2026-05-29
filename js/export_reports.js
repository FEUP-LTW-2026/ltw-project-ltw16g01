// Applies dynamic bar widths and opens the print dialog.
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('[data-bar-width]').forEach(function (bar) {
        const rawValue = parseFloat(bar.dataset.barWidth);
        const width = Math.max(0, Math.min(100, isNaN(rawValue) ? 0 : rawValue));

        bar.style.width = width + '%';
    });

    window.print();
});