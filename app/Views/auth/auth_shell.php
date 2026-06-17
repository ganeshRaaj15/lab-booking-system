<?php helper(['url', 'asset', 'auth']); ?>
<?php
$requestPath = trim((string) service('request')->getUri()->getPath(), '/');
$registerPath = trim((string) parse_url(url_to('register'), PHP_URL_PATH), '/');
$oldInputMissing = '__slams_missing__';
$sessionErrors = (array) (session('errors') ?? []);
$hasRegisterAttempt = old('username', $oldInputMissing) !== $oldInputMissing
    || old('password_confirm', $oldInputMissing) !== $oldInputMissing
    || array_key_exists('username', $sessionErrors)
    || array_key_exists('password_confirm', $sessionErrors);
$hasLoginAttempt = ! $hasRegisterAttempt
    && (
        old('password', $oldInputMissing) !== $oldInputMissing
        || array_key_exists('password', $sessionErrors)
        || session()->has('error')
    );
$defaultActiveMode = $hasRegisterAttempt
    ? 'register'
    : ($hasLoginAttempt ? 'login' : ($requestPath === $registerPath ? 'register' : 'login'));
$activeMode = ($activeMode ?? $defaultActiveMode) === 'register' ? 'register' : 'login';
$heroFallbackUrl = base_url('images/fkmp/FKMP.jpeg');
$heroDarkVideoUrl = base_url('images/uthm-aerial-compressed.mp4');
$heroLightVideoUrl = base_url('images/day-time-aerial-compressed.mp4');
$loginError = $loginError ?? ($activeMode === 'login' ? session('error') : null);
$loginErrorsByField = (array) ($loginErrorsByField ?? ($activeMode === 'login' ? $sessionErrors : []));
$loginErrors = array_values(array_filter((array) ($loginErrors ?? $loginErrorsByField)));
$loginSuccess = $loginSuccess ?? ($activeMode === 'login' ? session('success') : null);
$registerError = $registerError ?? ($activeMode === 'register' ? session('error') : null);
$registerErrorsByField = (array) ($registerErrorsByField ?? ($activeMode === 'register' ? $sessionErrors : []));
$registerErrors = array_values(array_filter((array) ($registerErrors ?? $registerErrorsByField)));
$fieldError = static function (array $errors, string $field): ?string {
    $error = $errors[$field] ?? null;
    if (is_array($error)) {
        $error = implode(' ', array_filter(array_map('strval', $error)));
    }

    return is_string($error) && trim($error) !== '' ? $error : null;
};
$loginEmailError = $fieldError($loginErrorsByField, 'email');
$loginPasswordError = $fieldError($loginErrorsByField, 'password');
$registerUsernameError = $fieldError($registerErrorsByField, 'username');
$registerEmailError = $fieldError($registerErrorsByField, 'email');
$registerPasswordError = $fieldError($registerErrorsByField, 'password');
$registerPasswordConfirmError = $fieldError($registerErrorsByField, 'password_confirm');
?>

<?= $this->extend('layouts/main_user'); ?>

<?= $this->section('styles'); ?>
<link href="<?= slams_asset('css/auth.css') ?>" rel="stylesheet">
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>

