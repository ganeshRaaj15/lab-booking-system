<?php helper('url'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'SLAMS | FKMP UTHM') ?></title>

    <script src="<?= base_url('js/theme.js') ?>"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <link href="<?= base_url('css/theme.css') ?>" rel="stylesheet">
    <?= $this->renderSection('styles') ?>
</head>

<body class="slams-app slams-layout-user">
    <?= $this->include('components/navbar_user') ?>
    <?= $this->include('components/theme_toggle') ?>

    <main class="container py-4 slams-main">
        <?= $this->renderSection('content') ?>
    </main>

    <?= $this->include('components/footer') ?>
    <?= $this->include('components/chatbot') ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
