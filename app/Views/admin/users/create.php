<?= $this->extend('layouts/main_admin') ?>

<?= $this->section('content') ?>

<style>
    .user-form-page {
        --card-radius: 16px;
        --card-padding: 24px;
        --transition-speed: 0.3s;
    }

    /* Glass Card Styling */
    .glass-card {
        background: linear-gradient(135deg,
            rgba(255, 255, 255, 0.95),
            rgba(255, 255, 255, 0.98)
        );
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: var(--card-radius);
        border: 1px solid rgba(59, 130, 246, 0.15);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        transition: all var(--transition-speed) ease;
        overflow: hidden;
    }

    /* Form group styling */
    .form-group-glass {
        margin-bottom: 1.5rem;
    }

    .form-group-glass label {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-group-glass label i {
        color: #3b82f6;
    }

    .form-control-glass {
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(59, 130, 246, 0.2);
        border-radius: 10px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
        color: #1e293b;
    }

    .form-control-glass:focus {
        background: white;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-control-glass::placeholder {
        color: #94a3b8;
    }

    /* Role checkboxes - FIXED */
    .role-checkbox-group {
        background: rgba(241, 245, 249, 0.8);
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid rgba(59, 130, 246, 0.1);
    }

    .form-check-glass {
        margin-bottom: 0.75rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .form-check-input-glass {
        width: 20px;
        height: 20px;
        border: 2px solid rgba(59, 130, 246, 0.3);
        border-radius: 6px;
        cursor: pointer;
        transition: all 0.3s ease;
        flex-shrink: 0;
        margin: 0;
    }

    .form-check-input-glass:checked {
        background-color: #3b82f6;
        border-color: #3b82f6;
    }

    .form-check-label-glass {
        font-weight: 500;
        color: #475569;
        cursor: pointer;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 0.95rem;
    }

    /* Role badges preview */
    .role-badge-preview {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 0.875rem;
        font-weight: 500;
        margin: 0;
    }

    .role-badge-preview.admin {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        color: white;
    }

    .role-badge-preview.manager {
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        color: white;
    }

    .role-badge-preview.pic {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
    }

    .role-badge-preview.student {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
    }

    .role-badge-preview.external {
        background: linear-gradient(135deg, #6b7280, #4b5563);
        color: white;
    }

    /* Buttons */
    .btn-glass {
        padding: 10px 24px;
        border-radius: 10px;
        border: 1px solid rgba(59, 130, 246, 0.2);
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-glass:hover {
        background: rgba(59, 130, 246, 0.2);
        border-color: rgba(59, 130, 246, 0.4);
        transform: translateY(-2px);
    }

    .btn-primary-glass {
        background: linear-gradient(135deg, #3b82f6, #1e40af);
        color: white;
        border: none;
    }

    .btn-primary-glass:hover {
        background: linear-gradient(135deg, #1e40af, #1e3a8a);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    /* Dashboard header */
    .dashboard-header {
        margin-bottom: 2rem;
    }

    .dashboard-header h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }

    .dashboard-header p {
        color: #64748b;
        font-size: 0.95rem;
    }

    /* Form hints */
    .form-hint {
        font-size: 0.875rem;
        color: #64748b;
        margin-top: 0.25rem;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .glass-card {
            padding: 1rem !important;
        }
        
        .role-checkbox-group {
            padding: 1rem;
        }
    }

    /* Section titles */
    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e293b;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid rgba(59, 130, 246, 0.1);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: #3b82f6;
    }
</style>

<div class="user-form-page">
    <!-- PAGE HEADER -->
    <div class="dashboard-header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h1>Add New User</h1>
                <p>Create a new user account with appropriate roles and permissions</p>
            </div>
            <a href="/admin/users" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Users
            </a>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger glass-card mb-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                <div class="flex-grow-1"><?= session()->getFlashdata('error') ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- USER FORM -->
    <div class="glass-card">
        <div class="card-body p-4">
            <form method="post" action="/admin/users/store" id="userForm">
                <?= csrf_field() ?>
                
                <div class="mb-5">
                    <h5 class="section-title">
                        <i class="bi bi-person-badge"></i>
                        Basic Information
                    </h5>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-group-glass">
                                <label for="username">
                                    <i class="bi bi-person"></i>
                                    Username
                                </label>
                                <input type="text" 
                                       class="form-control form-control-glass" 
                                       id="username" 
                                       name="username" 
                                       value="<?= old('username') ?>" 
                                       required
                                       placeholder="Enter username">
                                <div class="form-hint">Choose a unique username for the user</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group-glass">
                                <label for="email">
                                    <i class="bi bi-envelope"></i>
                                    Email Address
                                </label>
                                <input type="email" 
                                       class="form-control form-control-glass" 
                                       id="email" 
                                       name="email" 
                                       value="<?= old('email') ?>" 
                                       required
                                       placeholder="user@example.com">
                                <div class="form-hint">Enter a valid email address</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-glass">
                                <label for="full_name">
                                    <i class="bi bi-person-vcard"></i>
                                    Full Name
                                </label>
                                <input type="text"
                                       class="form-control form-control-glass"
                                       id="full_name"
                                       name="full_name"
                                       value="<?= old('full_name') ?>"
                                       placeholder="Enter full name">
                                <div class="form-hint">Optional display name for reports and profile pages</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-glass">
                                <label for="phone">
                                    <i class="bi bi-telephone"></i>
                                    Phone Number
                                </label>
                                <input type="text"
                                       class="form-control form-control-glass"
                                       id="phone"
                                       name="phone"
                                       value="<?= old('phone') ?>"
                                       placeholder="Enter phone number">
                                <div class="form-hint">Optional contact number for operational communication</div>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <div class="form-group-glass">
                                <label for="faculty_id">
                                    <i class="bi bi-building"></i>
                                    Faculty
                                </label>
                                <select class="form-control form-control-glass" id="faculty_id" name="faculty_id">
                                    <option value="">No faculty assigned</option>
                                    <?php foreach ($faculties as $faculty): ?>
                                        <option value="<?= esc($faculty['id']) ?>" <?= old('faculty_id') == $faculty['id'] ? 'selected' : '' ?>>
                                            <?= esc($faculty['code']) ?> - <?= esc($faculty['name_en']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-hint">Used for booking workflows and user profile data</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <h5 class="section-title">
                        <i class="bi bi-shield-lock"></i>
                        Security
                    </h5>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-group-glass">
                                <label for="password">
                                    <i class="bi bi-key"></i>
                                    Password
                                </label>
                                <input type="password" 
                                       class="form-control form-control-glass" 
                                       id="password" 
                                       name="password" 
                                       required
                                       placeholder="Create a strong password">
                                <div class="form-hint">Minimum 8 characters with letters and numbers</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group-glass">
                                <label for="password_confirm">
                                    <i class="bi bi-key-fill"></i>
                                    Confirm Password
                                </label>
                                <input type="password" 
                                       class="form-control form-control-glass" 
                                       id="password_confirm" 
                                       name="password_confirm" 
                                       required
                                       placeholder="Confirm the password">
                                <div class="form-hint">Re-enter the password for verification</div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="alert alert-info glass-card mt-3">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <div class="small">
                                Password must be at least 8 characters long and include both letters and numbers.
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <h5 class="section-title">
                        <i class="bi bi-person-rolodex"></i>
                        Roles & Permissions
                    </h5>
                    
                    <div class="role-checkbox-group">
                        <div class="row">
                            <?php foreach ($allRoles as $role): ?>
                                <div class="col-md-6 col-lg-4">
                                    <div class="form-check-glass">
                                        <input class="form-check-input-glass" 
                                               type="checkbox" 
                                               name="roles[]" 
                                               value="<?= esc($role) ?>" 
                                               id="role_<?= esc($role) ?>"
                                               data-role="<?= esc($role) ?>"
                                               <?= old('roles') && in_array($role, old('roles')) ? 'checked' : '' ?>>
                                        <label class="form-check-label-glass" for="role_<?= esc($role) ?>">
                                            <i class="bi bi-person-badge me-2"></i>
                                            <?= ucfirst($role) ?>
                                        </label>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="mt-4">
                            <div class="alert alert-warning glass-card">
                                <div class="d-flex align-items-center">
                                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                    <div class="small">
                                        <strong>Note:</strong> Users can have multiple roles. Admin roles have full system access.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- FORM ACTIONS -->
                <div class="border-top pt-4">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <button type="submit" class="btn btn-primary-glass px-4">
                                <i class="bi bi-person-plus me-2"></i> Create User
                            </button>
                            <button type="button" class="btn btn-glass ms-2" id="previewUser">
                                <i class="bi bi-eye me-2"></i> Preview
                            </button>
                        </div>
                        
                        <a href="/admin/users" class="btn btn-outline-secondary">
                            <i class="bi bi-x-circle me-1"></i> Cancel
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Password strength indicator
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirm');
    
    passwordInput.addEventListener('input', function() {
        const password = this.value;
        const strength = checkPasswordStrength(password);
        
        // You could add visual feedback here
        if (password.length > 0 && password.length < 8) {
            this.style.borderColor = '#ef4444';
        } else if (strength === 'weak') {
            this.style.borderColor = '#f59e0b';
        } else if (strength === 'strong') {
            this.style.borderColor = '#10b981';
        }
    });
    
    // Password confirmation check
    passwordConfirmInput.addEventListener('input', function() {
        if (passwordInput.value !== this.value) {
            this.style.borderColor = '#ef4444';
        } else {
            this.style.borderColor = '#10b981';
        }
    });
    
    // Preview functionality
    document.getElementById('previewUser')?.addEventListener('click', function() {
        const username = document.getElementById('username').value;
        const email = document.getElementById('email').value;
        const selectedRoles = Array.from(document.querySelectorAll('input[name="roles[]"]:checked'))
            .map(cb => cb.value);
        
        let message = `New User Preview:\n\n`;
        message += `Username: ${username || '[Not set]'}\n`;
        message += `Email: ${email || '[Not set]'}\n`;
        message += `Roles: ${selectedRoles.length > 0 ? selectedRoles.join(', ') : '[No roles selected]'}\n\n`;
        message += `Click "Create User" to save.`;
        
        alert(message);
    });
    
    // Form validation
    document.getElementById('userForm').addEventListener('submit', function(e) {
        const password = passwordInput.value;
        const confirm = passwordConfirmInput.value;
        
        if (password.length < 8) {
            e.preventDefault();
            alert('Password must be at least 8 characters long.');
            passwordInput.focus();
            return;
        }
        
        if (password !== confirm) {
            e.preventDefault();
            alert('Passwords do not match.');
            passwordConfirmInput.focus();
            return;
        }
        
        const selectedRoles = Array.from(document.querySelectorAll('input[name="roles[]"]:checked'));
        if (selectedRoles.length === 0) {
            if (!confirm('No roles selected. Are you sure you want to create a user without any roles?')) {
                e.preventDefault();
            }
        }
    });
    
    function checkPasswordStrength(password) {
        if (password.length < 8) return 'too-short';
        
        const hasLetters = /[a-zA-Z]/.test(password);
        const hasNumbers = /[0-9]/.test(password);
        const hasSpecial = /[^a-zA-Z0-9]/.test(password);
        
        if (hasLetters && hasNumbers && hasSpecial) return 'strong';
        if (hasLetters && hasNumbers) return 'medium';
        return 'weak';
    }
});
</script>

<?= $this->endSection() ?>

