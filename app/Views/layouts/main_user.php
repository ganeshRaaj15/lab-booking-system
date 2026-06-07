<?php
helper(['url', 'asset', 'auth']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#0f766e">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-title" content="SLAMS">
    <title><?= esc($title ?? 'SLAMS | FKMP UTHM') ?></title>

    <script src="<?= slams_asset('js/theme.js') ?>"></script>
    <link rel="manifest" href="<?= slams_asset('manifest.webmanifest') ?>">
    <link rel="icon" href="<?= slams_asset('icons/slams-icon.png') ?>" type="image/png">
    <?= csrf_meta('slams-csrf-meta') ?>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= slams_asset('css/theme.css') ?>" rel="stylesheet">
    <link href="<?= slams_asset('css/mobile-app.css') ?>" rel="stylesheet">
    <?= $this->renderSection('styles') ?>
</head>

<body class="slams-app slams-mobile-app slams-layout-user<?= isset($bodyClass) && $bodyClass !== '' ? ' ' . esc($bodyClass, 'attr') : '' ?>">
    <?= $this->include('components/navbar_user') ?>

    <main class="<?= esc($mainClass ?? 'container py-4 slams-main', 'attr') ?>">
        <?= $this->renderSection('content') ?>
    </main>

    <?php if (! ($hideFooter ?? false)): ?>
        <?= $this->include('components/footer') ?>
    <?php endif; ?>
    <?= $this->include('components/theme_toggle') ?>
    <?php if (! ($hideChatbot ?? false)): ?>
        <?= $this->include('components/chatbot') ?>
    <?php endif; ?>
    <?php if (! ($hideMobileQuickActions ?? false)): ?>
        <?= $this->include('components/mobile_quick_actions') ?>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= slams_asset('js/mobile-app.js') ?>"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
