<?php
/** @var array $stats */
/** @var array $requests */
/** @var array $labs */
/** @var array $filters */
/** @var array $statusLabels */
/** @var mixed $requestModel */
?>
<?= $this->extend('layouts/main_user') ?>

<?= $this->section('content') ?>

<?php
$requests     = $requests ?? [];
$labs         = $labs ?? [];
$filters      = $filters ?? ['q' => '', 'status' => '', 'lab_id' => 0];
$stats        = $stats ?? [];
$statusLabels = $statusLabels ?? [];
$requestModel = $requestModel ?? null;
?>

<div class="dashboard-header">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-0">Lab Access Requests</h2>
            <p class="text-muted small mb-0">Request access to a FKMP laboratory. Submit your details, and the lab team will review and get back to you.</p>
        </div>
        <a href="/dashboard/external/request" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> New Request
        </a>
    </div>
</div>

<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success"><?= esc(session()->getFlashdata('success')) ?></div>
<?php endif; ?>
<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="card border-0 shadow-sm mb-4" style="background: linear-gradient(135deg, #eff6ff, #f0fdf4); border-left: 4px solid #2563eb !important;">
    <div class="card-body">
        <h6 class="fw-bold text-primary mb-2"><i class="bi bi-info-circle me-2"></i>How your request is reviewed</h6>
        <div class="row g-2 text-center">
            <div class="col-md-3">
                <div class="p-2 bg-white rounded-3 border h-100">
                    <i class="bi bi-send text-primary fs-5 mb-1 d-block"></i>
                    <div class="fw-semibold small">1. You Submit</div>
                    <div class="text-muted" style="font-size:0.78rem;">Fill in your details and preferred schedule, then click <strong>New Request</strong>.</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-2 bg-white rounded-3 border h-100">
                    <i class="bi bi-person-check text-primary fs-5 mb-1 d-block"></i>
                    <div class="fw-semibold small">2. Lab Supervisor Reviews</div>
                    <div class="text-muted" style="font-size:0.78rem;">The Person-in-Charge (PIC) of the lab checks your request first.</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-2 bg-white rounded-3 border h-100">
                    <i class="bi bi-person-workspace text-primary fs-5 mb-1 d-block"></i>
                    <div class="fw-semibold small">3. Lab Manager Reviews</div>
                    <div class="text-muted" style="font-size:0.78rem;">If the PIC approves, the Lab Manager gives the final decision.</div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="p-2 bg-white rounded-3 border h-100">
                    <i class="bi bi-bell text-primary fs-5 mb-1 d-block"></i>
                    <div class="fw-semibold small">4. You're Notified</div>
                    <div class="text-muted" style="font-size:0.78rem;">You'll receive an email and notification at every step. If more info is needed, you can update your request here.</div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-2">
        <div class="card shadow-sm border-0"><div class="card-body"><div class="small text-muted">Total Requests</div><div class="fs-3 fw-bold"><?= esc((int) ($stats['total'] ?? 0)) ?></div></div></div>
    </div>
    <div class="col-md-2">
        <div class="card shadow-sm border-0"><div class="card-body"><div class="small text-muted">With Supervisor</div><div class="fs-3 fw-bold"><?= esc((int) ($stats['pending_pic_approval'] ?? 0)) ?></div></div></div>
    </div>
    <div class="col-md-2">
        <div class="card shadow-sm border-0"><div class="card-body"><div class="small text-muted">With Manager</div><div class="fs-3 fw-bold"><?= esc((int) ($stats['pending_manager_approval'] ?? 0)) ?></div></div></div>
    </div>
    <div class="col-md-2">
        <div class="card shadow-sm border-0"><div class="card-body"><div class="small text-muted">Action Required</div><div class="fs-3 fw-bold text-warning"><?= esc((int) ($stats['needs_information'] ?? 0)) ?></div></div></div>
    </div>
    <div class="col-md-2">
        <div class="card shadow-sm border-0"><div class="card-body"><div class="small text-muted">Approved</div><div class="fs-3 fw-bold text-success"><?= esc((int) ($stats['approved_for_scheduling'] ?? 0)) ?></div></div></div>
    </div>
    <div class="col-md-2">
        <div class="card shadow-sm border-0"><div class="card-body"><div class="small text-muted">Rejected</div><div class="fs-3 fw-bold text-danger"><?= esc((int) ($stats['rejected'] ?? 0)) ?></div></div></div>
    </div>
</div>

