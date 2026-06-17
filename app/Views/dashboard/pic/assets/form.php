<?php
/** @var array|null $asset */
/** @var array $labs */
$asset  = $asset  ?? null;
$labs   = $labs   ?? [];
$isEdit = $asset !== null;
$title  = $isEdit ? 'Edit Asset' : 'Add Asset';
$action = $isEdit
    ? '/dashboard/pic/assets/update/' . (int) $asset['id']
    : '/dashboard/pic/assets/store';
$errors = session()->getFlashdata('errors') ?? [];
?>
<?= $this->extend('layouts/main_admin') ?>
<?= $this->section('content') ?>

<div class="container py-4">

    <div class="slams-page-header">
        <div class="slams-page-header-left">
            <h1 class="slams-page-title"><?= esc($title) ?></h1>
            <p class="slams-page-subtitle">
                <?= $isEdit ? 'Update asset details for your assigned laboratory.' : 'Add a new asset to your assigned laboratory.' ?>
            </p>
        </div>
        <div class="slams-page-header-actions">
            <a href="/dashboard/pic/assets" class="btn btn-glass btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Assets
            </a>
        </div>
    </div>

    <?php if (! empty($errors)): ?>
        <div class="alert alert-danger border-0 shadow-sm">
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h5 class="mb-0">Asset Record</h5></div>
                <div class="card-body">
                    <form method="post" action="<?= $action ?>" enctype="multipart/form-data" class="row g-3">
                        <?= csrf_field() ?>

                        <div class="col-md-6">
                            <label class="form-label">Laboratory <span class="text-danger">*</span></label>
                            <select name="lab_id" class="form-select" required>
                                <option value="">— Select laboratory —</option>
                                <?php foreach ($labs as $lab): ?>
                                    <option value="<?= (int) $lab['id'] ?>"
                                        <?= (int) old('lab_id', $asset['lab_id'] ?? 0) === (int) $lab['id'] ? 'selected' : '' ?>>
                                        <?= esc($lab['name']) ?> — <?= esc($lab['room'] ?? '') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Asset Code <span class="text-danger">*</span></label>
                            <input type="text" name="asset_code" class="form-control"
                                   value="<?= esc(old('asset_code', $asset['asset_code'] ?? '')) ?>"
                                   placeholder="e.g. COMP-001" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Asset Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control"
                                   value="<?= esc(old('name', $asset['name'] ?? '')) ?>"
                                   placeholder="e.g. Desktop Computer" required>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Category</label>
                            <input type="text" name="category" class="form-control"
                                   value="<?= esc(old('category', $asset['category'] ?? '')) ?>"
                                   placeholder="e.g. Computer, Instrument">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Brand</label>
                            <input type="text" name="brand" class="form-control"
                                   value="<?= esc(old('brand', $asset['brand'] ?? '')) ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Model</label>
                            <input type="text" name="model" class="form-control"
                                   value="<?= esc(old('model', $asset['model'] ?? '')) ?>">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Serial Number</label>
                            <input type="text" name="serial_number" class="form-control"
                                   value="<?= esc(old('serial_number', $asset['serial_number'] ?? '')) ?>">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Total Quantity <span class="text-danger">*</span></label>
                            <input type="number" min="1" name="total_quantity" class="form-control"
                                   value="<?= esc(old('total_quantity', $asset['total_quantity'] ?? 1)) ?>" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Purchase Date</label>
                            <input type="date" name="purchase_date" class="form-control"
                                   value="<?= esc(old('purchase_date', $asset['purchase_date'] ?? '')) ?>">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Location Note</label>
                            <input type="text" name="location_note" class="form-control"
                                   value="<?= esc(old('location_note', $asset['location_note'] ?? '')) ?>"
                                   placeholder="e.g. Row 3, Bench 2">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Specifications</label>
                            <textarea name="specifications" class="form-control" rows="3"><?= esc(old('specifications', $asset['specifications'] ?? '')) ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Asset Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <?php if ($isEdit && ! empty($asset['image_url'])): ?>
                                <div class="mt-2">
                                    <img src="<?= esc($asset['image_url']) ?>" alt="Asset image"
                                         style="width:160px;border-radius:8px;object-fit:cover;">
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a href="/dashboard/pic/assets" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i>
                                <?= $isEdit ? 'Save Changes' : 'Create Asset' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white"><h6 class="mb-0">Notes</h6></div>
                <div class="card-body small text-muted">
                    <ul class="ps-3 mb-0">
                        <li>Asset code must be unique system-wide.</li>
                        <li>Serial number must be unique if provided.</li>
                        <li>Total quantity is the full count of this asset type in the lab.</li>
                        <li>Available quantity is calculated automatically after maintenance records are applied.</li>
                        <li>Assets with maintenance history cannot be deleted — update their status instead.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
