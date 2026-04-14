<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= esc($title ?? 'FKMP Smart Lab') ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Theme -->
    <link href="/css/theme.css" rel="stylesheet">
</head>

<body class="slams-app">

<?= $this->include('components/navbar_user') ?>

<?= $this->include('components/theme_toggle') ?>
<!-- MAIN CONTENT AREA -->
<div class="container py-4 slams-main">
    <?= $this->renderSection('content') ?>
</div>

<?= $this->include('components/footer') ?>
<?= $this->include('components/chatbot') ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>















