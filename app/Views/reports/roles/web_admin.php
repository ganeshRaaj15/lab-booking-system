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

<!-- ── KPI Cards ─────────────────────────────────────────────────────── -->
<div class="row g-3 mb-3">

    <div class="col-6 col-md-4 col-xl-3">
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
    </div>

    <div class="col-6 col-md-4 col-xl-3">
        <div class="slams-kpi slams-kpi-success">
            <div class="slams-kpi-head">
                <div>
                    <div class="slams-kpi-label">Approval Rate</div>
                    <div class="slams-kpi-value"><?= esc((string) ($kpis['approval_rate'] ?? 0)) ?>%</div>
                </div>
                <div class="slams-kpi-icon slams-kpi-icon--success"><i class="bi bi-check-circle"></i></div>
            </div>
            <div class="slams-kpi-footer"><?= esc((string) ($kpis['approved'] ?? 0)) ?> approved</div>
        </div>
    </div>

    <div class="col-6 col-md-4 col-xl-3">
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
    </div>

    <div class="col-6 col-md-4 col-xl-3">
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
    </div>

    <div class="col-6 col-md-4 col-xl-3">
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
    </div>

    <div class="col-6 col-md-4 col-xl-3">
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
    </div>

    <?php if (($kpis['users'] ?? null) !== null): ?>
    <div class="col-6 col-md-4 col-xl-3">
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
    </div>
    <?php endif; ?>

    <div class="col-6 col-md-4 col-xl-3">
        <div class="slams-kpi slams-kpi-neutral">
            <div class="slams-kpi-head">
                <div>
                    <div class="slams-kpi-label">Notifications</div>
                    <div class="slams-kpi-value"><?= esc((string) ($kpis['notifications_total'] ?? 0)) ?></div>
                </div>
                <div class="slams-kpi-icon slams-kpi-icon--neutral"><i class="bi bi-bell"></i></div>
            </div>
            <div class="slams-kpi-footer">System-wide total</div>
        </div>
    </div>

</div>

<!-- ── Tab Navigation (Bootstrap nav-pills) ──────────────────────────── -->
<div class="rpt-nav-wrap">
    <ul class="nav rpt-nav" id="rptAdminTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-overview" data-bs-toggle="tab"
                    data-bs-target="#rpt-panel-overview" type="button" role="tab"
                    aria-controls="rpt-panel-overview" aria-selected="true">
                <i class="bi bi-grid-3x3-gap"></i> Overview
            </button>
        </li>
        <?php foreach ($sectionGroups as $group): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-<?= esc($group['id']) ?>"
                    data-bs-toggle="tab"
                    data-bs-target="#rpt-panel-<?= esc($group['id']) ?>"
                    type="button" role="tab"
                    aria-controls="rpt-panel-<?= esc($group['id']) ?>"
                    aria-selected="false">
                <i class="bi <?= esc($tabIcons[$group['id']] ?? 'bi-bar-chart') ?>"></i>
                <?= esc($group['title']) ?>
            </button>
        </li>
        <?php endforeach; ?>
    </ul>
</div>

