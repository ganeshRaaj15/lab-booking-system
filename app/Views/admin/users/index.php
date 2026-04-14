<?= $this->extend('layouts/main_admin') ?>

<?= $this->section('content') ?>

<style>
    /* User Management Specific Styles */
    .user-management {
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

    .glass-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 12px 40px rgba(0, 0, 0, 0.12);
        border-color: rgba(59, 130, 246, 0.25);
    }

    /* Glass Table */
    .glass-table {
        width: 100%;
        border-collapse: separate;
        border-spacing: 0;
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid rgba(59, 130, 246, 0.1);
    }

    .glass-table thead {
        background: linear-gradient(135deg,
            rgba(59, 130, 246, 0.1),
            rgba(30, 64, 175, 0.05)
        );
        backdrop-filter: blur(10px);
    }

    .glass-table th {
        border-bottom: 2px solid rgba(59, 130, 246, 0.15);
        padding: 1rem 1.5rem;
        font-weight: 600;
        color: #1e293b;
        white-space: nowrap;
    }

    .glass-table td {
        padding: 1rem 1.5rem;
        border-bottom: 1px solid rgba(59, 130, 246, 0.08);
        vertical-align: middle;
        color: #475569;
    }

    .glass-table tbody tr {
        transition: all 0.2s ease;
    }

    .glass-table tbody tr:hover {
        background: rgba(59, 130, 246, 0.04);
    }

    /* Status badges */
    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(5px);
    }

    .badge-active {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
    }

    .badge-inactive {
        background: linear-gradient(135deg, #ef4444, #dc2626);
        color: white;
    }

    /* Role badges */
    .role-badge {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 500;
        border: 1px solid rgba(59, 130, 246, 0.2);
        background: rgba(59, 130, 246, 0.1);
        color: #1e40af;
        margin: 2px;
    }

    .role-badge.admin {
        background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        color: white;
        border: none;
    }

    .role-badge.manager {
        background: linear-gradient(135deg, #0ea5e9, #0284c7);
        color: white;
        border: none;
    }

    .role-badge.pic {
        background: linear-gradient(135deg, #f59e0b, #d97706);
        color: white;
        border: none;
    }

    .role-badge.student {
        background: linear-gradient(135deg, #10b981, #059669);
        color: white;
        border: none;
    }

    .role-badge.external {
        background: linear-gradient(135deg, #6b7280, #4b5563);
        color: white;
        border: none;
    }

    /* Action buttons */
    .btn-glass {
        padding: 6px 16px;
        border-radius: 10px;
        border: 1px solid rgba(59, 130, 246, 0.2);
        background: rgba(59, 130, 246, 0.08);
        color: #3b82f6;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-glass:hover {
        background: rgba(59, 130, 246, 0.15);
        border-color: rgba(59, 130, 246, 0.3);
        transform: translateY(-2px);
    }

    .btn-action {
        width: 36px;
        height: 36px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 10px;
        transition: all 0.3s ease;
        border: 1px solid rgba(0, 0, 0, 0.1);
    }

    .btn-action.edit {
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
        border-color: rgba(59, 130, 246, 0.2);
    }

    .btn-action.edit:hover {
        background: rgba(59, 130, 246, 0.2);
        transform: translateY(-2px);
    }

    .btn-action.delete {
        background: rgba(239, 68, 68, 0.1);
        color: #ef4444;
        border-color: rgba(239, 68, 68, 0.2);
    }

    .btn-action.delete:hover {
        background: rgba(239, 68, 68, 0.2);
        transform: translateY(-2px);
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

    /* Search and filter bar */
    .filter-bar {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(10px);
        border: 1px solid rgba(59, 130, 246, 0.15);
        border-radius: 12px;
        padding: 1rem 1.5rem;
        margin-bottom: 1.5rem;
    }

    /* Quick stats */
    .quick-stat {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px 16px;
        background: rgba(241, 245, 249, 0.8);
        border-radius: 12px;
        border: 1px solid rgba(59, 130, 246, 0.15);
        min-width: 140px;
    }

    .quick-stat i {
        font-size: 1.5rem;
        color: #3b82f6;
        opacity: 0.8;
    }

    .quick-stat > div {
        flex: 1;
    }

    /* Empty state */
    .empty-state {
        padding: 3rem 1rem;
        text-align: center;
        color: #64748b;
    }

    .empty-state i {
        font-size: 3rem;
        margin-bottom: 1rem;
        color: #cbd5e1;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .glass-table th,
        .glass-table td {
            padding: 0.75rem 1rem;
        }
        
        .filter-bar {
            padding: 0.75rem 1rem;
        }
        
        .quick-stat {
            min-width: 120px;
        }
    }

    /* Loading skeleton */
    .skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
        border-radius: 4px;
    }

    @keyframes loading {
        0% { background-position: 200% 0; }
        100% { background-position: -200% 0; }
    }

    /* Custom scrollbar for tables */
    .table-responsive {
        border-radius: 12px;
    }

    .table-responsive::-webkit-scrollbar {
        height: 6px;
    }

    .table-responsive::-webkit-scrollbar-track {
        background: rgba(0, 0, 0, 0.05);
        border-radius: 10px;
    }

    .table-responsive::-webkit-scrollbar-thumb {
        background: rgba(59, 130, 246, 0.3);
        border-radius: 10px;
    }
</style>

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
                        <div class="fw-bold"><?= count($users) ?></div>
                    </div>
                </div>
                <div class="quick-stat">
                    <i class="bi bi-person-check"></i>
                    <div>
                        <div class="small text-muted">Active</div>
                        <div class="fw-bold"><?= count(array_filter($users, fn($u) => $u['active'])) ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- FILTER AND ACTION BAR -->
    <div class="filter-bar mb-4">
        <div class="row g-3 align-items-center">
            <div class="col-md-8">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-end-0">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" class="form-control border-start-0" id="userSearch" placeholder="Search users by name, email, or role...">
                    <button class="btn btn-outline-secondary" type="button" id="clearSearch">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            </div>
            <div class="col-md-4 text-md-end">
                <a href="/admin/users/create" class="btn btn-primary">
                    <i class="bi bi-person-plus me-1"></i> Add New User
                </a>
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
                                        <p class="text-muted">Start by adding your first user</p>
                                        <a href="/admin/users/create" class="btn btn-primary mt-2">
                                            <i class="bi bi-person-plus me-1"></i> Add User
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php $i = 1; foreach ($users as $u): ?>
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
                                                <small class="text-muted">ID: <?= $u['id'] ?></small>
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
                    Showing <span class="fw-semibold text-primary"><?= count($users) ?></span> users
                </div>
                <div>
                    <button class="btn btn-outline-primary btn-sm" id="exportUsers">
                        <i class="bi bi-download me-1"></i> Export List
                    </button>
                    <button class="btn btn-outline-secondary btn-sm ms-2" id="refreshUsers">
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
    // Search functionality
    const searchInput = document.getElementById('userSearch');
    const clearSearchBtn = document.getElementById('clearSearch');
    const userRows = document.querySelectorAll('.user-row');
    
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
    
    // Export functionality (basic)
    document.getElementById('exportUsers')?.addEventListener('click', function() {
        alert('Export functionality would generate a CSV file of all users.');
        // In a real implementation, you would make an AJAX call to generate a CSV
        // window.location.href = '/admin/users/export';
    });
    
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