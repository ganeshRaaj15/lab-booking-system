<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= esc($reportTitle) ?></title>
    <style>
        @page { margin: 28px 26px 34px; }
        body { font-family: "DejaVu Sans", Arial, sans-serif; font-size: 11px; color: #1f2937; }
        h1, h2, h3, p { margin: 0; }
        .header { margin-bottom: 18px; border-bottom: 2px solid #0f766e; padding-bottom: 12px; }
        .system { font-size: 11px; text-transform: uppercase; letter-spacing: 0.08em; color: #0f766e; margin-bottom: 6px; }
        .title { font-size: 22px; font-weight: 700; margin-bottom: 6px; }
        .meta { color: #6b7280; line-height: 1.5; }
        .section { margin-top: 18px; }
        .section h2 { font-size: 14px; color: #0f766e; margin-bottom: 8px; }
        .filters { width: 100%; border-collapse: collapse; }
        .filters td { padding: 6px 8px; border: 1px solid #d1d5db; vertical-align: top; }
        .summary { width: 100%; border-collapse: separate; border-spacing: 8px; margin-top: 6px; }
        .summary td { width: 25%; border: 1px solid #d1d5db; padding: 9px 10px; border-radius: 6px; }
        .summary .label { display: block; color: #6b7280; font-size: 9px; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 4px; }
        .summary .value { font-size: 16px; font-weight: 700; color: #111827; }
        table.data { width: 100%; border-collapse: collapse; margin-top: 8px; }
        table.data th, table.data td { padding: 7px 8px; border: 1px solid #d1d5db; vertical-align: top; }
        table.data th { background: #ecfeff; color: #0f172a; text-align: left; font-size: 10px; text-transform: uppercase; letter-spacing: 0.05em; }
        .empty { padding: 10px; border: 1px dashed #cbd5e1; color: #6b7280; }
    </style>
</head>
<body>
    <div class="header">
        <div class="system">Smart Laboratory and Asset Management System</div>
        <div class="title"><?= esc($reportTitle) ?></div>
        <div class="meta">
            Generated: <?= esc($generatedAt) ?><br>
            Scope: <?= esc($scopeLabel) ?>
        </div>
    </div>

    <div class="section">
        <h2>Applied Filters</h2>
        <table class="filters">
            <tbody>
                <?php foreach ($appliedFilters as $filter): ?>
                    <tr>
                        <td style="width: 28%;"><strong><?= esc($filter['label']) ?></strong></td>
                        <td><?= esc($filter['value']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if (! empty($summaryCards)): ?>
        <div class="section">
            <h2>Summary</h2>
            <table class="summary">
                <tr>
                    <?php foreach ($summaryCards as $index => $card): ?>
                        <td>
                            <span class="label"><?= esc($card['label']) ?></span>
                            <span class="value"><?= esc((string) ($card['value'] ?? 0)) ?></span>
                        </td>
                        <?php if (($index + 1) % 4 === 0 && ($index + 1) < count($summaryCards)): ?>
                            </tr><tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </tr>
            </table>
        </div>
    <?php endif; ?>

    <?php foreach ($sections as $section): ?>
        <div class="section">
            <h2><?= esc($section['title']) ?></h2>
            <?php if (($section['rows'] ?? []) === []): ?>
                <div class="empty">No matching records.</div>
            <?php else: ?>
                <table class="data">
                    <thead>
                        <tr>
                            <?php foreach ($section['columns'] as $column): ?>
                                <th><?= esc($column) ?></th>
                            <?php endforeach; ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($section['rows'] as $row): ?>
                            <tr>
                                <?php foreach ($row as $value): ?>
                                    <td><?= esc((string) $value) ?></td>
                                <?php endforeach; ?>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</body>
</html>
