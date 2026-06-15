<?php
helper(['auth', 'url']);

$appControlProfileHref = null;
$appControlProfilePhoto = '';
$appControlProfileAlt = 'Open profile';
$appControlUser = function_exists('auth') && auth()->loggedIn() ? auth()->user() : null;
$pushClientConfig = ['configured' => false, 'publicKey' => ''];

if ($appControlUser) {
    $appControlProfileAlt = trim((string) ($appControlUser->full_name ?? $appControlUser->username ?? 'User'));
    $pushClientConfig = (new \App\Libraries\WebPushConfiguration())->clientConfig();

    if (! ($appControlUser->inGroup('pic') || $appControlUser->inGroup('manager'))) {
        $appControlProfileHref = '/dashboard/profile';
        $profilePhoto = trim((string) ($appControlUser->profile_photo ?? ''));
        if ($profilePhoto !== '') {
            $appControlProfilePhoto = base_url(ltrim($profilePhoto, '/'));
        }
    }
}
?>

<div class="slams-navbar-app-actions" aria-label="App controls">
    <?php if ($appControlUser && ! empty($pushClientConfig['configured']) && ! empty($pushClientConfig['publicKey'])): ?>
        <button
            type="button"
            id="pushToggleBtn"
            class="slams-navbar-app-btn"
            data-push-configured="1"
            data-push-public-key="<?= esc($pushClientConfig['publicKey']) ?>"
            aria-pressed="false"
            title="Enable push notifications"
        >
            <i class="bi bi-bell" data-push-icon></i>
        </button>
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

    <?php if ($appControlUser): ?>
        <form action="/logout" method="post" class="d-none d-lg-inline-flex align-items-center">
            <?= csrf_field() ?>
            <button class="btn btn-glass btn-sm" type="submit">
                <i class="bi bi-box-arrow-right me-1"></i> Logout
            </button>
        </form>
    <?php endif; ?>
</div>
