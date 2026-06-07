<?php
$title = 'SLAMS | Sign in';
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
                <p class="auth-shell__eyebrow">FKMP UTHM</p>
                <h1 class="auth-shell__hero-title">Sign in to SLAMS</h1>
                <p class="auth-shell__hero-copy">Access your lab bookings, approvals, and profile from one place with the same aerial campus backdrop used in the tablet experience.</p>
            </div>
        </div>
    </aside>

    <section class="auth-shell__form-column">
        <div class="auth-shell__form-card">
            <div class="auth-shell__brand">
                <div class="auth-shell__brand-badge">
                    <i class="bi bi-building-gear"></i>
                </div>
                <div>
                    <h2 class="auth-shell__brand-name">SLAMS</h2>
                    <p class="auth-shell__brand-copy">Smart Laboratory Access Management System</p>
                </div>
            </div>

            <nav class="auth-shell__switcher" aria-label="Authentication pages">
                <a href="<?= url_to('login') ?>" class="auth-shell__switcher-link is-active" aria-current="page">Sign in</a>
                <a href="<?= url_to('register') ?>" class="auth-shell__switcher-link">Create account</a>
            </nav>

            <div class="auth-shell__panel">
                <header class="auth-shell__panel-header">
                    <h3 class="auth-shell__panel-title">Welcome back</h3>
                    <p class="auth-shell__panel-copy">Use your institutional email to continue into the laboratory booking system.</p>
                </header>

                <?php if (session()->has('error')): ?>
                    <div class="alert alert-danger">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?= esc((string) session('error')) ?>
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
                        <?= esc((string) session('success')) ?>
                    </div>
                <?php endif; ?>

                <form action="<?= url_to('login') ?>" method="post" class="login-form auth-shell__form-stack">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label" for="loginEmail">Email Address</label>
                        <input type="email" name="email" id="loginEmail" class="form-control form-control-lg" placeholder="Enter your email address" value="<?= esc(old('email')) ?>" autocomplete="email" required autofocus>
                        <small class="text-muted mt-1 d-block">Use your institutional email.</small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="loginPassword">Password</label>
                        <div class="password-wrapper">
                            <input type="password" name="password" id="loginPassword" class="form-control form-control-lg" placeholder="Enter your password" autocomplete="current-password" required>
                            <button type="button" class="toggle-password" id="toggleLoginPassword" aria-label="Show password">
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
                            <i class="bi bi-key me-1"></i>Use a sign-in link
                        </a>
                    </div>

                    <button type="submit" class="login-btn">
                        <i class="bi bi-box-arrow-in-right me-2"></i>
                        Sign in to Account
                    </button>
                </form>

                <p class="auth-shell__footer">
                    Need access first?
                    <a href="<?= url_to('register') ?>">Create your account</a>
                </p>
            </div>
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
