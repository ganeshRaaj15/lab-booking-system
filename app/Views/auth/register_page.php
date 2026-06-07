<?= $this->extend('layouts/main_user'); ?>
<?= $this->section('content'); ?>

<div class="auth-wrapper">
    <div class="auth-card">
        <div class="auth-logo">
            <i class="bi bi-person-plus"></i>
        </div>
        <h3 class="auth-title mb-1">FKMP Smart Lab</h3>
        <p class="auth-subtitle">Create your account to start booking equipment</p>

        <?php if (session()->has('errors')): ?>
            <div class="alert alert-danger">
                <?php foreach (session('errors') as $error): ?>
                    <?= esc($error) ?><br>
                <?php endforeach ?>
            </div>
        <?php endif; ?>

        <form action="/register" method="post">
            <?= csrf_field() ?>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Username</label>
                <input type="text" name="username"
                       class="form-control form-control-lg"
                       placeholder="Choose a username"
                       value="<?= old('username') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Email Address</label>
                <input type="email" name="email"
                       class="form-control form-control-lg"
                       placeholder="you@example.com"
                       value="<?= old('email') ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Password</label>
                <div class="password-wrapper">
                    <input type="password" id="password" name="password"
                           class="form-control form-control-lg"
                           placeholder="Create a password" required>
                    <button type="button" class="toggle-password" id="togglePassword" aria-label="Show password">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
                <div id="passwordCriteria" class="mt-2 small d-none">
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
                <label class="form-label small fw-semibold">Confirm Password</label>
                <div class="password-wrapper">
                    <input type="password" id="pass_confirm" name="password_confirm"
                           class="form-control form-control-lg"
                           placeholder="Re-enter password" required>
                    <button type="button" class="toggle-password" id="togglePasswordConfirm" aria-label="Show password">
                        <i class="bi bi-eye-slash"></i>
                    </button>
                </div>
            </div>

            <button type="submit" class="btn btn-fkmp-auth mb-3">
                <i class="bi bi-person-plus-fill me-1"></i> Create Account
            </button>

            <p class="auth-footer">
                Already have an account?
                <a href="/login">Login here</a>
            </p>
        </form>
    </div>
</div>

<style>
.pw-rule {
    display: inline-flex;
    align-items: center;
    color: #6c757d;
    transition: color 0.2s;
}
.pw-rule.met {
    color: #198754;
}
.pw-rule.met .bi::before {
    content: "\f26b";
}
</style>

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

    setupPasswordToggle("togglePassword", "password");
    setupPasswordToggle("togglePasswordConfirm", "pass_confirm");

    const passwordInput = document.getElementById("password");
    const criteriaBox = document.getElementById("passwordCriteria");

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
            if (met) {
                icon.className = "bi bi-check-circle-fill me-1";
            } else {
                icon.className = "bi bi-circle me-1";
            }
        });
    });
});
</script>

<?= $this->endSection(); ?>
