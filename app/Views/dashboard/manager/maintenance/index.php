<?php
/** @var array $records */
/** @var array $statusLabels */
/** @var array $filters */
/** @var array $upcomingForecasts */
/** @var array $modelSummary */
$records           = $records           ?? [];
$statusLabels      = $statusLabels      ?? [];
$filters           = $filters           ?? ['status' => '', 'asset_id' => 0];
$upcomingForecasts = $upcomingForecasts ?? [];
$modelSummary      = $modelSummary      ?? [];
$roleLabel         = $roleLabel         ?? 'Lab Manager';
$backUrl           = $backUrl           ?? '/dashboard/manager';
$basePath          = $basePath          ?? '/dashboard/manager/maintenance';

$priorityBadge = ['low' => 'secondary', 'medium' => 'primary', 'high' => 'warning', 'critical' => 'danger'];
$statusBadge   = ['reported' => 'info', 'scheduled' => 'primary', 'in_progress' => 'warning', 'testing' => 'secondary', 'completed' => 'success', 'cancelled' => 'danger'];
?>
<?= $this->extend($layoutView ?? 'layouts/main_user') ?>
<?= $this->section('content') ?>

<div class="container py-4">

    <div class="slams-page-header">
        <div class="slams-page-header-left">
            <h1 class="slams-page-title">Maintenance Records</h1>
            <p class="slams-page-subtitle">Read-only view of all system maintenance activity for <?= esc($roleLabel) ?>.</p>
        </div>
        <div class="slams-page-header-actions">
            <a href="<?= esc($backUrl) ?>" class="btn btn-glass btn-sm">
                <i class="bi bi-arrow-left me-1"></i> <?= esc($roleLabel) ?> Dashboard
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <!-- Filters -->
    <form method="get" action="<?= esc($basePath) ?>" class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All statuses</option>
                        <?php foreach ($statusLabels as $key => $label): ?>
                            <option value="<?= esc($key) ?>" <?= $filters['status'] === $key ? 'selected' : '' ?>>
                                <?= esc($label) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="<?= esc($basePath) ?>" class="btn btn-outline-secondary btn-sm">Clear</a>
                </div>
            </div>
        </div>
    </form>

    <!-- Summary counts -->
    <?php
    $counts = array_fill_keys(array_keys($statusLabels), 0);
    foreach ($records as $r) {
        $s = $r['status'] ?? '';
        if (isset($counts[$s])) { $counts[$s]++; }
    }
    ?>
    <div class="row g-3 mb-4">
        <?php foreach (['reported' => 'Reported', 'in_progress' => 'In Progress', 'completed' => 'Completed'] as $k => $lbl): ?>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-3 fw-bold text-<?= $statusBadge[$k] ?? 'secondary' ?>"><?= $counts[$k] ?></div>
                <div class="small text-muted"><?= $lbl ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Predictive Maintenance Forecast (read-only) -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-graph-up-arrow me-2 text-warning"></i>Predictive Maintenance Forecast
                </h6>
                <?php $isLearned = ($modelSummary['mode'] ?? '') === 'model_plus_rules'; ?>
                <small class="text-muted d-block">Risk scores and recommended actions for all assets — next 90 days.</small>
                <small class="<?= $isLearned ? 'text-success' : 'text-warning' ?>">
                    <i class="bi <?= $isLearned ? 'bi-check-circle-fill' : 'bi-exclamation-circle-fill' ?> me-1"></i>
                    <?= $isLearned
                        ? 'Scores are learned from actual maintenance history.'
                        : 'Scores are based on general guidelines — improves as more records are added.' ?>
                </small>
            </div>
            <span class="badge bg-light text-dark border">Next 90 days</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($upcomingForecasts)): ?>
                <div class="p-4 text-muted small">No assets require predictive maintenance action in the next 90 days.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Asset</th>
                                <th>Laboratory</th>
                                <th>Risk</th>
                                <th>Decision</th>
                                <th>Due Date</th>
                                <th>Last Completed</th>
                                <th>Reason</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingForecasts as $forecast): ?>
                                <?php
                                $nextDueLabel      = ! empty($forecast['next_due_at'])      ? date('d-m-Y', strtotime((string) $forecast['next_due_at']))      : '—';
                                $lastCompletedLabel= ! empty($forecast['last_completed_at']) ? date('d-m-Y', strtotime((string) $forecast['last_completed_at'])) : '—';
                                $riskBand          = $forecast['risk_band']    ?? 'low';
                                $riskPercent       = (int) ($forecast['risk_percent'] ?? 0);
                                $riskBadgeClass    = match ($riskBand) { 'high' => 'text-bg-danger', 'medium' => 'text-bg-warning', default => 'text-bg-success' };
                                $riskBarColor      = match ($riskBand) { 'high' => 'var(--bs-danger)', 'medium' => 'var(--bs-warning)', default => 'var(--bs-success)' };
                                $riskHeadline      = match (true) {
                                    $riskBand === 'high'   => 'High risk — schedule now',
                                    $riskBand === 'medium' => 'Moderate risk — inspect soon',
                                    default                => 'Low risk — monitor',
                                };
                                $reasons = $forecast['reasons'] ?? [];
                                $daysUntil = (int) ($forecast['days_until'] ?? 0);
                                $statusText = $daysUntil < 0
                                    ? 'Overdue by ' . abs($daysUntil) . ' day(s)'
                                    : 'Due in ' . $daysUntil . ' day(s)';
                                $statusClass = $daysUntil < 0 ? 'text-bg-danger' : 'text-bg-warning';
                                ?>
                                <tr>
                                    <td class="fw-semibold"><?= esc($forecast['name'] ?? '—') ?></td>
                                    <td class="small text-muted"><?= esc($forecast['lab_name'] ?? '—') ?></td>
                                    <td style="min-width:120px">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <span class="badge <?= esc($riskBadgeClass) ?>"><?= esc($riskPercent) ?>%</span>
                                        </div>
                                        <div class="progress" style="height:6px;border-radius:4px;background:#e9ecef;">
                                            <div class="progress-bar" role="progressbar"
                                                 style="width:<?= esc($riskPercent) ?>%;background:<?= $riskBarColor ?>;border-radius:4px;"
                                                 aria-valuenow="<?= esc($riskPercent) ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                        <div class="small text-muted mt-1"><?= esc($riskHeadline) ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold small"><?= esc($forecast['decision_label'] ?? 'Normal monitoring') ?></div>
                                        <small class="text-muted text-uppercase"><?= esc($forecast['decision_priority'] ?? 'low') ?> priority</small>
                                    </td>
                                    <td class="small"><?= esc($nextDueLabel) ?></td>
                                    <td class="small"><?= esc($lastCompletedLabel) ?></td>
                                    <td style="min-width:180px">
                                        <?php if (! empty($reasons)): ?>
                                            <ul class="mb-0 ps-3 small">
                                                <?php foreach ($reasons as $reason): ?>
                                                    <li class="mb-1"><?= esc($reason) ?></li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php else: ?>
                                            <span class="badge <?= esc($statusClass) ?>"><?= esc($statusText) ?></span>
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

    <!-- Table -->
    <?php if (empty($records)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-tools fs-1 mb-2 d-block"></i>
                No maintenance records found.
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Asset</th>
                            <th>Laboratory</th>
                            <th>Title</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Reported</th>
                            <th class="text-end">Detail</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $rec): ?>
                        <tr>
                            <td class="text-muted small"><?= (int) $rec['id'] ?></td>
                            <td>
                                <div class="fw-semibold"><?= esc($rec['asset_name'] ?? '—') ?></div>
                                <div class="text-muted small"><?= esc($rec['asset_code'] ?? '') ?></div>
                            </td>
                            <td class="small"><?= esc(($rec['laboratory_name'] ?? '') . ' ' . ($rec['laboratory_room'] ?? '')) ?></td>
                            <td><?= esc($rec['title'] ?? '—') ?></td>
                            <td>
                                <span class="badge bg-<?= $priorityBadge[$rec['priority'] ?? ''] ?? 'secondary' ?>">
                                    <?= esc(ucfirst($rec['priority'] ?? '—')) ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-<?= $statusBadge[$rec['status'] ?? ''] ?? 'secondary' ?>">
                                    <?= esc($statusLabels[$rec['status'] ?? ''] ?? ucfirst((string) ($rec['status'] ?? '—'))) ?>
                                </span>
                            </td>
                            <td class="small text-muted">
                                <?= $rec['created_at'] ? date('d-m-Y', strtotime((string) $rec['created_at'])) : '—' ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= esc($basePath) ?>/<?= (int) $rec['id'] ?>"
                                   class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (isset($pager)): ?>
                <div class="card-footer bg-white border-0"><?= $pager->links() ?></div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>
