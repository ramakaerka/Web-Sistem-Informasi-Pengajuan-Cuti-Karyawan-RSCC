document.addEventListener('livewire:init', () => {
    // Dapatkan komponen dengan cara yang lebih reliable
    const component = @this;
    
    function renderChart() {
        const ctx = document.getElementById('cutiTrendChart')?.getContext('2d');
        if (!ctx) {
            console.error('Canvas context tidak tersedia');
            return;
        }

        // Hancurkan chart sebelumnya jika ada
        if (window.cutiChart) {
            window.cutiChart.destroy();
        }

        // Buat chart baru
        window.cutiChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: component.chartData.labels,
                datasets: [{
                    label: 'Jumlah Cuti',
                    data: component.chartData.values,
                    backgroundColor: 'rgba(58, 113, 213, 0.2)',
                    borderColor: 'rgba(58, 113, 213, 1)',
                    borderWidth: 2,
                    tension: 0.3,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { 
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }

    // Render awal
    renderChart();

    // Update saat data berubah
    Livewire.on('updateChartData', () => {
        renderChart();
    });
});