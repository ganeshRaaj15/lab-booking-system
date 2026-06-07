<?php

use CodeIgniter\Shield\Entities\User;

$title = 'SLAMS | Two-Factor Verification';
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
                <i class="bi bi-shield-lock"></i>
            </div>
            <h1 class="auth-center__title">Two-Factor Verification</h1>
            <p class="auth-center__copy">Confirm your email address to receive a one-time verification code.</p>
        </div>

        <?php if (session('error')): ?>
            <div class="alert alert-danger mb-3">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= esc(session('error')) ?>
            </div>
        <?php endif ?>

        <form action="<?= url_to('auth-action-handle') ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label" for="emailInput">Email Address</label>
                <?php /** @var User $user */ ?>
                <input
                    type="email"
                    name="email"
                    id="emailInput"
                    class="form-control form-control-lg"
                    placeholder="Enter your email address"
                    value="<?= old('email', $user->email) ?>"
                    inputmode="email"
                    autocomplete="email"
                    required
                    autofocus
                >
                <small class="text-muted mt-1 d-block">A 6-digit code will be sent to this address.</small>
            </div>

            <button type="submit" class="btn btn-fkmp-auth w-100 login-btn">
                <i class="bi bi-send me-2"></i>Send Verification Code
            </button>
        </form>

        <div class="auth-center__footer">
            <a href="<?= url_to('logout') ?>">
                <i class="bi bi-arrow-left me-1"></i>Back to sign in
            </a>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
