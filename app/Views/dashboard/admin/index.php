<?php
/** @var array $stats */
/** @var array $trends */
/** @var array $facultyBreakdown */
/** @var array $pendingPic */
/** @var array $pendingMgr */
/** @var array $approved */
/** @var array $rejected */
$stats            = $stats ?? [];
$trends           = $trends ?? [];
$facultyBreakdown = $facultyBreakdown ?? [];
$pendingPic       = $pendingPic ?? [];
$pendingMgr       = $pendingMgr ?? [];
$approved         = $approved ?? [];
$rejected         = $rejected ?? [];
?>
<?= $this->extend('layouts/main_admin') ?>

<?= $this->section('content') ?>

<div class="admin-dashboard">

    <!-- PAGE HEADER -->
    <div class="slams-page-header">
        <div class="slams-page-header-left">
            <h1 class="slams-page-title">Admin Dashboard</h1>
            <p class="slams-page-subtitle">System overview &amp; real-time analytics</p>
        </div>
        <div class="slams-page-header-actions">
            <a href="/dashboard/reports/pdf" class="btn btn-glass btn-sm">
                <i class="bi bi-file-earmark-pdf me-1"></i> Report
            </a>
            <a href="/dashboard/reports/csv" class="btn btn-glass btn-sm">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
            </a>
            <div class="quick-stat">
                <i class="bi bi-calendar-week"></i>
                <div>
                    <div class="slams-text-xs text-muted">Today</div>
                    <div class="fw-bold slams-text-sm"><?= date('d M Y') ?></div>
                </div>
            </div>
        </div>
    </div>

    <!-- KPI WIDGETS -->
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="slams-kpi slams-kpi-warning">
                <div class="slams-kpi-head">
                    <div>
                        <div class="slams-kpi-label">Pending PIC</div>
                        <div class="slams-kpi-value"><?= esc($stats['pending'] ?? 0) ?></div>
                    </div>
                    <div class="slams-kpi-icon slams-kpi-icon--warning">
                        <i class="bi bi-clock-history"></i>
                    </div>
                </div>
                <div class="slams-kpi-footer">Awaiting PIC verification</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="slams-kpi slams-kpi-info">
                <div class="slams-kpi-head">
                    <div>
                        <div class="slams-kpi-label">Pending Manager</div>
                        <div class="slams-kpi-value"><?= esc($stats['pending_mgr'] ?? 0) ?></div>
                    </div>
                    <div class="slams-kpi-icon slams-kpi-icon--info">
                        <i class="bi bi-check2-square"></i>
                    </div>
                </div>
                <div class="slams-kpi-footer">Awaiting manager approval</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="slams-kpi slams-kpi-success">
                <div class="slams-kpi-head">
                    <div>
                        <div class="slams-kpi-label">Approved</div>
                        <div class="slams-kpi-value"><?= esc($stats['approved'] ?? 0) ?></div>
                    </div>
                    <div class="slams-kpi-icon slams-kpi-icon--success">
                        <i class="bi bi-check-circle"></i>
                    </div>
                </div>
                <div class="slams-kpi-footer">Successfully approved</div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="slams-kpi slams-kpi-danger">
                <div class="slams-kpi-head">
                    <div>
                        <div class="slams-kpi-label">Rejected</div>
                        <div class="slams-kpi-value"><?= esc($stats['rejected'] ?? 0) ?></div>
                    </div>
                    <div class="slams-kpi-icon slams-kpi-icon--danger">
                        <i class="bi bi-x-circle"></i>
                    </div>
                </div>
                <div class="slams-kpi-footer">Bookings declined</div>
            </div>
        </div>
    </div>

    <!-- STATUS SUMMARY BAR -->
    <div class="card border-0 mb-4">
        <div class="card-body py-2 px-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="fw-bold slams-text-sm me-1">Booking Status</span>
                <span class="status-badge status-badge-neutral">Total: <?= esc($stats['total'] ?? 0) ?></span>
                <span class="status-badge status-badge-pending">Pending PIC: <?= esc($stats['pending'] ?? 0) ?></span>
                <span class="status-badge status-badge-review">Pending Manager: <?= esc($stats['pending_mgr'] ?? 0) ?></span>
                <span class="status-badge status-badge-approved">Approved: <?= esc($stats['approved'] ?? 0) ?></span>
                <span class="status-badge status-badge-rejected">Rejected: <?= esc($stats['rejected'] ?? 0) ?></span>
                <span class="status-badge status-badge-cancelled">Cancelled: <?= esc($stats['cancelled'] ?? 0) ?></span>
            </div>
        </div>
    </div>

    <!-- CHARTS ROW -->
    <div class="row g-4 mb-4">
        <!-- Booking Trends -->
        <div class="col-lg-8">
            <div class="glass-card h-100">
                <div class="glass-card-header d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0" style="font-family:var(--slams-font-display);">
                        <i class="bi bi-graph-up me-2" style="color:var(--slams-primary);"></i>
                        Booking Trends
                    </h5>
                    <span class="slams-text-xs text-muted">Last 6 months</span>
                </div>
                <div class="card-body p-4">
                    <div class="chart-container-wrapper">
                        <div class="chart-container">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Faculty Breakdown -->
        <div class="col-lg-4">
            <div class="glass-card h-100">
                <div class="glass-card-header d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0" style="font-family:var(--slams-font-display);">
                        <i class="bi bi-pie-chart me-2" style="color:var(--slams-primary);"></i>
                        Faculty Mix
                    </h5>
                    <span class="stat-badge">Total: <?= array_sum(array_column($facultyBreakdown, 'total')) ?></span>
                </div>
                <div class="card-body p-4 d-flex flex-column">
                    <div class="chart-container-wrapper" style="height: 160px;">
                        <div class="chart-container">
                            <canvas id="facultyChart"></canvas>
                        </div>
                    </div>
                    <div class="chart-legend-container mt-3">
                        <div class="row g-2">
                            <?php
                            $facultyColors = generateFacultyColors(count($facultyBreakdown));
                            $i = 0;
                            foreach ($facultyBreakdown as $faculty):
                                $color = $facultyColors[$i++];
                            ?>
                            <div class="col-6">
                                <div class="d-flex align-items-center gap-2 mb-1">
                                    <span class="legend-dot flex-shrink-0" style="background:<?= $color ?>; width:10px;height:10px;border-radius:50%;display:inline-block;"></span>
                                    <span class="text-truncate slams-text-xs" title="<?= esc($faculty['faculty']) ?>"><?= esc($faculty['faculty']) ?></span>
                                    <span class="ms-auto fw-bold slams-text-xs"><?= $faculty['total'] ?></span>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- APPROVAL QUEUE -->
    <div class="glass-card mb-4">
        <div class="glass-card-header d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2">
            <div>
                <h5 class="fw-bold mb-1" style="font-family:var(--slams-font-display);">
                    <i class="bi bi-list-check me-2" style="color:var(--slams-primary);"></i>
                    Approval Queue
                </h5>
                <p class="slams-text-xs text-muted mb-0">Manage booking approvals across all stages</p>
            </div>
            <span class="stat-badge">
                <i class="bi bi-clock me-1"></i>Total Pending: <?= ($stats['pending'] ?? 0) + ($stats['pending_mgr'] ?? 0) ?>
            </span>
        </div>

        <div class="card-body p-4">
            <ul class="nav nav-tabs mb-4" id="approvalTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#pendingPicTab">
                        <i class="bi bi-clock-history me-1"></i>
                        <span class="d-none d-md-inline">Pending PIC</span>
                        <span class="badge bg-warning ms-1"><?= count($pendingPic) ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#pendingMgrTab">
                        <i class="bi bi-check2-square me-1"></i>
                        <span class="d-none d-md-inline">Pending Manager</span>
                        <span class="badge bg-primary ms-1"><?= count($pendingMgr) ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#approvedTab">
                        <i class="bi bi-check-circle me-1"></i>
                        <span class="d-none d-md-inline">Approved</span>
                        <span class="badge bg-success ms-1"><?= count($approved) ?></span>
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#rejectedTab">
                        <i class="bi bi-x-circle me-1"></i>
                        <span class="d-none d-md-inline">Rejected</span>
                        <span class="badge bg-danger ms-1"><?= count($rejected) ?></span>
                    </button>
                </li>
            </ul>

            <div class="tab-content">
                <div class="tab-pane fade show active" id="pendingPicTab">
                    <div class="table-responsive">
                        <?php include('partials/table_pending_pic.php'); ?>
                    </div>
                </div>
                <div class="tab-pane fade" id="pendingMgrTab">
                    <div class="table-responsive">
                        <?php include('partials/table_pending_manager.php'); ?>
                    </div>
                </div>
                <div class="tab-pane fade" id="approvedTab">
                    <div class="table-responsive">
                        <?php include('partials/table_approved.php'); ?>
                    </div>
                </div>
                <div class="tab-pane fade" id="rejectedTab">
                    <div class="table-responsive">
                        <?php include('partials/table_rejected.php'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- BOOKING DETAILS MODAL -->
<div class="modal fade" id="adminBookingModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold" style="font-family:var(--slams-font-display);">
                    <i class="bi bi-journal-text me-2" style="color:var(--slams-primary);"></i>Booking Details
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="adminBookingBody">
                <!-- populated by JS -->
            </div>
            <div class="modal-footer" id="adminBookingFooter" style="display:none;">
                <button type="button" class="btn btn-glass" data-bs-dismiss="modal">Close</button>
                <form id="adminRejectForm" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-outline-danger px-4">
                        <i class="bi bi-x-lg me-1"></i>Reject
                    </button>
                </form>
                <form id="adminApproveForm" method="post" class="d-inline">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-check-lg me-1"></i>Approve
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- CHART JS -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
<?php
function generateFacultyColors($count) {
    $basePalette = [
        '#1f77b4', '#ff7f0e', '#2ca02c', '#d62728', '#9467bd', '#8c564b',
        '#e377c2', '#7f7f7f', '#bcbd22', '#17becf', '#393b79', '#5254a3'
    ];
    $extendedPalette = [
        '#6b6ecf', '#9c9ede', '#637939', '#8ca252', '#b5cf6b', '#cedb9c',
        '#8c6d31', '#bd9e39', '#e7ba52', '#843c39', '#ad494a', '#d6616b',
        '#e7969c', '#7b4173', '#a55194', '#ce6dbd', '#de9ed6'
    ];
    $fullPalette = array_merge($basePalette, $extendedPalette);
    if ($count > count($fullPalette)) {
        $generatedColors = [];
        for ($i = 0; $i < $count; $i++) {
            $hue = ($i * 137.508) % 360;
            $saturation = 70 + (($i % 3) * 10);
            $lightness = 45 + (($i % 2) * 10);
            $generatedColors[] = hslToHex($hue, $saturation, $lightness);
        }
        return $generatedColors;
    }
    return array_slice($fullPalette, 0, $count);
}

function hslToHex($h, $s, $l) {
    $h /= 360; $s /= 100; $l /= 100;
    $r = $g = $b = $l;
    $v = ($l <= 0.5) ? ($l * (1.0 + $s)) : ($l + $s - $l * $s);
    if ($v > 0) {
        $m = $l + $l - $v; $sv = ($v - $m) / $v;
        $h *= 6.0; $sextant = floor($h); $fract = $h - $sextant;
        $vsf = $v * $sv * $fract; $mid1 = $m + $vsf; $mid2 = $v - $vsf;
        switch ($sextant) {
            case 0: $r=$v; $g=$mid1; $b=$m; break;
            case 1: $r=$mid2; $g=$v; $b=$m; break;
            case 2: $r=$m; $g=$v; $b=$mid1; break;
            case 3: $r=$m; $g=$mid2; $b=$v; break;
            case 4: $r=$mid1; $g=$m; $b=$v; break;
            case 5: $r=$v; $g=$m; $b=$mid2; break;
        }
    }
    return sprintf("#%02x%02x%02x", round($r*255), round($g*255), round($b*255));
}

$facultyColors = generateFacultyColors(count($facultyBreakdown));
$facultyChartData = [];
$i = 0;
foreach ($facultyBreakdown as $faculty) {
    $facultyChartData[] = [
        'label' => $faculty['faculty'],
        'value' => $faculty['total'],
        'color' => $facultyColors[$i]
    ];
    $i++;
}
?>

const trendCtx = document.getElementById('trendChart');
new Chart(trendCtx, {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($trends, 'month')) ?>,
        datasets: [{
            label: "Total Bookings",
            data: <?= json_encode(array_column($trends, 'total')) ?>,
            borderColor: "#3b82f6",
            backgroundColor: "rgba(59, 130, 246, 0.12)",
            pointBackgroundColor: "#1e40af",
            pointBorderColor: "#ffffff",
            pointBorderWidth: 2,
            pointRadius: 5,
            pointHoverRadius: 7,
            borderWidth: 2.5,
            tension: 0.4,
            fill: true
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                titleColor: '#fff',
                bodyColor: '#e2e8f0',
                borderColor: '#3b82f6',
                borderWidth: 1,
                cornerRadius: 8,
                padding: 10,
                displayColors: false
            }
        },
        scales: {
            y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { color: '#64748b' } },
            x: { grid: { color: 'rgba(0,0,0,0.05)' }, ticks: { color: '#64748b' } }
        }
    }
});

