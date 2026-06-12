<div class="section">
    <div class="section-title">Admin Governance Snapshot</div>
    <div class="section-description">System-wide reference summary for governance, audits, and operational review.</div>
    <table class="data">
        <thead>
            <tr>
                <th>Total Users</th>
                <th>Total Laboratories</th>
                <th>Total Assets</th>
                <th>Open Maintenance</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><?= esc((string) ($report['kpis']['users'] ?? 0)) ?></td>
                <td><?= esc((string) ($report['kpis']['total_labs'] ?? 0)) ?></td>
                <td><?= esc((string) ($report['kpis']['total_assets'] ?? 0)) ?></td>
                <td><?= esc((string) ($report['kpis']['maintenance_open'] ?? 0)) ?></td>
            </tr>
        </tbody>
    </table>
</div>
