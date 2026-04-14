<?= $this->extend('layouts/main_user'); ?>
<?= $this->section('content'); ?>




<div class="auth-wrapper">
    <div class="auth-card">

        <h3 class="auth-title mb-1">Magic Link Login</h3>
        <p class="text-center text-secondary small mb-4">
            Enter your email to receive a login link.
        </p>

        <?php if (session()->has('error')): ?>
            <div class="alert alert-danger"><?= session('error') ?></div>
        <?php endif; ?>

        <form action="<?= url_to('magic-link') ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Email Address</label>
                <input type="email" name="email" class="form-control form-control-lg"
                       placeholder="you@example.com" required>
            </div>

            <button type="submit" class="btn btn-fkmp-auth mb-3">
                <i class="bi bi-envelope-paper me-1"></i> Send Magic Link
            </button>

            <p class="text-center small">
                <a href="<?= url_to('login') ?>">Back to login</a>
            </p>
        </form>
    </div>
</div>

<?= $this->endSection(); ?>






