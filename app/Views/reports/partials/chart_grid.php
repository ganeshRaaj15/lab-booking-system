<?php if (($charts ?? []) !== []): ?>
    <div class="reports-chart-grid">
        <?php foreach (($charts ?? []) as $chart): ?>
            <div class="card reports-chart-card">
                <div class="card-body">
                    <h3><?= esc($chart['title'] ?? 'Chart') ?></h3>
                    <div class="reports-canvas-wrap" style="height: <?= esc((string) ($chart['height'] ?? 300)) ?>px;">
                        <canvas id="<?= esc($chart['id'] ?? 'chart') ?>"></canvas>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
