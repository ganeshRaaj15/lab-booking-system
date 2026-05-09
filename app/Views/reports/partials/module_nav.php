<div class="card reports-nav">
    <?php foreach ($navItems as $item): ?>
        <a href="<?= esc($item['href']) ?>" class="reports-nav-link<?= !empty($item['active']) ? ' active' : '' ?>">
            <?= esc($item['label']) ?>
        </a>
    <?php endforeach; ?>
</div>
