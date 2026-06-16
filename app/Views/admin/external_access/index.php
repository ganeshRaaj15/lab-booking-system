<?php
/** @var array $requests */
/** @var array $filters */
/** @var array $counts */
$requests = $requests ?? [];
$filters  = $filters  ?? ['status' => ''];
$counts   = $counts   ?? ['pending' => 0, 'approved' => 0, 'rejected' => 0];

$statusBadge = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
?>
<?= $this->extend('layouts/main_user') ?>
<?= $this->section('content') ?>

<div class="container-fluid py-4">

    <div class="slams-page-header">
        <div class="slams-page-header-left">
            <h1 class="slams-page-title">External Access Requests</h1>
            <p class="slams-page-subtitle">Review, approve, or reject external user registration requests.</p>
        </div>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success border-0 shadow-sm"><?= esc(session()->getFlashdata('message')) ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <!-- Status KPI cards -->
    <div class="row g-3 mb-4">
        <?php foreach (['pending' => 'Pending Review', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $key => $label): ?>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center py-3">
                <div class="fs-3 fw-bold text-<?= $statusBadge[$key] ?>"><?= (int) $counts[$key] ?></div>
                <div class="small text-muted"><?= $label ?></div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Filter -->
    <form method="get" class="card border-0 shadow-sm mb-4">
        <div class="card-body py-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All statuses</option>
                        <option value="pending"  <?= $filters['status'] === 'pending'  ? 'selected' : '' ?>>Pending</option>
                        <option value="approved" <?= $filters['status'] === 'approved' ? 'selected' : '' ?>>Approved</option>
                        <option value="rejected" <?= $filters['status'] === 'rejected' ? 'selected' : '' ?>>Rejected</option>
                    </select>
                </div>
                <div class="col-auto d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm">Filter</button>
                    <a href="/admin/external-access" class="btn btn-outline-secondary btn-sm">Clear</a>
                </div>
            </div>
        </div>
    </form>

    <div class="card border-0 shadow-sm">
        <?php if (empty($requests)): ?>
            <div class="card-body text-center py-5 text-muted">
                <i class="bi bi-person-check fs-1 d-block mb-2"></i>No external access requests found.
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Organization</th>
                            <th>Status</th>
                            <th>Submitted</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $req): ?>
                        <tr>
                            <td class="fw-semibold"><?= esc($req['full_name']) ?></td>
                            <td class="small"><?= esc($req['email']) ?></td>
                            <td class="small"><?= esc($req['organization']) ?></td>
                            <td>
                                <span class="badge bg-<?= $statusBadge[$req['status'] ?? ''] ?? 'secondary' ?>">
                                    <?= esc(ucfirst($req['status'] ?? '—')) ?>
                                </span>
                            </td>
                            <td class="small text-muted">
                                <?= $req['created_at'] ? date('d-m-Y', strtotime((string) $req['created_at'])) : '—' ?>
                            </td>
                            <td class="text-end">
                                <a href="/admin/external-access/<?= (int) $req['id'] ?>"
                                   class="btn btn-sm btn-outline-primary">View</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php if (isset($pager)): ?>
                <div class="card-footer bg-white border-0">
                    <?= $pager->links() ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
