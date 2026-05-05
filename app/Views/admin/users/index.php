<?= $this->extend('layouts/main_admin') ?>

<?= $this->section('styles') ?>
<style>
.user-control-panel .card-body {
    padding: 1rem 1.1rem;
}

.user-control-title {
    color: var(--slams-heading);
    font-family: var(--slams-font-display);
    font-size: 1.08rem;
    font-weight: 800;
}

.user-control-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.35rem;
    padding: 0.28rem 0.7rem;
    border-radius: 999px;
    background: var(--slams-primary-soft);
    color: var(--slams-primary);
    font-size: 0.78rem;
    font-weight: 700;
}

.user-role-pills {
    margin-bottom: 0;
}

.user-role-pills .nav-link {
    display: inline-flex;
    align-items: center;
    gap: 0.65rem;
    border: 1px solid transparent;
    border-radius: 999px;
    color: var(--slams-heading);
    font-weight: 600;
    padding: 0.75rem 1rem;
    background: transparent;
}

.user-role-pills .nav-link:hover {
    background: var(--slams-surface-soft);
    border-color: var(--slams-border);
}

.user-role-pills .nav-link.active {
    background: var(--slams-primary-soft);
    border-color: color-mix(in srgb, var(--slams-primary) 20%, transparent);
    color: var(--slams-primary);
}

.user-role-pills .nav-link .badge {
    font-size: 0.75rem;
}

.user-filter-strip {
    padding-top: 1rem;
    border-top: 1px solid var(--slams-border);
}

.user-filter-strip .form-label {
    color: var(--slams-muted);
    font-weight: 700;
}

.user-filter-actions {
    display: flex;
    gap: 0.75rem;
    justify-content: flex-end;
}

.user-filter-actions .btn {
    min-width: 56px;
}

