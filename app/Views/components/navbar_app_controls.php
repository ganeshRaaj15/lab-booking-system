<?php
use App\Models\NotificationModel;

helper(['auth', 'url']);

$appControlsShowNotificationCenter = $appControlsShowNotificationCenter ?? true;
$appControlsShowProfileLink = $appControlsShowProfileLink ?? true;
$appControlsShowDesktopLogout = $appControlsShowDesktopLogout ?? true;

$appControlProfileHref = null;
$appControlProfilePhoto = '';
$appControlProfileAlt = 'Open profile';
$appControlNotificationItems = [];
$appControlUnreadCount = 0;
$appControlUser = function_exists('auth') && auth()->loggedIn() ? auth()->user() : null;
$pushClientConfig = ['configured' => false, 'publicKey' => ''];

if ($appControlUser) {
    $appControlProfileAlt = trim((string) ($appControlUser->full_name ?? $appControlUser->username ?? 'User'));
    $pushClientConfig = (new \App\Libraries\WebPushConfiguration())->clientConfig();

    if ($appControlsShowNotificationCenter) {
        $notificationModel = new NotificationModel();
        $appControlUnreadCount = $notificationModel->where('user_id', $appControlUser->id)->where('is_read', 0)->countAllResults();
        $appControlNotificationItems = $notificationModel->where('user_id', $appControlUser->id)->orderBy('created_at', 'DESC')->findAll(5);
    }

    if ($appControlsShowProfileLink && ! ($appControlUser->inGroup('pic') || $appControlUser->inGroup('manager'))) {
        $appControlProfileHref = '/dashboard/profile';
        $profilePhoto = trim((string) ($appControlUser->profile_photo ?? ''));
        if ($profilePhoto !== '') {
            $appControlProfilePhoto = base_url(ltrim($profilePhoto, '/'));
        }
    }
}
?>

<div class="slams-navbar-app-actions" aria-label="App controls">
    <?php if ($appControlUser && $appControlsShowNotificationCenter): ?>
        <div class="dropdown">
            <button
                type="button"
                class="slams-navbar-app-btn notification-trigger slams-navbar-notification-trigger"
                data-bs-toggle="dropdown"
                aria-expanded="false"
                aria-label="Notifications"
                title="Notifications"
            >
                <i class="bi bi-bell"></i>
                <?php if ($appControlUnreadCount > 0): ?>
                    <span class="notification-badge" data-nav-notif-bubble><?= esc($appControlUnreadCount > 99 ? '99+' : (string) $appControlUnreadCount) ?></span>
                <?php else: ?>
                    <span class="notification-badge" data-nav-notif-bubble style="display:none">0</span>
                <?php endif; ?>
            </button>

            <div class="dropdown-menu dropdown-menu-end notification-menu p-0">
                <div class="dropdown-header d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fw-semibold text-dark">Notifications</div>
                        <div class="small text-muted"><span data-nav-notif-count><?= esc((int) $appControlUnreadCount) ?></span> unread</div>
                    </div>
                    <a href="/dashboard/notifications" class="small text-decoration-none">View all</a>
                </div>

                <?php if (empty($appControlNotificationItems)): ?>
                    <div class="px-3 py-4 text-center text-muted small">No notifications yet.</div>
                <?php else: ?>
                    <?php foreach ($appControlNotificationItems as $item): ?>
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

                <?php if (! empty($pushClientConfig['configured']) && ! empty($pushClientConfig['publicKey'])): ?>
                    <div class="notification-menu-device border-top px-3 py-3">
                        <div class="d-flex align-items-center justify-content-between gap-3">
                            <div class="small">
                                <div class="fw-semibold text-dark mb-1">Push alerts</div>
                                <div class="text-muted" data-push-status-copy>Off for this device.</div>
                            </div>

                            <button
                                type="button"
                                id="pushToggleBtn"
                                class="slams-navbar-app-btn notification-device-toggle"
                                data-push-configured="1"
                                data-push-public-key="<?= esc($pushClientConfig['publicKey']) ?>"
                                aria-pressed="false"
                                title="Enable push notifications"
                            >
                                <i class="bi bi-bell" data-push-icon></i>
                                <span data-push-label>Enable</span>
                            </button>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?php if ($appControlProfileHref !== null): ?>
        <a
            href="<?= esc($appControlProfileHref) ?>"
            class="slams-navbar-app-btn slams-navbar-profile-btn d-lg-none <?= url_is('dashboard/profile*') ? 'is-active' : '' ?>"
            aria-label="Open profile"
            title="Profile"
        >
            <?php if ($appControlProfilePhoto !== ''): ?>
                <img
                    src="<?= esc($appControlProfilePhoto) ?>"
                    alt="<?= esc($appControlProfileAlt) ?>"
                    class="slams-navbar-profile-avatar"
                >
            <?php else: ?>
                <span class="slams-navbar-profile-fallback" aria-hidden="true">
                    <i class="bi bi-person"></i>
                </span>
            <?php endif; ?>
        </a>
    <?php endif; ?>

    <?php if ($appControlUser && $appControlsShowDesktopLogout): ?>
        <form action="/logout" method="post" class="d-none d-lg-inline-flex align-items-center">
            <?= csrf_field() ?>
            <button class="btn btn-glass btn-sm" type="submit">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </button>
        </form>
    <?php endif; ?>
</div>

<?php if ($appControlUser && $appControlsShowNotificationCenter): ?>
<script>
(function () {
    function updateBadge(n) {
        const label = n > 99 ? '99+' : String(n);
        document.querySelectorAll('[data-nav-notif-bubble]').forEach(function (bubble) {
            bubble.textContent = label;
            bubble.style.display = n > 0 ? '' : 'none';
        });

        document.querySelectorAll('[data-nav-notif-count]').forEach(function (countEl) {
            countEl.textContent = String(n);
        });
    }

    function poll() {
        fetch('/dashboard/notifications/count', { credentials: 'same-origin' })
            .then(function (r) { return r.ok ? r.json() : null; })
            .then(function (data) { if (data && typeof data.unread === 'number') updateBadge(data.unread); })
            .catch(function () {});
    }

    window.slamsRefreshNotificationBadge = poll;
    window.addEventListener('slams:notifications-refresh', poll);
    window.addEventListener('focus', poll);
    document.addEventListener('visibilitychange', function () {
        if (document.visibilityState === 'visible') {
            poll();
        }
    });
    setInterval(poll, 8000);
}());
</script>
<?php endif; ?>