<div class="card shadow-sm border-0 mb-4">
    <div class="card-body">
        <form method="get" action="/dashboard/external" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label class="form-label small text-muted">Search</label>
                <input type="text" name="q" class="form-control" value="<?= esc($filters['q']) ?>" placeholder="Lab, organization, contact, or purpose">
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Status</label>
                <select name="status" class="form-select">
                    <option value="">All statuses</option>
                    <?php foreach ($statusLabels as $statusKey => $statusLabel): ?>
                        <option value="<?= esc($statusKey) ?>" <?= $filters['status'] === $statusKey ? 'selected' : '' ?>><?= esc($statusLabel) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted">Laboratory</label>
                <select name="lab_id" class="form-select">
                    <option value="0">All laboratories</option>
                    <?php foreach (($labs ?? []) as $lab): ?>
                        <option value="<?= esc($lab['id']) ?>" <?= (int) $filters['lab_id'] === (int) $lab['id'] ? 'selected' : '' ?>>
                            <?= esc($lab['name']) ?><?= !empty($lab['room']) ? ' (' . esc($lab['room']) . ')' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">Filter</button>
                <a href="/dashboard/external" class="btn btn-outline-secondary">Reset</a>
            </div>
        </form>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white">
        <h5 class="fw-semibold text-primary mb-0">My Requests</h5>
    </div>
    <div class="card-body">
        <?php if (empty($requests)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inboxes fs-1 mb-3"></i>
                <p class="mb-1 fw-semibold">You haven't submitted any requests yet.</p>
                <p class="small mb-1">To use an FKMP laboratory, click <strong>New Request</strong> above and fill in your details.</p>
                <p class="small mb-3">Once submitted, you can track the review progress right here.</p>
                <a href="/dashboard/external/request" class="btn btn-primary btn-sm">
                    <i class="bi bi-plus-circle me-1"></i> Submit Your First Request
                </a>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Request</th>
                            <th>Laboratory</th>
                            <th>Preferred Schedule</th>
                            <th>Status</th>
                            <th>Review Notes</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <?php
                            $status = (string) ($request['status'] ?? 'pending_pic_approval');
                            $badgeClass = $requestModel ? $requestModel->statusBadgeClass($status) : 'secondary';
                            $canEdit = $requestModel ? $requestModel->canUserEdit($request) : false;
                            $latestNote = $requestModel ? $requestModel->latestRequesterNote($request) : (string) ($request['review_notes'] ?? '');
                            $schedule = esc($request['preferred_date'] ?? '-');
                            if (!empty($request['preferred_start_time']) && !empty($request['preferred_end_time'])) {
                                $schedule .= '<br><small class="text-muted">' . esc(substr((string) $request['preferred_start_time'], 0, 5)) . ' - ' . esc(substr((string) $request['preferred_end_time'], 0, 5)) . '</small>';
                            }
                            ?>
                            <tr>
                                <td>
                                    <div class="fw-semibold"><?= esc($request['organization_name'] ?? '-') ?></div>
                                    <div class="small text-muted"><?= esc($request['contact_name'] ?? '-') ?>, <?= esc($request['participant_count'] ?? 0) ?> participant(s)</div>
                                    <div class="small text-muted">Submitted <?= esc(!empty($request['created_at']) ? date('d-m-Y H:i', strtotime((string) $request['created_at'])) : '-') ?></div>
                                    <div class="small text-muted">Current stage: <?= esc($requestModel ? $requestModel->stageLabel($requestModel->currentApprovalStage($request)) : ucfirst((string) ($request['current_approval_stage'] ?? 'pic'))) ?></div>
                                </td>
                                <td>
                                    <div class="fw-semibold"><?= esc($request['lab_name'] ?? '-') ?></div>
                                    <div class="small text-muted"><?= esc($request['lab_room'] ?? '') ?></div>
                                </td>
                                <td><?= $schedule ?></td>
                                <td>
                                    <span class="badge bg-<?= esc($badgeClass) ?>"><?= esc($statusLabels[$status] ?? ucwords(str_replace('_', ' ', $status))) ?></span>
                                </td>
                                <td>
                                    <?php if ($latestNote !== ''): ?>
                                        <div class="small"><?= nl2br(esc($latestNote)) ?></div>
                                    <?php else: ?>
                                        <span class="text-muted small">No notes yet.</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end">
                                    <?php if ($canEdit): ?>
                                        <a href="/dashboard/external/request/edit/<?= esc($request['id']) ?>" class="btn btn-sm btn-outline-primary">Update</a>
                                    <?php else: ?>
                                        <span class="text-muted small">Locked</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>
</div>

<?= $this->endSection() ?>
