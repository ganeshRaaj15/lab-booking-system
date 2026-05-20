<?= $this->extend('layouts/main_user') ?>
<?= $this->section('content') ?>

<div class="login-page">
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">
                <i class="bi bi-shield-check"></i>
            </div>
            <h1 class="login-title">Enter Verification Code</h1>
            <p class="login-subtitle">Check your email inbox and enter the 6-digit code below.</p>
        </div>

        <?php if (session('error') !== null): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= esc(session('error')) ?>
            </div>
        <?php endif ?>

        <form action="<?= url_to('auth-action-verify') ?>" method="post" class="login-form">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label">Verification Code</label>
                <input
                    type="number"
                    name="token"
                    class="form-control form-control-lg text-center"
                    placeholder="000000"
                    inputmode="numeric"
                    pattern="[0-9]*"
                    autocomplete="one-time-code"
                    maxlength="6"
                    style="letter-spacing: 0.35em; font-size: 1.5rem; font-weight: 700;"
                    required
                    autofocus
                >
                <small class="text-muted mt-1 d-block">The code expires shortly — enter it promptly.</small>
            </div>

            <button type="submit" class="login-btn">
                <i class="bi bi-check-circle me-2"></i>
                Verify Code
            </button>
        </form>

        <div class="auth-footer">
            <p class="mb-2">Did not receive the code?</p>
            <a href="<?= url_to('auth-action-show') ?>" class="register-btn">
                <i class="bi bi-arrow-clockwise me-1"></i>
                Resend Code
            </a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
