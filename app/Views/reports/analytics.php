<?= $this->extend($layoutView) ?>

<?= $this->section('styles') ?>
<?= view('reports/partials/module_styles') ?>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="rpt-shell">

    <!-- Page header -->
    <div class="slams-page-header">
        <div class="slams-page-header-left">
            <div class="d-flex align-items-center gap-2 mb-1 flex-wrap">
                <span class="badge rounded-pill text-bg-primary" style="font-size:0.74rem;font-weight:700;letter-spacing:0.04em">
                    <?= esc($report['roleDisplay'] ?? 'Report') ?>
                </span>
                <span class="text-muted" style="font-size:0.8rem"><?= esc($report['scopeLabel'] ?? '') ?></span>
            </div>
            <h1 class="slams-page-title"><?= esc($pageTitle) ?></h1>
            <p class="slams-page-subtitle mb-0"><?= esc($pageDescription) ?></p>
            <div class="text-muted mt-1" style="font-size:0.77rem">
                <i class="bi bi-clock me-1"></i>Generated: <?= esc($report['generatedAtDisplay'] ?? ($report['generatedAt'] ?? '')) ?>
            </div>
        </div>
        <div class="slams-page-header-actions">
            <button class="btn btn-glass btn-sm" type="button" id="rptFilterToggle" aria-expanded="false">
                <i class="bi bi-funnel me-1"></i> Filters
                <?php if (! empty($report['appliedFilters'])): ?>
                    <span class="badge rounded-pill bg-primary ms-1"><?= esc((string) count($report['appliedFilters'])) ?></span>
                <?php endif; ?>
            </button>
            <a href="<?= esc($summaryExportUrls['pdf']) ?>" class="btn btn-glass btn-sm">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export PDF
            </a>
            <a href="<?= esc($summaryExportUrls['csv']) ?>" class="btn btn-glass btn-sm">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export CSV
            </a>
        </div>
    </div>

    <!-- Collapsible filter panel -->
    <div class="rpt-filter-collapse" id="rptFilterPanel">
        <div>
            <?= view('reports/partials/filter_form', [
                'filterAction' => $filterAction,
                'filterFields' => $filterFields,
                'filters'      => $report['filters'] ?? [],
            ]) ?>
        </div>
    </div>

    <!-- Active filter pills -->
    <?php if (! empty($report['appliedFilters'])): ?>
        <div class="rpt-pill-row">
            <span class="text-muted fw-semibold" style="font-size:0.8rem">Active filters:</span>
            <?php foreach ($report['appliedFilters'] as $filter): ?>
                <span class="rpt-pill">
                    <span class="rpt-pill-label"><?= esc($filter['label'] ?? '') ?>:</span>
                    <span><?= esc($filter['value'] ?? '') ?></span>
                </span>
            <?php endforeach; ?>
            <a href="<?= esc($filterAction) ?>" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size:0.8rem">
                <i class="bi bi-x me-1"></i>Clear
            </a>
        </div>
    <?php endif; ?>

    <!-- Role-specific content -->
    <?= view('reports/roles/web_' . ($report['role'] ?? 'pic'), ['report' => $report]) ?>

    <!-- Data scope notes -->
    <?php if (! empty($report['limitations'])): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body py-3">
                <h6 class="fw-bold mb-2" style="font-size:0.83rem">
                    <i class="bi bi-info-circle me-1 text-muted"></i>Data Scope Notes
                </h6>
                <ul class="mb-0 ps-3" style="font-size:0.82rem;color:var(--slams-muted)">
                    <?php foreach ($report['limitations'] as $item): ?>
                        <li><?= esc($item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

</div>

<script>
(function () {
    const toggle = document.getElementById('rptFilterToggle');
    const panel  = document.getElementById('rptFilterPanel');
    if (!toggle || !panel) return;
    toggle.addEventListener('click', function () {
        const open = panel.classList.toggle('rpt-filter-open');
        this.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
})();
</script>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?= view('reports/partials/chart_scripts', ['charts' => $report['charts'] ?? []]) ?>
<?= $this->endSection() ?>
