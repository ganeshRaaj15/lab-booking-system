<?php
/** @var array $assets */
/** @var array $records */
/** @var array $filters */
/** @var array $statusLabels */
/** @var array $statusOptions */
/** @var array $modelSummary */
/** @var array $upcomingForecasts */
$assets           = $assets ?? [];
$records          = $records ?? [];
$filters          = $filters ?? [];
$statusLabels     = $statusLabels ?? [];
$statusOptions    = $statusOptions ?? [];
$modelSummary     = $modelSummary ?? [];
$upcomingForecasts = $upcomingForecasts ?? [];

$statusBadgeClass = [
    'reported'    => 'text-bg-danger',
    'scheduled'   => 'text-bg-primary',
    'in_progress' => 'text-bg-warning',
    'testing'     => 'text-bg-info',
    'completed'   => 'text-bg-success',
    'cancelled'   => 'text-bg-secondary',
];
$priorityBadgeClass = [
    'low'      => 'stat-badge stat-badge-success',
    'medium'   => 'stat-badge stat-badge-warning',
    'high'     => 'stat-badge stat-badge-danger',
    'critical' => 'badge text-bg-danger',
];
?>
<?= $this->extend('layouts/main_user') ?>
<?= $this->section('content') ?>
<div class="container-fluid">
    <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success border-0 shadow-sm"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-0">Maintenance Workflow</h2>
            <p class="text-muted small">Track, schedule, and resolve equipment maintenance cases across all laboratories.</p>
        </div>
        <a href="/technician/maintenance/create" class="btn btn-success btn-sm px-3 shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> New Planned Maintenance
        </a>
    </div>


    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h6 class="fw-bold text-dark mb-0"><i class="bi bi-funnel me-2 text-primary"></i>Filter Cases</h6>
        </div>
        <div class="card-body pt-0">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-md-3"><label class="form-label">Workflow Stage</label><select name="status" class="form-select"><option value="">All stages</option><?php foreach ($statusOptions as $status): ?><option value="<?= esc($status) ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>><?= esc($statusLabels[$status] ?? ucwords(str_replace('_', ' ', $status))) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label">Asset</label><select name="asset_id" class="form-select"><option value="0">All assets</option><?php foreach ($assets as $asset): ?><option value="<?= esc($asset['id']) ?>" <?= (int) $filters['asset_id'] === (int) $asset['id'] ? 'selected' : '' ?>><?= esc($asset['name']) ?><?= !empty($asset['lab_name']) ? ' - ' . esc($asset['lab_name']) : '' ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Scope</label><select name="scope" class="form-select"><option value="">All records</option><option value="mine" <?= $filters['scope'] === 'mine' ? 'selected' : '' ?>>Assigned to me</option></select></div>
                <div class="col-md-2 d-grid"><button type="submit" class="btn btn-primary">Filter</button></div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h6 class="fw-bold text-dark mb-0">
                    <i class="bi bi-graph-up-arrow me-2 text-warning"></i>Predictive Maintenance Decisions
                </h6>
                <small class="text-muted">Risk scores and recommended actions based on the local maintenance model and completed planned-maintenance history.</small>
            </div>
            <span class="stat-badge stat-badge-neutral">Next 90 days</span>
        </div>
        <div class="card-body p-0">
            <?php if (empty($upcomingForecasts)): ?>
                <div class="p-4 text-muted">No assets currently require predictive maintenance action in the next 90 days.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Asset</th>
                                <th>Risk</th>
                                <th>System Decision</th>
                                <th>Due Date</th>
                                <th>Last Completed</th>
                                <th>Reason</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($upcomingForecasts as $forecast): ?>
                                <?php
                                    $nextDueRaw = $forecast['next_due_at'] ?? '';
                                    $nextDueLabel = $nextDueRaw ? date('d M Y', strtotime($nextDueRaw)) : '-';
                                    $lastCompletedRaw = $forecast['last_completed_at'] ?? '';
                                    $lastCompletedLabel = $lastCompletedRaw ? date('d M Y', strtotime($lastCompletedRaw)) : '-';
                                    $intervalDays = (int) ($forecast['interval_days'] ?? 0);
                                    $months = $intervalDays > 0 ? max((int) round($intervalDays / 30), 1) : 0;
                                    $cycleLabel = $intervalDays > 0
                                        ? ($forecast['basis'] === 'average' ? 'Avg ' : 'Default ') . '~' . $months . ' mo'
                                        : '-';
                                    $daysUntil = (int) ($forecast['days_until'] ?? 0);
                                    $statusText = $daysUntil < 0
                                        ? 'Overdue by ' . abs($daysUntil) . ' day(s)'
                                        : 'Due in ' . $daysUntil . ' day(s)';
                                    $statusClass = $daysUntil < 0 ? 'text-bg-danger' : 'text-bg-warning';
                                    $scheduledFor = $nextDueRaw ? date('Y-m-d\\T09:00', strtotime($nextDueRaw)) : '';
                                    if ($scheduledFor === '') {
                                        $scheduledFor = date('Y-m-d\\T09:00', strtotime('+7 days'));
                                    }
                                    $recommendedPriority = $forecast['decision_priority'] ?? 'medium';
                                    $planQuery = http_build_query([
                                        'asset_id' => $forecast['asset_id'] ?? '',
                                        'scheduled_for' => $scheduledFor,
                                        'issue_type' => 'preventive',
                                        'title' => 'Preventive Maintenance - ' . ($forecast['name'] ?? 'Equipment'),
                                        'priority' => $recommendedPriority === 'high' ? 'high' : 'medium',
                                        'quantity_affected' => 1,
                                    ], '', '&', PHP_QUERY_RFC3986);
                                    $riskBand = $forecast['risk_band'] ?? 'low';
                                    $riskPercent = (int) ($forecast['risk_percent'] ?? 0);
                                    $riskBadgeClass = match ($riskBand) {
                                        'high' => 'text-bg-danger',
                                        'medium' => 'text-bg-warning',
                                        default => 'text-bg-success',
                                    };
                                    $riskBarColor = match ($riskBand) {
                                        'high' => 'var(--bs-danger)',
                                        'medium' => 'var(--bs-warning)',
                                        default => 'var(--bs-success)',
                                    };
                                    $riskHeadline = match (true) {
                                        $riskBand === 'high' => 'High risk — schedule maintenance now',
                                        $riskBand === 'medium' => 'Moderate risk — inspect soon',
                                        default => 'Low risk — continue monitoring',
                                    };
                                    $reasons = $forecast['reasons'] ?? [];
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= esc($forecast['name'] ?? '-') ?></div>
                                        <small class="text-muted"><?= esc($forecast['lab_name'] ?? '-') ?></small>
                                    </td>
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
                                        <div class="fw-semibold"><?= esc($forecast['decision_label'] ?? 'Normal monitoring') ?></div>
                                        <small class="text-muted text-uppercase"><?= esc($forecast['decision_priority'] ?? 'low') ?> priority</small>
                                    </td>
                                    <td><?= esc($nextDueLabel) ?></td>
                                    <td><?= esc($lastCompletedLabel) ?></td>
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
                                        <?php if ($intervalDays > 0): ?>
                                            <div class="small text-muted mt-1"><?= esc($cycleLabel) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="/technician/maintenance/create?<?= esc($planQuery) ?>">Plan</a></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
            <div>
                <h5 class="fw-bold text-dark mb-1">
                    <i class="bi bi-tools me-2 text-primary"></i>Maintenance Workflow
                </h5>
                <small class="text-muted">Each case is completed step by step: first schedule and diagnose, then record repair work, then test and close with evidence.</small>
            </div>
            <a href="/technician/maintenance/create" class="btn btn-success btn-sm">
                <i class="bi bi-plus-circle me-1"></i> New Planned Maintenance
            </a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Case</th><th>Asset</th><th>Stage</th><th>Priority</th><th>Reporter / Unit</th><th>Updated</th><th class="text-end">Action</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr><td colspan="8" class="text-center py-5 text-muted">No maintenance records matched the selected filters.</td></tr>
                        <?php else: ?>
                            <?php foreach ($records as $record): ?>
                                <?php
                                $label  = $statusLabels[$record['status']] ?? ucwords(str_replace('_', ' ', $record['status']));
                                $sBadge = $statusBadgeClass[$record['status']] ?? 'text-bg-secondary';
                                $pBadge = $priorityBadgeClass[$record['priority']] ?? 'stat-badge stat-badge-neutral';
                                ?>
                                <tr>
                                    <td><div class="fw-semibold"><?= esc($record['title']) ?></div><small class="text-muted">#<?= esc($record['id']) ?> | <?= esc(ucfirst($record['issue_type'])) ?></small></td>
                                    <td><div><?= esc($record['asset_name'] ?? '-') ?></div><small class="text-muted"><?= esc($record['laboratory_name'] ?? '-') ?></small></td>
                                    <td><span class="badge <?= esc($sBadge) ?>"><?= esc($label) ?></span></td>
                                    <td><span class="<?= esc($pBadge) ?> text-uppercase"><?= esc($record['priority']) ?></span></td>
                                    <td>
                                        <div class="small"><?= esc($record['reported_by_name'] ?: $record['reported_by_username'] ?: 'System') ?></div>
                                        <div class="small text-muted"><?= esc($record['unit_reference'] ?: 'No unit reference') ?></div>
                                    </td>
                                    <td><?= esc($record['updated_at'] ? date('d M Y H:i', strtotime($record['updated_at'])) : '-') ?></td>
                                    <td class="text-end">
                                        <a href="/technician/maintenance/edit/<?= esc($record['id']) ?>" class="btn btn-sm btn-outline-primary">Open Case</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
