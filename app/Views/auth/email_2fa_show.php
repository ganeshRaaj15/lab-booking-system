<?php

use CodeIgniter\Shield\Entities\User;

?>
<?= $this->extend('layouts/main_user') ?>
<?= $this->section('content') ?>

<div class="login-page">
    <div class="login-card">
        <div class="login-header">
            <div class="login-logo">
                <i class="bi bi-shield-lock"></i>
            </div>
            <h1 class="login-title">Two-Factor Verification</h1>
            <p class="login-subtitle">Confirm your email address to receive a one-time code.</p>
        </div>

        <?php if (session('error')): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <?= esc(session('error')) ?>
            </div>
        <?php endif ?>

        <form action="<?= url_to('auth-action-handle') ?>" method="post" class="login-form">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label">Email Address</label>
                <?php /** @var User $user */ ?>
                <input
                    type="email"
                    name="email"
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

            <button type="submit" class="login-btn">
                <i class="bi bi-send me-2"></i>
                Send Verification Code
            </button>
        </form>
    </div>
</div>

<?= $this->endSection() ?>
