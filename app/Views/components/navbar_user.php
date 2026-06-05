<?php
use App\Models\NotificationModel;

$userNavNotificationItems = [];
$userNavUnreadCount = 0;
if (function_exists('auth') && auth()->loggedIn()) {
    $userNavCurrentUser = auth()->user();
    $notificationModel = new NotificationModel();
    $userNavUnreadCount = $notificationModel->where('user_id', $userNavCurrentUser->id)->where('is_read', 0)->countAllResults();
    $userNavNotificationItems = $notificationModel->where('user_id', $userNavCurrentUser->id)->orderBy('created_at', 'DESC')->findAll(5);
}
?>

<nav class="navbar navbar-expand-lg glass-navbar py-2">
    <div class="container">
        <a class="navbar-brand fw-bold" href="/">
            <div class="d-flex align-items-center gap-2">
                <img src="<?= slams_asset('images/logo.png') ?>" alt="SLAMS" class="brand-logo-img">
                <span class="brand-suffix small">| FKMP</span>
            </div>
        </a>

        <div class="slams-navbar-app-shell">
            <?= $this->include('components/navbar_app_controls') ?>
        </div>

        <button class="navbar-toggler border-0 ms-2" type="button" data-bs-toggle="collapse" data-bs-target="#userNavbar" aria-controls="userNavbar" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="userNavbar">
            <ul class="navbar-nav ms-auto align-items-lg-center">
                <li class="nav-item me-2"><a class="nav-link position-relative" href="/"><i class="bi bi-house-door me-1"></i> Home<span class="nav-indicator"></span></a></li>
                <li class="nav-item me-2"><a class="nav-link position-relative" href="/laboratories"><i class="bi bi-building me-1"></i> Laboratories<span class="nav-indicator"></span></a></li>
                <li class="nav-item me-2"><a class="nav-link position-relative" href="/assets"><i class="bi bi-box-seam me-1"></i> Assets<span class="nav-indicator"></span></a></li>
                <li class="nav-item me-2"><a class="nav-link position-relative" href="/contact"><i class="bi bi-envelope me-1"></i> Contact<span class="nav-indicator"></span></a></li>

                <?php if (auth()->loggedIn()): ?>
                    <?php $user = auth()->user(); ?>
                    <?php if ($user->inGroup('external')): ?>
                        <li class="nav-item me-2"><a class="nav-link position-relative" href="/dashboard/external"><i class="bi bi-clipboard-check me-1"></i> Requests<span class="nav-indicator"></span></a></li>
                    <?php elseif ($user->inGroup('pic') || $user->inGroup('manager')): ?>
                        <li class="nav-item me-2"><a class="nav-link position-relative" href="/dashboard/external-requests"><i class="bi bi-clipboard-data me-1"></i> External Requests<span class="nav-indicator"></span></a></li>
                    <?php endif; ?>
                    <?php if ($user->inGroup('pic')): ?>
                        <li class="nav-item me-2 d-none d-lg-block"><a class="nav-link position-relative" href="/technician/maintenance"><i class="bi bi-wrench me-1"></i> Maintenance<span class="nav-indicator"></span></a></li>
                    <?php endif; ?>
                    <li class="nav-item me-2 dropdown notification-nav-item">
                        <a class="nav-link position-relative notification-nav-link" href="#" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="bi bi-bell me-1"></i> Notifications
                            <?php if ($userNavUnreadCount > 0): ?><span id="nav-notif-bubble" class="notification-bubble"><?= esc($userNavUnreadCount > 99 ? '99+' : (string) $userNavUnreadCount) ?></span><?php else: ?><span id="nav-notif-bubble" class="notification-bubble" style="display:none">0</span><?php endif; ?>
                            <span class="nav-indicator"></span>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end notification-menu p-0">
                            <div class="dropdown-header d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold text-dark">Notifications</div>
                                    <div class="small text-muted"><span id="nav-notif-count"><?= esc((int) $userNavUnreadCount) ?></span> unread</div>
                                </div>
                                <a href="/dashboard/notifications" class="small text-decoration-none">View all</a>
                            </div>
                            <?php if (empty($userNavNotificationItems)): ?>
                                <div class="px-3 py-4 text-center text-muted small">No notifications yet.</div>
                            <?php else: ?>
                                <?php foreach ($userNavNotificationItems as $item): ?>
                                    <a href="/dashboard/notifications" class="notification-item text-decoration-none">
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
                    </li>

                    <?php if (!($user->inGroup('pic') || $user->inGroup('manager'))): ?>
                        <?php $navProfilePhoto = trim((string) ($user->profile_photo ?? '')); ?>
                        <li class="nav-item me-2 d-none d-lg-flex align-items-center">
                            <a href="/dashboard/profile" class="slams-navbar-app-btn slams-navbar-profile-btn <?= url_is('dashboard/profile*') ? 'is-active' : '' ?>" aria-label="Profile" title="Profile">
                                <?php if ($navProfilePhoto !== ''): ?>
                                    <img src="<?= esc(base_url(ltrim($navProfilePhoto, '/'))) ?>" alt="Profile" class="slams-navbar-profile-avatar">
                                <?php else: ?>
                                    <span class="slams-navbar-profile-fallback" aria-hidden="true"><i class="bi bi-person"></i></span>
                                <?php endif; ?>
                            </a>
                        </li>
                    <?php endif; ?>
                    <li class="nav-item ms-lg-2">
                        <form action="/logout" method="post" class="d-inline"><?= csrf_field() ?><button class="btn btn-glass btn-sm"><i class="bi bi-box-arrow-right me-1"></i> Logout</button></form>
                    </li>
                <?php else: ?>
                    <li class="nav-item ms-lg-2"><a class="btn btn-glow btn-sm" href="/login"><i class="bi bi-box-arrow-in-right me-1"></i> Login</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<?php if (function_exists('auth') && auth()->loggedIn()): ?>
<script>
(function () {
    const bubble = document.getElementById('nav-notif-bubble');
    const countEl = document.getElementById('nav-notif-count');
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

    setInterval(poll, 30000);
}());
</script>
<?php endif; ?>
