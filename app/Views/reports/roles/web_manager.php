<?php
$sectionGroups = $report['sectionGroups'] ?? [];
$managerGroups = array_values(array_filter($sectionGroups, static fn(array $group): bool => in_array($group['id'] ?? '', ['laboratory', 'booking', 'maintenance', 'asset', 'notification'], true)));
?>

<?= view('reports/partials/summary_cards', ['summaryCards' => $report['summaryCards'] ?? []]) ?>
<?= view('reports/partials/chart_grid', ['charts' => $report['charts'] ?? []]) ?>

<div class="reports-role-layout-grid">
    <div class="card reports-table-card">
        <div class="card-body">
            <h3>Cross-Laboratory Comparison Snapshot</h3>
            <div class="table-responsive">
                <table class="table table-striped table-hover reports-mini-table">
                    <thead>
                        <tr>
                            <th>Laboratory</th>
                            <th>Bookings</th>
                            <th>Used Hours</th>
                            <th>Utilization (%)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (($report['labUtilization'] ?? []) === []): ?>
                            <tr><td colspan="4" class="text-center text-muted">No laboratory comparison data is available.</td></tr>
                        <?php else: ?>
                            <?php foreach (array_slice($report['labUtilization'] ?? [], 0, 8) as $lab): ?>
                                <tr>
                                    <td><?= esc($lab['laboratory_name'] ?? '-') ?></td>
                                    <td><?= esc((string) ($lab['total_bookings'] ?? 0)) ?></td>
                                    <td><?= esc((string) ($lab['total_used_hours'] ?? 0)) ?></td>
                                    <td><?= esc((string) ($lab['usage_percentage'] ?? 0)) ?></td>
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
            <h3>Most Frequently Maintained Assets</h3>
            <?php if (($report['topMaintenanceAssets'] ?? []) === []): ?>
                <div class="reports-empty">No maintenance hotspot data is available.</div>
            <?php else: ?>
                <div class="reports-item-stack">
                    <?php foreach (array_slice($report['topMaintenanceAssets'] ?? [], 0, 6) as $asset): ?>
                        <div class="reports-callout-box">
                            <div class="reports-callout-value"><?= esc($asset['asset_name'] ?? '-') ?></div>
                            <div class="text-muted small"><?= esc((string) ($asset['total'] ?? 0)) ?> maintenance case(s)</div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= view('reports/partials/section_tables', ['sectionGroups' => $managerGroups]) ?>
