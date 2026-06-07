<?php
$title = 'SLAMS | Create your account';
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
                <h1 class="auth-shell__hero-title">Create your account</h1>
                <p class="auth-shell__hero-copy">Use your institutional student email for student access. Other sign-ups default to external access.</p>
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

            <form action="<?= url_to('register') ?>" method="post" class="auth-shell__form-stack">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label class="form-label" for="registerUsername">Username</label>
                    <input
                        type="text"
                        name="username"
                        id="registerUsername"
                        class="form-control form-control-lg"
                        placeholder="Choose a username"
                        value="<?= esc(old('username')) ?>"
                        autocomplete="username"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label" for="registerEmail">Email</label>
                    <input
                        type="email"
                        name="email"
                        id="registerEmail"
                        class="form-control form-control-lg"
                        placeholder="name@example.com"
                        value="<?= esc(old('email')) ?>"
                        autocomplete="email"
                        required
                    >
                </div>

                <div class="mb-3">
                    <label class="form-label" for="registerPassword">Password</label>
                    <div class="password-wrapper">
                        <input
                            type="password"
                            id="registerPassword"
                            name="password"
                            class="form-control form-control-lg"
                            placeholder="Create a password"
                            autocomplete="new-password"
                            required
                        >
                        <button type="button" class="toggle-password" id="toggleRegisterPassword" aria-label="Show password">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="registerPasswordConfirm">Confirm Password</label>
                    <div class="password-wrapper">
                        <input
                            type="password"
                            id="registerPasswordConfirm"
                            name="password_confirm"
                            class="form-control form-control-lg"
                            placeholder="Repeat your password"
                            autocomplete="new-password"
                            required
                        >
                        <button type="button" class="toggle-password" id="toggleRegisterPasswordConfirm" aria-label="Show password">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <div class="auth-shell__actions">
                    <button type="submit" class="btn btn-fkmp-auth auth-shell__primary-button">
                        Create Account
                    </button>

                    <a href="<?= url_to('login') ?>" class="auth-shell__secondary-button">
                        Back to Sign In
                    </a>
                </div>
            </form>
        </div>
    </section>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    function setupPasswordToggle(toggleId, inputId) {
        const toggle = document.getElementById(toggleId);
        const input = document.getElementById(inputId);

        if (!toggle || !input) {
            return;
        }

        toggle.addEventListener("click", function () {
            const isHidden = input.type === "password";
            input.type = isHidden ? "text" : "password";
            toggle.setAttribute("aria-label", isHidden ? "Hide password" : "Show password");
            toggle.querySelector("i").classList.toggle("bi-eye", isHidden);
            toggle.querySelector("i").classList.toggle("bi-eye-slash", !isHidden);
            input.focus();
        });
    }

    setupPasswordToggle("toggleRegisterPassword", "registerPassword");
    setupPasswordToggle("toggleRegisterPasswordConfirm", "registerPasswordConfirm");
});
</script>

<?= $this->endSection(); ?>
