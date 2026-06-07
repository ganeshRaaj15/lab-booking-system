<?php $title = 'SLAMS | Create your account'; ?>

<?= $this->extend('layouts/main_user'); ?>
<?= $this->section('content'); ?>

<?= view('auth/partials/auth_shell', ['authMode' => 'register']); ?>

<?= $this->endSection(); ?>

<?= $this->section('scripts'); ?>
<script src="<?= slams_asset('js/auth-shell.js') ?>"></script>
<?= $this->endSection(); ?>
