<?= $this->extend('layouts/main_technician') ?>
<?= $this->section('content') ?>
<?php
$statusLabel = $statusLabels[$record['status'] ?? 'reported'] ?? ucwords(str_replace('_', ' ', $record['status'] ?? 'reported'));
$isEdit = $mode === 'edit';
$reporterName = $record['reported_by_name'] ?? $record['reported_by_username'] ?? 'System';
$issuePhoto = !empty($record['report_photo_path']) ? base_url($record['report_photo_path']) : null;
$completionPhoto = !empty($record['completion_photo_path']) ? base_url($record['completion_photo_path']) : null;
$actionUrl = $isEdit ? '/technician/maintenance/update/' . $record['id'] : '/technician/maintenance/store';
$stageTitle = match ($stageMode) {
    'pre' => $isEdit ? 'Pre-Maintenance Review' : 'Plan Preventive Maintenance',
    'post' => 'Post-Maintenance Completion',
    default => 'Maintenance Case',
};
$stageHelp = match ($stageMode) {
    'pre' => $isEdit
        ? 'Accept the report, note your diagnosis, and schedule the maintenance visit.'
        : 'Use this screen for planned work such as preventive checks, inspections, and calibration.',
    'post' => 'Record the repair outcome, testing notes, and final completion evidence only after the scheduled work is done.',
    default => 'This case is closed. You can review the details and evidence below.',
};
$readOnlyDetails = $stageMode === 'post' || $isLocked;
$submitLabel = match ($stageMode) {
    'pre' => $isEdit ? 'Accept And Schedule' : 'Create Planned Case',
    'post' => 'Complete Maintenance',
    default => 'Back',
};
?>
<div class="container-fluid">
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success border-0 shadow-sm"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-1"><?= esc($stageTitle) ?></h5>
                        <small class="text-muted"><?= esc($stageHelp) ?></small>
                    </div>
                    <span class="badge text-bg-secondary"><?= esc($statusLabel) ?></span>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= $actionUrl ?>" class="row g-3" enctype="multipart/form-data">
                        <?= csrf_field() ?>

                        <div class="col-md-6"><label class="form-label">Equipment</label><select name="asset_id" class="form-select" required <?= $readOnlyDetails ? 'disabled' : '' ?>><?php if (! $isEdit): ?><option value="">Select equipment</option><?php endif; ?><?php foreach ($assets as $asset): ?><option value="<?= esc($asset['id']) ?>" <?= (string) old('asset_id', (string) ($record['asset_id'] ?? '')) === (string) $asset['id'] ? 'selected' : '' ?>><?= esc($asset['name']) ?><?= !empty($asset['lab_name']) ? ' - ' . esc($asset['lab_name']) : '' ?> | <?= esc((int) ($asset['quantity'] ?? 0)) ?> available of <?= esc((int) ($asset['total_quantity'] ?? $asset['quantity'] ?? 0)) ?></option><?php endforeach; ?></select><?php if ($readOnlyDetails): ?><input type="hidden" name="asset_id" value="<?= esc(old('asset_id', $record['asset_id'] ?? '')) ?>"><?php endif; ?></div>
                        <div class="col-md-3"><label class="form-label">Affected Units</label><input type="number" name="quantity_affected" min="1" class="form-control" value="<?= esc(old('quantity_affected', $record['quantity_affected'] ?? 1)) ?>" required <?= $readOnlyDetails ? 'readonly' : '' ?>></div>
                        <div class="col-md-3"><label class="form-label">Priority</label><select name="priority" class="form-select" required <?= $readOnlyDetails ? 'disabled' : '' ?>><?php foreach ($priorities as $priority): ?><option value="<?= esc($priority) ?>" <?= old('priority', $record['priority'] ?? '') === $priority ? 'selected' : '' ?>><?= esc(ucfirst($priority)) ?></option><?php endforeach; ?></select><?php if ($readOnlyDetails): ?><input type="hidden" name="priority" value="<?= esc(old('priority', $record['priority'] ?? '')) ?>"><?php endif; ?></div>
                        <div class="col-md-8"><label class="form-label">Case Title</label><input type="text" name="title" class="form-control" value="<?= esc(old('title', $record['title'] ?? '')) ?>" required <?= $readOnlyDetails ? 'readonly' : '' ?>></div>
                        <div class="col-md-4"><label class="form-label">Case Type</label><select name="issue_type" class="form-select" required <?= ($readOnlyDetails || ($isEdit && ($record['issue_type'] ?? '') === 'corrective')) ? 'disabled' : '' ?>><?php foreach ($issueTypes as $issueType): ?><option value="<?= esc($issueType) ?>" <?= old('issue_type', $record['issue_type'] ?? '') === $issueType ? 'selected' : '' ?>><?= esc(ucfirst($issueType)) ?></option><?php endforeach; ?></select><?php if ($readOnlyDetails || ($isEdit && ($record['issue_type'] ?? '') === 'corrective')): ?><input type="hidden" name="issue_type" value="<?= esc(old('issue_type', $record['issue_type'] ?? '')) ?>"><?php endif; ?><?php if ($isEdit && ($record['issue_type'] ?? '') === 'corrective'): ?><div class="form-text">Corrective cases come from user reports and cannot be converted manually.</div><?php endif; ?></div>
                        <div class="col-12"><label class="form-label">Unit / Workstation Reference</label><input type="text" name="unit_reference" class="form-control" value="<?= esc(old('unit_reference', $record['unit_reference'] ?? '')) ?>" placeholder="Example: PC-07, Seat B3, Projector Unit 2" <?= $readOnlyDetails ? 'readonly' : '' ?>></div>
                        <div class="col-12"><label class="form-label">Issue Description</label><textarea name="description" class="form-control" rows="4" required <?= $readOnlyDetails ? 'readonly' : '' ?>><?= esc(old('description', $record['description'] ?? '')) ?></textarea></div>

                        <?php if ($stageMode === 'pre'): ?>
                            <div class="col-12"><div class="border rounded-3 p-3 bg-light-subtle"><div class="fw-semibold text-dark mb-1">Stage 1: Pre-Maintenance</div><div class="small text-muted mb-0">Accept the case, inspect the equipment, write your diagnosis, and set the service date.</div></div></div>
                            <div class="col-md-6"><label class="form-label">Scheduled For</label><input type="datetime-local" name="scheduled_for" class="form-control" value="<?= esc(old('scheduled_for', !empty($record['scheduled_for']) ? date('Y-m-d\TH:i', strtotime($record['scheduled_for'])) : '')) ?>"></div>
                            <div class="col-12"><label class="form-label">Diagnosis Notes</label><textarea name="diagnosis_notes" class="form-control" rows="4" placeholder="State what is faulty, what you observed, and why maintenance is needed."><?= esc(old('diagnosis_notes', $record['diagnosis_notes'] ?? '')) ?></textarea><div class="form-text">Keep it short and specific. This is the main note the system needs before work begins.</div></div>
                        <?php elseif ($stageMode === 'post'): ?>
                            <div class="col-12"><div class="border rounded-3 p-3 bg-light-subtle"><div class="fw-semibold text-dark mb-1">Stage 2: Post-Maintenance</div><div class="small text-muted mb-0">Once the scheduled maintenance is done, record the repair work, testing checks, completion summary, and final photo.</div></div></div>
                            <div class="col-12"><label class="form-label">Repair Work Notes</label><textarea name="work_notes" class="form-control" rows="4" placeholder="Describe the repair work or servicing that was carried out."><?= esc(old('work_notes', $record['work_notes'] ?? '')) ?></textarea></div>
                            <div class="col-12"><label class="form-label">Testing / Verification Notes</label><textarea name="test_notes" class="form-control" rows="4" placeholder="Explain how you checked that the equipment is working again."><?= esc(old('test_notes', $record['test_notes'] ?? '')) ?></textarea></div>
                            <div class="col-12"><label class="form-label">Completion Summary</label><textarea name="resolution_notes" class="form-control" rows="4" placeholder="Summarize the final condition of the equipment and what was resolved."><?= esc(old('resolution_notes', $record['resolution_notes'] ?? '')) ?></textarea></div>
                            <div class="col-12"><label class="form-label">Completion Photo</label><input type="file" name="completion_photo" class="form-control" accept="image/png,image/jpeg,image/webp"><div class="form-text">Attach one clear photo after the repair or servicing is finished.</div></div>
                        <?php endif; ?>

                        <?php if (! $isLocked): ?>
                            <div class="col-12 d-flex justify-content-end gap-2"><a href="/technician/maintenance" class="btn btn-outline-secondary">Back</a><button type="submit" class="btn btn-success"><?= esc($submitLabel) ?></button></div>
                        <?php else: ?>
                            <div class="col-12 d-flex justify-content-end"><a href="/technician/maintenance" class="btn btn-outline-secondary">Back</a></div>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h6 class="mb-1">Case Summary</h6></div>
                <div class="card-body small text-muted">
                    <div class="mb-2"><strong>Reporter:</strong> <?= esc($reporterName) ?></div>
                    <div class="mb-2"><strong>Current Stage:</strong> <?= esc($statusLabel) ?></div>
                    <div class="mb-2"><strong>Unit Reference:</strong> <?= esc($record['unit_reference'] ?? 'Not specified') ?></div>
                    <div class="mb-2"><strong>Accepted At:</strong> <?= esc(!empty($record['accepted_at']) ? date('d M Y H:i', strtotime($record['accepted_at'])) : '-') ?></div>
                    <div class="mb-2"><strong>Scheduled For:</strong> <?= esc(!empty($record['scheduled_for']) ? date('d M Y H:i', strtotime($record['scheduled_for'])) : '-') ?></div>
                    <div class="mb-2"><strong>Tested At:</strong> <?= esc(!empty($record['tested_at']) ? date('d M Y H:i', strtotime($record['tested_at'])) : '-') ?></div>
                    <div class="mb-0"><strong>Completed At:</strong> <?= esc(!empty($record['completed_at']) ? date('d M Y H:i', strtotime($record['completed_at'])) : '-') ?></div>
                </div>
            </div>

            <?php if ($issuePhoto || $completionPhoto): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white"><h6 class="mb-1">Evidence</h6></div>
                    <div class="card-body small text-muted d-flex flex-column gap-3">
                        <?php if ($issuePhoto): ?><div><div class="fw-semibold mb-2">Reported Issue Photo</div><img src="<?= esc($issuePhoto) ?>" alt="Issue evidence" class="img-fluid rounded-3 border"></div><?php endif; ?>
                        <?php if ($completionPhoto): ?><div><div class="fw-semibold mb-2">Completion Photo</div><img src="<?= esc($completionPhoto) ?>" alt="Completion evidence" class="img-fluid rounded-3 border"></div><?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white"><h6 class="mb-1">Simple Workflow</h6></div>
                <div class="card-body small text-muted">
                    <p class="mb-2"><strong>1. Pre-Maintenance</strong>: accept the case, inspect the equipment, record the diagnosis, and set the maintenance schedule.</p>
                    <p class="mb-0"><strong>2. Post-Maintenance</strong>: after the work is done, add repair notes, testing notes, completion summary, and one final photo.</p>
                </div>
            </div>

            <?php if ($isEdit): ?>
                <div class="card border-0 shadow-sm"><div class="card-header bg-white"><h6 class="mb-1">Activity Log</h6></div><div class="card-body"><?php if (empty($logs)): ?><p class="text-muted small mb-0">No activity logged for this case yet.</p><?php else: ?><div class="d-flex flex-column gap-3"><?php foreach ($logs as $log): ?><div class="border rounded-3 p-3 bg-light-subtle"><div class="fw-semibold small text-dark"><?= esc($statusLabels[$log['to_status']] ?? ucwords(str_replace('_', ' ', $log['to_status'] ?? 'updated'))) ?></div><div class="small text-muted"><?= esc($log['full_name'] ?: $log['username'] ?: 'System') ?> | <?= esc(!empty($log['created_at']) ? date('d M Y H:i', strtotime($log['created_at'])) : '-') ?></div><?php if (!empty($log['notes'])): ?><div class="small mt-2"><?= esc($log['notes']) ?></div><?php endif; ?></div><?php endforeach; ?></div><?php endif; ?></div></div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

