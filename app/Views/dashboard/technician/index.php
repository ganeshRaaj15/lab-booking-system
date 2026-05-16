<?php
/** @var array $stats */
/** @var array $recentRecords */
/** @var array $statusLabels */
$stats         = $stats ?? [];
$recentRecords = $recentRecords ?? [];
$statusLabels  = $statusLabels ?? [];

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
<?= $this->extend('layouts/main_technician') ?>
<?= $this->section('content') ?>
<div class="container-fluid">
    <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success border-0 shadow-sm"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-0">Technician Dashboard</h2>
            <p class="text-muted small">Monitor and manage maintenance workflows across all laboratory equipment.</p>
        </div>
        <a href="/technician/maintenance/create" class="btn btn-success btn-sm px-3 shadow-sm">
            <i class="bi bi-plus-circle me-1"></i> New Case
        </a>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <div class="card widget-card bg-gradient-danger text-white shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="small opacity-75 fw-semibold text-uppercase">Open Cases</div>
                        <div class="fs-3 fw-bold"><?= esc($stats['open_total']) ?></div>
                    </div>
                    <i class="bi bi-exclamation-triangle fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card widget-card bg-gradient-primary text-white shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="small opacity-75 fw-semibold text-uppercase">Assigned To Me</div>
                        <div class="fs-3 fw-bold"><?= esc($stats['assigned_to_me']) ?></div>
                    </div>
                    <i class="bi bi-person-check fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card widget-card bg-gradient-warning text-white shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="small opacity-75 fw-semibold text-uppercase">Awaiting Test</div>
                        <div class="fs-3 fw-bold"><?= esc($stats['awaiting_test']) ?></div>
                    </div>
                    <i class="bi bi-hourglass-split fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3">
            <div class="card widget-card bg-gradient-success text-white shadow-sm border-0 h-100">
                <div class="card-body d-flex align-items-center">
                    <div class="flex-grow-1">
                        <div class="small opacity-75 fw-semibold text-uppercase">Completed This Month</div>
                        <div class="fs-3 fw-bold"><?= esc($stats['completed_this_month']) ?></div>
                    </div>
                    <i class="bi bi-check-circle fs-1 opacity-75"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold text-dark mb-1">
                    <i class="bi bi-clock-history me-2 text-primary"></i>Recent Maintenance Activity
                </h5>
                <small class="text-muted">Open each case to move it through the guided maintenance workflow.</small>
            </div>
            <a href="/technician/maintenance" class="btn btn-outline-primary btn-sm">View All</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr><th>Asset</th><th>Laboratory</th><th>Stage</th><th>Priority</th><th>Scheduled</th><th class="text-end">Action</th></tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentRecords)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-check2-all fs-2 d-block mb-2 opacity-50"></i>
                                    No maintenance records available yet.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($recentRecords as $record): ?>
                                <?php
                                $sBadge = $statusBadgeClass[$record['status']] ?? 'text-bg-secondary';
                                $pBadge = $priorityBadgeClass[$record['priority']] ?? 'stat-badge stat-badge-neutral';
                                ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= esc($record['asset_name'] ?? 'Unknown asset') ?></div>
                                        <small class="text-muted">#<?= esc($record['id']) ?> <?= esc($record['title']) ?></small>
                                    </td>
                                    <td><?= esc($record['laboratory_name'] ?? '-') ?></td>
                                    <td><span class="badge <?= esc($sBadge) ?>"><?= esc($statusLabels[$record['status']] ?? ucwords(str_replace('_', ' ', $record['status']))) ?></span></td>
                                    <td><span class="<?= esc($pBadge) ?> text-uppercase"><?= esc($record['priority']) ?></span></td>
                                    <td><?= esc($record['scheduled_for'] ? date('d M Y H:i', strtotime($record['scheduled_for'])) : '-') ?></td>
                                    <td class="text-end"><a href="/technician/maintenance/edit/<?= esc($record['id']) ?>" class="btn btn-sm btn-outline-primary">Open Case</a></td>
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
