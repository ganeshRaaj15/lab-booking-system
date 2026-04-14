<!-- =======================
     ADMIN NAVBAR
======================= -->
<?php
use App\Models\NotificationModel;

$navNotificationItems = [];
$navUnreadCount = 0;
if (function_exists('auth') && auth()->loggedIn()) {
    $navUser = auth()->user();
    $notificationModel = new NotificationModel();
    $navUnreadCount = $notificationModel->where('user_id', $navUser->id)->where('is_read', 0)->countAllResults();
    $navNotificationItems = $notificationModel->where('user_id', $navUser->id)->orderBy('created_at', 'DESC')->findAll(5);
}
?>
<style>
:root {
    --admin-navbar-height: 72px;
    --admin-sidebar-width: 260px;
}
.admin-glass-navbar {
    position: fixed;
    top: 0;
    left: var(--admin-sidebar-width);
    right: 0;
    height: var(--admin-navbar-height);
    z-index: 980;
    background: linear-gradient(135deg, rgba(15, 23, 42, 0.92), rgba(30, 41, 59, 0.92));
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border-bottom: 1px solid rgba(59, 130, 246, 0.25);
    box-shadow: 0 4px 25px rgba(0, 0, 0, 0.35);
}
.navbar-content { height: 100%; display: flex; align-items: center; justify-content: space-between; }
.navbar-title h4 { margin: 0; font-size: 1.4rem; font-weight: 700; color: white; }
.breadcrumb-nav { display: flex; align-items: center; gap: 6px; margin-top: 2px; }
.breadcrumb-item { font-size: 0.85rem; color: rgba(255, 255, 255, 0.7); display: flex; align-items: center; gap: 6px; }
.breadcrumb-item.active { color: #60a5fa; font-weight: 500; }
.breadcrumb-divider { color: rgba(255, 255, 255, 0.35); }
.navbar-actions { display: flex; align-items: center; gap: 14px; }
.sidebar-toggle { width: 40px; height: 40px; display: none; align-items: center; justify-content: center; border-radius: 10px; border: 1px solid rgba(255, 255, 255, 0.2); background: rgba(255, 255, 255, 0.08); color: white; transition: all 0.2s ease; }
.sidebar-toggle:hover { background: rgba(255, 255, 255, 0.18); }
.notification-trigger {
    position: relative; display: inline-flex; align-items: center; justify-content: center; width: 42px; height: 42px;
    border-radius: 12px; color: white; text-decoration: none;
    background: linear-gradient(135deg, rgba(255,255,255,0.08), rgba(255,255,255,0.04));
    border: 1px solid rgba(255,255,255,0.15); transition: all 0.3s ease;
}
.notification-trigger:hover { transform: translateY(-2px); color: white; box-shadow: 0 6px 20px rgba(59, 130, 246, 0.25); }
.notification-badge {
    position: absolute; top: -6px; right: -5px; min-width: 20px; height: 20px; padding: 0 6px;
    border-radius: 999px; background: #ef4444; color: white; font-size: 0.7rem; font-weight: 700;
    display: inline-flex; align-items: center; justify-content: center; border: 2px solid rgba(15, 23, 42, 0.95);
}
.notification-menu {
    width: 340px; border: 0; border-radius: 16px; overflow: hidden;
    box-shadow: 0 20px 50px rgba(15, 23, 42, 0.24);
}
.notification-menu .dropdown-header { background: #f8fafc; padding: 14px 16px; }
.notification-item { display: block; padding: 12px 16px; text-decoration: none; color: inherit; border-bottom: 1px solid #eef2f7; }
.notification-item:hover { background: #f8fafc; }
.notification-item:last-child { border-bottom: 0; }
.user-profile-glass { display: flex; align-items: center; gap: 12px; padding: 6px 14px; border-radius: 14px; background: linear-gradient(135deg, rgba(255,255,255,0.08), rgba(255,255,255,0.04)); border: 1px solid rgba(255,255,255,0.15); transition: all 0.3s ease; }
.user-profile-glass:hover { transform: translateY(-2px); box-shadow: 0 6px 20px rgba(59, 130, 246, 0.25); }
.user-avatar { width: 36px; height: 36px; border-radius: 50%; overflow: hidden; background: linear-gradient(135deg, rgba(59,130,246,0.3), rgba(30,64,175,0.4)); display: flex; align-items: center; justify-content: center; }
.user-avatar i { font-size: 1.3rem; color: white; }
.user-info { display: flex; flex-direction: column; line-height: 1.1; }
.user-name { font-size: 0.9rem; font-weight: 600; color: white; }
.user-role { font-size: 0.75rem; color: rgba(255, 255, 255, 0.6); }
.btn-logout-glass { padding: 8px 18px; border-radius: 12px; background: linear-gradient(135deg, rgba(239,68,68,0.15), rgba(220,38,38,0.2)); color: #fecaca; border: 1px solid rgba(248,113,113,0.35); font-weight: 600; display: flex; align-items: center; gap: 6px; transition: all 0.3s ease; }
.btn-logout-glass:hover { background: linear-gradient(135deg, rgba(248,113,113,0.35), rgba(239,68,68,0.45)); color: white; transform: translateY(-2px); }
@media (max-width: 992px) {
    .admin-glass-navbar { position: sticky; left: 0; margin-left: 0; }
    .sidebar-toggle { display: inline-flex; }
    .user-info { display: none; }
}
@media (max-width: 768px) {
    .breadcrumb-nav { display: none; }
    .navbar-title h4 { font-size: 1.2rem; }
    .notification-menu { width: min(92vw, 340px); }
}
</style>

<nav class="admin-glass-navbar">
    <div class="navbar-content px-4">
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle sidebar"><i class="bi bi-list"></i></button>
            <div class="navbar-title">
                <h4><?= esc($page ?? 'Dashboard') ?></h4>
                <div class="breadcrumb-nav">
                    <span class="breadcrumb-item"><i class="bi bi-house-door"></i> Dashboard</span>
                    <?php if (isset($page) && $page !== 'Dashboard'): ?>
                        <span class="breadcrumb-divider"><i class="bi bi-chevron-right"></i></span>
                        <span class="breadcrumb-item active"><?= esc($page) ?></span>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="navbar-actions">
            <?php if (isset($user) && $user): ?>
                <div class="dropdown">
                    <a href="#" class="notification-trigger" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                        <i class="bi bi-bell"></i>
                        <?php if ($navUnreadCount > 0): ?><span class="notification-badge"><?= esc($navUnreadCount > 99 ? '99+' : (string) $navUnreadCount) ?></span><?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end notification-menu p-0">
                        <div class="dropdown-header d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold text-dark">Notifications</div>
                                <div class="small text-muted"><?= esc((int) $navUnreadCount) ?> unread</div>
                            </div>
                            <a href="/dashboard/notifications" class="small text-decoration-none">View all</a>
                        </div>
                        <?php if (empty($navNotificationItems)): ?>
                            <div class="px-3 py-4 text-center text-muted small">No notifications yet.</div>
                        <?php else: ?>
                            <?php foreach ($navNotificationItems as $item): ?>
                                <a href="/dashboard/notifications" class="notification-item">
                                    <div class="d-flex align-items-start gap-2">
                                        <span class="badge <?= (int) ($item['is_read'] ?? 0) === 0 ? 'bg-primary' : 'bg-secondary' ?> mt-1"><?= (int) ($item['is_read'] ?? 0) === 0 ? 'New' : 'Read' ?></span>
                                        <div class="flex-grow-1">
                                            <div class="fw-semibold small text-dark"><?= esc($item['title'] ?? 'Notification') ?></div>
                                            <div class="small text-muted"><?= esc($item['message'] ?? '') ?></div>
                                        </div>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>

                <a href="/dashboard/profile" class="user-profile-glass text-decoration-none">
                    <div class="user-avatar"><i class="bi bi-person-circle"></i></div>
                    <div class="user-info">
                        <div class="user-name"><?= esc($user->full_name ?? $user->username ?? 'User') ?></div>
                        <div class="user-role"><?= esc($user->role ?? 'Administrator') ?></div>
                    </div>
                </a>
            <?php endif; ?>

            <form action="/logout" method="post">
                <?= csrf_field() ?>
                <button class="btn-logout-glass"><i class="bi bi-box-arrow-right"></i> Logout</button>
            </form>
        </div>
    </div>
</nav>
