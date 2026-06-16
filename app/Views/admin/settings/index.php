<?= $this->extend('layouts/main_admin') ?>
<?= $this->section('content') ?>

<?php
$settingMeta = [
    'fkmp_faculty_id' => [
        'label' => 'Direct Approval Faculty ID',
        'hint' => 'Faculty ID that completes approval at the PIC stage. The legacy setting key is kept for backward compatibility.',
    ],
    'email_from_email' => [
        'label' => 'Outgoing Email Address',
    ],
    'email_from_name' => [
        'label' => 'Outgoing Email Name',
    ],
    'email_protocol' => [
        'label' => 'Email Protocol',
    ],
    'email_mail_path' => [
        'label' => 'Sendmail Path',
    ],
    'email_smtp_host' => [
        'label' => 'SMTP Host',
    ],
    'email_smtp_user' => [
        'label' => 'SMTP Username',
    ],
    'email_smtp_pass' => [
        'label' => 'SMTP Password',
    ],
    'email_smtp_port' => [
        'label' => 'SMTP Port',
    ],
    'email_smtp_crypto' => [
        'label' => 'SMTP Encryption',
    ],
    'email_smtp_helo_host' => [
        'label' => 'SMTP HELO Host',
    ],
];
?>

<div class="settings-page">
    <!-- PAGE HEADER -->
    <div class="slams-page-header">
        <div class="slams-page-header-left">
            <h1 class="slams-page-title">System Settings</h1>
            <p class="slams-page-subtitle">Manage configuration values for Smart Lab Management System</p>
        </div>
        <div class="slams-page-header-actions">
            <a href="/dashboard/admin" class="btn btn-glass btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success alert-glass mb-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                <div class="flex-grow-1"><?= session()->getFlashdata('message') ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('warning')): ?>
        <div class="alert alert-warning alert-glass mb-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                <div class="flex-grow-1"><?= esc(session()->getFlashdata('warning')) ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>

    <!-- VALIDATION ERRORS -->
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger alert-glass mb-4">
            <div class="d-flex">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                <div class="flex-grow-1">
                    <div class="fw-semibold mb-1">Please fix the following errors:</div>
                    <ul class="mb-0 ps-3">
                        <?php foreach (session()->getFlashdata('errors') as $error): ?>
                            <li><?= esc($error) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- GENERAL SETTINGS CARD -->
    <div class="glass-card mb-5">
        <div class="settings-card-header">
            <h5>
                <i class="bi bi-gear-fill"></i>
                General Settings
            </h5>
        </div>

        <div class="card-body p-4">
            <form action="/admin/settings/update" method="post">
                <?= csrf_field() ?>

                <div class="info-box">
                    <p class="d-flex align-items-center mb-0">
                        <i class="bi bi-info-circle-fill me-2" style="color: #3b82f6;"></i>
                        Configure general system parameters and behaviors
                    </p>
                </div>

                <div class="row g-4">
                    <?php foreach ($settings as $key => $row): ?>
                        <?php
                        $meta = $settingMeta[$key] ?? null;
                        $settingLabel = $meta['label'] ?? ucwords(str_replace('_', ' ', $key));
                        $settingHint = $meta['hint'] ?? ($row['hint'] ?? null);
                        ?>
                        <div class="col-md-6">
                            <div class="form-group-glass">
                                <label for="<?= esc($key) ?>">
                                    <?php if ($row['type'] === 'integer'): ?>
                                        <i class="bi bi-123"></i>
                                    <?php elseif ($row['type'] === 'bool'): ?>
                                        <i class="bi bi-toggle-on"></i>
                                    <?php else: ?>
                                        <i class="bi bi-input-cursor-text"></i>
                                    <?php endif; ?>
                                    <?= esc($settingLabel) ?>
                                </label>

                                <?php if ($key === 'email_protocol'): ?>
                                    <?php $currentProtocol = (string) old($key, $row['value']); ?>
                                    <select name="<?= esc($key) ?>" id="<?= esc($key) ?>" class="form-control form-control-glass" required>
                                        <option value="mail" <?= $currentProtocol === 'mail' ? 'selected' : '' ?>>mail</option>
                                        <option value="smtp" <?= $currentProtocol === 'smtp' ? 'selected' : '' ?>>smtp</option>
                                        <option value="sendmail" <?= $currentProtocol === 'sendmail' ? 'selected' : '' ?>>sendmail</option>
                                    </select>

                                <?php elseif ($key === 'email_smtp_crypto'): ?>
                                    <?php $currentCrypto = (string) old($key, $row['value']); ?>
                                    <select name="<?= esc($key) ?>" id="<?= esc($key) ?>" class="form-control form-control-glass">
                                        <option value="" <?= $currentCrypto === '' ? 'selected' : '' ?>>None</option>
                                        <option value="tls" <?= $currentCrypto === 'tls' ? 'selected' : '' ?>>tls</option>
                                        <option value="ssl" <?= $currentCrypto === 'ssl' ? 'selected' : '' ?>>ssl</option>
                                    </select>

                                <?php elseif ($row['type'] === 'integer'): ?>
                                    <input type="number" 
                                           name="<?= esc($key) ?>" 
                                           id="<?= esc($key) ?>"
                                           value="<?= esc(old($key, $row['value'])) ?>"
                                           class="form-control form-control-glass" 
                                           <?= $key === 'email_smtp_port' ? '' : 'required' ?>>

                                <?php elseif ($row['type'] === 'bool'): ?>
                                    <select name="<?= esc($key) ?>" 
                                            id="<?= esc($key) ?>"
                                            class="form-control form-control-glass" 
                                            required>
                                        <option value="1" <?= (old($key, $row['value']) ? 'selected' : '') ?>>Enabled</option>
                                        <option value="0" <?= (!old($key, $row['value']) ? 'selected' : '') ?>>Disabled</option>
                                    </select>

                                <?php else: ?>
                                    <input name="<?= esc($key) ?>" 
                                           id="<?= esc($key) ?>"
                                           value="<?= esc(old($key, $row['value'])) ?>"
                                           class="form-control form-control-glass"
                                           type="<?= $key === 'email_smtp_pass' ? 'password' : 'text' ?>"
                                           <?= $key === 'email_smtp_pass' ? 'autocomplete="new-password"' : '' ?>
                                           <?= in_array($key, ['email_from_email', 'email_from_name', 'email_mail_path', 'email_smtp_host', 'email_smtp_user', 'email_smtp_pass', 'email_smtp_helo_host'], true) ? '' : 'required' ?>>
                                <?php endif; ?>
                                
                                <div class="form-hint">
                                    <?php if (!empty($settingHint)): ?>
                                        <?= esc($settingHint) ?>
                                    <?php elseif ($row['type'] === 'integer'): ?>
                                        Numeric value
                                    <?php elseif ($row['type'] === 'bool'): ?>
                                        Toggle feature on/off
                                    <?php else: ?>
                                        Text configuration
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="border-top pt-4 mt-4">
                    <button type="submit" class="btn btn-primary-glass px-4">
                        <i class="bi bi-save me-2"></i> Save Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div class="glass-card mb-5">
        <div class="settings-card-header">
            <h5>
                <i class="bi bi-phone-vibrate"></i>
                Mobile Push Notifications
            </h5>
        </div>

        <div class="card-body p-4">
            <div class="info-box">
                <p class="d-flex align-items-center mb-0">
                    <i class="bi bi-info-circle-fill me-2" style="color: #3b82f6;"></i>
                    Web push lets signed-in devices receive booking, external request, and maintenance alerts even when the app is not open.
                </p>
            </div>

            <?php $webPush = $webPush ?? ['configured' => false, 'subject' => '', 'hasPublicKey' => false, 'hasPrivateKey' => false, 'defaultTtl' => 1800]; ?>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="form-group-glass h-100">
                        <label><i class="bi bi-shield-check"></i> Status</label>
                        <div class="pt-2">
                            <?php if (! empty($webPush['configured'])): ?>
                                <span class="badge bg-success fs-6">Configured</span>
                                <div class="form-hint mt-2">Push delivery is ready on the server side.</div>
                            <?php else: ?>
                                <span class="badge bg-warning text-dark fs-6">Not Configured</span>
                                <div class="form-hint mt-2">Add VAPID keys to `.env` before users can subscribe.</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group-glass h-100">
                        <label><i class="bi bi-key"></i> Keys</label>
                        <div class="pt-2 small text-muted">
                            <div>Public key: <?= ! empty($webPush['hasPublicKey']) ? 'present' : 'missing' ?></div>
                            <div>Private key: <?= ! empty($webPush['hasPrivateKey']) ? 'present' : 'missing' ?></div>
                            <div class="mt-2">Default TTL: <?= esc((int) ($webPush['defaultTtl'] ?? 1800)) ?> seconds</div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="form-group-glass h-100">
                        <label><i class="bi bi-envelope-paper"></i> Subject</label>
                        <div class="pt-2 small text-muted">
                            <?= ! empty($webPush['subject']) ? esc($webPush['subject']) : 'Not set yet' ?>
                        </div>
                    </div>
                </div>
            </div>

            <div class="border-top pt-4 mt-4">
                <div class="fw-semibold mb-2">Setup</div>
                <div class="small text-muted mb-3">Run the key generator once, copy the output to `.env`, restart the app, then enable push from a signed-in device.</div>
                <pre class="bg-dark text-light rounded-3 p-3 small mb-0"><code>php spark slams:generate-web-push-keys mailto:lab-admin@example.com</code></pre>
            </div>
        </div>
    </div>

    <!-- PREDICTIVE MAINTENANCE MODEL -->
    <div class="glass-card mb-5" id="maintenance-model-card">
        <div class="settings-card-header">
            <h5>
                <i class="bi bi-cpu-fill"></i>
                Predictive Maintenance Model
            </h5>
        </div>

        <div class="card-body p-4">
            <div class="info-box">
                <p class="d-flex align-items-center mb-0">
                    <i class="bi bi-info-circle-fill me-2" style="color: #3b82f6;"></i>
                    Retrains the prediction model using all current maintenance records and booking history. Run this after adding a significant number of new maintenance records to keep risk scores up to date.
                </p>
            </div>

            <div id="model-result" class="mb-3" style="display:none;">
                <div class="row g-3 mt-1" id="model-stats-row"></div>
            </div>

            <button id="trainModelBtn" type="button" class="btn btn-glass">
                <i class="bi bi-arrow-repeat me-2"></i> Retrain Maintenance Model
            </button>
        </div>
    </div>

    <!-- SCHEDULED TASK DEMO TRIGGER -->
    <div class="glass-card mb-5">
        <div class="settings-card-header">
            <h5>
                <i class="bi bi-bell-fill"></i>
                Reminder Checks
            </h5>
        </div>

        <div class="card-body p-4">
            <div class="info-box">
                <p class="d-flex align-items-center mb-0">
                    <i class="bi bi-info-circle-fill me-2" style="color: #3b82f6;"></i>
                    Run booking and maintenance reminder checks manually for demo. Production deployments should still use cron or Windows Task Scheduler.
                </p>
            </div>

            <form action="/admin/settings/run-scheduled-tasks" method="post">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-glass">
                    <i class="bi bi-play-circle me-2"></i> Run Reminder Checks Now
                </button>
            </form>
        </div>
    </div>

    <!-- BOOKING SLOT EDITOR -->
    <div class="glass-card">
        <div class="settings-card-header">
            <h5>
                <i class="bi bi-clock-history"></i>
                Booking Time Slots
            </h5>
        </div>

        <div class="card-body p-4">
            <div class="info-box">
                <p class="d-flex align-items-center mb-0">
                    <i class="bi bi-info-circle-fill me-2" style="color: #3b82f6;"></i>
                    These time slots determine what users may choose when booking a laboratory. You can add, remove, or edit booking slot times.
                </p>
            </div>

            <div class="table-responsive">
                <table class="table table-glass align-middle" id="slotTable">
                    <thead>
                        <tr>
                            <th style="width: 45%;">Start Time</th>
                            <th style="width: 45%;">End Time</th>
                            <th style="width: 10%;" class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($bookingSlots)): ?>
                            <?php foreach ($bookingSlots as $slot): ?>
                                <tr>
                                    <td>
                                        <input type="time" 
                                               class="form-control form-control-glass slot-start"
                                               value="<?= esc(substr($slot['start'], 0, 5)) ?>">
                                    </td>
                                    <td>
                                        <input type="time" 
                                               class="form-control form-control-glass slot-end"
                                               value="<?= esc(substr($slot['end'], 0, 5)) ?>">
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger remove-slot">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="3" class="text-center text-muted py-4">
                                    <i class="bi bi-clock me-2"></i> No time slots configured
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-4">
                <button id="addSlot" class="btn btn-glass" type="button">
                    <i class="bi bi-plus-circle me-2"></i> Add New Slot
                </button>
                
                <div>
                    <button id="saveSlots" class="btn btn-primary-glass px-4">
                        <i class="bi bi-save me-2"></i> Save Time Slots
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JAVASCRIPT -->
<script>
document.addEventListener("DOMContentLoaded", () => {

    // ── Retrain maintenance model ──────────────────────────────────────────
    document.querySelector("#trainModelBtn").addEventListener("click", () => {
        const btn = document.querySelector("#trainModelBtn");
        const resultBox = document.querySelector("#model-result");
        const statsRow = document.querySelector("#model-stats-row");

        btn.innerHTML = '<i class="bi bi-hourglass-split me-2"></i> Training...';
        btn.disabled = true;
        resultBox.style.display = "none";

        fetch("/api/native/admin/settings/train-maintenance-model", {
            method: "POST",
            headers: { "X-Requested-With": "XMLHttpRequest" },
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === "success") {
                const s = data.model_summary ?? {};
                const st = data.asset_stats ?? {};

                if (s.available === false) {
                    statsRow.innerHTML = `
                        <div class="col-12">
                            <div class="alert alert-warning border-0 mb-0">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                                <strong>Not enough maintenance history to learn from yet.</strong>
                                Risk scores are still shown across the system, but they are based on general
                                guidelines rather than your lab's specific history. Add more maintenance records
                                and retrain to enable learned predictions.
                            </div>
                        </div>
                    `;
                } else {
                    const trainedAt = s.trained_at
                        ? new Date(s.trained_at).toLocaleString("en-MY")
                        : "just now";
                    const samples  = s.dataset?.samples_total ?? "-";
                    const precision = s.metrics?.precision != null
                        ? (s.metrics.precision * 100).toFixed(1) + "%"
                        : "-";
                    const recall = s.metrics?.recall != null
                        ? (s.metrics.recall * 100).toFixed(1) + "%"
                        : "-";
                    const f1 = s.metrics?.f1 != null
                        ? (s.metrics.f1 * 100).toFixed(1) + "%"
                        : "-";

                    statsRow.innerHTML = `
                        <div class="col-6 col-md-3">
                            <div class="form-group-glass text-center">
                                <div class="fs-5 fw-bold" style="color:var(--slams-primary)">${st.high_risk ?? 0}</div>
                                <div class="form-hint">High Risk Assets</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="form-group-glass text-center">
                                <div class="fs-5 fw-bold" style="color:var(--slams-primary)">${st.due_soon ?? 0}</div>
                                <div class="form-hint">Due Soon</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="form-group-glass text-center">
                                <div class="fs-5 fw-bold" style="color:var(--slams-primary)">${samples}</div>
                                <div class="form-hint">Records Used</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="form-group-glass text-center">
                                <div class="fs-5 fw-bold" style="color:var(--slams-primary)">${f1}</div>
                                <div class="form-hint">Reliability Score</div>
                                <div class="form-hint" style="font-size:0.72rem;">Overall balance between accuracy and coverage</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="form-group-glass text-center">
                                <div class="fs-5 fw-bold" style="color:var(--slams-primary)">${precision}</div>
                                <div class="form-hint">Alert Accuracy</div>
                                <div class="form-hint" style="font-size:0.72rem;">When it flags an asset, it is correct this often</div>
                            </div>
                        </div>
                        <div class="col-6 col-md-6">
                            <div class="form-group-glass text-center">
                                <div class="fs-5 fw-bold" style="color:var(--slams-primary)">${recall}</div>
                                <div class="form-hint">Detection Rate</div>
                                <div class="form-hint" style="font-size:0.72rem;">Out of all assets that truly needed attention, the system caught this many</div>
                            </div>
                        </div>
                        <div class="col-12">
                            <div class="small text-success fw-semibold">
                                <i class="bi bi-check-circle-fill me-1"></i>
                                Scores are now based on your lab's actual maintenance history. Last trained: ${trainedAt}
                            </div>
                        </div>
                    `;
                }

                resultBox.style.display = "block";
                btn.innerHTML = '<i class="bi bi-check-circle me-2"></i> Retrain Again';
            } else {
                alert("Training failed: " + (data.message ?? "Unknown error"));
                btn.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i> Retrain Maintenance Model';
            }
        })
        .catch(() => {
            alert("An unexpected error occurred during model training.");
            btn.innerHTML = '<i class="bi bi-arrow-repeat me-2"></i> Retrain Maintenance Model';
        })
        .finally(() => {
            btn.disabled = false;
        });
    });

    // ── Booking slots ──────────────────────────────────────────────────────
    const slotTable = document.querySelector("#slotTable tbody");

    // Add a new empty row
    document.querySelector("#addSlot").addEventListener("click", () => {
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>
                <input type="time" class="form-control form-control-glass slot-start">
            </td>
            <td>
                <input type="time" class="form-control form-control-glass slot-end">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger remove-slot">
                    <i class="bi bi-trash"></i>
                </button>
            </td>`;
        slotTable.appendChild(row);
        
        // Remove the "no slots" message if present
        const emptyMessage = slotTable.querySelector('.text-muted');
        if (emptyMessage && emptyMessage.parentElement.tagName === 'TR') {
            emptyMessage.parentElement.remove();
        }
    });

    // Remove a row
    slotTable.addEventListener("click", (e) => {
        if (e.target.closest(".remove-slot")) {
            const row = e.target.closest("tr");
            row.remove();
            
            // Add "no slots" message if table is empty
            if (slotTable.children.length === 0) {
                const emptyRow = document.createElement("tr");
                emptyRow.innerHTML = `
                    <td colspan="3" class="text-center text-muted py-4">
                        <i class="bi bi-clock me-2"></i> No time slots configured
                    </td>`;
                slotTable.appendChild(emptyRow);
            }
        }
    });

    // Save slots via AJAX
    document.querySelector("#saveSlots").addEventListener("click", () => {
        const rows = slotTable.querySelectorAll("tr");
        const slots = [];

        rows.forEach(row => {
            const startInput = row.querySelector(".slot-start");
            const endInput = row.querySelector(".slot-end");
            
            // Skip rows that are the empty message
            if (startInput && endInput) {
                const start = startInput.value;
                const end = endInput.value;

                if (start && end) {
                    slots.push({ start, end });
                }
            }
        });

        // Validate at least one slot
        if (slots.length === 0) {
            alert("Please add at least one time slot.");
            return;
        }

        // Validate time order
        for (const slot of slots) {
            if (slot.start >= slot.end) {
                alert("Start time must be before end time for all slots.");
                return;
            }
        }

        // Validate no overlaps
        const sorted = [...slots].sort((a, b) => {
            if (a.start === b.start) return a.end.localeCompare(b.end);
            return a.start.localeCompare(b.start);
        });

        for (let i = 1; i < sorted.length; i++) {
            const prev = sorted[i - 1];
            const cur = sorted[i];
            if (cur.start < prev.end) {
                alert("Time slots cannot overlap.");
                return;
            }
        }

        // Show loading state
        const saveButton = document.querySelector("#saveSlots");
        const originalText = saveButton.innerHTML; 
        saveButton.innerHTML = '<i class="bi bi-hourglass-split me-2"></i>Saving...';
        saveButton.disabled = true;

        // POST to slot-saving endpoint
        fetch("/admin/settings/save-slots", {
            method: "POST",
            headers: { 
                "X-Requested-With": "XMLHttpRequest",
                "Content-Type": "application/x-www-form-urlencoded"
            },
            body: new URLSearchParams({
                slots: JSON.stringify(slots),
                "<?= csrf_token() ?>": "<?= csrf_hash() ?>",
            })
        })
        .then(r => r.json())
        .then(data => {
            if (data.status === "success") {
                // Show success message
                const alertDiv = document.createElement("div");
                alertDiv.className = "alert alert-success alert-glass mt-4";
                alertDiv.innerHTML = `
                    <div class="d-flex align-items-center">
                        <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                        <div class="flex-grow-1">Booking slots updated successfully!</div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                `;
                
                // Insert after the booking slots card
                document.querySelector(".glass-card:last-child").after(alertDiv);
                
                // Auto-remove after 5 seconds
                setTimeout(() => {
                    alertDiv.remove();
                }, 5000);
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(() => {
            alert("An unexpected error occurred while saving.");
        })
        .finally(() => {
            // Restore button state
            saveButton.innerHTML = originalText;
            saveButton.disabled = false;
        });
    });

});
</script>

<?= $this->endSection() ?>
