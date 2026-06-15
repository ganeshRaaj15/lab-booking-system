<?php
use App\Models\NotificationModel;

$userNavNotificationItems = [];
$userNavUnreadCount = 0;
$isLoggedIn = function_exists('auth') && auth()->loggedIn();
$user = $isLoggedIn ? auth()->user() : null;
$currentPath = trim((string) service('request')->getUri()->getPath(), '/');
$pathMatches = static function (array $patterns) use ($currentPath): bool {
    foreach ($patterns as $pattern) {
        $normalizedPattern = trim((string) $pattern, '/');

        if ($normalizedPattern === '') {
            if ($currentPath === '') {
                return true;
            }

            continue;
        }

        $regex = '#^' . str_replace('\*', '.*', preg_quote($normalizedPattern, '#')) . '$#i';
        if (preg_match($regex, $currentPath) === 1) {
            return true;
        }
    }

    return false;
};

$navItems = [
    [
        'label'    => 'Home',
        'href'     => '/',
        'icon'     => 'bi-house-door',
        'patterns' => [''],
    ],
    [
        'label'    => 'Laboratories',
        'href'     => '/laboratories',
        'icon'     => 'bi-building',
        'patterns' => ['laboratories*'],
    ],
    [
        'label'    => 'Assets',
        'href'     => '/assets',
        'icon'     => 'bi-box-seam',
        'patterns' => ['assets*'],
    ],
    [
        'label'    => 'Contact',
        'href'     => '/contact',
        'icon'     => 'bi-envelope',
        'patterns' => ['contact*'],
    ],
];

if ($isLoggedIn) {
    if ($user->inGroup('external')) {
        $navItems[] = [
            'label'    => 'Requests',
            'href'     => '/dashboard/external',
            'icon'     => 'bi-clipboard-check',
            'patterns' => ['dashboard/external*'],
        ];
    } elseif ($user->inGroup('pic') || $user->inGroup('manager')) {
        $navItems[] = [
            'label'    => 'External Requests',
            'href'     => '/dashboard/external-requests',
            'icon'     => 'bi-clipboard-data',
            'patterns' => ['dashboard/external-requests*', 'external-requests*'],
        ];
    }

    if ($user->inGroup('pic') || $user->inGroup('manager') || $user->inGroup('admin')) {
        $reportLabel = $user->inGroup('pic')
            ? 'Lab Report'
            : ($user->inGroup('manager') ? 'Lab Analytics' : 'System Analytics');

        $navItems[] = [
            'label'    => $reportLabel,
            'href'     => '/dashboard/reports/analytics',
            'icon'     => 'bi-bar-chart-line',
            'patterns' => ['dashboard/reports/analytics*'],
        ];
    }

    if ($user->inGroup('pic')) {
        $navItems[] = [
            'label'    => 'Labs',
            'href'     => '/admin/labs',
            'icon'     => 'bi-building-gear',
            'patterns' => ['admin/labs*', 'dashboard/pic/lab/*'],
        ];
        $navItems[] = [
            'label'    => 'Assets',
            'href'     => '/admin/assets',
            'icon'     => 'bi-boxes',
            'patterns' => ['admin/assets*', 'dashboard/pic/assets*', 'pic/assets*'],
        ];
        $navItems[] = [
            'label'    => 'Services',
            'href'     => '/admin/services',
            'icon'     => 'bi-diagram-3',
            'patterns' => ['admin/services*'],
        ];
        $navItems[] = [
            'label'    => 'Maintenance',
            'href'     => '/technician/maintenance',
            'icon'     => 'bi-wrench',
            'patterns' => ['technician/maintenance*'],
        ];
        $navItems[] = [
            'label'    => 'Reservations',
            'href'     => '/pic/reservations',
            'icon'     => 'bi-calendar-check',
            'patterns' => ['pic/reservations*'],
        ];
    }
}

$navItems = array_map(
    static function (array $item) use ($pathMatches): array {
        $item['active'] = $pathMatches($item['patterns'] ?? []);
        return $item;
    },
    $navItems
);

