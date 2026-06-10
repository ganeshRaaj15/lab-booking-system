<?php
/** @var array $asset */
/** @var array|null $forecast */
/** @var string $prefillTitle */
/** @var string $prefillDesc */
/** @var string $prefillPrio */
/** @var string $prefillDate */
$asset       = $asset       ?? [];
$forecast    = $forecast    ?? null;
$prefillTitle = old('title',       $prefillTitle ?? '');
$prefillDesc  = old('description', $prefillDesc  ?? '');
$prefillPrio  = old('priority',    $prefillPrio  ?? 'medium');
$prefillDate  = old('scheduled_for', $prefillDate ?? '');
$prefillUnit  = old('unit_reference', '');

$priorityBadge = ['low' => 'secondary', 'medium' => 'primary', 'high' => 'warning', 'critical' => 'danger'];
$riskBand      = $forecast['risk_band']   ?? 'low';
$riskPercent   = (int) ($forecast['risk_percent'] ?? 0);
$riskBandBadge = ['high' => 'danger', 'medium' => 'warning', 'low' => 'success'];
?>
<?= $this->extend('layouts/main_user') ?>
<?= $this->section('content') ?>

<div class="container py-4">

    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-1">Plan Maintenance</h2>
            <p class="text-muted mb-0 small">Review the pre-filled details and adjust before sending to the responsible PIC.</p>
        </div>
        <a href="/dashboard/manager" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger shadow-sm border-0"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="row g-4">

        <!-- Left: Form -->
        <div class="col-lg-7">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h5 class="fw-semibold text-primary mb-0">Maintenance Request Details</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <form method="post" action="/dashboard/manager/plan-maintenance" class="row g-3">
                        <?= csrf_field() ?>
                        <input type="hidden" name="asset_id" value="<?= (int) $asset['id'] ?>">

                        <div class="col-12">
                            <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" required
                                   maxlength="255" value="<?= esc($prefillTitle) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Priority <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                <?php foreach (['low' => 'Low', 'medium' => 'Medium', 'high' => 'High', 'critical' => 'Critical'] as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= $prefillPrio === $val ? 'selected' : '' ?>><?= $label ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Scheduled Date</label>
                            <input type="date" name="scheduled_for" class="form-control"
                                   min="<?= date('Y-m-d') ?>" value="<?= esc($prefillDate) ?>">
                            <div class="form-text">Leave blank if not yet scheduled.</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-semibold">Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="6" required
                                      minlength="10"><?= esc($prefillDesc) ?></textarea>
                        </div>

                        <?php if ((int) ($asset['total_quantity'] ?? 0) > 1): ?>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Unit Reference</label>
                            <input type="text" name="unit_reference" class="form-control" maxlength="120"
                                   placeholder="e.g. PC-03, Workstation 7" value="<?= esc($prefillUnit) ?>">
                            <div class="form-text">Specify the workstation or unit label if applicable.</div>
                        </div>
                        <?php endif; ?>

                        <div class="col-12 pt-2">
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="bi bi-send me-2"></i>Send to PIC
                            </button>
                            <a href="/dashboard/manager" class="btn btn-outline-secondary ms-2">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Right: Asset info + forecast -->
        <div class="col-lg-5">
            <div class="card shadow-sm border-0 rounded-4 mb-3">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h6 class="fw-semibold text-dark mb-0">Equipment</h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="fw-bold text-dark mb-1"><?= esc($asset['name'] ?? '—') ?></div>
                    <div class="text-muted small mb-2"><?= esc($asset['asset_code'] ?? '') ?></div>
                    <div class="text-muted small"><i class="bi bi-building me-1"></i><?= esc($asset['lab_name'] ?? '—') ?></div>
                    <hr class="my-3">
                    <div class="d-flex justify-content-between align-items-center">
                        <span class="text-muted small">Current Status</span>
                        <span class="badge bg-<?= match($asset['status'] ?? '') {
                            'available'  => 'success',
                            'maintenance'=> 'warning',
                            'faulty'     => 'danger',
                            default      => 'secondary',
                        } ?>"><?= esc(ucfirst($asset['status'] ?? '—')) ?></span>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mt-2">
                        <span class="text-muted small">Available Units</span>
                        <span class="fw-semibold"><?= (int) ($asset['quantity'] ?? 0) ?> / <?= (int) ($asset['total_quantity'] ?? 0) ?></span>
                    </div>
                </div>
            </div>

            <?php if ($forecast): ?>
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4">
                    <h6 class="fw-semibold text-dark mb-0">Predictive Risk</h6>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="d-flex align-items-center gap-3 mb-3">
                        <div class="display-6 fw-bold text-<?= $riskBandBadge[$riskBand] ?? 'secondary' ?>">
                            <?= $riskPercent ?>%
                        </div>
                        <span class="badge bg-<?= $riskBandBadge[$riskBand] ?? 'secondary' ?>">
                            <?= esc(ucfirst($riskBand)) ?> Risk
                        </span>
                    </div>
                    <?php if (! empty($forecast['decision_label'])): ?>
                        <div class="text-muted small mb-2"><strong>Recommendation:</strong> <?= esc($forecast['decision_label']) ?></div>
                    <?php endif; ?>
                    <?php if (! empty($forecast['next_due_at'])): ?>
                        <div class="text-muted small"><strong>Suggested due date:</strong> <?= esc(date('d M Y', strtotime($forecast['next_due_at']))) ?></div>
                    <?php endif; ?>
                    <?php
                    $reasons = array_values(array_filter(array_map('trim', (array) ($forecast['reasons'] ?? []))));
                    if ($reasons !== []):
                    ?>
                        <hr class="my-2">
                        <div class="text-muted small"><strong>Reasons:</strong></div>
                        <ul class="small text-muted mb-0 ps-3 mt-1">
                            <?php foreach ($reasons as $reason): ?>
                                <li><?= esc($reason) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>

    </div>
</div>

<?= $this->endSection() ?>
