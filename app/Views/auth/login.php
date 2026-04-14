<?php if (auth()->loggedIn()): ?>`r`n<?php header("Location: " . site_url('/dashboard')); exit; ?>`r`n<?php endif; ?>`r`n`r`n<?= $this->extend('layouts/main_user'); ?>
<?= $this->section('content'); ?>



<div class="login-page">
    <!-- Floating Background Elements -->
    <div class="floating-element floating-1"></div>
    <div class="floating-element floating-2"></div>
    <div class="floating-element floating-3"></div>
    
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-logo">
                    <i class="bi bi-building-gear"></i>
                </div>
                <h1 class="login-title">FKMP Smart Lab</h1>
                <p class="login-subtitle">Login to access the laboratory booking system</p>
            </div>

            <!-- Error Messages -->
            <?php if (session()->has('error')): ?>
                <div class="alert alert-danger">
                    <i class="bi bi-exclamation-triangle me-2"></i>
                    <?= session('error') ?>
                </div>
            <?php endif; ?>

            <!-- Success Messages -->
            <?php if (session()->has('success')): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle me-2"></i>
                    <?= session('success') ?>
                </div>
            <?php endif; ?>

            <!-- Login Form -->
            <form action="<?= url_to('login') ?>" method="post" class="login-form">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" 
                           name="email" 
                           class="form-control form-control-lg"
                           placeholder="Enter your email address"
                           required
                           autofocus>
                    <small class="text-muted mt-1 d-block">Use your institutional email</small>
                </div>

                <div class="form-group">
                    <label class="form-label">Password</label>
                    <div class="password-wrapper">
                        <input type="password" 
                               name="password" 
                               id="passwordInput"
                               class="form-control form-control-lg"
                               placeholder="Enter your password"
                               required>
                        <button type="button" class="toggle-password" id="togglePassword">
                            <i class="bi bi-eye-slash"></i>
                        </button>
                    </div>
                    <small class="text-muted mt-1 d-block">Minimum 8 characters</small>
                </div>

                <div class="login-options">
                    <div class="remember-me">
                        <input type="checkbox" 
                               name="remember" 
                               id="remember" 
                               class="remember-checkbox">
                        <label for="remember" class="remember-label">Remember me</label>
                    </div>
                    <a href="<?= url_to('magic-link') ?>" class="magic-link">
                        <i class="bi bi-magic me-1"></i>Magic Link
                    </a>
                </div>

                <button type="submit" class="login-btn">
                    <i class="bi bi-box-arrow-in-right me-2"></i>
                    Login to Account
                </button>
            </form>

            <div class="register-link">
                <p class="register-text">Don't have an account yet?</p>
                <a href="<?= url_to('register') ?>" class="register-btn">
                    <i class="bi bi-person-plus me-1"></i>
                    Create New Account
                </a>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const togglePassword = document.getElementById("togglePassword");
    const passwordInput = document.getElementById("passwordInput");
    const eyeIcon = togglePassword.querySelector("i");
    
    // Toggle password visibility
    togglePassword.addEventListener("click", function () {
        const isHidden = passwordInput.type === "password";
        passwordInput.type = isHidden ? "text" : "password";
        
        // Toggle icon
        if (isHidden) {
            eyeIcon.classList.remove("bi-eye-slash");
            eyeIcon.classList.add("bi-eye");
            togglePassword.title = "Hide password";
        } else {
            eyeIcon.classList.remove("bi-eye");
            eyeIcon.classList.add("bi-eye-slash");
            togglePassword.title = "Show password";
        }
        
        // Focus back on password field
        passwordInput.focus();
    });
    
    // Add visual feedback on form focus
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.style.transform = 'translateY(-2px)';
        });
        
        input.addEventListener('blur', function() {
            this.parentElement.style.transform = 'translateY(0)';
        });
    });
    
    // Add animation to login button on hover
    const loginBtn = document.querySelector('.login-btn');
    loginBtn.addEventListener('mouseenter', function() {
        this.style.transform = 'translateY(-2px) scale(1.02)';
    });
    
    loginBtn.addEventListener('mouseleave', function() {
        this.style.transform = 'translateY(0)';
    });
});
</script>

<?= $this->endSection(); ?>






