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
            <a href="<?= esc($summaryExportUrls['pdf']) ?>" class="btn btn-outline-primary">
                <i class="bi bi-file-earmark-pdf me-1"></i> Export Summary PDF
            </a>
            <a href="<?= esc($summaryExportUrls['csv']) ?>" class="btn btn-outline-success">
                <i class="bi bi-file-earmark-spreadsheet me-1"></i> Export Summary CSV
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

    <div class="reports-chart-grid">
        <div class="card reports-table-card">
            <div class="card-body">
                <h3>Most Used Laboratories</h3>
                <div class="table-responsive">
                    <table class="table table-hover reports-mini-table">
                        <thead>
                            <tr>
                                <th>Laboratory</th>
                                <th>Bookings</th>
                                <th>Used Hours</th>
                                <th>Usage %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($mostUsedLabs === []): ?>
                                <tr><td colspan="4" class="text-center text-muted">No usage data available.</td></tr>
                            <?php else: ?>
                                <?php foreach ($mostUsedLabs as $lab): ?>
                                    <tr>
                                        <td><?= esc($lab['laboratory_name']) ?></td>
                                        <td><?= esc((string) $lab['total_bookings']) ?></td>
                                        <td><?= esc(number_format((float) $lab['total_used_hours'], 1)) ?></td>
                                        <td><?= esc(number_format((float) $lab['usage_percentage'], 1)) ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card reports-table-card">
            <div class="card-body">
                <h3>Least Used Laboratories</h3>
                <div class="table-responsive">
                    <table class="table table-hover reports-mini-table">
                        <thead>
                            <tr>
                                <th>Laboratory</th>
                                <th>Bookings</th>
                                <th>Used Hours</th>
                                <th>Usage %</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($leastUsedLabs === []): ?>
                                <tr><td colspan="4" class="text-center text-muted">No usage data available.</td></tr>
                            <?php else: ?>
                                <?php foreach ($leastUsedLabs as $lab): ?>
                                    <tr>
                                        <td><?= esc($lab['laboratory_name']) ?></td>
                                        <td><?= esc((string) $lab['total_bookings']) ?></td>
                                        <td><?= esc(number_format((float) $lab['total_used_hours'], 1)) ?></td>
                                        <td><?= esc(number_format((float) $lab['usage_percentage'], 1)) ?>%</td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="reports-chart-grid">
        <div class="card reports-table-card">
            <div class="card-body">
                <h3>Most Frequently Maintained Assets</h3>
                <div class="table-responsive">
                    <table class="table table-hover reports-mini-table">
                        <thead>
                            <tr>
                                <th>Asset</th>
                                <th>Total Cases</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($frequentMaintenanceAssets === []): ?>
                                <tr><td colspan="2" class="text-center text-muted">No maintenance hotspots found.</td></tr>
                            <?php else: ?>
                                <?php foreach ($frequentMaintenanceAssets as $asset): ?>
                                    <tr>
                                        <td><?= esc($asset['asset_name'] ?? 'Unknown Asset') ?></td>
                                        <td><?= esc((string) ($asset['total'] ?? 0)) ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card reports-table-card">
            <div class="card-body">
                <h3>Recent Maintenance Activities</h3>
                <?php if ($recentMaintenance === []): ?>
                    <div class="reports-empty"><?= esc($emptyMaintenanceMessage) ?></div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover reports-mini-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Title</th>
                                    <th>Asset</th>
                                    <th>Laboratory</th>
                                    <th>Status</th>
                                    <th>Priority</th>
                                    <th>Assigned To</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentMaintenance as $activity): ?>
                                    <tr>
                                        <td><?= esc((string) $activity['id']) ?></td>
                                        <td><?= esc($activity['title'] ?? '-') ?></td>
                                        <td><?= esc($activity['asset_name'] ?? '-') ?></td>
                                        <td><?= esc($activity['laboratory_name'] ?? '-') ?></td>
                                        <td><?= esc(ucwords(str_replace('_', ' ', (string) ($activity['status'] ?? 'unknown')))) ?></td>
                                        <td><?= esc(ucwords((string) ($activity['priority'] ?? 'medium'))) ?></td>
                                        <td><?= esc($activity['technician_name'] ?? 'Unassigned') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<?= view('reports/partials/chart_scripts', ['charts' => $charts]) ?>
<?= $this->endSection() ?>
