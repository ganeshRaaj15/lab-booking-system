<?= $this->extend('layouts/main_user') ?>

<?= $this->section('content') ?>

<?php $filters = $filters ?? ['q' => '', 'status' => '', 'date_from' => '', 'date_to' => '']; ?>

<!-- ========================================================= -->
<!-- PAGE HEADER -->
<!-- ========================================================= -->
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold text-primary mb-0">External User Dashboard</h2>
        <p class="text-muted small mb-0">Welcome, <?= esc($user->full_name ?? $user->username ?? 'User') ?>!</p>
    </div>
</div>

<!-- ========================================================= -->
<!-- IMPORTANT NOTICE -->
<!-- ========================================================= -->
<div class="alert alert-info border-0 shadow-sm p-3 mb-4"
     style="border-left: 5px solid #2563eb; border-radius: 8px;">
    <div class="d-flex">
        <i class="bi bi-exclamation-circle text-primary fs-4 me-3"></i>
        <div>
            <strong>Important:</strong><br>
            External users <strong>cannot submit bookings directly</strong> through the system.<br>
            To make a booking, please contact the lab's <strong>Person-in-Charge (PIC)</strong>.
        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- STAT WIDGETS -->
<!-- ========================================================= -->
<div class="row g-3 mb-4">

    <!-- Pending -->
    <div class="col-md-3">
        <div class="card widget-card shadow-sm border-0 bg-gradient-warning text-dark">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="small fw-semibold text-uppercase opacity-75">Pending</div>
                    <div class="fs-3 fw-bold"><?= esc($stats['pending']) ?></div>
                </div>
                <i class="bi bi-hourglass-split fs-1 opacity-50"></i>
            </div>
        </div>
    </div>

    <!-- Approved -->
    <div class="col-md-3">
        <div class="card widget-card shadow-sm border-0 bg-gradient-success text-white">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="small fw-semibold text-uppercase opacity-75">Approved</div>
                    <div class="fs-3 fw-bold"><?= esc($stats['approved']) ?></div>
                </div>
                <i class="bi bi-check-circle fs-1 opacity-75"></i>
            </div>
        </div>
    </div>

    <!-- Rejected -->
    <div class="col-md-3">
        <div class="card widget-card shadow-sm border-0 bg-gradient-danger text-white">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="small fw-semibold text-uppercase opacity-75">Rejected</div>
                    <div class="fs-3 fw-bold"><?= esc($stats['rejected']) ?></div>
                </div>
                <i class="bi bi-x-circle fs-1 opacity-75"></i>
            </div>
        </div>
    </div>

    <div class="col-md-3">
        <div class="card widget-card shadow-sm border-0 bg-secondary text-white">
            <div class="card-body d-flex align-items-center">
                <div class="flex-grow-1">
                    <div class="small fw-semibold text-uppercase opacity-75">Cancelled</div>
                    <div class="fs-3 fw-bold"><?= esc($stats['cancelled'] ?? 0) ?></div>
                </div>
                <i class="bi bi-slash-circle fs-1 opacity-75"></i>
            </div>
        </div>
    </div>

</div>

<!-- ========================================================= -->
<!-- BOOKING TREND CHART -->
<!-- ========================================================= -->
<?php if (!empty($monthlyCounts)): ?>
<div class="card shadow-sm border-0 mb-4">
    <div class="card-header bg-white">
        <h6 class="fw-bold text-primary mb-0">Your Booking Activity (Past 6 Months)</h6>
    </div>
    <div class="card-body">
        <canvas id="externalTrendChart" height="140"></canvas>
    </div>
</div>
<?php endif; ?>

