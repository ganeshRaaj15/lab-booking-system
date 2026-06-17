<?php
/** @var array $record */
/** @var array $logs */
/** @var array|null $asset */
/** @var array $statusLabels */
$record       = $record       ?? [];
$logs         = $logs         ?? [];
$asset        = $asset        ?? null;
$statusLabels = $statusLabels ?? [];
$roleLabel    = $roleLabel    ?? 'Lab Manager';
$backUrl      = $backUrl      ?? '/dashboard/manager/maintenance';

$priorityBadge = ['low' => 'secondary', 'medium' => 'primary', 'high' => 'warning', 'critical' => 'danger'];
$statusBadge   = ['reported' => 'info', 'scheduled' => 'primary', 'in_progress' => 'warning', 'testing' => 'secondary', 'completed' => 'success', 'cancelled' => 'danger'];
$statusLabel   = $statusLabels[$record['status'] ?? ''] ?? ucfirst((string) ($record['status'] ?? '—'));
?>
<?= $this->extend($layoutView ?? 'layouts/main_user') ?>
<?= $this->section('content') ?>

<div class="container py-4">

    <div class="slams-page-header">
        <div class="slams-page-header-left">
            <h1 class="slams-page-title">Maintenance #<?= (int) $record['id'] ?></h1>
            <p class="slams-page-subtitle"><?= esc($record['title'] ?? '') ?></p>
        </div>
        <div class="slams-page-header-actions">
            <a href="<?= esc($backUrl) ?>" class="btn btn-glass btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row g-4">

        <!-- Left: Record details -->
        <div class="col-lg-8">

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Record Details</h5>
                    <span class="badge bg-<?= $statusBadge[$record['status'] ?? ''] ?? 'secondary' ?>">
                        <?= esc($statusLabel) ?>
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="text-muted small">Asset</div>
                            <div class="fw-semibold"><?= esc($record['asset_name'] ?? '—') ?></div>
                            <div class="text-muted small"><?= esc($record['asset_code'] ?? '') ?></div>
                        </div>
                        <div class="col-md-6">
                            <div class="text-muted small">Laboratory</div>
                            <div class="fw-semibold"><?= esc(($record['laboratory_name'] ?? '') . ' — ' . ($record['laboratory_room'] ?? '')) ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Issue Type</div>
                            <div><?= esc(ucfirst($record['issue_type'] ?? '—')) ?></div>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Priority</div>
                            <span class="badge bg-<?= $priorityBadge[$record['priority'] ?? ''] ?? 'secondary' ?>">
                                <?= esc(ucfirst($record['priority'] ?? '—')) ?>
                            </span>
                        </div>
                        <div class="col-md-4">
                            <div class="text-muted small">Quantity Affected</div>
                            <div><?= (int) ($record['quantity_affected'] ?? 0) ?></div>
                        </div>
                        <?php if (! empty($record['unit_reference'])): ?>
                        <div class="col-md-6">
                            <div class="text-muted small">Unit Reference</div>
                            <div><?= esc($record['unit_reference']) ?></div>
                        </div>
                        <?php endif; ?>
                        <div class="col-12">
                            <div class="text-muted small">Description</div>
                            <div><?= nl2br(esc($record['description'] ?? '—')) ?></div>
                        </div>
                        <?php if (! empty($record['diagnosis_notes'])): ?>
                        <div class="col-12">
                            <div class="text-muted small">Diagnosis Notes</div>
                            <div><?= nl2br(esc($record['diagnosis_notes'])) ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if (! empty($record['work_notes'])): ?>
                        <div class="col-12">
                            <div class="text-muted small">Work Notes</div>
                            <div><?= nl2br(esc($record['work_notes'])) ?></div>
                        </div>
                        <?php endif; ?>
                        <?php if (! empty($record['resolution_notes'])): ?>
                        <div class="col-12">
                            <div class="text-muted small">Resolution Notes</div>
                            <div><?= nl2br(esc($record['resolution_notes'])) ?></div>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Timeline / Audit log -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0">Activity Timeline</h5></div>
                <div class="card-body">
                    <?php if (empty($logs)): ?>
                        <p class="text-muted small mb-0">No log entries yet.</p>
                    <?php else: ?>
                        <ul class="list-unstyled mb-0">
                        <?php foreach ($logs as $log): ?>
                            <li class="d-flex gap-3 mb-3">
                                <div class="pt-1">
                                    <span class="badge bg-light text-dark border"><?= esc($log['to_status'] ?? '—') ?></span>
                                </div>
                                <div class="flex-grow-1">
                                    <div class="small text-muted"><?= esc($log['full_name'] ?? $log['username'] ?? 'System') ?> &middot; <?= $log['created_at'] ? date('d-m-Y H:i', strtotime((string) $log['created_at'])) : '—' ?></div>
                                    <?php if (! empty($log['notes'])): ?>
                                        <div><?= nl2br(esc($log['notes'])) ?></div>
                                    <?php endif; ?>
                                </div>
                            </li>
                        <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Right: Meta -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">People</h6></div>
                <div class="card-body small">
                    <div class="mb-2">
                        <span class="text-muted">Reported By</span>
                        <div><?= esc($record['reported_by_name'] ?? $record['reported_by_username'] ?? '—') ?></div>
                    </div>
                    <div class="mb-0">
                        <span class="text-muted">Assigned Technician</span>
                        <div><?= esc($record['technician_name'] ?? $record['technician_username'] ?? 'Unassigned') ?></div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Dates</h6></div>
                <div class="card-body small">
                    <?php $dateFields = [
                        'created_at'   => 'Reported',
                        'scheduled_for'=> 'Scheduled For',
                        'accepted_at'  => 'Accepted',
                        'started_at'   => 'Work Started',
                        'tested_at'    => 'Testing Started',
                        'completed_at' => 'Completed',
                    ]; ?>
                    <?php foreach ($dateFields as $field => $label): ?>
                        <?php if (! empty($record[$field])): ?>
                        <div class="mb-1">
                            <span class="text-muted"><?= $label ?>: </span>
                            <span><?= date('d-m-Y H:i', strtotime((string) $record[$field])) ?></span>
                        </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if (! empty($record['report_photo_path'])): ?>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Issue Photo</h6></div>
                <div class="card-body text-center">
                    <img src="<?= base_url($record['report_photo_path']) ?>" alt="Issue photo"
                         style="max-width:100%;border-radius:8px;">
                </div>
            </div>
            <?php endif; ?>

            <?php if (! empty($record['completion_photo_path'])): ?>
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Completion Photo</h6></div>
                <div class="card-body text-center">
                    <img src="<?= base_url($record['completion_photo_path']) ?>" alt="Completion photo"
                         style="max-width:100%;border-radius:8px;">
                </div>
            </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm">
                <div class="card-body small text-muted">
                    <i class="bi bi-info-circle me-1"></i>
                    This is a read-only view for <?= esc($roleLabel) ?>. Only the assigned PIC can modify maintenance records.
                </div>
            </div>
        </div>

    </div>
</div>

<?= $this->endSection() ?>
