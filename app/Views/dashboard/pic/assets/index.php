<?php
/** @var array $assets */
/** @var array $labs */
/** @var array $filters */
$assets  = $assets  ?? [];
$labs    = $labs    ?? [];
$filters = $filters ?? ['q' => '', 'lab_id' => 0, 'status' => ''];

$statusBadge = [
    'available'      => 'success',
    'maintenance'    => 'warning',
    'faulty'         => 'danger',
    'decommissioned' => 'secondary',
];
?>
<?= $this->extend('layouts/main_user') ?>
<?= $this->section('content') ?>

<div class="container py-4">

    <div class="slams-page-header">
        <div class="slams-page-header-left">
            <h1 class="slams-page-title">Asset Management</h1>
            <p class="slams-page-subtitle">Assets in your assigned laboratories.</p>
        </div>
        <div class="slams-page-header-actions">
            <a href="/dashboard/pic/assets/create" class="btn btn-primary btn-sm">
                <i class="bi bi-plus-circle me-1"></i> Add Asset
            </a>
        </div>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success border-0 shadow-sm"><?= esc(session()->getFlashdata('message')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <?php if (empty($labs)): ?>
        <div class="alert alert-warning border-0 shadow-sm">
            You are not assigned to any laboratory. Ask an administrator to assign a laboratory to your account first.
        </div>
    <?php else: ?>

    <!-- Filters -->
    <form method="get" action="/dashboard/pic/assets" class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-2 align-items-end">
                <div class="col-md-4">
                    <label class="form-label small mb-1">Search</label>
                    <input type="text" name="q" class="form-control form-control-sm"
                           value="<?= esc($filters['q']) ?>" placeholder="Name, code, category...">
                </div>
                <div class="col-md-3">
                    <label class="form-label small mb-1">Laboratory</label>
                    <select name="lab_id" class="form-select form-select-sm">
                        <option value="">All labs</option>
                        <?php foreach ($labs as $lab): ?>
                            <option value="<?= (int) $lab['id'] ?>" <?= (int) $filters['lab_id'] === (int) $lab['id'] ? 'selected' : '' ?>>
                                <?= esc($lab['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All</option>
                        <option value="available"   <?= $filters['status'] === 'available'   ? 'selected' : '' ?>>Available</option>
                        <option value="maintenance" <?= $filters['status'] === 'maintenance' ? 'selected' : '' ?>>Maintenance</option>
                        <option value="faulty"         <?= $filters['status'] === 'faulty'         ? 'selected' : '' ?>>Faulty</option>
                        <option value="decommissioned" <?= $filters['status'] === 'decommissioned' ? 'selected' : '' ?>>Decommissioned</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="/dashboard/pic/assets" class="btn btn-outline-secondary btn-sm">Clear</a>
                </div>
            </div>
        </div>
    </form>

    <!-- Table -->
    <?php if (empty($assets)): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-box-seam fs-1 mb-2 d-block"></i>
                No assets found.
                <a href="/dashboard/pic/assets/create" class="d-block mt-2">Add the first asset</a>
            </div>
        </div>
    <?php else: ?>
        <div class="card border-0 shadow-sm">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Laboratory</th>
                            <th>Category</th>
                            <th class="text-center">Qty Available</th>
                            <th class="text-center">Total Qty</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assets as $asset): ?>
                        <tr>
                            <td class="small text-muted"><?= esc($asset['asset_code'] ?? '—') ?></td>
                            <td class="fw-semibold"><?= esc($asset['name']) ?></td>
                            <td class="small"><?= esc($asset['lab_name'] ?? '—') ?></td>
                            <td class="small"><?= esc($asset['category'] ?? '—') ?></td>
                            <td class="text-center"><?= (int) ($asset['quantity'] ?? 0) ?></td>
                            <td class="text-center"><?= (int) ($asset['total_quantity'] ?? 0) ?></td>
                            <td>
                                <span class="badge bg-<?= $statusBadge[$asset['status'] ?? ''] ?? 'secondary' ?>">
                                    <?= esc(ucfirst($asset['status'] ?? '—')) ?>
                                </span>
                                <?php if (($asset['maintenance_quantity'] ?? 0) > 0): ?>
                                    <span class="badge bg-warning text-dark ms-1">
                                        <?= (int) $asset['maintenance_quantity'] ?> in maint.
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <div class="d-inline-flex gap-1">
                                    <a href="/dashboard/pic/assets/edit/<?= (int) $asset['id'] ?>"
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <?php if (($asset['status'] ?? '') === 'decommissioned'): ?>
                                        <form method="post" action="/dashboard/pic/assets/decommission/<?= (int) $asset['id'] ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="restore">
                                            <button type="submit" class="btn btn-sm btn-outline-success" title="Restore asset"><i class="bi bi-arrow-counterclockwise"></i></button>
                                        </form>
                                    <?php else: ?>
                                        <form method="post" action="/dashboard/pic/assets/decommission/<?= (int) $asset['id'] ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <input type="hidden" name="action" value="decommission">
                                            <button type="submit" class="btn btn-sm btn-outline-secondary" title="Decommission asset"
                                                    onclick="return confirm('Decommission this asset? It will be removed from public views.')"><i class="bi bi-x-octagon"></i></button>
                                        </form>
                                    <?php endif; ?>
                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                            onclick="confirmDelete(<?= (int) $asset['id'] ?>, '<?= esc($asset['name'], 'js') ?>')">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (isset($pager)): ?>
                <div class="card-footer bg-white border-0"><?= $pager->links() ?></div>
            <?php endif; ?>
        </div>

        <!-- Delete confirmation modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Delete Asset</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        Are you sure you want to delete <strong id="deleteAssetName"></strong>?
                        This action cannot be undone. Assets with maintenance history cannot be deleted.
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <form id="deleteForm" method="post" class="d-inline">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-danger">Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <script>
        function confirmDelete(id, name) {
            document.getElementById('deleteAssetName').textContent = name;
            document.getElementById('deleteForm').action = '/dashboard/pic/assets/delete/' + id;
            new bootstrap.Modal(document.getElementById('deleteModal')).show();
        }
        </script>
    <?php endif; ?>
    <?php endif; ?>

    <div class="mt-3">
        <a href="/dashboard/pic" class="text-muted small">
            <i class="bi bi-arrow-left me-1"></i>Back to PIC Dashboard
        </a>
    </div>
</div>

<?= $this->endSection() ?>
