<!-- table_pending_pic.php -->

<?php if (empty($pendingPic)): ?>
    <div class="alert alert-info">No bookings require PIC approval.</div>
<?php else: ?>
<table class="table table-hover align-middle">
    <thead class="table-light">
        <tr>
            <th>Lab</th>
            <th>Date</th>
            <th>Time</th>
            <th>Activity</th>
            <th>Faculty</th>
            <th>Applicant</th>
            <th class="text-end">Actions</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($pendingPic as $b): ?>
        <tr>
            <td class="fw-semibold"><?= esc($b['lab_name']) ?></td>
            <td><?= esc($b['date']) ?></td>
            <td class="text-nowrap"><?= esc(substr($b['start_time'], 0, 5)) ?> – <?= esc(substr($b['end_time'], 0, 5)) ?></td>
            <td class="text-truncate" style="max-width:160px;" title="<?= esc($b['activity']) ?>"><?= esc($b['activity']) ?></td>
            <td><?= esc($b['faculty_name']) ?></td>
            <td><?= esc($b['user_id']) ?: 'External' ?></td>

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
