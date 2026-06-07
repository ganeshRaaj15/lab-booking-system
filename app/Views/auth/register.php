<?php
$title = 'SLAMS | Create your account';
$bodyClass = 'auth-page-layout';
$mainClass = 'container py-4 slams-main slams-main--auth';
$hideFooter = true;
$hideChatbot = true;
$hideMobileQuickActions = true;
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
                <h1 class="auth-shell__hero-title">Create your account</h1>
                <p class="auth-shell__hero-copy">Open your SLAMS access and start booking laboratories with a single account.</p>
            </div>
        </div>
    </aside>

    <section class="auth-shell__form-column">
        <div class="auth-shell__form-card">
            <div class="auth-shell__brand">
                <div class="auth-shell__brand-badge">
                    <i class="bi bi-person-plus"></i>
                </div>
                <div>
                    <h2 class="auth-shell__brand-name">Join SLAMS</h2>
                    <p class="auth-shell__brand-copy">Create an account for laboratory booking and approvals.</p>
                </div>
            </div>

            <nav class="auth-shell__switcher" aria-label="Authentication pages">
                <a href="<?= url_to('login') ?>" class="auth-shell__switcher-link">Sign in</a>
                <a href="<?= url_to('register') ?>" class="auth-shell__switcher-link is-active" aria-current="page">Create account</a>
            </nav>

            <div class="auth-shell__panel">
                <header class="auth-shell__panel-header">
                    <h3 class="auth-shell__panel-title">Create your account</h3>
                    <p class="auth-shell__panel-copy">Use a clear username, your institutional email, and a strong password.</p>
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

                <form action="<?= url_to('register') ?>" method="post" class="auth-shell__form-stack">
                    <?= csrf_field() ?>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold" for="registerUsername">Username</label>
                        <input type="text" name="username" id="registerUsername" class="form-control form-control-lg" placeholder="Choose a username" value="<?= esc(old('username')) ?>" autocomplete="username" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold" for="registerEmail">Email Address</label>
                        <input type="email" name="email" id="registerEmail" class="form-control form-control-lg" placeholder="you@example.com" value="<?= esc(old('email')) ?>" autocomplete="email" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold" for="registerPassword">Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="registerPassword" name="password" class="form-control form-control-lg" placeholder="Create a password" autocomplete="new-password" required>
                            <button type="button" class="toggle-password" id="toggleRegisterPassword" aria-label="Show password">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                        <div id="passwordCriteria" class="auth-shell__password-criteria mt-2 small d-none">
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
                        <label class="form-label small fw-semibold" for="registerPasswordConfirm">Confirm Password</label>
                        <div class="password-wrapper">
                            <input type="password" id="registerPasswordConfirm" name="password_confirm" class="form-control form-control-lg" placeholder="Re-enter password" autocomplete="new-password" required>
                            <button type="button" class="toggle-password" id="toggleRegisterPasswordConfirm" aria-label="Show password">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-fkmp-auth mb-3">
                        <i class="bi bi-person-plus-fill me-1"></i> Create Account
                    </button>
                </form>

                <p class="auth-shell__footer">
                    Already registered?
                    <a href="<?= url_to('login') ?>">Sign in here</a>
                </p>
            </div>
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

    const passwordInput = document.getElementById("registerPassword");
    const criteriaBox = document.getElementById("passwordCriteria");

    if (!passwordInput || !criteriaBox) {
        return;
    }

    const rules = {
        length: (v) => v.length >= 8,
        upper: (v) => /[A-Z]/.test(v),
        lower: (v) => /[a-z]/.test(v),
        number: (v) => /[0-9]/.test(v),
        special: (v) => /[^A-Za-z0-9]/.test(v),
    };

    passwordInput.addEventListener("input", function () {
        const val = this.value;

        if (val.length === 0) {
            criteriaBox.classList.add("d-none");
            return;
        }

        criteriaBox.classList.remove("d-none");

        criteriaBox.querySelectorAll(".pw-rule").forEach((el) => {
            const rule = el.dataset.rule;
            const met = rules[rule] && rules[rule](val);
            el.classList.toggle("met", met);

            const icon = el.querySelector("i");
            if (icon) {
                icon.className = met ? "bi bi-check-circle-fill me-1" : "bi bi-circle me-1";
            }
        });
    });
});
</script>

<?= $this->endSection(); ?>
