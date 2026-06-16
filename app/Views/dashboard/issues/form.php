<?php
/** @var array $labs */
/** @var array $priorities */
/** @var array $recentReports */
$labs          = $labs          ?? [];
$priorities    = $priorities    ?? [];
$recentReports = $recentReports ?? [];
$oldLabId      = (int) old('lab_id');
$oldAssetId    = (int) old('asset_id');
?>
<?= $this->extend('layouts/main_user') ?>
<?= $this->section('content') ?>

<div class="container py-4">
    <div class="slams-page-header">
        <div class="slams-page-header-left">
            <h1 class="slams-page-title">Report Asset Issue</h1>
            <p class="slams-page-subtitle">Select the laboratory, then the affected equipment. Fill in the details and attach a photo if possible.</p>
        </div>
        <div class="slams-page-header-actions">
            <a href="/dashboard" class="btn btn-glass btn-sm"><i class="bi bi-arrow-left me-1"></i>Back to Dashboard</a>
        </div>
    </div>

    <?php if (session()->getFlashdata('success')): ?><div class="alert alert-success shadow-sm border-0"><?= esc(session()->getFlashdata('success')) ?></div><?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger shadow-sm border-0"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-7">
            <div class="glass-card">
                <div class="glass-card-header"><h5 class="mb-0">New Issue Report</h5></div>
                <div class="card-body px-4 pb-4">
                    <form method="post" action="/dashboard/report-issue/store" class="row g-3" enctype="multipart/form-data" id="issueForm">
                        <?= csrf_field() ?>

                        <div class="col-12">
                            <div class="alert alert-info border-0 mb-0">
                                <div class="fw-semibold text-dark mb-2">What You Need To Fill</div>
                                <ul class="small mb-0 ps-3">
                                    <li>Select the laboratory first, then choose the affected equipment.</li>
                                    <li>Enter how many units are affected and identify the exact unit if there are multiple similar units.</li>
                                    <li>Write a short summary and a clear problem description.</li>
                                    <li>Add a photo if it helps show the issue.</li>
                                </ul>
                            </div>
                        </div>

                        <!-- Step 1: Lab selector -->
                        <div class="col-12">
                            <label class="form-label">Laboratory <span class="text-danger">*</span></label>
                            <select name="lab_id" id="labSelect" class="form-select" required>
                                <option value="">— Select laboratory first —</option>
                                <?php foreach ($labs as $lab): ?>
                                    <option value="<?= (int) $lab['id'] ?>" <?= $oldLabId === (int) $lab['id'] ? 'selected' : '' ?>>
                                        <?= esc($lab['name']) ?><?= ! empty($lab['room']) ? ' — ' . esc($lab['room']) : '' ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Select the laboratory where the faulty equipment is located.</div>
                        </div>

                        <!-- Step 2: Asset selector (populated via AJAX) -->
                        <div class="col-12">
                            <label class="form-label">Equipment With The Problem <span class="text-danger">*</span></label>
                            <select name="asset_id" id="assetSelect" class="form-select" required disabled>
                                <option value="">— Select laboratory first —</option>
                            </select>
                            <div class="form-text" id="assetHint">Pick the exact asset. The option text shows available units.</div>
                            <div id="assetLoading" class="form-text text-primary d-none"><i class="bi bi-arrow-repeat me-1"></i>Loading assets…</div>
                            <div id="assetError" class="form-text text-danger d-none"></div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Short Problem Summary <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" value="<?= esc(old('title')) ?>" placeholder="Example: PC does not power on" required>
                            <div class="form-text">Use a short title that someone else can recognize quickly.</div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Units Affected <span class="text-danger">*</span></label>
                            <input type="number" name="quantity_affected" class="form-control" min="1" value="<?= esc(old('quantity_affected', 1)) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Urgency <span class="text-danger">*</span></label>
                            <select name="priority" class="form-select" required>
                                <?php foreach ($priorities as $priority): ?>
                                    <option value="<?= esc($priority) ?>" <?= old('priority', 'medium') === $priority ? 'selected' : '' ?>><?= esc(ucfirst($priority)) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Unit / Workstation Reference</label>
                            <input type="text" name="unit_reference" class="form-control" value="<?= esc(old('unit_reference')) ?>" placeholder="Example: PC-07, Seat B3, Projector Unit 2">
                            <div class="form-text">Required for multi-unit equipment such as PCs, monitors, or laptops.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Problem Description <span class="text-danger">*</span></label>
                            <textarea name="description" rows="6" class="form-control" placeholder="Describe what is wrong, what you observed, and when it happened…" required><?= esc(old('description')) ?></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Photo Evidence</label>
                            <input type="file" name="report_photo" class="form-control" accept="image/png,image/jpeg,image/webp">
                            <div class="form-text">Optional but recommended. A clear photo helps the technician identify the issue faster.</div>
                        </div>
                        <div class="col-12 d-flex justify-content-end">
                            <button type="submit" class="btn btn-primary"><i class="bi bi-send me-2"></i>Submit Report</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card shadow-sm border-0 rounded-4 mb-4">
                <div class="card-header bg-white border-0 pt-4 px-4"><h6 class="fw-semibold text-primary mb-1">What Happens Next</h6></div>
                <div class="card-body px-4 pb-4 small text-muted">
                    <p class="mb-2"><strong>1.</strong> You report the issue and identify the affected unit.</p>
                    <p class="mb-2"><strong>2.</strong> The technician reviews the report, adds a diagnosis, and schedules the maintenance work.</p>
                    <p class="mb-2"><strong>3.</strong> The technician records the repair work and tests the equipment.</p>
                    <p class="mb-0"><strong>4.</strong> The unit becomes available again only after the technician completes the workflow with notes and proof.</p>
                </div>
            </div>
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-header bg-white border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h6 class="fw-semibold text-primary mb-1">Your Recent Reports</h6>
                    <span class="badge bg-light text-dark border"><?= esc(count($recentReports)) ?></span>
                </div>
                <div class="card-body px-4 pb-4">
                    <?php if (empty($recentReports)): ?>
                        <p class="text-muted small mb-0">You have not submitted any equipment issue reports yet.</p>
                    <?php else: ?>
                        <div class="d-flex flex-column gap-3">
                            <?php foreach ($recentReports as $report): ?>
                                <div class="border rounded-3 p-3 bg-light">
                                    <div class="fw-semibold"><?= esc($report['title']) ?></div>
                                    <div class="small text-muted"><?= esc($report['asset_name'] ?? '—') ?> | <?= esc($report['laboratory_name'] ?? '—') ?></div>
                                    <?php if (! empty($report['unit_reference'])): ?><div class="small text-muted">Unit: <?= esc($report['unit_reference']) ?></div><?php endif; ?>
                                    <div class="small text-muted text-uppercase mt-1"><?= esc(str_replace('_', ' ', $report['status'])) ?> | <?= esc($report['priority']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
(function () {
    const labSelect   = document.getElementById('labSelect');
    const assetSelect = document.getElementById('assetSelect');
    const loading     = document.getElementById('assetLoading');
    const errorEl     = document.getElementById('assetError');
    const hint        = document.getElementById('assetHint');

    const oldLabId   = <?= $oldLabId ?>;
    const oldAssetId = <?= $oldAssetId ?>;

    function setLoading(on) {
        loading.classList.toggle('d-none', !on);
        hint.classList.toggle('d-none', on);
    }

    function showError(msg) {
        errorEl.textContent = msg;
        errorEl.classList.remove('d-none');
    }
    function clearError() {
        errorEl.classList.add('d-none');
    }

    function populateAssets(assets, selectId) {
        assetSelect.innerHTML = '';
        if (assets.length === 0) {
            const opt = new Option('— No available assets in this laboratory —', '');
            assetSelect.appendChild(opt);
            assetSelect.disabled = true;
            return;
        }
        assetSelect.appendChild(new Option('— Select equipment —', ''));
        assets.forEach(function (a) {
            const code  = a.asset_code ? a.asset_code + ' — ' : '';
            const avail = ' (' + a.quantity + ' of ' + a.total_quantity + ' available)';
            const opt   = new Option(code + a.name + avail, a.id);
            if (a.id === selectId) opt.selected = true;
            assetSelect.appendChild(opt);
        });
        assetSelect.disabled = false;
        assetSelect.required = true;
    }

    function fetchAssets(labId, selectId) {
        setLoading(true);
        clearError();
        assetSelect.innerHTML = '';
        assetSelect.disabled  = true;

        fetch('/dashboard/report-issue/assets-by-lab/' + labId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(function (r) { return r.json(); })
        .then(function (data) {
            setLoading(false);
            if (data.status === 'success') {
                populateAssets(data.assets, selectId);
            } else {
                showError(data.message || 'Could not load assets.');
            }
        })
        .catch(function () {
            setLoading(false);
            showError('Network error — could not load assets. Please refresh and try again.');
        });
    }

    labSelect.addEventListener('change', function () {
        clearError();
        const labId = parseInt(this.value, 10);
        if (!labId) {
            assetSelect.innerHTML = '<option value="">— Select laboratory first —</option>';
            assetSelect.disabled  = true;
            return;
        }
        fetchAssets(labId, 0);
    });

    // On page load: if returning after validation failure, reload the old selection.
    if (oldLabId > 0) {
        fetchAssets(oldLabId, oldAssetId);
    }
}());
</script>

<?= $this->endSection() ?>
