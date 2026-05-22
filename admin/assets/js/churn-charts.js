/**
 * Churn Dashboard — Chart Initialization
 * 
 * Renders the donut chart (risk level distribution) and 
 * bar chart (churn probability distribution) using Chart.js.
 * 
 * Expects global variables: window.churnChartData = { counts, distData }
 */
(function () {
    const data = window.churnChartData;
    if (!data) return;

    const { counts, distData } = data;

    // Donut chart — Risk Level Distribution
    new Chart(document.getElementById('donutChart'), {
        type: 'doughnut',
        data: {
            labels: ['Critical', 'At Risk', 'Loyal'],
            datasets: [{
                data: [counts.high_c, counts.med_c, counts.low_c],
                backgroundColor: ['#ef4444', '#eab308', '#22c55e'],
                borderWidth: 0,
                borderRadius: 4
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            cutout: '65%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { color: '#64748b', font: { size: 11 }, padding: 16, boxWidth: 12 }
                }
            }
        }
    });

    // Bar chart — Probability Distribution
    new Chart(document.getElementById('barChart'), {
        type: 'bar',
        data: {
            labels: ['0–0.25\n(Very Low)', '0.25–0.50\n(Low)', '0.50–0.75\n(Medium)', '0.75–1.00\n(High)'],
            datasets: [{
                label: 'Customers',
                data: [distData.d1, distData.d2, distData.d3, distData.d4],
                backgroundColor: ['#86efac', '#fde047', '#fb923c', '#f87171'],
                borderRadius: 6,
                borderSkipped: false
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: {
                y: { beginAtZero: true, grid: { color: '#f1f5f9' }, ticks: { color: '#94a3b8', font: { size: 11 } } },
                x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 10 } } }
            }
        }
    });
})();
