<?php if (auth()->loggedIn()): ?>
    <?php header('Location: ' . site_url('/dashboard')); exit; ?>
<?php endif; ?>

<?php
$title = 'SLAMS | Sign in';
$authMode = 'login';
$heroNightVideoUrl = base_url('images/uthm-aerial-compressed.mp4');
$heroDayVideoUrl = base_url('images/day-time-aerial-compressed.mp4');
$heroFallbackUrl = base_url('images/fkmp/FKMP.jpeg');

$heroContent = [
    'login' => [
        'title' => 'Sign in to SLAMS',
        'copy'  => 'Access bookings, approvals, requests, alerts, and the rest of your lab workspace without restarting the background footage when you switch screens.',
    ],
    'register' => [
        'title' => 'Create your account',
        'copy'  => 'Use your institutional student email for student access. Other sign-ups default to external access until they are reviewed.',
    ],
];
?>

<?= $this->extend('layouts/main_user'); ?>
<?= $this->section('content'); ?>

<div
    class="auth-shell"
    data-auth-shell
    data-auth-mode="<?= esc($authMode, 'attr') ?>"
    data-login-url="<?= esc(site_url('/login'), 'attr') ?>"
    data-register-url="<?= esc(site_url('/register'), 'attr') ?>"
    data-login-title="<?= esc($heroContent['login']['title'], 'attr') ?>"
    data-login-copy="<?= esc($heroContent['login']['copy'], 'attr') ?>"
    data-register-title="<?= esc($heroContent['register']['title'], 'attr') ?>"
    data-register-copy="<?= esc($heroContent['register']['copy'], 'attr') ?>"
