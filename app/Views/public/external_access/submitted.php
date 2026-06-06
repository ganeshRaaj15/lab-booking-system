<?= $this->extend('layouts/main_user') ?>
<?= $this->section('content') ?>

<div class="container py-5 text-center" style="max-width:560px">
    <div class="mb-4">
        <i class="bi bi-check-circle-fill text-success" style="font-size:4rem"></i>
    </div>
    <h2 class="fw-bold text-primary mb-3">Request Submitted</h2>
    <p class="text-muted mb-4">
        Your external access request has been received. An administrator will review it and send your login credentials to the email address you provided once approved.
    </p>
    <p class="text-muted small mb-4">
        If you do not receive a response within 3 working days, contact the FKMP lab administrator directly.
    </p>
    <a href="/" class="btn btn-outline-primary">Return to Home</a>
</div>

<?= $this->endSection() ?>
