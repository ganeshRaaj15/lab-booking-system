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
            <div class="small text-muted mt-2">Scope: <?= esc($report['scopeLabel'] ?? '') ?></div>
            <div class="small text-muted">Generated: <?= esc($report['generatedAtDisplay'] ?? ($report['generatedAt'] ?? '')) ?></div>
        </div>
        <div class="reports-export-group">
            <a href="<?= esc($summaryExportUrls['pdf']) ?>" class="btn btn-outline-primary">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
            </a>
            <a href="<?= esc($summaryExportUrls['csv']) ?>" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
            </a>
        </div>
    </div>

    <?php if (! empty($report['uiProfile'])): ?>
        <div class="card reports-role-card">
            <div class="card-body">
                <div class="reports-role-grid">
                    <div>
                        <div class="reports-role-kicker"><?= esc($report['roleDisplay'] ?? 'Report Role') ?></div>
                        <h3><?= esc($report['uiProfile']['headline'] ?? '') ?></h3>
                        <p class="text-muted mb-0"><?= esc($report['uiProfile']['subheadline'] ?? '') ?></p>
                    </div>
                    <div class="reports-focus-list">
                        <?php foreach (($report['uiProfile']['focusAreas'] ?? []) as $area): ?>
                            <span class="reports-pill"><?= esc($area) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>

                <?php if (($report['uiProfile']['highlights'] ?? []) !== []): ?>
                    <div class="reports-role-highlight-grid">
                        <?php foreach (($report['uiProfile']['highlights'] ?? []) as $item): ?>
                            <div class="reports-summary-card reports-tone-<?= esc($item['tone'] ?? 'primary') ?>">
                                <small><?= esc($item['label'] ?? 'Highlight') ?></small>
                                <div class="reports-summary-value"><?= esc((string) ($item['value'] ?? '0')) ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <?php if (($report['uiProfile']['webCallout'] ?? []) !== []): ?>
                    <div class="reports-callout-grid">
                        <?php foreach (($report['uiProfile']['webCallout'] ?? []) as $callout): ?>
                            <div class="reports-callout-box">
                                <div class="reports-callout-label"><?= esc($callout['label'] ?? 'Reference') ?></div>
                                <div class="reports-callout-value"><?= esc($callout['value'] ?? '-') ?></div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

    <?= view('reports/partials/filter_form', [
        'filterAction' => $filterAction,
        'filterFields' => $filterFields,
        'filters' => $report['filters'] ?? [],
    ]) ?>

    <?php if (($report['appliedFilters'] ?? []) !== []): ?>
        <div class="reports-pill-row">
            <?php foreach (($report['appliedFilters'] ?? []) as $filter): ?>
                <span class="reports-pill">
                    <span class="reports-pill-label"><?= esc($filter['label'] ?? 'Filter') ?>:</span>
                    <span><?= esc($filter['value'] ?? '') ?></span>
                </span>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <?= view('reports/roles/web_' . ($report['role'] ?? 'pic'), ['report' => $report]) ?>

    <?php if (($report['limitations'] ?? []) !== []): ?>
        <div class="card reports-limitations-card">
            <div class="card-body">
                <h3>Data Scope Notes</h3>
                <ul class="mb-0">
                    <?php foreach (($report['limitations'] ?? []) as $item): ?>
                        <li><?= esc($item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?= view('reports/partials/chart_scripts', ['charts' => $report['charts'] ?? []]) ?>
<?= $this->endSection() ?>
