<?php $sectionGroups = $report['sectionGroups'] ?? []; ?>

<?= view('reports/partials/summary_cards', ['summaryCards' => $report['summaryCards'] ?? []]) ?>
<?= view('reports/partials/chart_grid', ['charts' => $report['charts'] ?? []]) ?>

<div class="reports-role-layout-grid">
    <div class="card reports-table-card">
        <div class="card-body">
            <h3>System Scope Snapshot</h3>
            <div class="reports-callout-grid">
                <div class="reports-callout-box">
                    <div class="reports-callout-label">Laboratories</div>
                    <div class="reports-callout-value"><?= esc((string) ($report['kpis']['total_labs'] ?? 0)) ?></div>
                </div>
                <div class="reports-callout-box">
                    <div class="reports-callout-label">Assets</div>
                    <div class="reports-callout-value"><?= esc((string) ($report['kpis']['total_assets'] ?? 0)) ?></div>
                </div>
                <div class="reports-callout-box">
                    <div class="reports-callout-label">Users</div>
                    <div class="reports-callout-value"><?= esc((string) ($report['kpis']['users'] ?? 0)) ?></div>
                </div>
                <div class="reports-callout-box">
                    <div class="reports-callout-label">Notifications</div>
                    <div class="reports-callout-value"><?= esc((string) ($report['kpis']['notifications_total'] ?? 0)) ?></div>
                </div>
            </div>
        </div>
    </div>

    <div class="card reports-table-card">
        <div class="card-body">
            <h3>High-Level Governance Snapshot</h3>
            <div class="table-responsive">
                <table class="table table-striped table-hover reports-mini-table">
                    <thead>
                        <tr>
                            <th>Laboratory</th>
                            <th>Bookings</th>
                            <th>Utilization (%)</th>
                            <th>Peak Usage Day</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (($report['labUtilization'] ?? []) === []): ?>
                            <tr><td colspan="4" class="text-center text-muted">No governance comparison data is available.</td></tr>
                        <?php else: ?>
                            <?php foreach (array_slice($report['labUtilization'] ?? [], 0, 8) as $lab): ?>
                                <tr>
                                    <td><?= esc($lab['laboratory_name'] ?? '-') ?></td>
                                    <td><?= esc((string) ($lab['total_bookings'] ?? 0)) ?></td>
                                    <td><?= esc((string) ($lab['usage_percentage'] ?? 0)) ?></td>
                                    <td><?= esc((string) ($lab['peak_usage_day'] ?? 'N/A')) ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?= view('reports/partials/section_tables', ['sectionGroups' => $sectionGroups]) ?>
