<!-- table_pending_manager.php -->

<?php if (empty($pendingMgr)): ?>
    <div class="alert alert-info">No bookings waiting for Manager approval.</div>
<?php else: ?>
<table class="table table-hover align-middle">
    <thead class="table-light">
        <tr>
            <th>Lab</th>
            <th>Date</th>
            <th>Time</th>
            <th>Activity</th>
            <th>Faculty</th>
            <th>PIC Approved</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>

    <tbody>
        <?php foreach ($pendingMgr as $b): ?>
        <tr>
            <td class="fw-semibold"><?= esc($b['lab_name']) ?></td>
            <td><?= esc($b['date']) ?></td>
            <td class="text-nowrap"><?= esc(substr($b['start_time'], 0, 5)) ?> – <?= esc(substr($b['end_time'], 0, 5)) ?></td>
            <td class="text-truncate" style="max-width:160px;" title="<?= esc($b['activity']) ?>"><?= esc($b['activity']) ?></td>
            <td><?= esc($b['faculty_name']) ?></td>
            <td><span class="badge bg-success-subtle text-success border border-success-subtle">
                <i class="bi bi-check-circle me-1"></i>Yes
            </span></td>

            <td class="text-end">
                <button type="button"
                        class="btn btn-outline-secondary btn-sm px-3"
                        onclick="adminViewBooking(<?= (int) $b['id'] ?>)"
                        title="View full booking details">
                    <i class="bi bi-eye me-1"></i>View
                </button>
            </td>
        </tr>
        <?php endforeach ?>
    </tbody>
</table>
<?php endif; ?>
