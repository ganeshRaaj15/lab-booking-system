<?php if (! empty($charts)): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js"></script>
    <script>
        (() => {
            const charts = <?= json_encode($charts, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
            charts.forEach((chart) => {
                const canvas = document.getElementById(chart.id);
                if (!canvas) {
                    return;
                }

                const options = {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: true,
                            position: chart.type === 'doughnut' ? 'bottom' : 'top'
                        }
                    }
                };

                if (chart.type === 'bar' || chart.type === 'line') {
                    options.scales = {
                        y: {
                            beginAtZero: true
                        }
                    };
                }

                new Chart(canvas.getContext('2d'), {
                    type: chart.type,
                    data: chart.data,
                    options
                });
            });
        })();
    </script>
<?php endif; ?>