<!-- ========================================================= -->
<!-- BOOKINGS TABLE -->
<!-- ========================================================= -->
<div class="card shadow-sm border-0">
    <div class="card-header bg-white pb-2">
        <h5 class="fw-semibold mb-0 text-primary">
            <i class="bi bi-calendar-check me-2"></i>Your Bookings
        </h5>
    </div>

    <div class="card-body">
        <form method="get" action="/dashboard/external" class="row g-3 align-items-end mb-4">
            <div class="col-md-4">
                <label class="form-label small text-muted">Search</label>
                <input type="text" name="q" class="form-control form-control-sm" value="<?= esc($filters['q']) ?>" placeholder="Lab, room, or activity">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">Status</label>
                <select name="status" class="form-select form-select-sm">
                    <option value="">All</option>
                    <option value="PENDING" <?= $filters['status'] === 'PENDING' ? 'selected' : '' ?>>Pending</option>
                    <option value="APPROVED" <?= $filters['status'] === 'APPROVED' ? 'selected' : '' ?>>Approved</option>
                    <option value="REJECTED" <?= $filters['status'] === 'REJECTED' ? 'selected' : '' ?>>Rejected</option>
                    <option value="CANCELLED" <?= $filters['status'] === 'CANCELLED' ? 'selected' : '' ?>>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">From</label>
                <input type="date" name="date_from" class="form-control form-control-sm" value="<?= esc($filters['date_from']) ?>">
            </div>
            <div class="col-md-2">
                <label class="form-label small text-muted">To</label>
                <input type="date" name="date_to" class="form-control form-control-sm" value="<?= esc($filters['date_to']) ?>">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary btn-sm flex-fill"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="/dashboard/external" class="btn btn-outline-secondary btn-sm"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>

        <?php if (empty($bookings)): ?>

            <div class="text-center py-4 text-muted">
                <i class="bi bi-calendar-x fs-1 mb-2"></i>
                <p class="mb-1">No bookings found.</p>
                <p class="small">Contact a lab PIC to make an external booking.</p>
            </div>

        <?php else: ?>

            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Lab</th>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Activity</th>
                            <th>Status</th>
                            <th class="text-end">PDF</th>
                        </tr>
                    </thead>

                    <tbody>
                        <?php foreach ($bookings as $b): ?>
                            <tr>
                                <td>
                                    <strong><?= esc($b['lab_name']) ?></strong><br>
                                    <small class="text-muted">Room <?= esc($b['lab_room']) ?></small>
                                </td>

                                <td><?= esc($b['date']) ?></td>
                                <td><?= esc($b['start_time']) ?> to <?= esc($b['end_time']) ?></td>
                                <td><?= esc($b['activity']) ?></td>

                                <td>
                                    <?php
                                        $statusColors = [
                                            'PENDING'  => 'warning text-dark',
                                            'APPROVED' => 'success',
                                            'REJECTED' => 'danger',
                                            'CANCELLED' => 'secondary',
                                        ];
                                        $badge = $statusColors[$b['status']] ?? 'secondary';
                                    ?>
                                    <span class="badge bg-<?= $badge ?>">
                                        <?= esc($b['status']) ?>
                                    </span>
                                </td>

                                <td class="text-end">
                                    <?php if ($b['pdf_path']): ?>
                                        <a href="<?= site_url('document/pdf/' . basename($b['pdf_path'])) ?>" target="_blank"
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-file-earmark-pdf"></i>
                                        </a>
                                    <?php endif; ?>
                                </td>

                            </tr>
                        <?php endforeach; ?>
                    </tbody>

                </table>
            </div>

        <?php endif; ?>

    </div>

</div>

<!-- ========================================================= -->
<!-- CHART JS -->
<!-- ========================================================= -->

<?php if (!empty($monthlyCounts)): ?>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
new Chart(document.getElementById('externalTrendChart'), {
    type: 'line',
    data: {
        labels: <?= json_encode(array_column($monthlyCounts, 'month')) ?>,
        datasets: [{
            label: "Bookings",
            data: <?= json_encode(array_column($monthlyCounts, 'count')) ?>,
            borderColor: "#2563eb",
            backgroundColor: "rgba(37, 99, 235, 0.25)",
            borderWidth: 2,
            tension: 0.4,
            fill: true
        }]
    }
});
</script>
<?php endif; ?>


<!-- ========================================================= -->
<!-- STYLES -->
<!-- ========================================================= -->

<?= $this->endSection() ?>
