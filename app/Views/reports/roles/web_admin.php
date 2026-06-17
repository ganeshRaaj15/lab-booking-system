<?php
/** @var array $report */
$sectionGroups = $report['sectionGroups'] ?? [];
$charts        = $report['charts']        ?? [];
$kpis          = $report['kpis']          ?? [];

$tabIcons = [
    'booking'      => 'bi-calendar-check',
    'laboratory'   => 'bi-building',
    'asset'        => 'bi-box-seam',
    'maintenance'  => 'bi-tools',
    'notification' => 'bi-bell',
    'users'        => 'bi-people',
];
?>

<!-- ── KPI Grid ──────────────────────────────────────────────────────── -->
<div class="rpt-kpi-grid">

    <div class="slams-kpi slams-kpi-info">
        <div class="slams-kpi-head">
            <div>
                <div class="slams-kpi-label">Total Bookings</div>
                <div class="slams-kpi-value"><?= esc((string) ($kpis['total_bookings'] ?? 0)) ?></div>
            </div>
            <div class="slams-kpi-icon slams-kpi-icon--info"><i class="bi bi-calendar3"></i></div>
        </div>
        <div class="slams-kpi-footer">Across all labs in scope</div>
    </div>

    <div class="slams-kpi slams-kpi-success">
        <div class="slams-kpi-head">
            <div>
                <div class="slams-kpi-label">Approval Rate</div>
                <div class="slams-kpi-value"><?= esc((string) ($kpis['approval_rate'] ?? 0)) ?>%</div>
            </div>
            <div class="slams-kpi-icon slams-kpi-icon--success"><i class="bi bi-check-circle"></i></div>
        </div>
        <div class="slams-kpi-footer"><?= esc((string) ($kpis['approved'] ?? 0)) ?> approved bookings</div>
    </div>

    <div class="slams-kpi slams-kpi-warning">
        <div class="slams-kpi-head">
            <div>
                <div class="slams-kpi-label">Pending Bookings</div>
                <div class="slams-kpi-value"><?= esc((string) ($kpis['pending'] ?? 0)) ?></div>
            </div>
            <div class="slams-kpi-icon slams-kpi-icon--warning"><i class="bi bi-hourglass-split"></i></div>
        </div>
        <div class="slams-kpi-footer">Awaiting action</div>
    </div>

    <div class="slams-kpi slams-kpi-danger">
        <div class="slams-kpi-head">
            <div>
                <div class="slams-kpi-label">Rejected / Cancelled</div>
                <div class="slams-kpi-value"><?= esc((string) (($kpis['rejected'] ?? 0) + ($kpis['cancelled'] ?? 0))) ?></div>
            </div>
            <div class="slams-kpi-icon slams-kpi-icon--danger"><i class="bi bi-x-circle"></i></div>
        </div>
        <div class="slams-kpi-footer"><?= esc((string) ($kpis['rejection_rate'] ?? 0)) ?>% rejection rate</div>
    </div>

    <div class="slams-kpi slams-kpi-neutral">
        <div class="slams-kpi-head">
            <div>
                <div class="slams-kpi-label">Total Assets</div>
                <div class="slams-kpi-value"><?= esc((string) ($kpis['total_assets'] ?? 0)) ?></div>
            </div>
            <div class="slams-kpi-icon slams-kpi-icon--neutral"><i class="bi bi-box-seam"></i></div>
        </div>
        <div class="slams-kpi-footer"><?= esc((string) ($kpis['asset_availability_rate'] ?? 0)) ?>% availability</div>
    </div>

    <div class="slams-kpi slams-kpi-warning">
        <div class="slams-kpi-head">
            <div>
                <div class="slams-kpi-label">Open Maintenance</div>
                <div class="slams-kpi-value"><?= esc((string) ($kpis['maintenance_open'] ?? 0)) ?></div>
            </div>
            <div class="slams-kpi-icon slams-kpi-icon--warning"><i class="bi bi-tools"></i></div>
        </div>
        <div class="slams-kpi-footer"><?= esc((string) ($kpis['maintenance_completed'] ?? 0)) ?> completed</div>
    </div>

    <?php if (($kpis['users'] ?? null) !== null): ?>
    <div class="slams-kpi slams-kpi-info">
        <div class="slams-kpi-head">
            <div>
                <div class="slams-kpi-label">System Users</div>
                <div class="slams-kpi-value"><?= esc((string) ($kpis['users'] ?? 0)) ?></div>
            </div>
            <div class="slams-kpi-icon slams-kpi-icon--info"><i class="bi bi-people"></i></div>
        </div>
        <div class="slams-kpi-footer">Registered accounts</div>
    </div>
    <?php endif; ?>

    <div class="slams-kpi slams-kpi-neutral">
        <div class="slams-kpi-head">
            <div>
                <div class="slams-kpi-label">Notifications</div>
                <div class="slams-kpi-value"><?= esc((string) ($kpis['notifications_total'] ?? 0)) ?></div>
            </div>
            <div class="slams-kpi-icon slams-kpi-icon--neutral"><i class="bi bi-bell"></i></div>
        </div>
        <div class="slams-kpi-footer">System-wide</div>
    </div>

