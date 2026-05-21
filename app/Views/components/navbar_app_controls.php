<?php
helper(['auth', 'url']);

$appControlProfileHref = null;
$appControlProfilePhoto = '';
$appControlProfileAlt = 'Open profile';
$appControlUser = function_exists('auth') && auth()->loggedIn() ? auth()->user() : null;

if ($appControlUser) {
    $appControlProfileAlt = trim((string) ($appControlUser->full_name ?? $appControlUser->username ?? 'User'));

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
</div>
