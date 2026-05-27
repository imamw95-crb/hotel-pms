document.addEventListener('DOMContentLoaded', function() {
    if (typeof Chart === 'undefined') return;

    var occupancyCanvas = document.getElementById('occupancyChart');
    var revenueCanvas = document.getElementById('revenueChart');

    if (occupancyCanvas) {
        var labels = JSON.parse(occupancyCanvas.dataset.labels || '[]');
        var data = JSON.parse(occupancyCanvas.dataset.occupancy || '[]');

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
        var labels = JSON.parse(revenueCanvas.dataset.labels || '[]');
        var data = JSON.parse(revenueCanvas.dataset.revenue || '[]');

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
