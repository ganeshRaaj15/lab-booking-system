<?= $this->extend('layouts/main_user'); ?>
<?= $this->section('content'); ?>




<div class="auth-wrapper">
    <div class="auth-card">

        <h3 class="auth-title mb-3">Magic Link Sent</h3>
        <p class="text-secondary small mb-4">
            If the email exists in our system, you will receive a login link shortly.
        </p>

        <i class="bi bi-envelope-check auth-icon"></i>

        <p class="mt-4 small">
            <a href="<?= url_to('login') ?>">Back to login</a>
        </p>

    </div>
</div>

<?= $this->endSection(); ?>






