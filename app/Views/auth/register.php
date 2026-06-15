<?php if (auth()->loggedIn()): ?>
    <?php header('Location: ' . site_url('/dashboard')); exit; ?>
<?php endif; ?>

<?= $this->extend('layouts/main_user'); ?>

<?= $this->section('styles'); ?>
<link href="<?= slams_asset('css/auth.css') ?>" rel="stylesheet">
<?= $this->endSection(); ?>

<?= $this->section('content'); ?>
<?= view('auth/_auth_shell', [
    'activeMode' => 'register',
    'loginError' => null,
    'loginErrors' => [],
    'loginSuccess' => null,
    'registerErrors' => (array) (session('errors') ?? []),
]); ?>
<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script src="<?= slams_asset('js/auth-ui.js') ?>"></script>
<?= $this->endSection(); ?>
