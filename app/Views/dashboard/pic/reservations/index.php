<?= $this->extend('layouts/main_user') ?>
<?= $this->section('content') ?>

<?php
$filters  = $filters  ?? ['lab_id' => 0, 'type' => ''];
$dayNames = $dayNames ?? [];
?>

<div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
    <div>
        <h1 class="h3 mb-1">Lab Reservations</h1>
        <p class="text-muted mb-0">Block time slots for walk-in use or recurring class schedules.</p>
    </div>
    <a href="/pic/reservations/create" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Reservation</a>
</div>

<?php if (session()->getFlashdata('message')): ?>
    <div class="alert alert-success border-0 shadow-sm"><?= esc(session()->getFlashdata('message')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?php if (! empty($labs)): ?>
<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="/pic/reservations" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small text-muted">Laboratory</label>
                <select name="lab_id" class="form-select">
                    <option value="">All my labs</option>
                    <?php foreach ($labs as $lab): ?>
                        <option value="<?= esc($lab['id']) ?>" <?= (int) $filters['lab_id'] === (int) $lab['id'] ? 'selected' : '' ?>>
                            <?= esc($lab['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Type</label>
                <select name="type" class="form-select">
                    <option value="">All types</option>
                    <option value="manual" <?= $filters['type'] === 'manual' ? 'selected' : '' ?>>Manual Block</option>
                    <option value="class"  <?= $filters['type'] === 'class'  ? 'selected' : '' ?>>Class Schedule</option>
                </select>
            </div>
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-funnel me-1"></i>Filter</button>
                <a href="/pic/reservations" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <?php if (empty($reservations)): ?>
            <div class="text-center text-muted py-5">No reservations found. <a href="/pic/reservations/create">Add one</a>.</div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Title</th>
                            <th>Lab</th>
                            <th>Type</th>
                            <th>Schedule</th>
                            <th>Time</th>
                            <th>Validity</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservations as $r): ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= esc($r['title']) ?></div>
                                    <?php if (! empty($r['notes'])): ?>
                                        <div class="small text-muted"><?= esc($r['notes']) ?></div>
                                    <?php endif; ?>
                                </td>
                                <td><?= esc($r['lab_name'] ?? '—') ?></td>
                                <td>
                                    <?php if ($r['type'] === 'class'): ?>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">Class</span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">Manual</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($r['recurrence'] === 'weekly'): ?>
                                        <span class="badge bg-secondary-subtle text-secondary">Every <?= esc($dayNames[(int) $r['day_of_week']] ?? '?') ?></span>
                                    <?php else: ?>
                                        <?= esc(date('d-m-Y', strtotime((string) $r['date']))) ?>
                                    <?php endif; ?>
                                </td>
                                <td class="text-nowrap">
                                    <?= esc(substr((string) $r['start_time'], 0, 5)) ?> – <?= esc(substr((string) $r['end_time'], 0, 5)) ?>
                                </td>
                                <td class="small text-muted text-nowrap">
                                    <?php if ($r['recurrence'] === 'weekly'): ?>
                                        <?= $r['valid_from']  ? esc(date('d-m-Y', strtotime((string) $r['valid_from'])))  : '∞' ?>
                                        –
                                        <?= $r['valid_until'] ? esc(date('d-m-Y', strtotime((string) $r['valid_until']))) : '∞' ?>
                                    <?php else: ?>
                                        —
                                    <?php endif; ?>
                                </td>
                                <td class="text-center text-nowrap">
                                    <a href="/pic/reservations/edit/<?= esc($r['id']) ?>" class="btn btn-sm btn-outline-secondary me-1">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <form action="/pic/reservations/delete/<?= esc($r['id']) ?>" method="post" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="if(confirm('Delete this reservation?')){this.closest('form').submit();}">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
