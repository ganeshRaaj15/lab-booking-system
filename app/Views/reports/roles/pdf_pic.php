<div class="section">
    <div class="section-title">PIC Operational Snapshot</div>
    <div class="section-description">Assigned laboratory operations, booking queue, and maintenance workload summary.</div>
    <table class="data">
        <thead>
            <tr>
                <th>Assigned Laboratory</th>
                <th>Room</th>
            </tr>
        </thead>
        <tbody>
            <?php if (($report['scopeLaboratories'] ?? []) === []): ?>
                <tr><td colspan="2" class="muted">No assigned laboratories were found.</td></tr>
            <?php else: ?>
                <?php foreach (($report['scopeLaboratories'] ?? []) as $lab): ?>
                    <tr>
                        <td><?= esc($lab['name'] ?? '-') ?></td>
                        <td><?= esc($lab['room'] ?? '-') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>