$maxVisibleNavItems = 5;
$primaryNavItems = array_slice($navItems, 0, $maxVisibleNavItems);
$overflowNavItems = array_slice($navItems, $maxVisibleNavItems);
$hasOverflowNav = ! empty($overflowNavItems);
$hasActiveOverflowNav = count(array_filter($overflowNavItems, static fn(array $item): bool => ! empty($item['active']))) > 0;
$desktopMoreCollapseId = 'desktopNavbarMoreLinks';
$navProfilePhoto = $isLoggedIn ? trim((string) ($user->profile_photo ?? '')) : '';

$renderNavLink = static function (array $item, string $itemClass = 'nav-item me-2', string $linkClass = 'nav-link position-relative'): void {
    ?>
    <li class="<?= esc($itemClass) ?>">
        <a class="<?= esc($linkClass . (! empty($item['active']) ? ' active' : '')) ?>" href="<?= esc($item['href']) ?>">
            <i class="bi <?= esc($item['icon']) ?> me-1"></i> <?= esc($item['label']) ?><span class="nav-indicator"></span>
        </a>
    </li>
    <?php
};

$renderNotificationNav = static function (bool $isActive = false, string $itemClass = 'nav-item me-2 dropdown notification-nav-item') use (&$userNavUnreadCount, &$userNavNotificationItems): void {
    ?>
    <li class="<?= esc($itemClass) ?>">
        <a class="nav-link position-relative notification-nav-link<?= $isActive ? ' active' : '' ?>" href="#" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-bell me-1"></i> Notifications
            <?php if ($userNavUnreadCount > 0): ?>
                <span class="notification-bubble" data-nav-notif-bubble><?= esc($userNavUnreadCount > 99 ? '99+' : (string) $userNavUnreadCount) ?></span>
            <?php else: ?>
                <span class="notification-bubble" data-nav-notif-bubble style="display:none">0</span>
            <?php endif; ?>
            <span class="nav-indicator"></span>
        </a>
        <div class="dropdown-menu dropdown-menu-end notification-menu p-0">
            <div class="dropdown-header d-flex justify-content-between align-items-center">
                <div>
                    <div class="fw-semibold text-dark">Notifications</div>
                    <div class="small text-muted"><span data-nav-notif-count><?= esc((int) $userNavUnreadCount) ?></span> unread</div>
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
    <?php
};

