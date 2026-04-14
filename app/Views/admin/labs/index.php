<?= $this->extend('layouts/main_admin') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="d-flex flex-column flex-lg-row justify-content-between align-items-lg-center gap-3 mb-4">
        <div>
            <h1 class="h3 mb-1">Laboratory Management</h1>
            <p class="text-muted mb-0">Manage laboratory profiles, PIC ownership, capacity, and asset readiness.</p>
        </div>
        <a href="/admin/labs/create" class="btn btn-primary"><i class="bi bi-plus-circle me-2"></i>Add Lab</a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success border-0 shadow-sm"><?= esc(session()->getFlashdata('message')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <?php if (empty($labs)): ?>
                <div class="text-center text-muted py-5">No laboratories have been added yet.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Laboratory</th>
                                <th>PIC</th>
                                <th>Profile</th>
                                <th>Assets</th>
                                <th class="text-center">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($labs as $lab): ?>
                                <tr>
                                    <td>
                                        <div class="fw-semibold"><?= esc($lab['name']) ?></div>
                                        <div class="small text-muted">Room <?= esc($lab['room']) ?></div>
                                    </td>
                                    <td>
                                        <div><?= esc($lab['pic_name']) ?></div>
                                        <div class="small text-muted"><?= esc($lab['pic_email'] ?: '-') ?></div>
                                        <div class="small text-muted"><?= esc($lab['pic_phone'] ?: '-') ?></div>
                                    </td>
                                    <td>
                                        <div>Capacity: <?= esc($lab['capacity'] ?: '-') ?></div>
                                        <div class="small text-muted"><?= esc($lab['availability_note'] ?: 'No availability note') ?></div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= esc($lab['asset_total']) ?> asset(s)</div>
                                        <div class="small text-muted"><?= esc($lab['assets_in_maintenance']) ?> in maintenance</div>
                                        <div class="small text-muted"><?= esc($lab['faulty_assets']) ?> faulty</div>
                                    </td>
                                    <td class="text-center">
                                        <a href="/admin/labs/edit/<?= esc($lab['id']) ?>" class="btn btn-sm btn-outline-primary"><i class="bi bi-pencil"></i></a>
                                        <form method="post" action="/admin/labs/delete/<?= esc($lab['id']) ?>" class="d-inline">
                                            <?= csrf_field() ?>
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this laboratory?')"><i class="bi bi-trash"></i></button>
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
</div>

<?= $this->endSection() ?>