<?php
/**
 * Reusable empty state partial.
 *
 * Usage:
 *   <?= view('components/empty_state', [
 *       'icon'      => 'bi-inbox',        // Bootstrap Icons class (without 'bi ')
 *       'title'     => 'Nothing here yet',
 *       'message'   => 'Descriptive text.',
 *       'cta_label' => 'Get started',     // optional
 *       'cta_href'  => '/some/path',      // optional
 *   ]) ?>
 */
?>
<div class="slams-empty-state">
    <div class="slams-empty-state-icon">
        <i class="bi <?= esc($icon ?? 'bi-inbox') ?>"></i>
    </div>
    <h6 class="slams-empty-state-title"><?= esc($title ?? 'Nothing here yet') ?></h6>
    <?php if (!empty($message)): ?>
        <p class="slams-empty-state-message"><?= esc($message) ?></p>
    <?php endif; ?>
    <?php if (!empty($cta_label) && !empty($cta_href)): ?>
        <a href="<?= esc($cta_href) ?>" class="btn btn-sm btn-primary"><?= esc($cta_label) ?></a>
    <?php endif; ?>
</div>
