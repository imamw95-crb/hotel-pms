document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') return;

    const occupancyCanvas = document.getElementById('occupancyChart');
    const revenueCanvas = document.getElementById('revenueChart');

    if (occupancyCanvas) {
        const labels = JSON.parse(occupancyCanvas.dataset.labels || '[]');
        const data = JSON.parse(occupancyCanvas.dataset.occupancy || '[]');

        new Chart(occupancyCanvas, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Okupansi (%)',
                    data: data,
                    borderColor: '#3b82f6',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    fill: true
                }]
            }
        });
    }

    if (revenueCanvas) {
        const labels = JSON.parse(revenueCanvas.dataset.labels || '[]');
        const data = JSON.parse(revenueCanvas.dataset.revenue || '[]');

        new Chart(revenueCanvas, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: data,
                    backgroundColor: '#10b981'
                }]
            }
        });
    }
});
