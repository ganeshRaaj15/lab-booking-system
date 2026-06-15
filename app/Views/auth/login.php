<?php if (auth()->loggedIn()): ?>
    <?php header('Location: ' . site_url('/dashboard')); exit; ?>
<?php endif; ?>

<?= $this->extend('layouts/main_user'); ?>

<?= $this->section('styles'); ?>
<link href="<?= slams_asset('css/auth.css') ?>" rel="stylesheet">
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<?= view('auth/_auth_shell', [
    'activeMode' => 'login',
    'loginError' => session('error'),
    'loginErrors' => (array) (session('errors') ?? []),
    'loginSuccess' => session('success'),
    'registerErrors' => [],
]); ?>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script src="<?= slams_asset('js/auth-ui.js') ?>"></script>
<?= $this->endSection(); ?>