</div>

<!-- ── Tab Navigation ────────────────────────────────────────────────── -->
<div class="rpt-tabs-wrap">
    <div class="rpt-tabs-bar">
        <button class="rpt-tab active" data-rpt-tab="overview">
            <i class="bi bi-grid-3x3-gap"></i> Overview
        </button>
        <?php foreach ($sectionGroups as $group): ?>
            <button class="rpt-tab" data-rpt-tab="<?= esc($group['id']) ?>">
                <i class="bi <?= esc($tabIcons[$group['id']] ?? 'bi-bar-chart') ?>"></i>
                <?= esc($group['title']) ?>
            </button>
        <?php endforeach; ?>
    </div>
</div>

<!-- ── Overview Tab ──────────────────────────────────────────────────── -->
<div id="rpt-panel-overview" class="rpt-tab-panel rpt-tab-panel--active">

    <!-- Scope stat strip -->
    <div class="rpt-stat-strip">
        <div class="rpt-stat-box">
            <div class="rpt-stat-box-label">Laboratories</div>
            <div class="rpt-stat-box-value"><?= esc((string) ($kpis['total_labs'] ?? 0)) ?></div>
        </div>
        <div class="rpt-stat-box">
            <div class="rpt-stat-box-label">Assets</div>
            <div class="rpt-stat-box-value"><?= esc((string) ($kpis['total_assets'] ?? 0)) ?></div>
        </div>
        <?php if (($kpis['users'] ?? null) !== null): ?>
        <div class="rpt-stat-box">
            <div class="rpt-stat-box-label">Users</div>
            <div class="rpt-stat-box-value"><?= esc((string) ($kpis['users'] ?? 0)) ?></div>
        </div>
        <?php endif; ?>
        <div class="rpt-stat-box">
            <div class="rpt-stat-box-label">Notifications</div>
            <div class="rpt-stat-box-value"><?= esc((string) ($kpis['notifications_total'] ?? 0)) ?></div>
        </div>
        <div class="rpt-stat-box">
            <div class="rpt-stat-box-label">Maintenance Total</div>
            <div class="rpt-stat-box-value"><?= esc((string) ($kpis['maintenance_total'] ?? 0)) ?></div>
        </div>
        <div class="rpt-stat-box">
            <div class="rpt-stat-box-label">Asset Availability</div>
            <div class="rpt-stat-box-value"><?= esc((string) ($kpis['asset_availability_rate'] ?? 0)) ?>%</div>
        </div>
    </div>

    <!-- Charts -->
    <?php if ($charts !== []): ?>
    <div class="rpt-chart-pair">
        <?php foreach ($charts as $chart): ?>
            <div class="rpt-chart-card">
                <h4><?= esc($chart['title'] ?? 'Chart') ?></h4>
                <div class="rpt-canvas-wrap" style="height:<?= esc((string) ($chart['height'] ?? 280)) ?>px">
                    <canvas id="<?= esc($chart['id'] ?? '') ?>"></canvas>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <!-- Lab governance table -->
    <?php if (! empty($report['labUtilization'])): ?>
    <div class="rpt-gov-card">
        <div class="rpt-gov-card-head">
            <h4><i class="bi bi-building me-2 text-muted"></i>Laboratory Utilization Snapshot</h4>
        </div>
        <div class="table-responsive">
            <table class="table table-hover mb-0" style="font-size:0.83rem">
                <thead>
                    <tr>
                        <th style="font-size:0.74rem;font-weight:700;text-transform:uppercase;color:var(--slams-muted);padding:0.55rem 0.9rem">Laboratory</th>
                        <th style="font-size:0.74rem;font-weight:700;text-transform:uppercase;color:var(--slams-muted);padding:0.55rem 0.9rem">Bookings</th>
                        <th style="font-size:0.74rem;font-weight:700;text-transform:uppercase;color:var(--slams-muted);padding:0.55rem 0.9rem">Utilization</th>
                        <th style="font-size:0.74rem;font-weight:700;text-transform:uppercase;color:var(--slams-muted);padding:0.55rem 0.9rem">Used Hours</th>
                        <th style="font-size:0.74rem;font-weight:700;text-transform:uppercase;color:var(--slams-muted);padding:0.55rem 0.9rem">Peak Day</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach (array_slice($report['labUtilization'], 0, 10) as $lab): ?>
                        <tr>
                            <td style="padding:0.5rem 0.9rem;font-weight:500;color:var(--slams-heading)">
                                <?= esc($lab['laboratory_name'] ?? '-') ?>
                                <?php if (! empty($lab['laboratory_room'])): ?>
                                    <span class="text-muted" style="font-size:0.78rem;margin-left:0.35rem"><?= esc($lab['laboratory_room']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:0.5rem 0.9rem"><?= esc((string) ($lab['total_bookings'] ?? 0)) ?></td>
                            <td style="padding:0.5rem 0.9rem">
                                <?php $pct = (float) ($lab['usage_percentage'] ?? 0); ?>
                                <div class="d-flex align-items-center gap-2">
                                    <div style="flex:1;height:5px;background:var(--slams-border);border-radius:999px;max-width:70px">
                                        <div style="height:100%;border-radius:999px;width:<?= min((int) $pct, 100) ?>%;background:var(--slams-primary)"></div>
                                    </div>
                                    <span><?= esc((string) $pct) ?>%</span>
                                </div>
                            </td>
                            <td style="padding:0.5rem 0.9rem"><?= esc((string) ($lab['total_used_hours'] ?? 0)) ?>h</td>
                            <td style="padding:0.5rem 0.9rem"><?= esc((string) ($lab['peak_usage_day'] ?? 'N/A')) ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</div>

<!-- ── Section Tabs ───────────────────────────────────────────────────── -->
<?php foreach ($sectionGroups as $group): ?>
<div id="rpt-panel-<?= esc($group['id']) ?>" class="rpt-tab-panel">

    <div class="rpt-section-header">
        <h3><?= esc($group['title']) ?></h3>
        <?php if (! empty($group['description'])): ?>
            <p><?= esc($group['description']) ?></p>
        <?php endif; ?>
    </div>

    <div class="rpt-section-grid">
        <?php foreach ($group['tables'] ?? [] as $table): ?>
            <div class="rpt-table-block <?= ($table['fullWidth'] ?? false) ? 'rpt-full-width' : '' ?>">
                <div class="rpt-table-block-head">
                    <h4><?= esc($table['title'] ?? 'Table') ?></h4>
                    <?php if (! empty($table['description'])): ?>
                        <p><?= esc($table['description']) ?></p>
                    <?php endif; ?>
                </div>
                <div class="rpt-table-body">
                    <?php if (empty($table['rows'])): ?>
                        <div class="rpt-empty">
                            <i class="bi bi-inbox text-muted d-block mb-1" style="font-size:1.25rem"></i>
                            <?= esc($table['emptyMessage'] ?? 'No data available.') ?>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead>
                                    <tr>
                                        <?php foreach ($table['columns'] ?? [] as $col): ?>
                                            <th><?= esc($col['label'] ?? $col['key'] ?? '') ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($table['rows'] as $row): ?>
                                        <tr>
                                            <?php foreach ($table['columns'] ?? [] as $col): ?>
                                                <td><?= esc((string) ($row[$col['key']] ?? '')) ?></td>
                                            <?php endforeach; ?>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

</div>
<?php endforeach; ?>

<script>
(function () {
    const tabs   = document.querySelectorAll('.rpt-tab');
    const panels = document.querySelectorAll('.rpt-tab-panel');

    tabs.forEach(function (btn) {
        btn.addEventListener('click', function () {
            tabs.forEach(function (t) { t.classList.remove('active'); });
            panels.forEach(function (p) { p.classList.remove('rpt-tab-panel--active'); });
            this.classList.add('active');
            var target = document.getElementById('rpt-panel-' + this.dataset.rptTab);
            if (target) { target.classList.add('rpt-tab-panel--active'); }
        });
    });
})();
</script>