<!-- ── Tab Content ────────────────────────────────────────────────────── -->
<div class="tab-content" id="rptAdminTabContent">

    <!-- Overview -->
    <div class="tab-pane fade show active" id="rpt-panel-overview"
         role="tabpanel" aria-labelledby="tab-overview">

        <!-- Scope stat strip -->
        <div class="row g-3 mb-3">
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body rpt-stat-box">
                        <div class="rpt-stat-label">Laboratories</div>
                        <div class="rpt-stat-value"><?= esc((string) ($kpis['total_labs'] ?? 0)) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body rpt-stat-box">
                        <div class="rpt-stat-label">Assets</div>
                        <div class="rpt-stat-value"><?= esc((string) ($kpis['total_assets'] ?? 0)) ?></div>
                    </div>
                </div>
            </div>
            <?php if (($kpis['users'] ?? null) !== null): ?>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body rpt-stat-box">
                        <div class="rpt-stat-label">Users</div>
                        <div class="rpt-stat-value"><?= esc((string) ($kpis['users'] ?? 0)) ?></div>
                    </div>
                </div>
            </div>
            <?php endif; ?>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body rpt-stat-box">
                        <div class="rpt-stat-label">Notifications</div>
                        <div class="rpt-stat-value"><?= esc((string) ($kpis['notifications_total'] ?? 0)) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body rpt-stat-box">
                        <div class="rpt-stat-label">Maintenance Total</div>
                        <div class="rpt-stat-value"><?= esc((string) ($kpis['maintenance_total'] ?? 0)) ?></div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body rpt-stat-box">
                        <div class="rpt-stat-label">Asset Availability</div>
                        <div class="rpt-stat-value"><?= esc((string) ($kpis['asset_availability_rate'] ?? 0)) ?>%</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Charts -->
        <?php if ($charts !== []): ?>
        <div class="row g-3 mb-3">
            <?php foreach ($charts as $chart): ?>
            <div class="col-12 col-md-6">
                <div class="rpt-chart-card h-100">
                    <h6><?= esc($chart['title'] ?? 'Chart') ?></h6>
                    <div style="position:relative;height:<?= esc((string) ($chart['height'] ?? 280)) ?>px">
                        <canvas id="<?= esc($chart['id'] ?? '') ?>"></canvas>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <!-- Lab utilization table -->
        <?php if (! empty($report['labUtilization'])): ?>
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-transparent fw-bold" style="font-size:0.88rem">
                <i class="bi bi-building me-2 text-muted"></i>Laboratory Utilization Snapshot
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0 rpt-table-card">
                    <thead>
                        <tr>
                            <th>Laboratory</th>
                            <th>Bookings</th>
                            <th>Utilization</th>
                            <th>Used Hours</th>
                            <th>Peak Day</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach (array_slice($report['labUtilization'], 0, 10) as $lab): ?>
                        <tr>
                            <td>
                                <span class="fw-semibold"><?= esc($lab['laboratory_name'] ?? '-') ?></span>
                                <?php if (! empty($lab['laboratory_room'])): ?>
                                    <span class="text-muted ms-1" style="font-size:0.78rem"><?= esc($lab['laboratory_room']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?= esc((string) ($lab['total_bookings'] ?? 0)) ?></td>
                            <td style="min-width:130px">
                                <?php $pct = min((float) ($lab['usage_percentage'] ?? 0), 100); ?>
                                <div class="d-flex align-items-center gap-2">
                                    <div class="progress flex-grow-1" style="height:5px;max-width:80px">
                                        <div class="progress-bar bg-primary" style="width:<?= (int) $pct ?>%"></div>
                                    </div>
                                    <span class="text-muted" style="font-size:0.8rem;white-space:nowrap"><?= esc((string) $pct) ?>%</span>
                                </div>
                            </td>
                            <td><?= esc((string) ($lab['total_used_hours'] ?? 0)) ?>h</td>
                            <td><?= esc((string) ($lab['peak_usage_day'] ?? 'N/A')) ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php endif; ?>

    </div><!-- /overview -->

    <!-- Section Tabs -->
    <?php foreach ($sectionGroups as $group): ?>
    <div class="tab-pane fade" id="rpt-panel-<?= esc($group['id']) ?>"
         role="tabpanel" aria-labelledby="tab-<?= esc($group['id']) ?>">

        <?php if (! empty($group['description'])): ?>
        <p class="text-muted mb-3" style="font-size:0.85rem"><?= esc($group['description']) ?></p>
        <?php endif; ?>

        <div class="row g-3">
            <?php foreach ($group['tables'] ?? [] as $table): ?>
            <div class="<?= ($table['fullWidth'] ?? false) ? 'col-12' : 'col-12 col-lg-6' ?>">
                <div class="card border-0 shadow-sm h-100 rpt-table-card">
                    <div class="card-header bg-transparent">
                        <?= esc($table['title'] ?? '') ?>
                    </div>
                    <?php if (empty($table['rows'])): ?>
                        <div class="card-body rpt-empty">
                            <i class="bi bi-inbox d-block mb-1" style="font-size:1.3rem"></i>
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

</div><!-- /tab-content -->
