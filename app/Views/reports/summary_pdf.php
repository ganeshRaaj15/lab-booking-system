<?php $report = $report ?? []; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= esc($report['reportTitle'] ?? 'SLAMS Analytics Report') ?></title>
    <style>
        @page {
            margin: 26px 28px 34px 28px;
        }

        body {
            font-family: "DejaVu Sans", Arial, sans-serif;
            color: #1f2937;
            font-size: 11px;
            line-height: 1.45;
        }

        h1, h2, h3, h4, p {
            margin: 0;
        }

        .cover {
            border: 1px solid #dbe3ee;
            border-radius: 10px;
            padding: 24px;
            margin-bottom: 18px;
            background: #f8fbff;
        }

        .system-name {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.18em;
            color: #5b7088;
            margin-bottom: 10px;
        }

        .report-title {
            font-size: 24px;
            font-weight: bold;
            color: #0f2d52;
            margin-bottom: 8px;
        }

        .report-subtitle {
            font-size: 12px;
            color: #445468;
            margin-bottom: 14px;
        }

        .meta-grid,
        .kpi-grid {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-bottom: 16px;
        }

        .meta-grid td,
        .kpi-grid td {
            border: 1px solid #dbe3ee;
            padding: 10px 12px;
            vertical-align: top;
        }

        .meta-grid td {
            width: 50%;
        }

        .label {
            display: block;
            color: #708399;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            margin-bottom: 4px;
        }

        .value {
            font-size: 11px;
            color: #182433;
        }

        .kpi-card {
            min-height: 64px;
        }

        .kpi-value {
            font-size: 18px;
            font-weight: bold;
            color: #102847;
            margin-top: 6px;
        }

        .section {
            page-break-inside: avoid;
            margin-bottom: 18px;
        }

        .section.page-break {
            page-break-before: always;
        }

        .section-title {
            font-size: 16px;
            font-weight: bold;
            color: #10335f;
            margin-bottom: 4px;
        }

        .section-description {
            color: #5d6f83;
            margin-bottom: 10px;
        }

        .table-block {
            margin-bottom: 14px;
        }

        .table-title {
            font-size: 12px;
            font-weight: bold;
            color: #193a62;
            margin-bottom: 6px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.data th,
        table.data td {
            border: 1px solid #dbe3ee;
            padding: 7px 8px;
            font-size: 10px;
            vertical-align: top;
            word-wrap: break-word;
        }

        table.data th {
            background: #edf4fb;
            text-align: left;
            color: #183c64;
        }

        .muted {
            color: #6b7c90;
        }

        .notes {
            border: 1px solid #dbe3ee;
            padding: 12px;
            background: #fbfdff;
        }

        .notes ul {
            margin: 8px 0 0 18px;
            padding: 0;
        }
    </style>
</head>
<body>
    <div class="cover">
        <div class="system-name">SLAMS Laboratory And Asset Management System</div>
        <div class="report-title"><?= esc($report['reportTitle'] ?? 'SLAMS Analytics Report') ?></div>
        <div class="report-subtitle"><?= esc($report['scopeDescription'] ?? '') ?></div>
        <?php if (! empty($report['uiProfile']['headline'])): ?>
            <div class="report-subtitle"><strong><?= esc($report['uiProfile']['headline']) ?></strong></div>
        <?php endif; ?>

        <table class="meta-grid">
            <tr>
                <td>
                    <span class="label">Report Scope</span>
                    <span class="value"><?= esc($report['scopeLabel'] ?? '-') ?></span>
                </td>
                <td>
                    <span class="label">User Role</span>
                    <span class="value"><?= esc($report['roleDisplay'] ?? '-') ?></span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class="label">Generated Date And Time</span>
                    <span class="value"><?= esc($report['generatedAtDisplay'] ?? ($report['generatedAt'] ?? '-')) ?></span>
                </td>
                <td>
                    <span class="label">Selected Filters</span>
                    <span class="value">
                        <?php if (($report['appliedFilters'] ?? []) === []): ?>
                            No filters applied
                        <?php else: ?>
                            <?php foreach (($report['appliedFilters'] ?? []) as $index => $filter): ?>
                                <?= $index > 0 ? ' | ' : '' ?><?= esc(($filter['label'] ?? 'Filter') . ': ' . ($filter['value'] ?? '')) ?>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </span>
                </td>
            </tr>
        </table>

        <table class="kpi-grid">
            <?php $cards = array_values($report['summaryCards'] ?? []); ?>
            <?php foreach (array_chunk($cards, 4) as $row): ?>
                <tr>
                    <?php foreach ($row as $card): ?>
                        <td class="kpi-card">
                            <span class="label"><?= esc($card['label'] ?? 'Metric') ?></span>
                            <div class="kpi-value"><?= esc((string) ($card['value'] ?? '0')) ?></div>
                        </td>
                    <?php endforeach; ?>
                    <?php for ($i = count($row); $i < 4; $i++): ?>
                        <td class="kpi-card"></td>
                    <?php endfor; ?>
                </tr>
            <?php endforeach; ?>
        </table>

        <?php if (($report['uiProfile']['focusAreas'] ?? []) !== []): ?>
            <table class="meta-grid">
                <tr>
                    <td colspan="2">
                        <span class="label">Presentation Focus</span>
                        <span class="value">
                            <?php foreach (($report['uiProfile']['focusAreas'] ?? []) as $index => $area): ?>
                                <?= $index > 0 ? ' | ' : '' ?><?= esc($area) ?>
                            <?php endforeach; ?>
                        </span>
                    </td>
                </tr>
            </table>
        <?php endif; ?>
    </div>

    <?= view('reports/roles/pdf_' . ($report['role'] ?? 'pic'), ['report' => $report]) ?>

    <?php foreach (($report['sectionGroups'] ?? []) as $sectionIndex => $group): ?>
        <div class="section <?= $sectionIndex > 0 ? 'page-break' : '' ?>">
            <div class="section-title"><?= esc($group['title'] ?? 'Analytics Section') ?></div>
            <?php if (! empty($group['description'])): ?>
                <div class="section-description"><?= esc($group['description']) ?></div>
            <?php endif; ?>

            <?php foreach (($group['tables'] ?? []) as $table): ?>
                <div class="table-block">
                    <div class="table-title"><?= esc($table['title'] ?? 'Table') ?></div>
                    <table class="data">
                        <thead>
                            <tr>
                                <?php foreach (($table['columns'] ?? []) as $column): ?>
                                    <th><?= esc($column['label'] ?? $column['key'] ?? 'Column') ?></th>
                                <?php endforeach; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (($table['rows'] ?? []) === []): ?>
                                <tr>
                                    <td colspan="<?= esc((string) count($table['columns'] ?? [])) ?>" class="muted">
                                        <?= esc($table['emptyMessage'] ?? 'No data available.') ?>
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach (($table['rows'] ?? []) as $row): ?>
                                    <tr>
                                        <?php foreach (($table['columns'] ?? []) as $column): ?>
                                            <td><?= esc((string) ($row[$column['key']] ?? '')) ?></td>
                                        <?php endforeach; ?>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endforeach; ?>

    <?php if (($report['limitations'] ?? []) !== []): ?>
        <div class="section page-break">
            <div class="section-title">Data Scope Notes</div>
            <div class="notes">
                <p class="muted">The following analytics areas depend on fields available in the current SLAMS database schema.</p>
                <ul>
                    <?php foreach (($report['limitations'] ?? []) as $item): ?>
                        <li><?= esc($item) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>
