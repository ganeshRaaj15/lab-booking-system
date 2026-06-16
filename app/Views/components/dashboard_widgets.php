<?php
/**
 * Dashboard Widgets (KPI Cards + Charts)
 * Usage:
 *   echo view('components/dashboard_widgets', [
 *       'stats' => $stats,
 *       'trends' => $trends
 *   ]);
 */
?>

<!-- KPI CARDS -->
<div class="row g-3 mb-4">

    <!-- Total Bookings -->
    <div class="col-md-3 col-sm-6">
        <div class="slams-kpi slams-kpi-primary">
            <div class="slams-kpi-head">
                <div>
                    <div class="slams-kpi-label">Total Bookings</div>
                    <div class="slams-kpi-value"><?= $stats['total'] ?? 0 ?></div>
                </div>
                <div class="slams-kpi-icon">
                    <i class="bi bi-calendar3"></i>
                </div>
            </div>
            <div class="slams-kpi-footer">All submitted requests</div>
        </div>
    </div>

    <!-- Pending -->
    <div class="col-md-3 col-sm-6">
        <div class="slams-kpi slams-kpi-warning">
            <div class="slams-kpi-head">
                <div>
                    <div class="slams-kpi-label">Pending</div>
                    <div class="slams-kpi-value"><?= $stats['pending'] ?? 0 ?></div>
                </div>
                <div class="slams-kpi-icon slams-kpi-icon--warning">
                    <i class="bi bi-clock-history"></i>
                </div>
            </div>
            <div class="slams-kpi-footer">Awaiting review</div>
        </div>
    </div>

    <!-- Approved -->
    <div class="col-md-3 col-sm-6">
        <div class="slams-kpi slams-kpi-success">
            <div class="slams-kpi-head">
                <div>
                    <div class="slams-kpi-label">Approved</div>
                    <div class="slams-kpi-value"><?= $stats['approved'] ?? 0 ?></div>
                </div>
                <div class="slams-kpi-icon slams-kpi-icon--success">
                    <i class="bi bi-check-circle"></i>
                </div>
            </div>
            <div class="slams-kpi-footer">Successfully confirmed</div>
        </div>
    </div>

    <!-- Rejected -->
    <div class="col-md-3 col-sm-6">
        <div class="slams-kpi slams-kpi-danger">
            <div class="slams-kpi-head">
                <div>
                    <div class="slams-kpi-label">Rejected</div>
                    <div class="slams-kpi-value"><?= $stats['rejected'] ?? 0 ?></div>
                </div>
                <div class="slams-kpi-icon slams-kpi-icon--danger">
                    <i class="bi bi-x-circle"></i>
                </div>
            </div>
            <div class="slams-kpi-footer">Not approved</div>
        </div>
    </div>

</div>

<!-- MONTHLY TREND CHART -->
<div class="card border-0">
    <div class="card-header glass-card-header">
        <span class="fw-bold" style="font-family: var(--slams-font-display);">Monthly Booking Trend</span>
    </div>
    <div class="card-body">
        <canvas id="trendChart" height="100"></canvas>
    </div>
</div>


<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const ctx = document.getElementById('trendChart');
    if (!ctx || typeof Chart === 'undefined') {
        return;
    }

    const styles = getComputedStyle(document.documentElement);
    const primary = styles.getPropertyValue('--slams-primary').trim() || '#0f766e';

    const trendLabels = <?= json_encode(array_column($trends, 'month')) ?>;
    const trendData   = <?= json_encode(array_column($trends, 'total')) ?>;

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: trendLabels,
            datasets: [{
                label: 'Bookings Per Month',
                data: trendData,
                fill: true,
                tension: 0.4,
                borderWidth: 2,
                borderColor: primary,
                backgroundColor: 'rgba(15, 118, 110, 0.14)'
            }]
        },
        options: {
            plugins: { legend: { display: false } },
            scales: {
                x: { grid: { color: 'rgba(0,0,0,0.05)' } },
                y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' } }
            }
        }
    });
});
</script>