@media (max-width: 991.98px) {
    .user-filter-actions {
        justify-content: stretch;
    }

    .user-filter-actions .btn {
        flex: 1 1 auto;
    }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>

<?php
$filters = $filters ?? ['q' => '', 'role' => '', 'status' => '', 'per_page' => 10, 'page' => 1];
$pagination = $pagination ?? ['total' => count($users), 'page' => 1, 'per_page' => 10, 'page_count' => 1];
$stats = $stats ?? ['total' => count($users), 'active' => count(array_filter($users, fn($u) => $u['active']))];
$allRoles = $allRoles ?? [];
$roleTabs = $roleTabs ?? $allRoles;
$roleTabCounts = $roleTabCounts ?? ['all' => count($users)];
$roleTabLabels = [
    'all' => 'All Users',
    'student' => 'Students',
    'staff' => 'Staff',
    'external' => 'External',
    'pic' => 'PICs',
    'technician' => 'Technicians',
    'manager' => 'Managers',
    'admin' => 'Admins',
];
$currentRoleLabel = $filters['role'] !== ''
    ? ($roleTabLabels[$filters['role']] ?? ucfirst($filters['role']))
    : $roleTabLabels['all'];
$baseQuery = [
    'q' => $filters['q'],
    'role' => $filters['role'],
    'status' => $filters['status'],
    'per_page' => $filters['per_page'],
];
$exportQuery = array_filter($baseQuery, static fn($value) => $value !== '' && $value !== null);
$tabBaseQuery = [
    'q' => $filters['q'],
    'status' => $filters['status'],
    'per_page' => $filters['per_page'],
];
$buildRoleUrl = static function (string $role = '') use ($tabBaseQuery): string {
    $query = array_filter(
        array_merge($tabBaseQuery, $role !== '' ? ['role' => $role] : []),
        static fn($value) => $value !== '' && $value !== null
    );

    return '/admin/users' . ($query !== [] ? '?' . http_build_query($query) : '');
};
$hasActiveFilters = $filters['q'] !== '' || $filters['role'] !== '' || $filters['status'] !== '';
?>


<div class="user-management">
    <!-- PAGE HEADER -->
    <div class="dashboard-header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h1>User Management</h1>
                <p>Manage system users, roles, and permissions</p>
            </div>
            <div class="d-flex gap-3">
                <div class="quick-stat">
                    <i class="bi bi-people-fill"></i>
                    <div>
                        <div class="small text-muted">Total Users</div>
                        <div class="fw-bold"><?= esc($stats['total']) ?></div>
                    </div>
                </div>
                <div class="quick-stat">
                    <i class="bi bi-person-check"></i>
                    <div>
                        <div class="small text-muted">Active</div>
                        <div class="fw-bold"><?= esc($stats['active']) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="glass-card user-control-panel mb-4">
        <div class="card-body p-3 p-lg-4">
            <div class="d-flex flex-column gap-4">
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-start gap-3">
                    <div>
                        <div class="small text-uppercase text-muted fw-semibold mb-1">User Categories</div>
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <div class="user-control-title">Filter and browse users from one place</div>
                            <span class="user-control-chip">
                                <i class="bi bi-sliders2-vertical"></i>
                                <?= esc($currentRoleLabel) ?>
                            </span>
                        </div>
                        <p class="text-muted mb-0">Use the role tabs as the primary switch, then refine the list with search and status filters below.</p>
                    </div>
                    <a href="/admin/users/create" class="btn btn-primary align-self-xl-center">
                        <i class="bi bi-person-plus me-1"></i> Add New User
                    </a>
                </div>
                <div class="d-flex flex-column flex-xl-row justify-content-between align-items-xl-center gap-3">
                    <ul class="nav nav-pills user-role-pills flex-wrap gap-2">
                        <?php foreach (array_merge(['all'], $roleTabs) as $roleKey): ?>
                            <?php
                            $roleValue = $roleKey === 'all' ? '' : $roleKey;
                            $isActive = $roleKey === 'all'
                                ? $filters['role'] === ''
                                : $filters['role'] === $roleValue;
                            $badgeClass = $isActive ? 'text-bg-light' : 'text-bg-secondary';
                            ?>
                            <li class="nav-item">
                                <a class="nav-link <?= $isActive ? 'active' : '' ?>" href="<?= esc($buildRoleUrl($roleValue)) ?>">
                                    <span><?= esc($roleTabLabels[$roleKey] ?? ucfirst($roleKey)) ?></span>
                                    <span class="badge <?= esc($badgeClass) ?>"><?= esc($roleTabCounts[$roleKey] ?? 0) ?></span>
                                </a>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="user-filter-strip">
                    <form method="get" action="/admin/users" class="row g-3 align-items-end">
                        <input type="hidden" name="role" value="<?= esc($filters['role']) ?>">
                        <div class="col-xl-6 col-lg-6">
                            <label class="form-label small text-muted">Search</label>
                            <input type="text" name="q" class="form-control" value="<?= esc($filters['q']) ?>" placeholder="Name, username, email, or phone">
                        </div>
                        <div class="col-xl-2 col-md-4">
                            <label class="form-label small text-muted">Status</label>
                            <select name="status" class="form-select">
                                <option value="">All statuses</option>
                                <option value="active" <?= $filters['status'] === 'active' ? 'selected' : '' ?>>Active</option>
                                <option value="inactive" <?= $filters['status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                            </select>
                        </div>
                        <div class="col-xl-2 col-md-4">
                            <label class="form-label small text-muted">Rows</label>
                            <select name="per_page" class="form-select">
                                <?php foreach ([10, 25, 50] as $perPage): ?>
                                    <option value="<?= esc($perPage) ?>" <?= (int) $filters['per_page'] === $perPage ? 'selected' : '' ?>><?= esc($perPage) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-xl-2 col-12 user-filter-actions">
                            <button type="submit" class="btn btn-primary flex-fill"><i class="bi bi-funnel me-1"></i>Filter</button>
                            <a href="/admin/users" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success glass-card mb-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                <div class="flex-grow-1"><?= session()->getFlashdata('message') ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger glass-card mb-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                <div class="flex-grow-1"><?= session()->getFlashdata('error') ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- USERS TABLE -->
    <div class="glass-card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="glass-table">
                    <thead>
                        <tr>
                            <th style="width: 60px;">#</th>
                            <th>User</th>
                            <th>Email</th>
                            <th>Roles</th>
                            <th>Status</th>
                            <th style="width: 100px;" class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody id="userTableBody">
                        <?php if (empty($users)): ?>
                            <tr>
                                <td colspan="6">
                                    <div class="empty-state">
                                        <i class="bi bi-people"></i>
                                        <h4 class="text-muted mb-2">No Users Found</h4>
                                        <p class="text-muted"><?= $hasActiveFilters ? 'Try a different role or clear the current filters.' : 'Start by adding your first user.' ?></p>
                                        <a href="<?= $hasActiveFilters ? '/admin/users' : '/admin/users/create' ?>" class="btn <?= $hasActiveFilters ? 'btn-outline-secondary' : 'btn-primary' ?> mt-2">
                                            <i class="bi <?= $hasActiveFilters ? 'bi-arrow-counterclockwise' : 'bi-person-plus' ?> me-1"></i>
                                            <?= $hasActiveFilters ? 'Clear Filters' : 'Add User' ?>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $i = (($pagination['page'] - 1) * $pagination['per_page']) + 1; foreach ($users as $u): ?>
                                <tr class="user-row" data-username="<?= strtolower(esc($u['username'])) ?>" 
                                    data-email="<?= strtolower(esc($u['email'])) ?>" 
                                    data-roles="<?= strtolower(implode(',', $u['roles'])) ?>">
                                    <td class="text-muted fw-semibold"><?= $i++ ?></td>
                                    
                                    <td>
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="icon-container bg-light rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                <i class="bi bi-person fs-5 text-primary"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold"><?= esc($u['username']) ?></div>
                                                <?php if (!empty($u['full_name'])): ?>
                                                    <small class="text-muted"><?= esc($u['full_name']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="bi bi-envelope text-muted"></i>
                                            <span><?= esc($u['email']) ?></span>
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <div class="d-flex flex-wrap gap-1">
                                            <?php if (!empty($u['roles'])): ?>
                                                <?php foreach ($u['roles'] as $role): ?>
                                                    <span class="role-badge <?= $role ?>">
                                                        <i class="bi bi-person-badge me-1"></i>
                                                        <?= esc(ucfirst($role)) ?>
                                                    </span>
                                                <?php endforeach; ?>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">No roles</span>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    
                                    <td>
                                        <?php if ($u['active']): ?>
                                            <span class="badge badge-active">
                                                <i class="bi bi-check-circle me-1"></i>
                                                Active
                                            </span>
                                        <?php else: ?>
                                            <span class="badge badge-inactive">
                                                <i class="bi bi-x-circle me-1"></i>
                                                Inactive
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                    
                                    <td class="text-end">
                                        <div class="d-flex justify-content-end gap-2">
                                            <a href="/admin/users/edit/<?= $u['id'] ?>" 
                                               class="btn-action edit" 
                                               title="Edit User">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            
                                            <form action="/admin/users/delete/<?= $u['id'] ?>" 
                                                  method="post" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete user <?= esc($u['username']) ?>? This action cannot be undone.');">
                                                <?= csrf_field() ?>
                                                <button type="submit" 
                                                        class="btn-action delete" 
                                                        title="Delete User">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
                <!-- TABLE FOOTER -->
        <?php if (!empty($users)): ?>
        <div class="card-footer border-top-0 bg-transparent py-3 px-4">
            <div class="d-flex justify-content-between align-items-center">
                <div class="text-muted small">
                    <i class="bi bi-people me-1"></i>
                    Showing <span class="fw-semibold text-primary"><?= count($users) ?></span> of <?= esc($pagination['total']) ?> matching user(s)
                </div>
                <div class="d-flex flex-wrap gap-2 align-items-center">
                    <?php if (($pagination['page_count'] ?? 1) > 1): ?>
                        <nav aria-label="User pagination">
                            <ul class="pagination pagination-sm mb-0">
                                <?php for ($pageNum = 1; $pageNum <= (int) $pagination['page_count']; $pageNum++): ?>
                                    <?php $pageQuery = array_filter(array_merge($baseQuery, ['page' => $pageNum]), static fn($value) => $value !== '' && $value !== null); ?>
                                    <li class="page-item <?= (int) $pagination['page'] === $pageNum ? 'active' : '' ?>">
                                        <a class="page-link" href="/admin/users?<?= esc(http_build_query($pageQuery)) ?>"><?= esc($pageNum) ?></a>
                                    </li>
                                <?php endfor; ?>
                            </ul>
                        </nav>
                    <?php endif; ?>
                    <a class="btn btn-outline-primary btn-sm" href="/admin/users/export?<?= esc(http_build_query($exportQuery)) ?>">
                        <i class="bi bi-download me-1"></i> Export CSV
                    </a>
                    <button class="btn btn-outline-secondary btn-sm" id="refreshUsers">
                        <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                    </button>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('userSearch');
    const clearSearchBtn = document.getElementById('clearSearch');
    const userRows = document.querySelectorAll('.user-row');

    if (searchInput && clearSearchBtn) {
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase().trim();

            userRows.forEach(row => {
                const username = row.dataset.username;
                const email = row.dataset.email;
                const roles = row.dataset.roles;

                const matches = username.includes(searchTerm) ||
                               email.includes(searchTerm) ||
                               roles.includes(searchTerm);

                row.style.display = matches ? '' : 'none';
            });
        });

        clearSearchBtn.addEventListener('click', function() {
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
        });
    }
    
    // Confirmation for delete actions
    const deleteForms = document.querySelectorAll('form[action*="delete"]');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            if (!confirm('Are you sure you want to delete this user? This action cannot be undone.')) {
                e.preventDefault();
            }
        });
    });
    
    // Add loading effect on actions
    const actionButtons = document.querySelectorAll('.btn-action');
    actionButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            this.classList.add('loading');
            setTimeout(() => {
                this.classList.remove('loading');
            }, 1000);
        });
    });
});

    // Refresh functionality
    document.getElementById('refreshUsers')?.addEventListener('click', function() {
        this.innerHTML = '<i class="bi bi-arrow-clockwise me-1"></i> Refreshing...';
        this.disabled = true;
        
        setTimeout(() => {
            window.location.reload();
        }, 500);
    });
</script>

<?= $this->endSection() ?>
