<?php
use App\Models\NotificationModel;

$navUser = isset($user) && $user ? $user : ((function_exists('auth') && auth()->loggedIn()) ? auth()->user() : null);
$isPicWorkspace = $navUser && $navUser->inGroup('pic') && ! $navUser->inGroup('admin');
$dashboardLabel = $isPicWorkspace ? 'PIC Workspace' : 'Admin Dashboard';

$navNotificationItems = [];
$navUnreadCount = 0;
if (function_exists('auth') && auth()->loggedIn()) {
    $navUser = auth()->user();
    $notificationModel = new NotificationModel();
    $navUnreadCount = $notificationModel->where('user_id', $navUser->id)->where('is_read', 0)->countAllResults();
    $navNotificationItems = $notificationModel->where('user_id', $navUser->id)->orderBy('created_at', 'DESC')->findAll(5);
}
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
            <?= $this->include('components/navbar_app_controls') ?>
            <?php if ($navUser): ?>
                <div class="dropdown">
                    <a href="#" class="notification-trigger" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Notifications">
                        <i class="bi bi-bell"></i>
                        <?php if ($navUnreadCount > 0): ?><span id="admin-nav-notif-bubble" class="notification-badge"><?= esc($navUnreadCount > 99 ? '99+' : (string) $navUnreadCount) ?></span><?php else: ?><span id="admin-nav-notif-bubble" class="notification-badge" style="display:none">0</span><?php endif; ?>
                    </a>
                    <div class="dropdown-menu dropdown-menu-end notification-menu p-0">
                        <div class="dropdown-header d-flex justify-content-between align-items-center">
                            <div>
                                <div class="fw-semibold text-dark">Notifications</div>
                                <div class="small text-muted"><span id="admin-nav-notif-count"><?= esc((int) $navUnreadCount) ?></span> unread</div>
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

<?php if (function_exists('auth') && auth()->loggedIn()): ?>
<script>
(function () {
    const bubble = document.getElementById('admin-nav-notif-bubble');
    const countEl = document.getElementById('admin-nav-notif-count');
    if (!bubble || !countEl) return;

    function updateBadge(n) {
        const label = n > 99 ? '99+' : String(n);
        bubble.textContent = label;
        countEl.textContent = String(n);
        bubble.style.display = n > 0 ? '' : 'none';
    }

    function poll() {
        fetch('/dashboard/notifications/count', { credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) { if (data && typeof data.unread === 'number') updateBadge(data.unread); })
            .catch(function () {});
    }

    window.slamsRefreshNotificationBadge = poll;
    window.addEventListener('slams:notifications-refresh', poll);
    setInterval(poll, 30000);
}());
</script>
<?php endif; ?>
