<?php
$title = 'SLAMS | Enter Verification Code';
$bodyClass = 'auth-page-layout';
$mainClass = 'container py-4 slams-main slams-main--auth';
$hideFooter = true;
$hideChatbot = true;
$hideMobileQuickActions = true;
?>
<?= $this->extend('layouts/main_user') ?>
<?= $this->section('content') ?>

<div class="auth-center">
    <div class="auth-center__card">
        <div class="auth-center__head">
            <div class="auth-center__icon">
                <i class="bi bi-shield-check"></i>
            </div>
            <h1 class="auth-center__title">Enter Verification Code</h1>
            <p class="auth-center__copy">Check your inbox and enter the 6-digit code below. It expires shortly.</p>
        </div>

        <?php if (session('error') !== null): ?>
            <div class="alert alert-danger mb-3">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= esc(session('error')) ?>
            </div>
        <?php endif ?>

        <form action="<?= url_to('auth-action-verify') ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label" for="tokenInput">Verification Code</label>
                <input
                    type="number"
                    name="token"
                    id="tokenInput"
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
            </div>

            <button type="submit" class="btn btn-fkmp-auth w-100 login-btn">
                <i class="bi bi-check-circle me-2"></i>Verify Code
            </button>
        </form>

        <div class="auth-center__footer">
            Did not receive the code?
            <a href="<?= url_to('auth-action-show') ?>">
                <i class="bi bi-arrow-clockwise me-1"></i>Resend code
            </a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
