<?php
/**
 * Reusable breadcrumb partial.
 *
 * Usage:
 *   <?= view('components/breadcrumb', [
 *       'breadcrumbs' => [
 *           ['label' => 'Dashboard', 'url' => '/dashboard/admin'],
 *           ['label' => 'Users',     'url' => '/admin/users'],
 *           ['label' => 'Edit User'],   // last item — no url, rendered as active
 *       ],
 *   ]) ?>
 */
?>
<?php if (!empty($breadcrumbs)): ?>
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb breadcrumb-sm">
        <?php foreach ($breadcrumbs as $i => $crumb): ?>
            <?php $isLast = $i === count($breadcrumbs) - 1; ?>
            <li class="breadcrumb-item <?= $isLast ? 'active' : '' ?>">
                <?php if (!$isLast && !empty($crumb['url'])): ?>
                    <a href="<?= esc($crumb['url']) ?>"><?= esc($crumb['label']) ?></a>
                <?php else: ?>
                    <?= esc($crumb['label']) ?>
                <?php endif; ?>
            </li>
        <?php endforeach; ?>
    </ol>
</nav>
<?php endif; ?>
