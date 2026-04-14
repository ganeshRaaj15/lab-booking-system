<!-- =======================
     ADMIN SIDEBAR
======================= -->

<style>
/* ============================================================
   GLASS SIDEBAR
   ============================================================ */
.glass-sidebar {
    position: fixed;
    top: 0;
    left: 0;

    width: var(--admin-sidebar-width);
    height: 100vh;

    padding: 24px 18px;
    z-index: 1000;

    background: linear-gradient(135deg,
        rgba(15, 23, 42, 0.95),
        rgba(30, 41, 59, 0.95)
    );

    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);

    border-right: 1px solid rgba(59, 130, 246, 0.25);
    box-shadow: 6px 0 25px rgba(0, 0, 0, 0.35);
    transition: transform 0.2s ease;
}

/* Branding */
.sidebar-logo {
    width: 52px;
    height: 52px;
    margin: 0 auto 8px;

    background: linear-gradient(135deg,
        rgba(59, 130, 246, 0.3),
        rgba(30, 64, 175, 0.4)
    );

    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;

    box-shadow: 0 6px 20px rgba(59, 130, 246, 0.35);
}

.sidebar-divider {
    border-color: rgba(255, 255, 255, 0.15);
    margin: 18px 0;
}

/* Links */
.sidebar-link {
    display: flex;
    align-items: center;
    gap: 10px;

    padding: 12px 14px;
    margin-bottom: 8px;

    color: rgba(255, 255, 255, 0.85);
    text-decoration: none;
    font-weight: 500;
    border-radius: 10px;

    transition: all 0.3s ease;
    position: relative;
}

.sidebar-link:hover {
    color: white;
    background: linear-gradient(135deg,
        rgba(59, 130, 246, 0.25),
        rgba(30, 64, 175, 0.35)
    );
    transform: translateX(6px);
}

.sidebar-link.active {
    background: linear-gradient(135deg,
        rgba(59, 130, 246, 0.35),
        rgba(30, 64, 175, 0.45)
    );
    font-weight: 600;
}

.sidebar-link.active::before {
    content: '';
    position: absolute;
    left: -18px;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 70%;

    background: linear-gradient(180deg, #60a5fa, #3b82f6);
    border-radius: 4px;
}

.sidebar-link i {
    font-size: 1.1rem;
}

/* Responsive */
@media (max-width: 992px) {
    .glass-sidebar {
        width: 80vw;
        max-width: 280px;
        height: 100vh;
        transform: translateX(-100%);
    }
}

body.sidebar-open .glass-sidebar {
    transform: translateX(0);
}
</style>

<div class="glass-sidebar d-flex flex-column">

    <div class="text-center mb-4 mt-3">
        <div class="sidebar-logo">
            <i class="bi bi-speedometer2 text-white fs-4"></i>
        </div>
        <div class="fw-bold text-white">Admin Dashboard</div>
        <small class="text-light opacity-75">SLAMS | FKMP</small>
    </div>

    <hr class="sidebar-divider">

    <a href="/dashboard/admin" class="sidebar-link <?= url_is('dashboard/admin') ? 'active' : '' ?>">
        <i class="bi bi-briefcase"></i> Admin Panel
    </a>

    <a href="/admin/labs" class="sidebar-link <?= url_is('admin/labs*') ? 'active' : '' ?>">
        <i class="bi bi-building"></i> Manage Labs
    </a>

    <a href="/admin/assets" class="sidebar-link <?= url_is('admin/assets*') ? 'active' : '' ?>">
        <i class="bi bi-box-seam"></i> Manage Assets
    </a>

    <a href="/admin/users" class="sidebar-link <?= url_is('admin/users*') ? 'active' : '' ?>">
        <i class="bi bi-people"></i> User Management
    </a>

    <a href="/admin/settings" class="sidebar-link <?= url_is('admin/settings') ? 'active' : '' ?>">
        <i class="bi bi-gear"></i> System Settings
    </a>

</div>
