<?php
$title = 'SLAMS | Sign-in Link';
$bodyClass = 'auth-page-layout';
$mainClass = 'container py-4 slams-main slams-main--auth';
$hideFooter = true;
$hideChatbot = true;
$hideMobileQuickActions = true;
?>
<?= $this->extend('layouts/main_user'); ?>
<?= $this->section('content'); ?>

<div class="auth-center">
    <div class="auth-center__card">
        <div class="auth-center__head">
            <div class="auth-center__icon">
                <i class="bi bi-envelope-paper"></i>
            </div>
            <h1 class="auth-center__title">Sign in with a link</h1>
            <p class="auth-center__copy">Enter your email or username and we'll send a secure one-time sign-in link to your registered address.</p>
        </div>

        <?php if (session()->has('error')): ?>
            <div class="alert alert-danger mb-3">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= esc(session('error')) ?>
            </div>
        <?php endif; ?>

        <?php if (session()->has('errors')): ?>
            <div class="alert alert-danger mb-3">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?php foreach ((array) session('errors') as $error): ?>
                    <div><?= esc($error) ?></div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <form action="<?= url_to('magic-link') ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label" for="accountInput">Email or Username</label>
                <input
                    type="text"
                    name="account"
                    id="accountInput"
                    class="form-control form-control-lg"
                    value="<?= esc(old('account')) ?>"
                    placeholder="you@example.com"
                    autocomplete="username"
                    required
                    autofocus
                >
            </div>

            <button type="submit" class="btn btn-fkmp-auth w-100 login-btn">
                <i class="bi bi-send me-2"></i>Send Secure Link
            </button>
        </form>

        <div class="auth-center__footer">
            <a href="<?= url_to('login') ?>">
                <i class="bi bi-arrow-left me-1"></i>Back to sign in
            </a>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
