<?= $this->extend('layouts/main_admin') ?>
<?= $this->section('content') ?>

<?php
$rows     = $rows     ?? [];
$dayNames = $dayNames ?? [];
$valid    = array_filter($rows, fn($r) => empty($r['error']));
$errors   = array_filter($rows, fn($r) => ! empty($r['error']));
?>

<div class="container-fluid">
    <div class="mb-4">
        <a href="/admin/reservations/upload" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left me-1"></i>Back to Upload</a>
        <h1 class="h3 mb-1">Preview Class Schedule Import</h1>
        <p class="text-muted mb-0">
            <strong><?= count($valid) ?></strong> valid row(s) ready to import.
            <?php if ($errors): ?>
                <strong class="text-danger"><?= count($errors) ?> row(s) have errors and will be skipped.</strong>
            <?php endif; ?>
        </p>
    </div>

    <?php if (! empty($valid)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-success-subtle border-0">
                <h6 class="mb-0 text-success"><i class="bi bi-check-circle me-2"></i>Valid rows (<?= count($valid) ?>)</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Line</th><th>Lab</th><th>Title</th><th>Day</th>
                                <th>Time</th><th>Valid From</th><th>Valid Until</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($valid as $row): ?>
                                <tr>
                                    <td class="text-muted"><?= esc($row['line']) ?></td>
                                    <td><?= esc($row['lab_name']) ?></td>
                                    <td><?= esc($row['title']) ?></td>
                                    <td><?= esc($row['day_label'] ?? ($dayNames[$row['day_of_week']] ?? '?')) ?></td>
                                    <td class="text-nowrap"><?= esc(substr($row['start_time'], 0, 5)) ?> – <?= esc(substr($row['end_time'], 0, 5)) ?></td>
                                    <td><?= $row['valid_from']  ? esc(date('d-m-Y', strtotime($row['valid_from'])))  : '—' ?></td>
                                    <td><?= $row['valid_until'] ? esc(date('d-m-Y', strtotime($row['valid_until']))) : '—' ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (! empty($errors)): ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-danger-subtle border-0">
                <h6 class="mb-0 text-danger"><i class="bi bi-exclamation-triangle me-2"></i>Rows with errors (will be skipped)</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm align-middle mb-0">
                        <thead class="table-light">
                            <tr><th>Line</th><th>Error</th><th>Raw Value</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach ($errors as $row): ?>
                                <tr>
                                    <td class="text-muted"><?= esc($row['line']) ?></td>
                                    <td class="text-danger"><?= esc($row['error']) ?></td>
                                    <td class="small text-muted font-monospace"><?= esc($row['raw'] ?? '') ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (empty($rows)): ?>
        <div class="alert alert-warning border-0">The CSV file contained no data rows.</div>
    <?php endif; ?>

    <div class="d-flex gap-3 align-items-center">
        <?php if (! empty($valid)): ?>
            <form method="post" action="/admin/reservations/upload/confirm">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-success">
                    <i class="bi bi-check2-all me-2"></i>Confirm Import (<?= count($valid) ?> rows)
                </button>
            </form>
        <?php endif; ?>
        <a href="/admin/reservations/upload" class="btn btn-outline-secondary">Upload Different File</a>
        <a href="/admin/reservations" class="btn btn-link text-muted">Cancel</a>
    </div>
</div>

<?= $this->endSection() ?>
