<?= $this->extend('layouts/main_admin') ?>
<?= $this->section('content') ?>

<?php
$isActive    = (bool) ($user->active ?? false);
$memberSince = $user->created_at ? date('d M Y', strtotime($user->created_at)) : '—';
$lastLogin   = $user->last_active  ? date('d M Y', strtotime($user->last_active))  : 'Never';
?>

<!-- Page header -->
<div class="slams-page-header">
    <div class="slams-page-header-left">
        <h1 class="slams-page-title">Edit User</h1>
        <p class="slams-page-subtitle">Update details, role, and account status for this user</p>
    </div>
    <div class="slams-page-header-actions">
        <a href="/admin/users" class="btn btn-glass btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Users
        </a>
    </div>
</div>

<!-- Flash messages -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success border-0 shadow-sm d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill"></i>
        <div><?= esc(session()->getFlashdata('success')) ?></div>
    </div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i>
        <div><?= esc(session()->getFlashdata('error')) ?></div>
    </div>
<?php endif; ?>

<form method="post" action="/admin/users/update/<?= (int) $user->id ?>" id="userForm">
    <?= csrf_field() ?>
    <input type="hidden" name="active" id="activeInput" value="<?= $isActive ? '1' : '0' ?>">

    <div class="row g-4">

        <!-- ── Left column: form sections ──────────────────────────────── -->
        <div class="col-lg-8">

            <!-- Basic information -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent py-3 d-flex align-items-center gap-2">
                    <i class="bi bi-person-badge text-primary"></i>
                    <span class="fw-bold">Basic Information</span>
                </div>
                <div class="card-body">
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="username">
                                <i class="bi bi-person me-1 text-muted"></i>Username
                            </label>
                            <input type="text" class="form-control" id="username" name="username"
                                   value="<?= esc(old('username', $user->username)) ?>"
                                   required placeholder="Enter username">
                            <div class="form-text">Unique identifier for the user</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="email">
                                <i class="bi bi-envelope me-1 text-muted"></i>Email Address
                            </label>
                            <input type="email" class="form-control" id="email" name="email"
                                   value="<?= esc(old('email', $email)) ?>"
                                   required placeholder="user@example.com">
                            <div class="form-text">Primary email for login and notifications</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="full_name">
                                <i class="bi bi-person-vcard me-1 text-muted"></i>Full Name
                            </label>
                            <input type="text" class="form-control" id="full_name" name="full_name"
                                   value="<?= esc(old('full_name', $user->full_name ?? '')) ?>"
                                   placeholder="Enter full name">
                            <div class="form-text">Shown in reports and user-facing pages</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="phone">
                                <i class="bi bi-telephone me-1 text-muted"></i>Phone Number
                            </label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                   value="<?= esc(old('phone', $user->phone ?? '')) ?>"
                                   placeholder="e.g. 011-23456789">
                            <div class="form-text">Contact number for approvals and notifications</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="faculty_id">
                                <i class="bi bi-building me-1 text-muted"></i>Faculty
                            </label>
                            <select class="form-select" id="faculty_id" name="faculty_id">
                                <option value="">No faculty assigned</option>
                                <?php foreach ($faculties as $faculty): ?>
                                    <option value="<?= esc($faculty['id']) ?>"
                                        <?= (string) old('faculty_id', $user->faculty_id ?? '') === (string) $faculty['id'] ? 'selected' : '' ?>>
                                        <?= esc($faculty['code']) ?> — <?= esc($faculty['name_en']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">Used in bookings and report scoping</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">
                                <i class="bi bi-toggle-on me-1 text-muted"></i>Account Status
                            </label>
                            <div class="form-control d-flex align-items-center gap-2" style="cursor:default;background:var(--slams-surface-soft)">
                                <span class="badge rounded-pill <?= $isActive ? 'text-bg-success' : 'text-bg-secondary' ?>">
                                    <i class="bi bi-<?= $isActive ? 'check-circle' : 'x-circle' ?> me-1"></i>
                                    <?= $isActive ? 'Active' : 'Inactive' ?>
                                </span>
                                <span class="text-muted small">Use the action button to change</span>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Security -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent py-3 d-flex align-items-center gap-2">
                    <i class="bi bi-shield-lock text-primary"></i>
                    <span class="fw-bold">Security</span>
                </div>
                <div class="card-body">
                    <div class="alert alert-info border-0 d-flex gap-2 mb-3">
                        <i class="bi bi-info-circle-fill flex-shrink-0 mt-1"></i>
                        <div class="small">Leave both fields blank to keep the current password. Use <strong>Send Recovery Link</strong> below to let the user reset it themselves.</div>
                    </div>
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="password">
                                <i class="bi bi-key me-1 text-muted"></i>New Password
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password" name="password"
                                       placeholder="Leave blank to keep current">
                                <button type="button" class="btn btn-outline-secondary" id="togglePassword">
                                    <i class="bi bi-eye" id="togglePasswordIcon"></i>
                                </button>
                            </div>
                            <div class="form-text">Minimum 8 characters with letters and numbers</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold" for="password_confirm">
                                <i class="bi bi-key-fill me-1 text-muted"></i>Confirm Password
                            </label>
                            <div class="input-group">
                                <input type="password" class="form-control" id="password_confirm" name="password_confirm"
                                       placeholder="Re-enter new password">
                                <button type="button" class="btn btn-outline-secondary" id="togglePasswordConfirm">
                                    <i class="bi bi-eye" id="togglePasswordConfirmIcon"></i>
                                </button>
                            </div>
                            <div class="form-text" id="passwordMatchHint"></div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Roles -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent py-3 d-flex align-items-center gap-2">
                    <i class="bi bi-person-rolodex text-primary"></i>
                    <span class="fw-bold">Role</span>
                </div>
                <div class="card-body">
                    <div class="alert alert-warning border-0 d-flex gap-2 mb-3">
                        <i class="bi bi-exclamation-triangle-fill flex-shrink-0 mt-1"></i>
                        <div class="small">Each user has exactly one role. Changing it takes effect immediately after saving.</div>
                    </div>
                    <div class="row g-2">
                        <?php
                        $roleIcons = [
                            'admin'    => ['icon' => 'bi-shield-fill-check', 'color' => 'danger'],
                            'manager'  => ['icon' => 'bi-briefcase-fill',    'color' => 'warning'],
                            'pic'      => ['icon' => 'bi-person-workspace',  'color' => 'info'],
                            'student'  => ['icon' => 'bi-mortarboard-fill',  'color' => 'primary'],
                            'staff'    => ['icon' => 'bi-person-badge-fill', 'color' => 'success'],
                            'external' => ['icon' => 'bi-globe',             'color' => 'secondary'],
                        ];
                        ?>
                        <?php foreach ($allRoles as $role): ?>
                            <?php
                            $meta    = $roleIcons[$role] ?? ['icon' => 'bi-person-fill', 'color' => 'secondary'];
                            $checked = in_array($role, $roles);
                            ?>
                            <div class="col-6 col-md-4">
                                <label class="d-block h-100" for="role_<?= esc($role) ?>" style="cursor:pointer">
                                    <input type="radio" name="roles[]" value="<?= esc($role) ?>"
                                           id="role_<?= esc($role) ?>" class="d-none role-radio"
                                           <?= $checked ? 'checked' : '' ?>>
                                    <div class="card border-2 h-100 role-card <?= $checked ? 'border-primary bg-primary-subtle' : 'border' ?>"
                                         style="border-radius:10px;transition:all 0.15s">
                                        <div class="card-body py-3 text-center">
                                            <div class="mb-2">
                                                <i class="bi <?= esc($meta['icon']) ?> text-<?= esc($meta['color']) ?>"
                                                   style="font-size:1.5rem"></i>
                                            </div>
                                            <div class="fw-bold" style="font-size:0.88rem"><?= esc(ucfirst($role)) ?></div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Form actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-body d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-save me-1"></i> Save Changes
                        </button>
                        <button type="submit"
                                class="btn btn-outline-warning"
                                formaction="/admin/users/send-recovery/<?= (int) $user->id ?>"
                                formmethod="post"
                                formnovalidate
                                onclick="return confirm('Send a recovery sign-in link to this user\'s registered email?')">
                            <i class="bi bi-envelope-paper me-1"></i> Send Recovery Link
                        </button>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="/admin/users" class="btn btn-outline-secondary">
                            <i class="bi bi-x me-1"></i> Cancel
                        </a>
                        <button type="button" class="btn <?= $isActive ? 'btn-outline-danger' : 'btn-outline-success' ?>"
                                id="toggleStatusBtn">
                            <i class="bi bi-person-<?= $isActive ? 'x' : 'check' ?> me-1"></i>
                            <?= $isActive ? 'Deactivate Account' : 'Activate Account' ?>
                        </button>
                    </div>
                </div>
            </div>

        </div><!-- /col-lg-8 -->

        <!-- ── Right column: user profile sidebar ──────────────────────── -->
        <div class="col-lg-4">

            <!-- Profile card -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-body text-center py-4">
                    <div class="mx-auto mb-3 d-flex align-items-center justify-content-center rounded-circle bg-primary-subtle"
                         style="width:72px;height:72px">
                        <i class="bi bi-person-fill text-primary" style="font-size:2rem"></i>
                    </div>
                    <h5 class="fw-bold mb-0"><?= esc($user->username) ?></h5>
                    <?php if (! empty($user->full_name)): ?>
                        <div class="text-muted small mb-1"><?= esc($user->full_name) ?></div>
                    <?php endif; ?>
                    <div class="text-muted small mb-3"><?= esc($email) ?></div>
                    <span class="badge rounded-pill <?= $isActive ? 'text-bg-success' : 'text-bg-secondary' ?> px-3 py-2">
                        <i class="bi bi-<?= $isActive ? 'check-circle' : 'x-circle' ?> me-1"></i>
                        <?= $isActive ? 'Active Account' : 'Inactive Account' ?>
                    </span>
                </div>
            </div>

            <!-- Account stats -->
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-transparent py-2 px-3">
                    <span class="fw-bold small text-muted text-uppercase" style="letter-spacing:0.06em">Account Details</span>
                </div>
                <div class="list-group list-group-flush">
                    <div class="list-group-item px-3 py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2 text-muted small">
                            <i class="bi bi-person-badge"></i> User ID
                        </div>
                        <span class="fw-semibold small">#<?= (int) $user->id ?></span>
                    </div>
                    <div class="list-group-item px-3 py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2 text-muted small">
                            <i class="bi bi-shield-check"></i> Role
                        </div>
                        <span class="fw-semibold small">
                            <?= esc(ucfirst($roles[0] ?? 'None')) ?>
                        </span>
                    </div>
                    <div class="list-group-item px-3 py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2 text-muted small">
                            <i class="bi bi-calendar-plus"></i> Member Since
                        </div>
                        <span class="fw-semibold small"><?= esc($memberSince) ?></span>
                    </div>
                    <div class="list-group-item px-3 py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2 text-muted small">
                            <i class="bi bi-clock-history"></i> Last Login
                        </div>
                        <span class="fw-semibold small"><?= esc($lastLogin) ?></span>
                    </div>
                </div>
            </div>

            <!-- Tip card -->
            <div class="card border-0 shadow-sm border-start border-4 border-primary">
                <div class="card-body py-3">
                    <div class="d-flex gap-2">
                        <i class="bi bi-lightbulb text-primary flex-shrink-0 mt-1"></i>
                        <div class="small text-muted">
                            To remove a user permanently, deactivate the account instead. Active bookings and records linked to this user will be preserved.
                        </div>
                    </div>
                </div>
            </div>

        </div><!-- /col-lg-4 -->

    </div><!-- /row -->
</form>

<script>
document.addEventListener('DOMContentLoaded', function () {
    /* ── Password visibility toggles ── */
    function makeToggle(btnId, inputId, iconId) {
        document.getElementById(btnId)?.addEventListener('click', function () {
            var inp  = document.getElementById(inputId);
            var icon = document.getElementById(iconId);
            if (!inp) return;
            var show = inp.type === 'password';
            inp.type = show ? 'text' : 'password';
            icon.className = show ? 'bi bi-eye-slash' : 'bi bi-eye';
        });
    }
    makeToggle('togglePassword',        'password',         'togglePasswordIcon');
    makeToggle('togglePasswordConfirm', 'password_confirm', 'togglePasswordConfirmIcon');

    /* ── Password match hint ── */
    var pwInput    = document.getElementById('password');
    var pwConfirm  = document.getElementById('password_confirm');
    var matchHint  = document.getElementById('passwordMatchHint');

    function checkMatch() {
        if (!pwInput.value && !pwConfirm.value) { matchHint.textContent = ''; return; }
        if (pwInput.value === pwConfirm.value) {
            matchHint.textContent = 'Passwords match.';
            matchHint.className = 'form-text text-success';
        } else {
            matchHint.textContent = 'Passwords do not match.';
            matchHint.className = 'form-text text-danger';
        }
    }
    pwInput?.addEventListener('input', checkMatch);
    pwConfirm?.addEventListener('input', checkMatch);

    /* ── Role card highlight on select ── */
    document.querySelectorAll('.role-radio').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.role-card').forEach(function (card) {
                card.classList.remove('border-primary', 'bg-primary-subtle');
                card.classList.add('border');
            });
            if (this.checked) {
                var card = this.closest('label').querySelector('.role-card');
                if (card) {
                    card.classList.remove('border');
                    card.classList.add('border-primary', 'bg-primary-subtle');
                }
            }
        });
    });

    /* ── Activate / Deactivate ── */
    document.getElementById('toggleStatusBtn')?.addEventListener('click', function () {
        var isActive = document.getElementById('activeInput').value === '1';
        var action   = isActive ? 'deactivate' : 'activate';
        var username = '<?= esc(addslashes($user->username)) ?>';
        if (confirm('Are you sure you want to ' + action + ' ' + username + '?')) {
            document.getElementById('activeInput').value = isActive ? '0' : '1';
            document.getElementById('userForm').submit();
        }
    });

    /* ── Form validation ── */
    document.getElementById('userForm').addEventListener('submit', function (e) {
        var pw  = pwInput.value;
        var pwc = pwConfirm.value;
        if (pw.length > 0) {
            if (pw.length < 8) {
                e.preventDefault();
                alert('Password must be at least 8 characters long.');
                pwInput.focus();
                return;
            }
            if (pw !== pwc) {
                e.preventDefault();
                alert('Passwords do not match.');
                pwConfirm.focus();
                return;
            }
        }
        if (!document.querySelector('input[name="roles[]"]:checked')) {
            e.preventDefault();
            alert('Please select a role for this user.');
        }
    });
});
</script>

<?= $this->endSection() ?>
