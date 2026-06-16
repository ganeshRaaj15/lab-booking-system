<?= $this->extend($layoutView) ?>

<?= $this->section('styles') ?>
<?= view('reports/partials/module_styles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="reports-shell">
    <div class="card reports-hero">
        <div>
            <h2 class="fw-bold text-primary mb-1"><?= esc($pageTitle) ?></h2>
            <p class="text-muted"><?= esc($pageDescription) ?></p>
            <div class="small text-muted mt-2">Scope: <?= esc($scopeLabel) ?></div>
        </div>
        <div class="reports-export-group">
            <a href="<?= esc($exportUrls['pdf']) ?>" class="btn btn-glass btn-sm">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
            </a>
            <a href="<?= esc($exportUrls['csv']) ?>" class="btn btn-glass btn-sm">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
            </a>
        </div>
    </div>

    <?= view('reports/partials/module_nav', ['navItems' => $navItems]) ?>
    <?= view('reports/partials/filter_form', ['filterAction' => $filterAction, 'filterFields' => $filterFields, 'filters' => $filters]) ?>

    <div class="reports-pill-row">
        <?php foreach ($appliedFilters as $filter): ?>
            <span class="reports-pill">
                <span class="reports-pill-label"><?= esc($filter['label']) ?>:</span>
                <span><?= esc($filter['value']) ?></span>
            </span>
        <?php endforeach; ?>
    </div>

    <?= view('reports/partials/summary_cards', ['summaryCards' => $summaryCards]) ?>

    <?php if (! empty($charts)): ?>
        <div class="reports-chart-grid">
            <?php foreach ($charts as $chart): ?>
                <div class="card reports-chart-card">
                    <div class="card-body">
                        <h3><?= esc($chart['title']) ?></h3>
                        <div class="reports-canvas-wrap" style="height: <?= esc((string) ($chart['height'] ?? 300)) ?>px;">
                            <canvas id="<?= esc($chart['id']) ?>"></canvas>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <div class="card reports-table-card">
        <div class="card-body">
            <h3><?= esc($pageTitle) ?> Data</h3>
            <?php if ($rows === []): ?>
                <div class="reports-empty"><?= esc($emptyMessage) ?></div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover reports-mini-table">
                        <thead>
                            <tr>
                                <?php foreach ($columns as $column): ?>
                                    <th><?= esc($column['label']) ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $row): ?>
                                <tr>
                                    <?php foreach ($columns as $column): ?>
                                        <td><?= esc((string) ($row[$column['key']] ?? '-')) ?></td>
                                    <?php endforeach; ?>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?= view('reports/partials/chart_scripts', ['charts' => $charts]) ?>
<?= $this->endSection() ?>
