<?php
/** @var array $req */
$req        = $req ?? [];
$statusBadge = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
?>
<?= $this->extend('layouts/main_user') ?>
<?= $this->section('content') ?>

<div class="container py-4" style="max-width:800px">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-primary">Access Request #<?= (int) $req['id'] ?></h2>
            <p class="text-muted small mb-0"><?= esc($req['full_name']) ?> — <?= esc($req['organization']) ?></p>
        </div>
        <a href="/admin/external-access" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to List
        </a>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Request Details</h5>
                    <span class="badge bg-<?= $statusBadge[$req['status'] ?? ''] ?? 'secondary' ?>">
                        <?= esc(ucfirst($req['status'] ?? '—')) ?>
                    </span>
                </div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4 text-muted">Full Name</dt>
                        <dd class="col-sm-8"><?= esc($req['full_name']) ?></dd>

                        <dt class="col-sm-4 text-muted">Email</dt>
                        <dd class="col-sm-8"><?= esc($req['email']) ?></dd>

                        <dt class="col-sm-4 text-muted">Phone</dt>
                        <dd class="col-sm-8"><?= esc($req['phone'] ?? '—') ?></dd>

                        <dt class="col-sm-4 text-muted">Organization</dt>
                        <dd class="col-sm-8"><?= esc($req['organization']) ?></dd>

                        <dt class="col-sm-4 text-muted">Purpose</dt>
                        <dd class="col-sm-8"><?= nl2br(esc($req['purpose'])) ?></dd>

                        <?php if (! empty($req['notes'])): ?>
                        <dt class="col-sm-4 text-muted">Notes</dt>
                        <dd class="col-sm-8"><?= nl2br(esc($req['notes'])) ?></dd>
                        <?php endif; ?>

                        <dt class="col-sm-4 text-muted">Submitted</dt>
                        <dd class="col-sm-8"><?= $req['created_at'] ? date('d-m-Y H:i', strtotime((string) $req['created_at'])) : '—' ?></dd>

                        <?php if ($req['status'] !== 'pending'): ?>
                        <dt class="col-sm-4 text-muted">Reviewed At</dt>
                        <dd class="col-sm-8"><?= $req['reviewed_at'] ? date('d-m-Y H:i', strtotime((string) $req['reviewed_at'])) : '—' ?></dd>
                        <?php endif; ?>

                        <?php if (! empty($req['rejection_reason'])): ?>
                        <dt class="col-sm-4 text-muted">Rejection Reason</dt>
                        <dd class="col-sm-8 text-danger"><?= nl2br(esc($req['rejection_reason'])) ?></dd>
                        <?php endif; ?>
                    </dl>
                </div>
            </div>

            <?php if ($req['status'] === 'pending'): ?>
            <!-- Approve -->
            <div class="card border-0 shadow-sm border-success mb-3" style="border-left:4px solid var(--bs-success) !important">
                <div class="card-body">
                    <h6 class="text-success mb-2"><i class="bi bi-check-circle me-1"></i>Approve Request</h6>
                    <p class="small text-muted mb-3">
                        Approving will create an external user account with a temporary password and send a login link to <strong><?= esc($req['email']) ?></strong>.
                    </p>
                    <form method="post" action="/admin/external-access/<?= (int) $req['id'] ?>/approve">
                        <?= csrf_field() ?>
                        <button type="button" class="btn btn-success"
                                onclick="if(confirm('Approve this request and create an external account for <?= esc(addslashes($req['email'])) ?>?')){this.closest('form').submit();}">
                            <i class="bi bi-person-plus me-1"></i>Approve &amp; Create Account
                        </button>
                    </form>
                </div>
            </div>

            <!-- Reject -->
            <div class="card border-0 shadow-sm" style="border-left:4px solid var(--bs-danger) !important">
                <div class="card-body">
                    <h6 class="text-danger mb-2"><i class="bi bi-x-circle me-1"></i>Reject Request</h6>
                    <form method="post" action="/admin/external-access/<?= (int) $req['id'] ?>/reject">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label small">Rejection Reason (optional)</label>
                            <textarea name="rejection_reason" class="form-control form-control-sm" rows="3"
                                      placeholder="Briefly explain why this request is being declined…"></textarea>
                        </div>
                        <button type="button" class="btn btn-danger btn-sm"
                                onclick="if(confirm('Reject this access request?')){this.closest('form').submit();}">
                            <i class="bi bi-x-circle me-1"></i>Reject Request
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
