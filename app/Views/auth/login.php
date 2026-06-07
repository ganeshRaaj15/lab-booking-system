<?php if (auth()->loggedIn()): ?>
    <?php header('Location: ' . site_url('/dashboard')); exit; ?>
<?php endif; ?>

<?php $title = 'SLAMS | Sign in'; ?>

<?= $this->extend('layouts/main_user'); ?>
<?= $this->section('content'); ?>

<?= view('auth/partials/auth_shell', ['authMode' => 'login']); ?>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script src="<?= slams_asset('js/auth-shell.js') ?>"></script>
<?= $this->endSection(); ?>
