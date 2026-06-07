<?php
$title = 'SLAMS | Check Your Email';
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
            <div class="auth-center__icon auth-center__icon--success">
                <i class="bi bi-envelope-check"></i>
            </div>
            <h1 class="auth-center__title">Check your email</h1>
            <p class="auth-center__copy">
                If an account matches what you entered, we sent a secure one-time sign-in link to its registered address.
                The link expires in <?= esc((string) ceil((int) setting('Auth.magicLinkLifetime') / MINUTE)) ?> minutes.
            </p>
        </div>

        <p class="text-muted small mb-0">Didn't get anything? Check your spam folder or try again with a different email.</p>

        <div class="auth-center__footer">
            <a href="<?= url_to('magic-link') ?>">
                <i class="bi bi-arrow-clockwise me-1"></i>Try again
            </a>
            &nbsp;&middot;&nbsp;
            <a href="<?= url_to('login') ?>">Back to sign in</a>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>