>
    <div class="auth-shell__hero">
        <div class="auth-shell__hero-media">
            <div class="video-background auth-shell__video" aria-hidden="true">
                <video class="hero-video hero-video-dark" autoplay muted loop playsinline webkit-playsinline preload="auto" poster="<?= esc($heroFallbackUrl, 'attr') ?>">
                    <source src="<?= esc($heroNightVideoUrl, 'attr') ?>" type="video/mp4">
                </video>
                <video class="hero-video hero-video-light" autoplay muted loop playsinline webkit-playsinline preload="auto" poster="<?= esc($heroFallbackUrl, 'attr') ?>">
                    <source src="<?= esc($heroDayVideoUrl, 'attr') ?>" type="video/mp4">
                </video>
            </div>
            <div class="hero-overlay auth-shell__hero-overlay" aria-hidden="true"></div>

            <div class="auth-shell__hero-card" aria-live="polite">
                <p class="auth-shell__eyebrow">SLAMS</p>
                <h1 class="auth-shell__hero-title" data-auth-hero-title>
                    <?= esc($heroContent[$authMode]['title']) ?>
                </h1>
                <p class="auth-shell__hero-copy" data-auth-hero-copy>
                    <?= esc($heroContent[$authMode]['copy']) ?>
                </p>
            </div>
        </div>
    </div>

    <div class="auth-shell__form-column">
        <div class="auth-shell__form-card">
            <div class="auth-shell__brand">
                <div class="auth-shell__brand-badge" aria-hidden="true">
                    <i class="bi bi-building-gear"></i>
                </div>
                <div>
                    <p class="auth-shell__brand-name">SLAMS</p>
                    <p class="auth-shell__brand-copy">Smart Lab Administration &amp; Management System</p>
                </div>
            </div>

            <div class="auth-shell__switcher" role="tablist" aria-label="Authentication pages">
                <a
                    id="auth-shell-login-tab"
                    href="<?= site_url('/login') ?>"
                    class="auth-shell__switcher-link is-active"
                    data-auth-mode-link="login"
                    data-auth-mode-tab
                    role="tab"
                    aria-controls="auth-shell-login-panel"
                    aria-selected="true"
                >
                    Sign In
                </a>
                <a
                    id="auth-shell-register-tab"
                    href="<?= site_url('/register') ?>"
                    class="auth-shell__switcher-link"
                    data-auth-mode-link="register"
                    data-auth-mode-tab
                    role="tab"
                    aria-controls="auth-shell-register-panel"
                    aria-selected="false"
                >
                    Create Account
                </a>
            </div>

            <section
                id="auth-shell-login-panel"
                class="auth-shell__panel"
                data-auth-panel="login"
                role="tabpanel"
                aria-labelledby="auth-shell-login-tab auth-shell-login-title"
            >
                <div class="auth-shell__panel-header">
                    <h2 id="auth-shell-login-title" class="auth-shell__panel-title">Welcome back</h2>
                    <p class="auth-shell__panel-copy">Sign in with your SLAMS account to continue.</p>
                </div>

                <?php if (session()->has('error')): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?= session('error') ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->has('errors')): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?php foreach ((array) session('errors') as $error): ?>
                            <div><?= esc($error) ?></div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (session()->has('success')): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle me-2"></i>
                        <?= session('success') ?>
                    </div>
                <?php endif; ?>

                <form action="<?= url_to('login') ?>" method="post" class="auth-shell__form-stack">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="loginEmail" class="form-label">Email Address</label>
                        <input
                            type="email"
                            name="email"
                            id="loginEmail"
                            class="form-control form-control-lg"
                            placeholder="Enter your email address"
                            value="<?= esc(old('email') ?? '', 'attr') ?>"
                            autocomplete="email"
                            required
                            autofocus
                        >
                        <small class="text-muted mt-1 d-block">Use your institutional email whenever possible.</small>
                    </div>

                    <div class="mb-3">
                        <label for="loginPassword" class="form-label">Password</label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                name="password"
                                id="loginPassword"
                                class="form-control form-control-lg"
                                placeholder="Enter your password"
                                autocomplete="current-password"
                                required
                            >
                            <button
                                type="button"
                                class="toggle-password"
                                data-password-toggle
                                data-password-target="loginPassword"
                                aria-label="Show password"
                            >
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <div class="login-options">
                        <div class="form-check">
                            <input type="checkbox" name="remember" id="remember" class="form-check-input" <?= old('remember') ? 'checked' : '' ?>>
                            <label for="remember" class="form-check-label">Remember me</label>
                        </div>
                        <a href="<?= url_to('magic-link') ?>" class="magic-link">
                            <i class="bi bi-key me-1"></i>Forgot password?
                        </a>
                    </div>

                    <button type="submit" class="login-btn">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Sign In
                    </button>
                </form>

                <div class="auth-shell__footer">
                    <p class="mb-2">Need access for the first time?</p>
                    <a href="<?= site_url('/register') ?>" class="register-btn" data-auth-mode-link="register">
                        <i class="bi bi-person-plus me-1"></i>
                        Create New Account
                    </a>
                </div>
            </section>

            <section
                id="auth-shell-register-panel"
                class="auth-shell__panel"
                data-auth-panel="register"
                role="tabpanel"
                aria-labelledby="auth-shell-register-tab auth-shell-register-title"
                hidden
            >
                <div class="auth-shell__panel-header">
                    <h2 id="auth-shell-register-title" class="auth-shell__panel-title">Create your account</h2>
                    <p class="auth-shell__panel-copy">Student emails are mapped automatically when the domain matches the configured student domain.</p>
                </div>

                <form action="<?= url_to('register') ?>" method="post" class="auth-shell__form-stack">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label for="registerUsername" class="form-label small fw-semibold">Username</label>
                        <input
                            type="text"
                            name="username"
                            id="registerUsername"
                            class="form-control form-control-lg"
                            placeholder="Choose a username"
                            value="<?= esc(old('username') ?? '', 'attr') ?>"
                            autocomplete="username"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="registerEmail" class="form-label small fw-semibold">Email Address</label>
                        <input
                            type="email"
                            name="email"
                            id="registerEmail"
                            class="form-control form-control-lg"
                            placeholder="you@example.com"
                            value="<?= esc(old('email') ?? '', 'attr') ?>"
                            autocomplete="email"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label for="registerPassword" class="form-label small fw-semibold">Password</label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                name="password"
                                id="registerPassword"
                                class="form-control form-control-lg"
                                placeholder="Create a password"
                                autocomplete="new-password"
                                required
                            >
                            <button
                                type="button"
                                class="toggle-password"
                                data-password-toggle
                                data-password-target="registerPassword"
                                aria-label="Show password"
                            >
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                        <div data-password-criteria class="auth-shell__password-criteria mt-2 small d-none">
                            <div class="d-flex flex-wrap gap-2">
                                <span class="pw-rule" data-rule="length"><i class="bi bi-circle me-1"></i>8+ characters</span>
                                <span class="pw-rule" data-rule="upper"><i class="bi bi-circle me-1"></i>Uppercase</span>
                                <span class="pw-rule" data-rule="lower"><i class="bi bi-circle me-1"></i>Lowercase</span>
                                <span class="pw-rule" data-rule="number"><i class="bi bi-circle me-1"></i>Number</span>
                                <span class="pw-rule" data-rule="special"><i class="bi bi-circle me-1"></i>Special character</span>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="registerPasswordConfirm" class="form-label small fw-semibold">Confirm Password</label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                name="password_confirm"
                                id="registerPasswordConfirm"
                                class="form-control form-control-lg"
                                placeholder="Re-enter password"
                                autocomplete="new-password"
                                required
                            >
                            <button
                                type="button"
                                class="toggle-password"
                                data-password-toggle
                                data-password-target="registerPasswordConfirm"
                                aria-label="Show password"
                            >
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-fkmp-auth mb-3">
                        <i class="bi bi-person-plus-fill me-1"></i> Create Account
                    </button>

                    <p class="auth-shell__inline-footer">
                        Already have an account?
                        <a href="<?= site_url('/login') ?>" data-auth-mode-link="login">Sign in here</a>
                    </p>
                </form>
            </section>
        </div>
    </div>
</div>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script src="<?= slams_asset('js/auth-shell.js') ?>"></script>
<?= $this->endSection(); ?>
