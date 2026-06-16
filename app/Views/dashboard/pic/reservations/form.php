<?= $this->extend('layouts/main_user') ?>
<?= $this->section('content') ?>

<?php
$record   = $record   ?? null;
$isEdit   = $record !== null;
$dayNames = $dayNames ?? [];
$old      = fn(string $key, $default = '') => old($key, $isEdit ? ($record[$key] ?? $default) : $default);
?>

<div class="slams-page-header">
    <div class="slams-page-header-left">
        <h1 class="slams-page-title"><?= esc($title ?? 'Reservation') ?></h1>
    </div>
    <div class="slams-page-header-actions">
        <a href="/pic/reservations" class="btn btn-glass btn-sm"><i class="bi bi-arrow-left me-1"></i>Back</a>
    </div>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm" style="max-width:680px">
    <div class="card-body">
        <form method="post" action="<?= $isEdit ? '/pic/reservations/update/' . esc($record['id']) : '/pic/reservations/store' ?>">
            <?= csrf_field() ?>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Laboratory <span class="text-danger">*</span></label>
                    <select name="lab_id" class="form-select" required>
                        <option value="">Select lab…</option>
                        <?php foreach ($labs as $lab): ?>
                            <option value="<?= esc($lab['id']) ?>" <?= (int) $old('lab_id') === (int) $lab['id'] ? 'selected' : '' ?>>
                                <?= esc($lab['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select" required>
                        <option value="manual" <?= $old('type', 'manual') === 'manual' ? 'selected' : '' ?>>Manual Block (walk-in use)</option>
                        <option value="class"  <?= $old('type', 'manual') === 'class'  ? 'selected' : '' ?>>Class Schedule</option>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" class="form-control" required
                           value="<?= esc($old('title')) ?>"
                           placeholder="e.g. Walk-in Use or BDA2223 – Fluid Mechanics">
                    <div class="form-text">Shown to users in the booking modal when this slot is unavailable.</div>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Recurrence <span class="text-danger">*</span></label>
                    <select name="recurrence" id="recurrenceSelect" class="form-select" required>
                        <option value="none"   <?= $old('recurrence', 'none') === 'none'   ? 'selected' : '' ?>>One-off (specific date)</option>
                        <option value="weekly" <?= $old('recurrence', 'none') === 'weekly' ? 'selected' : '' ?>>Weekly (recurring)</option>
                    </select>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">Start Time <span class="text-danger">*</span></label>
                    <input type="time" name="start_time" class="form-control" required
                           value="<?= esc(substr((string) $old('start_time'), 0, 5)) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold">End Time <span class="text-danger">*</span></label>
                    <input type="time" name="end_time" class="form-control" required
                           value="<?= esc(substr((string) $old('end_time'), 0, 5)) ?>">
                </div>

                <div id="oneOffFields" class="col-md-4">
                    <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                    <input type="date" name="date" id="dateInput" class="form-control"
                           value="<?= esc($old('date')) ?>" min="<?= date('Y-m-d') ?>">
                </div>

                <div id="weeklyFields" class="col-12 d-none">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Day of Week <span class="text-danger">*</span></label>
                            <select name="day_of_week" id="dowSelect" class="form-select">
                                <?php foreach ($dayNames as $dow => $name): ?>
                                    <option value="<?= esc($dow) ?>" <?= (string) $old('day_of_week', '') === (string) $dow ? 'selected' : '' ?>>
                                        <?= esc($name) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Valid From</label>
                            <input type="date" name="valid_from" class="form-control"
                                   value="<?= esc($old('valid_from')) ?>">
                            <div class="form-text">Leave blank for no start restriction.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Valid Until</label>
                            <input type="date" name="valid_until" class="form-control"
                                   value="<?= esc($old('valid_until')) ?>">
                            <div class="form-text">Leave blank for no end restriction.</div>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Notes <span class="text-muted small">(optional)</span></label>
                    <textarea name="notes" class="form-control" rows="2"><?= esc($old('notes')) ?></textarea>
                </div>
            </div>

            <div class="d-flex gap-2 mt-4">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i><?= $isEdit ? 'Save Changes' : 'Add Reservation' ?>
                </button>
                <a href="/pic/reservations" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
(function () {
    const sel    = document.getElementById('recurrenceSelect');
    const oneOff = document.getElementById('oneOffFields');
    const weekly = document.getElementById('weeklyFields');
    const dateIn = document.getElementById('dateInput');
    const dowSel = document.getElementById('dowSelect');

    function toggle() {
        const isWeekly = sel.value === 'weekly';
        oneOff.classList.toggle('d-none', isWeekly);
        weekly.classList.toggle('d-none', !isWeekly);
        dateIn.required = !isWeekly;
        dowSel.required = isWeekly;
    }

    sel.addEventListener('change', toggle);
    toggle();
})();
</script>
<?= $this->endSection() ?>

<?= $this->endSection() ?>
