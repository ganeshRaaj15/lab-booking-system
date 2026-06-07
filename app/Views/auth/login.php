<?php
$title = 'SLAMS | Sign in';
$bodyClass = 'auth-page-layout';
$mainClass = 'container py-4 slams-main slams-main--auth';
$hideFooter = true;
$hideChatbot = true;
$hideMobileQuickActions = true;
$logoUrl = slams_asset('images/logo.png');
$heroVideoUrl = base_url('images/uthm-aerial-compressed.mp4');
$heroDayVideoUrl = base_url('images/day-time-aerial-compressed.mp4');
$heroFallbackUrl = base_url('images/fkmp/FKMP.jpeg');
?>
<?= $this->extend('layouts/main_user'); ?>
<?= $this->section('content'); ?>

<div class="auth-shell">
    <aside class="auth-shell__hero" aria-hidden="true">
        <div class="auth-shell__hero-media" style="--slams-hero-fallback-image: url('<?= esc($heroFallbackUrl, 'attr') ?>');">
            <div class="video-background">
                <video class="hero-video auth-shell__video hero-video-dark" autoplay muted loop playsinline webkit-playsinline preload="auto" poster="<?= esc($heroFallbackUrl, 'attr') ?>">
                    <source src="<?= esc($heroVideoUrl, 'attr') ?>" type="video/mp4">
                </video>
                <video class="hero-video auth-shell__video hero-video-light" autoplay muted loop playsinline webkit-playsinline preload="auto" poster="<?= esc($heroFallbackUrl, 'attr') ?>">
                    <source src="<?= esc($heroDayVideoUrl, 'attr') ?>" type="video/mp4">
                </video>
            </div>
            <div class="hero-overlay auth-shell__hero-overlay"></div>
            <div class="auth-shell__hero-card">
                <p class="auth-shell__eyebrow">SLAMS</p>
                <h1 class="auth-shell__hero-title">Sign in to SLAMS</h1>
                <p class="auth-shell__hero-copy">Access bookings, approvals, requests, alerts, and your workspace tools.</p>
            </div>
        </div>
    </aside>

    <section class="auth-shell__form-column">
        <div class="auth-shell__form-card">
            <div class="auth-shell__logo-badge" aria-hidden="true">
                <img src="<?= esc($logoUrl, 'attr') ?>" alt="" class="auth-shell__logo-image">
            </div>

            <?php if (session()->has('error')): ?>
                <div class="alert alert-danger auth-shell__inline-alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= esc((string) session('error')) ?>
                </div>
            <?php endif; ?>

            <?php if (session()->has('errors')): ?>
                <div class="alert alert-danger auth-shell__inline-alert">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?php foreach ((array) session('errors') as $error): ?>
                        <div><?= esc($error) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?php if (session()->has('success')): ?>
                <div class="alert alert-success auth-shell__inline-alert">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= esc((string) session('success')) ?>
                </div>
            <?php endif; ?>

            <form action="<?= url_to('login') ?>" method="post" class="auth-shell__form-stack">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label" for="loginEmail">Email</label>
                    <input
                        type="email"
                        name="email"
                        id="loginEmail"
                        class="form-control form-control-lg"
                        placeholder="name@example.com"
                        value="<?= esc(old('email')) ?>"
                        autocomplete="email"
                        required
                        autofocus
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label" for="loginPassword">Password</label>
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
                        <button type="button" class="toggle-password" id="toggleLoginPassword" aria-label="Show password">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <div class="auth-shell__actions">
                    <button type="submit" class="login-btn auth-shell__primary-button">
                        Sign In
                    </button>

                    <a href="<?= url_to('register') ?>" class="auth-shell__secondary-button">
                        Create Account
                    </a>
                </div>

                <p class="auth-shell__support-link">
                    <a href="<?= url_to('magic-link') ?>">Use a sign-in link</a>
                </p>
            </form>
        </div>
    </section>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const togglePassword = document.getElementById("toggleLoginPassword");
    const passwordInput = document.getElementById("loginPassword");

    if (!togglePassword || !passwordInput) {
        return;
    }

    togglePassword.addEventListener("click", function () {
        const isHidden = passwordInput.type === "password";
        passwordInput.type = isHidden ? "text" : "password";
        togglePassword.setAttribute("aria-label", isHidden ? "Hide password" : "Show password");
        togglePassword.querySelector("i").classList.toggle("bi-eye", isHidden);
        togglePassword.querySelector("i").classList.toggle("bi-eye-slash", !isHidden);
        passwordInput.focus();
    });
});
</script>

<?= $this->endSection(); ?>
