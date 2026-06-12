<?php
$sectionGroups = $report['sectionGroups'] ?? [];
$bookingAndOps = array_values(array_filter($sectionGroups, static fn(array $group): bool => in_array($group['id'] ?? '', ['booking', 'maintenance', 'asset', 'notification'], true)));
?>

<?= view('reports/partials/summary_cards', ['summaryCards' => $report['summaryCards'] ?? []]) ?>
<?= view('reports/partials/chart_grid', ['charts' => array_slice($report['charts'] ?? [], 0, 3)]) ?>

<div class="reports-role-layout-grid">
    <div class="card reports-table-card">
        <div class="card-body">
            <h3>Assigned Laboratory Scope</h3>
            <div class="reports-scope-list">
                <?php foreach (($report['scopeLaboratories'] ?? []) as $lab): ?>
                    <div class="reports-callout-box">
                        <div class="reports-callout-label">Laboratory</div>
                        <div class="reports-callout-value"><?= esc($lab['name'] ?? '-') ?></div>
                        <?php if (! empty($lab['room'])): ?>
                            <div class="text-muted small"><?= esc($lab['room']) ?></div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="card reports-table-card">
        <div class="card-body">
            <h3>Upcoming Operational Queue</h3>
            <?php if (($report['upcomingBookings'] ?? []) === []): ?>
                <div class="reports-empty">No approved or pending operational activity is scheduled.</div>
            <?php else: ?>
                <div class="reports-item-stack">
                    <?php foreach (array_slice($report['upcomingBookings'] ?? [], 0, 6) as $booking): ?>
                        <div class="reports-callout-box">
                            <div class="reports-callout-value"><?= esc($booking['lab_name'] ?? '-') ?></div>
                            <div class="text-muted small"><?= esc($booking['date'] ?? '-') ?> | <?= esc(substr((string) ($booking['start_time'] ?? ''), 0, 5)) ?> - <?= esc(substr((string) ($booking['end_time'] ?? ''), 0, 5)) ?></div>
                            <div class="text-muted small"><?= esc($booking['status'] ?? '-') ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?= view('reports/partials/section_tables', ['sectionGroups' => $bookingAndOps]) ?>