const facultyCtx = document.getElementById('facultyChart');
const facultyChartData = <?= json_encode($facultyChartData) ?>;
new Chart(facultyCtx, {
    type: 'doughnut',
    data: {
        labels: facultyChartData.map(d => d.label),
        datasets: [{
            data: facultyChartData.map(d => d.value),
            backgroundColor: facultyChartData.map(d => d.color),
            borderColor: '#ffffff',
            borderWidth: 2,
            borderRadius: 6,
            spacing: 3
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                titleColor: '#fff',
                bodyColor: '#e2e8f0',
                cornerRadius: 8,
                padding: 10,
                callbacks: {
                    label: ctx => {
                        const total = ctx.dataset.data.reduce((a, b) => a + b, 0);
                        return `${ctx.label}: ${ctx.raw} (${Math.round(ctx.raw/total*100)}%)`;
                    }
                }
            }
        },
        cutout: '65%'
    }
});

function adminViewBooking(id) {
    const modalEl = document.getElementById('adminBookingModal');
    const body    = document.getElementById('adminBookingBody');
    const footer  = document.getElementById('adminBookingFooter');

    if (window.slamsPrepareModal) {
        window.slamsPrepareModal(modalEl);
    } else if (modalEl.parentElement !== document.body) {
        document.body.appendChild(modalEl);
    }

    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    footer.style.display = 'none';
    body.innerHTML = `<div class="d-flex justify-content-center py-5"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div></div>`;
    modal.show();

    fetch(`/dashboard/admin/booking-details/${id}`, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
    .then(r => r.json())
    .then(data => {
        if (data.status !== 'success') {
            body.innerHTML = `<div class="alert alert-danger">${data.message}</div>`;
            return;
        }
        const b = data.booking;
        const isPending = b.status === 'PENDING';

        let assetsHtml = '<p class="text-muted mb-0">No assets selected.</p>';
        if (b.assets && b.assets.length > 0) {
            assetsHtml = '<div class="row g-2">';
            b.assets.forEach(a => {
                assetsHtml += `<div class="col-md-6"><div class="d-flex align-items-center gap-2 border rounded p-2">${a.image ? `<img src="${a.image}" width="36" height="36" class="rounded object-fit-cover" alt="${a.name}">` : '<i class="bi bi-tools fs-5 text-muted"></i>'}<div><div class="fw-semibold slams-text-sm">${a.name}</div><div class="text-muted slams-text-xs">Qty: ${a.quantity_used}</div></div></div></div>`;
            });
            assetsHtml += '</div>';
        }

        const pdfHtml = b.pdf_url ? `<a href="${b.pdf_url}" target="_blank" class="btn btn-outline-primary btn-sm mt-2"><i class="bi bi-file-pdf me-1"></i>View PDF</a>` : '';

        const statusClass = b.status === 'APPROVED' ? 'status-badge-approved' : b.status === 'REJECTED' ? 'status-badge-rejected' : 'status-badge-pending';

        body.innerHTML = `
            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <div class="card border h-100"><div class="card-body">
                        <div class="slams-form-section-header">Laboratory</div>
                        <div class="fw-bold">${b.lab_name}</div>
                        <div class="text-muted slams-text-sm">Room ${b.lab_room || '—'}</div>
                        ${b.pic_name ? `<div class="text-muted slams-text-sm mt-1">PIC: ${b.pic_name}</div>` : ''}
                    </div></div>
                </div>
                <div class="col-md-6">
                    <div class="card border h-100"><div class="card-body">
                        <div class="slams-form-section-header">Schedule</div>
                        <div class="slams-text-sm mb-1"><strong>Date:</strong> ${b.date}</div>
                        <div class="slams-text-sm mb-1"><strong>Time:</strong> ${b.start_time?.substring(0,5)} – ${b.end_time?.substring(0,5)}</div>
                        <div class="slams-text-sm mb-2"><strong>Faculty:</strong> ${b.faculty_name || '—'}</div>
                        <span class="status-badge ${statusClass}">${b.status}</span>
                    </div></div>
                </div>
            </div>
            <div class="card border mb-3"><div class="card-body">
                <div class="slams-form-section-header">Activity</div>
                <p class="mb-0">${b.activity || '—'}</p>
            </div></div>
            <div class="card border mb-3"><div class="card-body">
                <div class="slams-form-section-header">Supervisor</div>
                <div class="row g-1 slams-text-sm">
                    <div class="col-md-4"><strong>Name:</strong> ${b.supervisor_name || '—'}</div>
                    <div class="col-md-4"><strong>Email:</strong> ${b.supervisor_email || '—'}</div>
                    <div class="col-md-4"><strong>Phone:</strong> ${b.supervisor_phone || '—'}</div>
                </div>
            </div></div>
            <div class="card border mb-3"><div class="card-body">
                <div class="slams-form-section-header">Equipment</div>
                ${assetsHtml}
                ${pdfHtml}
            </div></div>`;

        if (isPending) {
            document.getElementById('adminApproveForm').action = `/booking/approve/${b.id}`;
            document.getElementById('adminRejectForm').action  = `/booking/reject/${b.id}`;
            footer.style.display = '';
        }
    })
    .catch(() => {
        body.innerHTML = `<div class="alert alert-danger">Could not load booking details.</div>`;
    });
}
</script>

<?= $this->endSection() ?>
