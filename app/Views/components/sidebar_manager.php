<?php
$sidebarUser = function_exists('auth') && auth()->loggedIn() ? auth()->user() : null;
?>

<aside class="glass-sidebar d-flex flex-column">
    <div class="text-center mb-3 mt-2">
        <div class="sidebar-logo">
            <img src="<?= slams_asset('icons/slams-icon.png') ?>" alt="SLAMS" class="sidebar-logo-img">
        </div>
        <div class="fw-bold" style="color: var(--slams-nav-text); font-family: var(--slams-font-display);">Lab Manager</div>
        <small style="color: var(--slams-nav-muted); font-size: 0.72rem;">SLAMS | FKMP</small>
    </div>

    <hr class="sidebar-divider">

    <span class="sidebar-section-label">Core</span>

    <a href="/dashboard/manager" class="sidebar-link <?= url_is('dashboard/manager') ? 'active' : '' ?>">
        <i class="bi bi-briefcase"></i> Manager Dashboard
    </a>

    <span class="sidebar-section-label">Operations</span>

    <a href="/dashboard/external-requests" class="sidebar-link <?= url_is('dashboard/external-requests*') ? 'active' : '' ?>">
        <i class="bi bi-clipboard-data"></i> External Requests
    </a>

    <a href="/dashboard/manager/maintenance" class="sidebar-link <?= url_is('dashboard/manager/maintenance*') ? 'active' : '' ?>">
        <i class="bi bi-wrench-adjustable"></i> Maintenance
    </a>

    <a href="/dashboard/reports/analytics" class="sidebar-link <?= url_is('dashboard/reports/analytics*') || url_is('dashboard/reports/pdf*') || url_is('dashboard/reports/csv*') ? 'active' : '' ?>">
        <i class="bi bi-bar-chart-line"></i> System Analytics
    </a>
</aside>
