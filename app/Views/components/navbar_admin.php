<?php
$navUser = isset($user) && $user ? $user : ((function_exists('auth') && auth()->loggedIn()) ? auth()->user() : null);
$isPicWorkspace = $navUser && $navUser->inGroup('pic') && ! $navUser->inGroup('admin');
$dashboardLabel = $isPicWorkspace ? 'PIC Workspace' : 'Admin Dashboard';
?>

<nav class="admin-glass-navbar">
    <div class="navbar-content px-3">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar"><i class="bi bi-list"></i></button>
            <div class="navbar-title">
                <h4><?= esc($page ?? 'Dashboard') ?></h4>
                <div class="breadcrumb-nav">
                    <span class="breadcrumb-item"><i class="bi bi-house-door"></i> <?= esc($dashboardLabel) ?></span>
                    <?php if (isset($page) && $page !== 'Dashboard'): ?>
                        <span class="breadcrumb-divider"><i class="bi bi-chevron-right"></i></span>
                        <span class="breadcrumb-item active"><?= esc($page) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="navbar-actions">
            <?= $this->include('components/navbar_app_controls', [
                'appControlsShowProfileLink' => false,
                'appControlsShowDesktopLogout' => false,
            ]) ?>
            <?php if ($navUser): ?>
                <a href="/dashboard/profile" class="user-profile-glass">
                    <div class="user-avatar"><i class="bi bi-person-circle"></i></div>
                    <div class="user-info">
                        <div class="user-name"><?= esc($navUser->full_name ?? $navUser->username ?? 'User') ?></div>
                        <div class="user-role"><?= esc($isPicWorkspace ? 'PIC' : ($navUser->role ?? 'User')) ?></div>
                    </div>
                </a>
            <?php endif; ?>

            <form action="/logout" method="post">
                <?= csrf_field() ?>
                <button class="btn-logout-glass" type="submit"><i class="bi bi-box-arrow-right"></i> Logout</button>
            </form>
        </div>
    </div>
</nav>
