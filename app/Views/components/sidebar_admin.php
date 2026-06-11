<?php
$sidebarUser = function_exists('auth') && auth()->loggedIn() ? auth()->user() : null;
$isPicWorkspace = $sidebarUser && $sidebarUser->inGroup('pic') && ! $sidebarUser->inGroup('admin');
$dashboardUrl = $isPicWorkspace ? '/dashboard/pic' : '/dashboard/admin';
$dashboardLabel = $isPicWorkspace ? 'PIC Panel' : 'Admin Panel';
$workspaceTitle = $isPicWorkspace ? 'PIC Workspace' : 'Admin Dashboard';
$reservationUrl = $isPicWorkspace ? '/pic/reservations' : '/admin/reservations';
$maintenanceUrl = $isPicWorkspace ? '/technician/maintenance' : '/dashboard/admin/maintenance';
?>

<aside class="glass-sidebar d-flex flex-column">
    <div class="text-center mb-4 mt-3">
        <div class="sidebar-logo">
            <img src="<?= slams_asset('icons/slams-icon.png') ?>" alt="SLAMS" class="sidebar-logo-img">
        </div>
        <div class="fw-bold text-white"><?= esc($workspaceTitle) ?></div>
        <small class="text-light opacity-75">SLAMS | FKMP</small>
    </div>

    <hr class="sidebar-divider">

    <a href="<?= esc($dashboardUrl) ?>" class="sidebar-link <?= url_is('dashboard/admin') || url_is('dashboard/pic') ? 'active' : '' ?>">
        <i class="bi bi-briefcase"></i> <?= esc($dashboardLabel) ?>
    </a>

    <a href="/admin/labs" class="sidebar-link <?= url_is('admin/labs*') ? 'active' : '' ?>">
        <i class="bi bi-building"></i> Manage Labs
    </a>

    <a href="/admin/assets" class="sidebar-link <?= url_is('admin/assets*') ? 'active' : '' ?>">
        <i class="bi bi-box-seam"></i> Manage Assets
    </a>

    <a href="/admin/services" class="sidebar-link <?= url_is('admin/services*') ? 'active' : '' ?>">
        <i class="bi bi-diagram-3"></i> Manage Services
    </a>

    <a href="/dashboard/external-requests" class="sidebar-link <?= url_is('dashboard/external-requests*') ? 'active' : '' ?>">
        <i class="bi bi-clipboard-data"></i> External Requests
    </a>

    <a href="<?= esc($reservationUrl) ?>" class="sidebar-link <?= url_is('admin/reservations*') || url_is('pic/reservations*') ? 'active' : '' ?>">
        <i class="bi bi-calendar-check"></i> Lab Reservations
    </a>

    <a href="<?= esc($maintenanceUrl) ?>" class="sidebar-link <?= url_is('technician/maintenance*') || url_is('dashboard/admin/maintenance*') ? 'active' : '' ?>">
        <i class="bi bi-wrench-adjustable"></i> Maintenance
    </a>

    <?php if (! $isPicWorkspace): ?>
        <a href="/admin/users" class="sidebar-link <?= url_is('admin/users*') ? 'active' : '' ?>">
            <i class="bi bi-people"></i> User Management
        </a>

        <a href="/admin/settings" class="sidebar-link <?= url_is('admin/settings') ? 'active' : '' ?>">
            <i class="bi bi-gear"></i> System Settings
        </a>

        <a href="/admin/contact-settings" class="sidebar-link <?= url_is('admin/contact-settings*') ? 'active' : '' ?>">
            <i class="bi bi-telephone"></i> Contact Page
        </a>
    <?php endif; ?>
</aside>