<div
    class="auth-shell"
    data-auth-shell
    data-auth-mode="<?= esc($activeMode, 'attr') ?>"
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
                <span class="auth-panel-badge-icon"><i class="bi bi-fingerprint"></i></span>
                <div>
                    <div class="auth-panel-badge-title">SLAMS</div>
                    <div class="auth-panel-badge-copy">Smart Laboratory and Asset Management System</div>
                </div>
            </div>

            <div class="auth-mode-switch" role="tablist" aria-label="Authentication mode">
                <button
                    type="button"
                    class="auth-mode-switch-btn<?= $activeMode === 'login' ? ' is-active' : '' ?>"
                    data-auth-target="login"
                    role="tab"
                    aria-selected="<?= $activeMode === 'login' ? 'true' : 'false' ?>"
                >
                    Sign in
                </button>
                <button
                    type="button"
                    class="auth-mode-switch-btn<?= $activeMode === 'register' ? ' is-active' : '' ?>"
                    data-auth-target="register"
                    role="tab"
                    aria-selected="<?= $activeMode === 'register' ? 'true' : 'false' ?>"
                >
                    Create account
                </button>
            </div>
        </div>

        <div class="auth-form-stage" data-auth-stage>
            <div class="auth-form-view<?= $activeMode === 'login' ? ' is-active' : '' ?>" data-auth-view="login" aria-hidden="<?= $activeMode === 'login' ? 'false' : 'true' ?>">
                <div class="auth-form-copy">
                    <h2>Welcome back</h2>
                    <p>Sign in with your institutional email to continue.</p>
                </div>

                <?php if ($loginError): ?>
                    <div class="alert alert-danger auth-alert" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?= esc($loginError) ?>
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

                <?php if ($loginSuccess): ?>
                    <div class="alert alert-success auth-alert" role="alert">
                        <i class="bi bi-check-circle me-2"></i>
                        <?= esc($loginSuccess) ?>
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
                            class="form-control form-control-lg<?= $loginEmailError ? ' is-invalid' : '' ?>"
                            placeholder="Enter your email address"
                            value="<?= esc(old('email'), 'attr') ?>"
                            required
                            aria-invalid="<?= $loginEmailError ? 'true' : 'false' ?>"
                            <?= $activeMode === 'login' ? 'autofocus' : '' ?>
                        >
                        <?php if ($loginEmailError): ?>
                            <div class="auth-field-error"><?= esc($loginEmailError) ?></div>
                        <?php endif; ?>
                        <small class="auth-field-hint">Use your institutional email.</small>
                    </div>

                    <div class="auth-field">
                        <label class="form-label" for="loginPassword">Password</label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                name="password"
                                id="loginPassword"
                                class="form-control form-control-lg<?= $loginPasswordError ? ' is-invalid' : '' ?>"
                                placeholder="Enter your password"
                                required
                                aria-invalid="<?= $loginPasswordError ? 'true' : 'false' ?>"
                            >
                            <button type="button" class="toggle-password" data-password-toggle="loginPassword" aria-label="Show password">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                        <?php if ($loginPasswordError): ?>
                            <div class="auth-field-error"><?= esc($loginPasswordError) ?></div>
                        <?php endif; ?>
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

            <div class="auth-form-view<?= $activeMode === 'register' ? ' is-active' : '' ?>" data-auth-view="register" aria-hidden="<?= $activeMode === 'register' ? 'false' : 'true' ?>">
                <div class="auth-form-copy">
                    <h2>Create account</h2>
                    <p>Set up your credentials to start booking laboratories and equipment.</p>
                </div>

                <?php if ($registerError): ?>
                    <div class="alert alert-danger auth-alert" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <?= esc($registerError) ?>
                    </div>
                <?php endif; ?>

                <?php if (! empty($registerErrors)): ?>
                    <div class="alert alert-danger auth-alert" role="alert">
                        <i class="bi bi-exclamation-triangle me-2"></i>
                        <div>
                            <?php foreach ($registerErrors as $error): ?>
                                <div><?= esc($error) ?></div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>

                <form action="<?= url_to('register') ?>" method="post" class="auth-form">
                    <?= csrf_field() ?>

                    <div class="auth-field">
                        <label class="form-label" for="registerUsername">Username</label>
                        <input
                            type="text"
                            name="username"
                            id="registerUsername"
                            class="form-control form-control-lg<?= $registerUsernameError ? ' is-invalid' : '' ?>"
                            placeholder="Choose a username"
                            value="<?= esc(old('username'), 'attr') ?>"
                            required
                            aria-invalid="<?= $registerUsernameError ? 'true' : 'false' ?>"
                            <?= $activeMode === 'register' ? 'autofocus' : '' ?>
                        >
                        <?php if ($registerUsernameError): ?>
                            <div class="auth-field-error"><?= esc($registerUsernameError) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="auth-field">
                        <label class="form-label" for="registerEmail">Email Address</label>
                        <input
                            type="email"
                            name="email"
                            id="registerEmail"
                            class="form-control form-control-lg<?= $registerEmailError ? ' is-invalid' : '' ?>"
                            placeholder="you@example.com"
                            value="<?= esc(old('email'), 'attr') ?>"
                            required
                            aria-invalid="<?= $registerEmailError ? 'true' : 'false' ?>"
                        >
                        <?php if ($registerEmailError): ?>
                            <div class="auth-field-error"><?= esc($registerEmailError) ?></div>
                        <?php endif; ?>
                    </div>

                    <div class="auth-field">
                        <label class="form-label" for="registerPassword">Password</label>
                        <div class="password-wrapper">
                            <input
                                type="password"
                                id="registerPassword"
                                name="password"
                                class="form-control form-control-lg<?= $registerPasswordError ? ' is-invalid' : '' ?>"
                                placeholder="Create a password"
                                required
                                aria-invalid="<?= $registerPasswordError ? 'true' : 'false' ?>"
                                data-password-criteria-input
                            >
                            <button type="button" class="toggle-password" data-password-toggle="registerPassword" aria-label="Show password">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                        <?php if ($registerPasswordError): ?>
                            <div class="auth-field-error"><?= esc($registerPasswordError) ?></div>
                        <?php endif; ?>
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
                                class="form-control form-control-lg<?= $registerPasswordConfirmError ? ' is-invalid' : '' ?>"
                                placeholder="Re-enter password"
                                required
                                aria-invalid="<?= $registerPasswordConfirmError ? 'true' : 'false' ?>"
                            >
                            <button type="button" class="toggle-password" data-password-toggle="registerPasswordConfirm" aria-label="Show password">
                                <i class="bi bi-eye-slash"></i>
                            </button>
                        </div>
                        <?php if ($registerPasswordConfirmError): ?>
                            <div class="auth-field-error"><?= esc($registerPasswordConfirmError) ?></div>
                        <?php endif; ?>
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
