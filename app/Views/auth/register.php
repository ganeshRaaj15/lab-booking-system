<?= $this->extend('layouts/main_user'); ?>
<?= $this->section('content'); ?>




<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <i class="bi bi-person-plus"></i>
        </div>
        <h3 class="auth-title mb-1">FKMP Smart Lab</h3>
        <p class="auth-subtitle">Create your account to start booking equipment</p>

        <!-- SHOW ERRORS -->
        <?php if (session()->has('errors')): ?>
            <div class="alert alert-danger">
                <?php foreach (session('errors') as $error): ?>
                    <?= esc($error) ?><br>
                <?php endforeach ?>
            </div>
        <?php endif; ?>

        <form action="<?= url_to('register') ?>" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Username</label>
                <input type="text" name="username"
                       class="form-control form-control-lg"
                       placeholder="Choose a username" required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Email Address</label>
                <input type="email" name="email"
                       class="form-control form-control-lg"
                       placeholder="you@example.com" required>
            </div>

            <div class="mb-3 input-wrapper">
                <label class="form-label small fw-semibold">Password</label>
                <input type="password" id="password" name="password"
                       class="form-control form-control-lg"
                       placeholder="Create a password" required>
                <i class="bi bi-eye-slash password-toggle" onclick="togglePassword('password', this)"></i>
            </div>

            <div class="mb-3 input-wrapper">
                <label class="form-label small fw-semibold">Confirm Password</label>
                <input type="password" id="pass_confirm" name="password_confirm"
                       class="form-control form-control-lg"
                       placeholder="Re-enter password" required>
                <i class="bi bi-eye-slash password-toggle" onclick="togglePassword('pass_confirm', this)"></i>
            </div>

            <button type="submit" class="btn btn-fkmp-auth mb-3">
                <i class="bi bi-person-plus-fill me-1"></i> Create Account
            </button>

            <p class="auth-footer">
                Already have an account?
                <a href="<?= url_to(controller: 'login') ?>">Login here</a>
            </p>
        </form>
    </div>
</div>

<script>
function togglePassword(fieldId, iconEl) {
    const input = document.getElementById(fieldId);
    if (input.type === "password") {
        input.type = "text";
        iconEl.classList.remove("bi-eye-slash");
        iconEl.classList.add("bi-eye");
    } else {
        input.type = "password";
        iconEl.classList.remove("bi-eye");
        iconEl.classList.add("bi-eye-slash");
    }
}
</script>

<?= $this->endSection(); ?>






