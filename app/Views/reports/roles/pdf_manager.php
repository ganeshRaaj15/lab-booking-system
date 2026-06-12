<div class="section">
    <div class="section-title">Lab Manager Comparison Snapshot</div>
    <div class="section-description">Cross-laboratory comparison emphasis for operational demand and utilization.</div>
    <table class="data">
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
                <tr><td colspan="4" class="muted">No comparison data is available.</td></tr>
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
