/* assets/js/dashboard.js */

document.addEventListener('DOMContentLoaded', function() {
    // 1. Initialize Role-Based Charts
    const charts = document.querySelectorAll('canvas');
    
    charts.forEach(canvas => {
        const id = canvas.id;
        const ctx = canvas.getContext('2d');
        let chartConfig = {};

        // Simplified Chart Configs for Demo
        if (id === 'exec_chart') {
            chartConfig = {
                type: 'bar',
                data: {
                    labels: ['Project A', 'Project B', 'Project C'],
                    datasets: [{
                        label: 'Progress %',
                        data: [72, 45, 90],
                        backgroundColor: '#ffcc00',
                        borderColor: '#ffcc00',
                        borderWidth: 1
                    }]
                },
                options: getChartOptions()
            };
        } else if (id === 'finance_chart') {
            chartConfig = {
                type: 'line',
                data: {
                    labels: ['W1', 'W2', 'W3', 'W4'],
                    datasets: [{
                        label: 'Expense Flow',
                        data: [12000, 19000, 15000, 22000],
                        borderColor: '#ffcc00',
                        backgroundColor: 'rgba(255, 204, 0, 0.1)',
                        fill: true,
                        tension: 0.4
                    }]
                },
                options: getChartOptions()
            };
        } else {
            // Default generic chart for other roles
            chartConfig = {
                type: 'doughnut',
                data: {
                    labels: ['Used', 'Remaining'],
                    datasets: [{
                        data: [60, 40],
                        backgroundColor: ['#ffcc00', 'rgba(255, 255, 255, 0.1)'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } }
                }
            };
        }

        new Chart(ctx, chartConfig);
    });
});

function getChartOptions() {
    return {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                grid: { color: 'rgba(255, 255, 255, 0.05)' },
                ticks: { color: '#a0a0a0' }
            },
            x: {
                grid: { display: false },
                ticks: { color: '#a0a0a0' }
            }
        }
    };
}
