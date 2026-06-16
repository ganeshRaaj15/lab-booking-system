<?= $this->extend($layout ?? 'layouts/main_user') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <?php if (session()->getFlashdata('error')): ?><div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div><?php endif; ?>

    <div class="slams-page-header">
        <div class="slams-page-header-left">
            <h1 class="slams-page-title">Email Inbox</h1>
            <p class="slams-page-subtitle">Preview outgoing system emails generated for your account.</p>
        </div>
        <div class="slams-page-header-actions">
            <a href="/dashboard/notifications" class="btn btn-glass btn-sm">Back to Notifications</a>
        </div>
    </div>

    <div class="glass-card">
        <div class="card-body p-0">
            <?php if (empty($emails)): ?>
                <div class="p-5 text-center text-muted">
                    <i class="bi bi-envelope fs-1 d-block mb-3"></i>
                    <div class="fw-semibold mb-1">No email previews yet</div>
                    <div class="small">Email previews will appear here once the system generates notification emails.</div>
                </div>
            <?php else: ?>
                <div class="list-group list-group-flush">
                    <?php foreach ($emails as $email): ?>
                        <div class="list-group-item px-4 py-4">
                            <div class="d-flex flex-column flex-lg-row justify-content-between gap-3">
                                <div class="flex-grow-1">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <?php if (! empty($email['notification_type'])): ?>
                                            <span class="badge rounded-pill bg-primary-subtle text-primary border text-capitalize"><?= esc($email['notification_type']) ?></span>
                                        <?php endif; ?>
                                        <?php if (! empty($email['has_attachment'])): ?>
                                            <span class="badge rounded-pill bg-light text-dark border">Attachment</span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="fw-semibold text-dark mb-1"><?= esc($email['subject'] ?? 'Email') ?></div>
                                    <div class="text-muted small mb-2">To: 
                                        <span class="fw-semibold"><?= esc($email['to_email'] ?? '-') ?></span>
                                    </div>
                                    <div class="small text-muted">
                                        <i class="bi bi-clock me-1"></i>
                                        <?= esc(! empty($email['created_at']) ? date('d-m-Y H:i', strtotime($email['created_at'])) : '-') ?>
                                    </div>
                                </div>
                                <div class="d-flex align-items-start gap-2">
                                    <a href="/dashboard/emails/<?= esc($email['id']) ?>" class="btn btn-outline-primary btn-sm">View Email</a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="p-3 border-top">
                    <?= $pager->links() ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
