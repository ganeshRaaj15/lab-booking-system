<?= $this->extend('layouts/main_user') ?>
<?= $this->section('content') ?>

<?php $filters = $filters ?? ['q' => '', 'date_from' => '', 'date_to' => '']; ?>

<div class="slams-page-header">
    <div class="slams-page-header-left">
        <h1 class="slams-page-title">Booking Approvals</h1>
        <p class="slams-page-subtitle">Review and act on pending booking requests assigned to you.</p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-body">
        <form method="get" action="/dashboard/approvals" class="row g-3 align-items-end">
            <div class="col-md-5">
                <label class="form-label small text-muted">Search</label>
                <input type="text" name="q" class="form-control" value="<?= esc($filters['q']) ?>" placeholder="Student, lab, room, or activity">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">From</label>
                <input type="date" name="date_from" class="form-control" value="<?= esc($filters['date_from']) ?>">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">To</label>
                <input type="date" name="date_to" class="form-control" value="<?= esc($filters['date_to']) ?>">
            </div>
            <div class="col-md-1 d-flex gap-2">
                <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i></button>
                <a href="/dashboard/approvals" class="btn btn-outline-secondary"><i class="bi bi-x-lg"></i></a>
            </div>
        </form>
    </div>
</div>

<div class="glass-card">
    <div class="glass-card-header">
        <h5 class="mb-0">Pending Approval Requests</h5>
    </div>

    <div class="card-body p-0">

        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th>Student</th>
                    <th>Lab</th>
                    <th>Date</th>
                    <th>Time</th>
                    <th>Activity</th>
                    <th>Approval</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>

            <tbody>

            <?php if (empty($bookings)): ?>
                <tr>
                    <td colspan="7" class="text-center py-4 text-muted">
                        No pending bookings at this moment.
                    </td>
                </tr>
            <?php else: ?>

                <?php foreach ($bookings as $b): ?>
                    <tr id="row-<?= $b['id'] ?>" class="<?= ((int) ($focusBookingId ?? 0) === (int) $b['id']) ? 'table-primary' : '' ?>">

                        <td><?= esc($b['username'] ?? 'Student') ?></td>

                        <td>
                            <strong><?= esc($b['lab_name']) ?></strong><br>
                            <small class="text-muted">Room <?= esc($b['lab_room']) ?></small>
                        </td>

                        <td><?= esc($b['date']) ?></td>

                        <td><?= esc($b['start_time']) ?> to <?= esc($b['end_time']) ?></td>

                        <td><?= esc($b['activity']) ?></td>

                        <td>
                            <?php if ($b['approval_flow'] === 'FKMP_APPROVAL'): ?>
                                <span class="badge bg-success">FKMP (PIC Final)</span>
                            <?php elseif ($b['approval_flow'] === 'EXTERNAL_APPROVAL'): ?>
                                <span class="badge bg-secondary">External Request</span>
                            <?php else: ?>
                                <span class="badge bg-info">PIC to Manager</span>
                            <?php endif; ?>

                            <div class="small mt-1">
                                PIC:
                                <span class="<?= $b['approved_by_pic'] ? 'text-success' : 'text-warning' ?>">
                                    <?= $b['approved_by_pic'] ? 'Approved' : 'Pending' ?>
                                </span>
                            </div>

                            <div class="small">
                                Manager:
                                <span class="<?= $b['approved_by_manager'] ? 'text-success' : 'text-muted' ?>">
                                    <?= $b['approved_by_manager'] ? 'Approved' : 'Pending' ?>
                                </span>
                            </div>
                        </td>

                        <td class="text-end">
                            <button class="btn btn-success btn-sm me-1 approveBtn" data-id="<?= $b['id'] ?>">
                                <i class="bi bi-check-lg"></i>
                            </button>
                            <button class="btn btn-danger btn-sm rejectBtn" data-id="<?= $b['id'] ?>">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </td>

                    </tr>
                <?php endforeach; ?>

            <?php endif; ?>

            </tbody>
        </table>

    </div>
</div>

<script>
const csrfHeaderName = "X-CSRF-TOKEN";
let csrfTokenValue = "<?= csrf_hash() ?>";

function refreshCsrf(data) {
    if (data && data.csrf_hash) csrfTokenValue = data.csrf_hash;
}

document.querySelectorAll(".approveBtn").forEach(btn => {
    btn.addEventListener("click", () => {
        let id = btn.dataset.id;

        fetch(`/booking/approve/${id}`, {
            method:"POST",
            headers: {
                "X-Requested-With":"XMLHttpRequest",
                [csrfHeaderName]: csrfTokenValue
            }
        })
        .then(r => r.json())
        .then(data => {
            refreshCsrf(data);
            if (data.status === "success") {
                document.getElementById(`row-${id}`)?.remove();
            } else alert(data.message);
        })
        .catch(() => alert("Request failed. Please refresh the page and try again."));
    });
});

document.querySelectorAll(".rejectBtn").forEach(btn => {
    btn.addEventListener("click", () => {
        let id = btn.dataset.id;

        fetch(`/booking/reject/${id}`, {
            method:"POST",
            headers: {
                "X-Requested-With":"XMLHttpRequest",
                [csrfHeaderName]: csrfTokenValue
            }
        })
        .then(r => r.json())
        .then(data => {
            refreshCsrf(data);
            if (data.status === "success") {
                document.getElementById(`row-${id}`)?.remove();
            } else alert(data.message);
        })
        .catch(() => alert("Request failed. Please refresh the page and try again."));
    });
});
</script>

<?= $this->endSection() ?>

