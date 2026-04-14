<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? 'Dashboard | FKMP Smart Lab') ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@400;500;600;700&family=DM+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Theme -->
    <link href="/css/theme.css" rel="stylesheet">

    <style>
        html, body {
            margin: 0;
            padding: 0;
            overflow-x: hidden;
        }

        body.sidebar-open {
            overflow: hidden;
        }

        :root {
            --admin-navbar-height: 72px;
            --admin-sidebar-width: 260px;
        }

        /* MAIN LAYOUT */
        .admin-layout {
            padding-left: var(--admin-sidebar-width);
            min-height: 100vh;
            background: transparent;
        }

        /* MAIN CONTENT */
        .content-area {
            padding-top: calc(var(--admin-navbar-height) + 20px);
            padding-left: 24px;
            padding-right: 24px;
            padding-bottom: 24px;
            min-height: 100vh;
        }

        .sidebar-overlay {
            position: fixed;
            inset: 0;
            background: rgba(15, 23, 42, 0.45);
            z-index: 990;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.2s ease, visibility 0.2s ease;
        }

        body.sidebar-open .sidebar-overlay {
            opacity: 1;
            visibility: visible;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .admin-layout {
                padding-left: 0;
            }
            .content-area {
                padding-top: calc(var(--admin-navbar-height) + 16px);
            }
        }
    </style>
</head>

<body class="slams-app">

<?= $this->include('components/sidebar_admin') ?>


<?= \->include('components/theme_toggle') ?>\n<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="admin-layout">
    <?= $this->include('components/navbar_admin') ?>
<div class="content-area">
        <?= $this->renderSection('content') ?>
    </div>
</div>

<?= $this->include('components/footer') ?>
<?= $this->include('components/chatbot') ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const body = document.body;
    const overlay = document.getElementById('sidebarOverlay');
    const toggle = document.getElementById('sidebarToggle');
    const mq = window.matchMedia('(min-width: 992px)');

    if (toggle) {
        toggle.addEventListener('click', function() {
            body.classList.toggle('sidebar-open');
        });
    }

    if (overlay) {
        overlay.addEventListener('click', function() {
            body.classList.remove('sidebar-open');
        });
    }

    const syncSidebarState = function() {
        if (mq.matches) {
            body.classList.remove('sidebar-open');
        }
    };

    syncSidebarState();
    mq.addEventListener('change', syncSidebarState);
});
</script>
</body>
</html>


