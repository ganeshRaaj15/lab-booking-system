<?php helper(['url', 'asset', 'auth']); ?>
<?php if (auth()->loggedIn()): ?>
    <?php header('Location: ' . site_url('/dashboard')); exit; ?>
<?php endif; ?>

<?php
$heroFallbackUrl = base_url('images/fkmp/FKMP.jpeg');
$heroDarkVideoUrl = base_url('images/uthm-aerial-compressed.mp4');
$heroLightVideoUrl = base_url('images/day-time-aerial-compressed.mp4');
$loginErrors = array_values(array_filter((array) (session('errors') ?? [])));
?>

<?= $this->extend('layouts/main_user'); ?>

<?= $this->section('styles'); ?>
<link href="<?= slams_asset('css/auth.css') ?>" rel="stylesheet">
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<div
    class="auth-shell"
    data-auth-shell
    data-auth-mode="login"
    data-login-url="<?= esc(url_to('login'), 'attr') ?>"
    data-register-url="<?= esc(url_to('register'), 'attr') ?>"
>
    <section class="auth-showcase slams-reveal is-visible" style="--slams-hero-fallback-image: url('<?= esc($heroFallbackUrl, 'attr') ?>');">
        <div class="video-background auth-showcase-media" aria-hidden="true">
            <video class="hero-video hero-video-dark" autoplay muted loop playsinline webkit-playsinline preload="auto" poster="<?= esc($heroFallbackUrl, 'attr') ?>">
                <source src="<?= esc($heroDarkVideoUrl, 'attr') ?>" type="video/mp4">
            </video>
            <video class="hero-video hero-video-light" autoplay muted loop playsinline webkit-playsinline preload="auto" poster="<?= esc($heroFallbackUrl, 'attr') ?>">
                <source src="<?= esc($heroLightVideoUrl, 'attr') ?>" type="video/mp4">
            </video>
        </div>

        <div class="auth-showcase-overlay" aria-hidden="true"></div>

        <div class="auth-showcase-content">
            <div class="auth-showcase-copy" data-auth-copy="login">
                <span class="auth-showcase-chip">FKMP UTHM</span>
                <h1>Sign in to SLAMS</h1>
                <p>View bookings, approvals, and equipment access from one place.</p>
            </div>

            <div class="auth-showcase-copy" data-auth-copy="register">
                <span class="auth-showcase-chip">New to SLAMS</span>
                <h1>Create your account</h1>
                <p>Register with your institutional email to start booking labs and assets.</p>
            </div>
        </div>
    </section>

    <section class="auth-panel slams-reveal is-visible">
        <div class="auth-panel-header">
            <div class="auth-panel-badge">
                <span class="auth-panel-badge-icon"><i class="bi bi-building"></i></span>
                <div>
                    <div class="auth-panel-badge-title">SLAMS</div>
                    <div class="auth-panel-badge-copy">Smart Laboratory and Asset Management System</div>
                </div>
            </div>

            <div class="auth-mode-switch" role="tablist" aria-label="Authentication mode">
                <button type="button" class="auth-mode-switch-btn is-active" data-auth-target="login" role="tab" aria-selected="true">
                    Sign in
                </button>
                <button type="button" class="auth-mode-switch-btn" data-auth-target="register" role="tab" aria-selected="false">
                    Create account
                </button>
            </div>
        </div>

        <div class="auth-form-stage" data-auth-stage>
            <div class="auth-form-view is-active" data-auth-view="login" aria-hidden="false">
                <div class="auth-form-copy">
                    <h2>Welcome back</h2>
                    <p>Sign in with your institutional email to continue.</p>
                </div>

                <?php if (session()->has('error')): ?>
                    <div class="alert alert-danger auth-alert" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?= esc(session('error')) ?>
                    </div>
                <?php endif; ?>

                <?php if (! empty($loginErrors)): ?>
                    <div class="alert alert-danger auth-alert" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <div>
                            <?php foreach ($loginErrors as $error): ?>
                                <div><?= esc($error) ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if (session()->has('success')): ?>
                    <div class="alert alert-success auth-alert" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        <?= esc(session('success')) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= url_to('login') ?>" method="post" class="auth-form">
                    <?= csrf_field() ?>

                    <div class="auth-field">
                        <label class="form-label" for="loginEmail">Email Address</label>
                        <input
                            type="email"
                            name="email"
                            id="loginEmail"
                            class="form-control form-control-lg"
                            placeholder="Enter your email address"
                            value="<?= esc(old('email'), 'attr') ?>"
                            required
                            autofocus
                        >
                        <small class="auth-field-hint">Use your institutional email.</small>
                    </div>

                    <div class="auth-field">
                        <label class="form-label" for="loginPassword">Password</label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                name="password"
                                id="loginPassword"
                                class="form-control form-control-lg"
                                placeholder="Enter your password"
                                required
                            >
                            <button type="button" class="toggle-password" data-password-toggle="loginPassword" aria-label="Show password">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="auth-bottom-row">
                        <label class="auth-remember-label" for="remember">
                            <input type="checkbox" name="remember" id="remember" class="auth-remember-input" role="switch" <?= old('remember') ? 'checked' : '' ?>>
                            <span class="auth-remember-track" aria-hidden="true"></span>
                            Remember me
                        </label>
                        <a href="<?= url_to('magic-link') ?>" class="auth-inline-link">
                            <i class="bi bi-key"></i>
                            Forgot password?
                        </a>
                    </div>

                    <button type="submit" class="auth-submit-btn">
                        <i class="bi bi-box-arrow-in-right"></i>
                        Sign in to Account
                    </button>

                    <p class="auth-form-note">
                        Need an account?
                        <a href="<?= url_to('register') ?>" data-auth-toggle="register">Create one here</a>
                    </p>
                </form>
            </div>

            <div class="auth-form-view" data-auth-view="register" aria-hidden="true">
                <div class="auth-form-copy">
                    <h2>Create account</h2>
                    <p>Set up your credentials to start booking laboratories and equipment.</p>
                </div>

                <form action="<?= url_to('register') ?>" method="post" class="auth-form">
                    <?= csrf_field() ?>

                    <div class="auth-field">
                        <label class="form-label" for="registerUsername">Username</label>
                        <input
                            type="text"
                            name="username"
                            id="registerUsername"
                            class="form-control form-control-lg"
                            placeholder="Choose a username"
                            value="<?= esc(old('username'), 'attr') ?>"
                            required
                        >
                    </div>

                    <div class="auth-field">
                        <label class="form-label" for="registerEmail">Email Address</label>
                        <input
                            type="email"
                            name="email"
                            id="registerEmail"
                            class="form-control form-control-lg"
                            placeholder="you@example.com"
                            value="<?= esc(old('email'), 'attr') ?>"
                            required
                        >
                    </div>

                    <div class="auth-field">
                        <label class="form-label" for="registerPassword">Password</label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                id="registerPassword"
                                name="password"
                                class="form-control form-control-lg"
                                placeholder="Create a password"
                                required
                                data-password-criteria-input
                            >
                            <button type="button" class="toggle-password" data-password-toggle="registerPassword" aria-label="Show password">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                        <div class="auth-password-criteria d-none" data-password-criteria>
                            <span class="auth-password-rule" data-rule="length"><i class="bi bi-circle"></i>8+ characters</span>
                            <span class="auth-password-rule" data-rule="upper"><i class="bi bi-circle"></i>Uppercase</span>
                            <span class="auth-password-rule" data-rule="lower"><i class="bi bi-circle"></i>Lowercase</span>
                            <span class="auth-password-rule" data-rule="number"><i class="bi bi-circle"></i>Number</span>
                            <span class="auth-password-rule" data-rule="special"><i class="bi bi-circle"></i>Special character</span>
                        </div>
                    </div>

                    <div class="auth-field">
                        <label class="form-label" for="registerPasswordConfirm">Confirm Password</label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                id="registerPasswordConfirm"
                                name="password_confirm"
                                class="form-control form-control-lg"
                                placeholder="Re-enter password"
                                required
                            >
                            <button type="button" class="toggle-password" data-password-toggle="registerPasswordConfirm" aria-label="Show password">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="auth-submit-btn">
                        <i class="bi bi-person-plus"></i>
                        Create Account
                    </button>

                    <p class="auth-form-note">
                        Already have an account?
                        <a href="<?= url_to('login') ?>" data-auth-toggle="login">Sign in here</a>
                    </p>
                </form>
            </div>
        </div>
    </section>
</div>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script src="<?= slams_asset('js/auth-ui.js') ?>"></script>
<?= $this->endSection(); ?>
