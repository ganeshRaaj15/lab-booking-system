<?= $this->extend('layouts/main_admin') ?>
<?= $this->section('content') ?>

<style>
.asset-metric { border-radius: 14px; background: #fff; border: 1px solid rgba(59,130,246,.12); box-shadow: 0 8px 24px rgba(15,23,42,.06); }
.asset-table-card { background: linear-gradient(135deg, rgba(255,255,255,.96), rgba(255,255,255,.99)); border: 1px solid rgba(59,130,246,.12); border-radius: 18px; box-shadow: 0 10px 30px rgba(15,23,42,.08); }
.asset-code { font-size: .75rem; letter-spacing: .06em; color: #64748b; text-transform: uppercase; }
.asset-thumb { width: 64px; height: 64px; object-fit: cover; border-radius: 14px; }
</style>

<?php
$totalAssets = count($assets);
$openMaintenance = array_sum(array_map(static fn($asset) => (int) $asset['maintenance_open'], $assets));
$unitsInMaintenance = array_sum(array_map(static fn($asset) => (int) ($asset['maintenance_quantity'] ?? 0), $assets));
?>

<div class="container-fluid">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Asset Management</h1>
            <p class="text-muted mb-0">Track equipment specifications, live availability, and maintenance history in one place.</p>
        </div>
        <a href="/admin/assets/create" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Asset</a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success border-0 shadow-sm"><?= esc(session()->getFlashdata('message')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="asset-metric p-3"><div class="text-muted small text-uppercase">Registered Assets</div><div class="display-6 fw-bold"><?= esc($totalAssets) ?></div></div></div>
        <div class="col-md-4"><div class="asset-metric p-3"><div class="text-muted small text-uppercase">Open Maintenance Records</div><div class="display-6 fw-bold"><?= esc($openMaintenance) ?></div></div></div>
        <div class="col-md-4"><div class="asset-metric p-3"><div class="text-muted small text-uppercase">Units Under Maintenance</div><div class="display-6 fw-bold"><?= esc($unitsInMaintenance) ?></div></div></div>
    </div>

    <div class="asset-table-card p-3 p-lg-4">
        <?php if (empty($assets)): ?>
            <div class="text-center text-muted py-5">
                <i class="bi bi-box-seam fs-1 d-block mb-3"></i>
                No assets have been added yet.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Asset</th>
                            <th>Lab</th>
                            <th>Specification</th>
                            <th>Availability</th>
                            <th>Maintenance</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assets as $asset): ?>
                            <?php $imagePath = !empty($asset['image']) ? base_url($asset['image']) : base_url('images/assets/placeholder_asset.png'); ?>
                            <?php $badge = ($asset['quantity'] ?? 0) > 0 ? (($asset['maintenance_quantity'] ?? 0) > 0 ? 'warning text-dark' : 'success') : 'secondary'; ?>
                            <tr>
                                <td>
                                    <div class="d-flex gap-3 align-items-center">
                                        <img src="<?= esc($imagePath) ?>" alt="Asset image" class="asset-thumb shadow-sm">
                                        <div>
                                            <div class="asset-code"><?= esc($asset['asset_code']) ?></div>
                                            <div class="fw-semibold"><?= esc($asset['name']) ?></div>
                                            <div class="small text-muted"><?= esc($asset['category'] ?: 'Uncategorized') ?></div>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= esc($asset['lab_name'] ?? '-') ?></div>
                                    <div class="small text-muted"><?= esc($asset['lab_room'] ?? '-') ?></div>
                                </td>
                                <td>
                                    <div><?= esc(trim(($asset['brand'] ?: '-') . ' ' . ($asset['model'] ?: ''))) ?></div>
                                    <div class="small text-muted">SN: <?= esc($asset['serial_number'] ?: '-') ?></div>
                                    <div class="small text-muted">Total stock: <?= esc($asset['total_quantity']) ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-<?= $badge ?> text-uppercase"><?= esc($asset['status']) ?></span>
                                    <div class="small text-muted mt-1">Available now: <?= esc($asset['quantity']) ?> unit(s)</div>
                                    <div class="small text-muted">Under maintenance: <?= esc($asset['maintenance_quantity']) ?> unit(s)</div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= esc($asset['maintenance_total']) ?> total record(s)</div>
                                    <div class="small text-muted"><?= esc($asset['maintenance_open']) ?> open</div>
                                    <div class="small text-muted">Last completed: <?= esc($asset['last_completed_at'] ? date('d M Y', strtotime($asset['last_completed_at'])) : '-') ?></div>
                                </td>
                                <td class="text-center">
                                    <a href="/admin/assets/edit/<?= esc($asset['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                    <button type="button" onclick="deleteAsset(<?= esc($asset['id']) ?>)" class="btn btn-sm btn-outline-danger"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function deleteAsset(id) {
    if (!confirm('Delete this asset? This will be blocked if maintenance history exists.')) {
        return;
    }
    fetch(`/admin/assets/delete/${id}`, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': '<?= csrf_hash() ?>'
        }
    }).then(async (response) => {
        if (!response.ok) {
            const data = await response.json().catch(() => ({}));
            alert(data.message || 'Unable to delete this asset.');
            return;
        }
        location.reload();
    });
}
</script>

<?= $this->endSection() ?>
