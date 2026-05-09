<?php if (! empty($summaryCards)): ?>
    <div class="reports-summary-grid">
        <?php foreach ($summaryCards as $card): ?>
            <div class="reports-summary-card reports-tone-<?= esc($card['tone'] ?? 'primary') ?>">
                <small><?= esc($card['label'] ?? 'Metric') ?></small>
                <div class="reports-summary-value"><?= esc((string) ($card['value'] ?? 0)) ?></div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
