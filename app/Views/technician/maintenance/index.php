<?= $this->extend('layouts/main_technician') ?>
<?= $this->section('content') ?>
<div class="container-fluid">
    <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success border-0 shadow-sm"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" class="row g-3 align-items-end">
                <div class="col-md-3"><label class="form-label">Workflow Stage</label><select name="status" class="form-select"><option value="">All stages</option><?php foreach ($statusOptions as $status): ?><option value="<?= esc($status) ?>" <?= $filters['status'] === $status ? 'selected' : '' ?>><?= esc($statusLabels[$status] ?? ucwords(str_replace('_', ' ', $status))) ?></option><?php endforeach; ?></select></div>
                <div class="col-md-4"><label class="form-label">Asset</label><select name="asset_id" class="form-select"><option value="0">All assets</option><?php foreach ($assets as $asset): ?><option value="<?= esc($asset['id']) ?>" <?= (int) $filters['asset_id'] === (int) $asset['id'] ? 'selected' : '' ?>><?= esc($asset['name']) ?><?= !empty($asset['lab_name']) ? ' - ' . esc($asset['lab_name']) : '' ?></option><?php endforeach; ?></select></div>
                <div class="col-md-3"><label class="form-label">Scope</label><select name="scope" class="form-select"><option value="">All records</option><option value="mine" <?= $filters['scope'] === 'mine' ? 'selected' : '' ?>>Assigned to me</option></select></div>
                <div class="col-md-2 d-grid"><button type="submit" class="btn btn-success">Filter</button></div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div><h5 class="mb-1">Maintenance Workflow</h5><small class="text-muted">User-reported faults should move through schedule, repair, testing, and completion with clear evidence.</small></div>
            <a href="/technician/maintenance/create" class="btn btn-success btn-sm"><i class="bi bi-plus-circle"></i> Plan Preventive Work</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light"><tr><th>Case</th><th>Asset</th><th>Stage</th><th>Priority</th><th>Reporter / Unit</th><th>Updated</th><th class="text-end">Action</th></tr></thead>
                    <tbody>
                        <?php if (empty($records)): ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted">No maintenance records matched the selected filters.</td></tr>
                        <?php else: ?>
                            <?php foreach ($records as $record): ?>
                                <?php $label = $statusLabels[$record['status']] ?? ucwords(str_replace('_', ' ', $record['status'])); ?>
                                <tr>
                                    <td><div class="fw-semibold"><?= esc($record['title']) ?></div><small class="text-muted">#<?= esc($record['id']) ?> | <?= esc(ucfirst($record['issue_type'])) ?></small></td>
                                    <td><div><?= esc($record['asset_name'] ?? '-') ?></div><small class="text-muted"><?= esc($record['laboratory_name'] ?? '-') ?></small></td>
                                    <td><span class="badge text-bg-secondary"><?= esc($label) ?></span></td>
                                    <td><span class="badge text-bg-light border text-uppercase"><?= esc($record['priority']) ?></span></td>
                                    <td>
                                        <div class="small"><?= esc($record['reported_by_name'] ?: $record['reported_by_username'] ?: 'System') ?></div>
                                        <div class="small text-muted"><?= esc($record['unit_reference'] ?: 'No unit reference') ?></div>
                                    </td>
                                    <td><?= esc($record['updated_at'] ? date('d M Y H:i', strtotime($record['updated_at'])) : '-') ?></td>
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