if (function_exists('auth') && auth()->loggedIn()) {
    $notificationModel = new NotificationModel();
    $userNavUnreadCount = $notificationModel->where('user_id', $user->id)->where('is_read', 0)->countAllResults();
    $userNavNotificationItems = $notificationModel->where('user_id', $user->id)->orderBy('created_at', 'DESC')->findAll(5);
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
            <div class="d-lg-none">
                <ul class="navbar-nav ms-auto">
                    <?php foreach ($navItems as $item): ?>
                        <?php $renderNavLink($item); ?>
                    <?php endforeach; ?>

                    <?php if ($isLoggedIn): ?>
                        <?php $renderNotificationNav($pathMatches(['dashboard/notifications*'])); ?>

                        <?php if (!($user->inGroup('pic') || $user->inGroup('manager'))): ?>
                            <li class="nav-item me-2">
                                <a href="/dashboard/profile" class="nav-link position-relative<?= $pathMatches(['dashboard/profile*']) ? ' active' : '' ?>">
                                    <i class="bi bi-person me-1"></i> Profile<span class="nav-indicator"></span>
                                </a>
                            </li>
                        <?php endif; ?>

                        <li class="nav-item mt-2">
                            <form action="/logout" method="post" class="d-inline"><?= csrf_field() ?><button class="btn btn-glass btn-sm"><i class="bi bi-box-arrow-right me-1"></i> Logout</button></form>
                        </li>
                    <?php else: ?>
                        <li class="nav-item mt-2"><a class="btn btn-glow btn-sm" href="/login"><i class="bi bi-box-arrow-in-right me-1"></i> Login</a></li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="d-none d-lg-block w-100">
                <?php if ($hasOverflowNav): ?>
                    <div class="slams-tiered-navbar">
                        <div class="slams-tiered-navbar__top">
                            <ul class="navbar-nav slams-tiered-navbar__primary">
                                <?php foreach ($primaryNavItems as $item): ?>
                                    <?php $renderNavLink($item); ?>
                                <?php endforeach; ?>

                                <li class="nav-item me-2">
                                    <button
                                        class="nav-link position-relative slams-tiered-navbar__more-toggle<?= $hasActiveOverflowNav ? ' active' : '' ?>"
                                        type="button"
                                        data-bs-toggle="collapse"
                                        data-bs-target="#<?= esc($desktopMoreCollapseId) ?>"
                                        aria-expanded="<?= $hasActiveOverflowNav ? 'true' : 'false' ?>"
                                        aria-controls="<?= esc($desktopMoreCollapseId) ?>"
                                    >
                                        <i class="bi bi-three-dots me-1"></i> More
                                        <i class="bi bi-chevron-down slams-tiered-navbar__more-caret"></i>
                                    </button>
                                </li>
                            </ul>

                            <ul class="navbar-nav slams-tiered-navbar__utilities">
                                <?php if ($isLoggedIn): ?>
                                    <?php $renderNotificationNav($pathMatches(['dashboard/notifications*']), 'nav-item me-2 dropdown notification-nav-item'); ?>

                                    <?php if (!($user->inGroup('pic') || $user->inGroup('manager'))): ?>
                                        <li class="nav-item me-2 d-flex align-items-center">
                                            <a href="/dashboard/profile" class="slams-navbar-app-btn slams-navbar-profile-btn <?= $pathMatches(['dashboard/profile*']) ? 'is-active' : '' ?>" aria-label="Profile" title="Profile">
                                                <?php if ($navProfilePhoto !== ''): ?>
                                                    <img src="<?= esc(base_url(ltrim($navProfilePhoto, '/'))) ?>" alt="Profile" class="slams-navbar-profile-avatar">
                                                <?php else: ?>
                                                    <span class="slams-navbar-profile-fallback" aria-hidden="true"><i class="bi bi-person"></i></span>
                                                <?php endif; ?>
                                            </a>
                                        </li>
                                    <?php endif; ?>

                                <?php else: ?>
                                    <li class="nav-item ms-lg-2"><a class="btn btn-glow btn-sm" href="/login"><i class="bi bi-box-arrow-in-right me-1"></i> Login</a></li>
                                <?php endif; ?>
                            </ul>
                        </div>

                        <div class="collapse slams-tiered-navbar__secondary<?= $hasActiveOverflowNav ? ' show' : '' ?>" id="<?= esc($desktopMoreCollapseId) ?>">
                            <ul class="navbar-nav slams-tiered-navbar__secondary-list">
                                <?php foreach ($overflowNavItems as $item): ?>
                                    <?php $renderNavLink($item, 'nav-item me-2 mb-2 mb-xl-0'); ?>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>
                <?php else: ?>
                    <ul class="navbar-nav ms-auto align-items-lg-center">
                        <?php foreach ($navItems as $item): ?>
                            <?php $renderNavLink($item); ?>
                        <?php endforeach; ?>

                        <?php if ($isLoggedIn): ?>
                            <?php $renderNotificationNav($pathMatches(['dashboard/notifications*']), 'nav-item me-2 dropdown notification-nav-item'); ?>

                            <?php if (!($user->inGroup('pic') || $user->inGroup('manager'))): ?>
                                <li class="nav-item me-2 d-none d-lg-flex align-items-center">
                                    <a href="/dashboard/profile" class="slams-navbar-app-btn slams-navbar-profile-btn <?= $pathMatches(['dashboard/profile*']) ? 'is-active' : '' ?>" aria-label="Profile" title="Profile">
                                        <?php if ($navProfilePhoto !== ''): ?>
                                            <img src="<?= esc(base_url(ltrim($navProfilePhoto, '/'))) ?>" alt="Profile" class="slams-navbar-profile-avatar">
                                        <?php else: ?>
                                            <span class="slams-navbar-profile-fallback" aria-hidden="true"><i class="bi bi-person"></i></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                            <?php endif; ?>

                        <?php else: ?>
                            <li class="nav-item ms-lg-2"><a class="btn btn-glow btn-sm" href="/login"><i class="bi bi-box-arrow-in-right me-1"></i> Login</a></li>
                        <?php endif; ?>
                    </ul>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<?php if ($isLoggedIn): ?>
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
    setInterval(poll, 30000);
}());
</script>
<?php endif; ?>
