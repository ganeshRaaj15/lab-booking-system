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

    /* Read-only fields */
    .form-control-glass[readonly] {
        background: rgba(241, 245, 249, 0.8);
        border-color: rgba(148, 163, 184, 0.3);
        color: #64748b;
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

    /* Role badges */
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

    .btn-warning-glass {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        border: none;
    }

    .btn-warning-glass:hover {
        background: linear-gradient(135deg, #d97706, #b45309);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(245, 158, 11, 0.3);
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

    /* User info summary */
    .user-summary {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(30, 64, 175, 0.03));
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid rgba(59, 130, 246, 0.1);
        margin-bottom: 2rem;
    }

    .user-summary-header {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
    }

    .user-avatar {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        background: linear-gradient(135deg, #3b82f6, #1e40af);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.5rem;
    }

    .user-info h4 {
        margin: 0;
        color: #1e293b;
    }

    .user-info .text-muted {
        font-size: 0.875rem;
    }

    .user-stats {
        display: flex;
        gap: 1.5rem;
    }

    .stat-item {
        text-align: center;
        padding: 0.5rem 1rem;
        background: rgba(255, 255, 255, 0.8);
        border-radius: 8px;
        border: 1px solid rgba(59, 130, 246, 0.1);
    }

    .stat-value {
        font-size: 1.25rem;
        font-weight: 700;
        color: #3b82f6;
    }

    .stat-label {
        font-size: 0.75rem;
        color: #64748b;
    }

    /* Toggle password */
    .password-toggle {
        position: relative;
    }

    .toggle-password {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        color: #64748b;
        cursor: pointer;
    }

    .toggle-password:hover {
        color: #3b82f6;
    }
</style>

<div class="user-form-page">
    <!-- PAGE HEADER -->
    <div class="dashboard-header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h1>Edit User</h1>
                <p>Update user details, roles, and permissions</p>
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

    <!-- USER SUMMARY -->
    <div class="user-summary glass-card">
        <div class="user-summary-header">
            <div class="user-avatar">
                <i class="bi bi-person-fill"></i>
            </div>
            <div class="user-info flex-grow-1">
                <h4><?= esc($user->username) ?></h4>
                <div class="text-muted">User ID: <?= $user->id ?> • Email: <?= esc($email) ?></div>
            </div>
            <div class="d-flex gap-2">
                <span class="badge <?= $user->active ? 'badge-active' : 'badge-inactive' ?>">
                    <i class="bi bi-<?= $user->active ? 'check-circle' : 'x-circle' ?> me-1"></i>
                    <?= $user->active ? 'Active' : 'Inactive' ?>
                </span>
            </div>
        </div>
        
        <div class="user-stats">
            <div class="stat-item">
                <div class="stat-value"><?= count($roles) ?></div>
                <div class="stat-label">Roles Assigned</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?= date('M d, Y', strtotime($user->created_at ?? 'now')) ?></div>
                <div class="stat-label">Member Since</div>
            </div>
            <div class="stat-item">
                <div class="stat-value">
                    <?php 
                    $lastLogin = $user->last_active ? date('M d', strtotime($user->last_active)) : 'Never';
                    echo $lastLogin;
                    ?>
                </div>
                <div class="stat-label">Last Login</div>
            </div>
        </div>
    </div>

    <!-- USER FORM -->
    <div class="glass-card">
        <div class="card-body p-4">
            <form method="post" action="/admin/users/update/<?= $user->id ?>" id="userForm">
                <?= csrf_field() ?>
                <input type="hidden" name="active" id="activeInput" value="<?= $user->active ? '1' : '0' ?>">
                
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
                                       value="<?= old('username', $user->username) ?>" 
                                       required
                                       placeholder="Enter username">
                                <div class="form-hint">Unique identifier for the user</div>
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
                                       value="<?= old('email', $email) ?>" 
                                       required
                                       placeholder="user@example.com">
                                <div class="form-hint">Primary email for communication</div>
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
                                       value="<?= old('full_name', $user->full_name ?? '') ?>"
                                       placeholder="Enter full name">
                                <div class="form-hint">Display name used in reports and user-facing pages</div>
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
                                       value="<?= old('phone', $user->phone ?? '') ?>"
                                       placeholder="Enter phone number">
                                <div class="form-hint">Operational contact number for approvals and notifications</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-glass">
                                <label for="faculty_id">
                                    <i class="bi bi-building"></i>
                                    Faculty
                                </label>
                                <select class="form-control form-control-glass" id="faculty_id" name="faculty_id">
                                    <option value="">No faculty assigned</option>
                                    <?php foreach ($faculties as $faculty): ?>
                                        <option value="<?= esc($faculty['id']) ?>" <?= (string) old('faculty_id', $user->faculty_id ?? '') === (string) $faculty['id'] ? 'selected' : '' ?>>
                                            <?= esc($faculty['code']) ?> - <?= esc($faculty['name_en']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                                <div class="form-hint">Primary faculty used in profile and booking workflows</div>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-group-glass">
                                <label for="activeDisplay">
                                    <i class="bi bi-toggle-on"></i>
                                    Account Status
                                </label>
                                <input type="text"
                                       class="form-control form-control-glass"
                                       id="activeDisplay"
                                       value="<?= $user->active ? 'Active' : 'Inactive' ?>"
                                       readonly>
                                <div class="form-hint">Use the activate/deactivate action below to change status</div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mb-5">
                    <h5 class="section-title">
                        <i class="bi bi-shield-lock"></i>
                        Security
                    </h5>
                    
                    <div class="alert alert-info glass-card mb-4">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle-fill me-2"></i>
                            <div class="small">
                                Leave password fields blank if you don't want to change the password.
                            </div>
                        </div>
                    </div>
                    
                    <div class="row g-4">
                        <div class="col-md-6">
                            <div class="form-group-glass password-toggle">
                                <label for="password">
                                    <i class="bi bi-key"></i>
                                    New Password
                                </label>
                                <input type="password" 
                                       class="form-control form-control-glass" 
                                       id="password" 
                                       name="password" 
                                       placeholder="Leave blank to keep current">
                                <button type="button" class="toggle-password" data-target="password">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <div class="form-hint">Minimum 8 characters with letters and numbers</div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="form-group-glass password-toggle">
                                <label for="password_confirm">
                                    <i class="bi bi-key-fill"></i>
                                    Confirm Password
                                </label>
                                <input type="password" 
                                       class="form-control form-control-glass" 
                                       id="password_confirm" 
                                       name="password_confirm" 
                                       placeholder="Confirm new password">
                                <button type="button" class="toggle-password" data-target="password_confirm">
                                    <i class="bi bi-eye"></i>
                                </button>
                                <div class="form-hint">Re-enter the password for verification</div>
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
                                               <?= in_array($role, $roles) ? 'checked' : '' ?>>
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
                                        <strong>Warning:</strong> Changing roles will affect user permissions immediately.
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
                                <i class="bi bi-save me-2"></i> Save Changes
                            </button>
                            <button type="button" class="btn btn-warning-glass ms-2" id="resetPassword">
                                <i class="bi bi-arrow-clockwise me-2"></i> Reset Password
                            </button>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <a href="/admin/users" class="btn btn-outline-secondary">
                                <i class="bi bi-x-circle me-1"></i> Cancel
                            </a>
                            <button type="button" class="btn btn-outline-danger" id="deactivateUser">
                                <i class="bi bi-person-x me-1"></i> 
                                <?= $user->active ? 'Deactivate' : 'Activate' ?>
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Toggle password visibility
    document.querySelectorAll('.toggle-password').forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            const icon = this.querySelector('i');
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'bi bi-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'bi bi-eye';
            }
        });
    });
    
    // Reset password functionality
    document.getElementById('resetPassword')?.addEventListener('click', function() {
        if (confirm('Generate a random password for this user? The new password will be shown once.')) {
            // Generate random password
            const chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
            let password = '';
            for (let i = 0; i < 12; i++) {
                password += chars.charAt(Math.floor(Math.random() * chars.length));
            }
            
            // Set the password fields
            document.getElementById('password').value = password;
            document.getElementById('password_confirm').value = password;
            
            // Show the password
            alert(`New Password: ${password}\n\nCopy this password and provide it to the user.`);
        }
    });
    
    // Deactivate/Activate user
    document.getElementById('deactivateUser')?.addEventListener('click', function() {
        const action = this.innerHTML.includes('Deactivate') ? 'deactivate' : 'activate';
        const username = '<?= esc($user->username) ?>';
        
        if (confirm(`Are you sure you want to ${action} ${username}?`)) {            const activeInput = document.getElementById('activeInput');
            if (activeInput) {
                activeInput.value = action === 'deactivate' ? '0' : '1';
            }
            document.getElementById('userForm')?.submit();
        }
    });
    
    // Password validation
    const passwordInput = document.getElementById('password');
    const passwordConfirmInput = document.getElementById('password_confirm');
    
    passwordInput.addEventListener('input', function() {
        if (this.value.length === 0) return;
        
        const password = this.value;
        const strength = checkPasswordStrength(password);
        
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
        if (passwordInput.value.length === 0 && this.value.length === 0) return;
        
        if (passwordInput.value !== this.value) {
            this.style.borderColor = '#ef4444';
        } else {
            this.style.borderColor = '#10b981';
        }
    });
    
    // Form validation
    document.getElementById('userForm').addEventListener('submit', function(e) {
        const password = passwordInput.value;
        const confirm = passwordConfirmInput.value;
        
        if (password.length > 0) {
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
        }
        
        const selectedRoles = Array.from(document.querySelectorAll('input[name="roles[]"]:checked'));
        if (selectedRoles.length === 0) {
            if (!confirm('No roles selected. Are you sure you want to remove all roles from this user?')) {
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


