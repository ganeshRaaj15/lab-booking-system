<?= $this->extend('layouts/main_admin') ?>
<?= $this->section('content') ?>

<style>
    .settings-page {
        --card-radius: 16px;
        --card-padding: 24px;
        --transition-speed: 0.3s;
    }

    /* Glass Card Styling */
    .glass-card {
        background: linear-gradient(135deg,
            rgba(255, 255, 255, 0.95),
            rgba(255, 255, 255, 0.98)
        );
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: var(--card-radius);
        border: 1px solid rgba(59, 130, 246, 0.15);
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.08);
        transition: all var(--transition-speed) ease;
        overflow: hidden;
    }

    /* Form group styling */
    .form-group-glass {
        margin-bottom: 1.5rem;
    }

    .form-group-glass label {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 0.5rem;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .form-group-glass label i {
        color: #3b82f6;
    }

    .form-control-glass {
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(59, 130, 246, 0.2);
        border-radius: 10px;
        padding: 0.75rem 1rem;
        transition: all 0.3s ease;
        color: #1e293b;
    }

    .form-control-glass:focus {
        background: white;
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1);
    }

    .form-control-glass::placeholder {
        color: #94a3b8;
    }

    /* Buttons */
    .btn-glass {
        padding: 10px 24px;
        border-radius: 10px;
        border: 1px solid rgba(59, 130, 246, 0.2);
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .btn-glass:hover {
        background: rgba(59, 130, 246, 0.2);
        border-color: rgba(59, 130, 246, 0.4);
        transform: translateY(-2px);
    }

    .btn-primary-glass {
        background: linear-gradient(135deg, #3b82f6, #1e40af);
        color: white;
        border: none;
    }

    .btn-primary-glass:hover {
        background: linear-gradient(135deg, #1e40af, #1e3a8a);
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
    }

    /* Dashboard header */
    .dashboard-header {
        margin-bottom: 2rem;
    }

    .dashboard-header h1 {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1e293b;
        margin-bottom: 0.5rem;
    }

    .dashboard-header p {
        color: #64748b;
        font-size: 0.95rem;
    }

    /* Section titles */
    .section-title {
        font-size: 1.1rem;
        font-weight: 600;
        color: #1e293b;
        padding-bottom: 0.5rem;
        border-bottom: 2px solid rgba(59, 130, 246, 0.1);
        margin-bottom: 1.5rem;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .section-title i {
        color: #3b82f6;
    }

    /* Alerts */
    .alert-glass {
        background: linear-gradient(135deg,
            rgba(255, 255, 255, 0.95),
            rgba(255, 255, 255, 0.98)
        );
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border-radius: 12px;
        border: 1px solid;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.05);
    }

    .alert-glass.alert-success {
        border-color: rgba(34, 197, 94, 0.2);
        background: linear-gradient(135deg,
            rgba(240, 253, 244, 0.95),
            rgba(220, 252, 231, 0.98)
        );
    }

    .alert-glass.alert-danger {
        border-color: rgba(239, 68, 68, 0.2);
        background: linear-gradient(135deg,
            rgba(254, 242, 242, 0.95),
            rgba(254, 226, 226, 0.98)
        );
    }

    /* Table styling */
    .table-glass {
        background: rgba(255, 255, 255, 0.9);
        border-radius: 12px;
        overflow: hidden;
        border: 1px solid rgba(59, 130, 246, 0.1);
    }

    .table-glass thead {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(30, 64, 175, 0.05));
        border-bottom: 2px solid rgba(59, 130, 246, 0.1);
    }

    .table-glass th {
        font-weight: 600;
        color: #1e293b;
        padding: 1rem;
    }

    .table-glass td {
        padding: 1rem;
        border-color: rgba(59, 130, 246, 0.1);
    }

    .table-glass tbody tr:hover {
        background: rgba(59, 130, 246, 0.03);
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .glass-card {
            padding: 1rem !important;
        }
    }

    /* Form hints */
    .form-hint {
        font-size: 0.875rem;
        color: #64748b;
        margin-top: 0.25rem;
    }

    /* Settings card header */
    .settings-card-header {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.08), rgba(30, 64, 175, 0.05));
        border-bottom: 1px solid rgba(59, 130, 246, 0.1);
        padding: 1.25rem 1.5rem;
    }

    .settings-card-header h5 {
        margin: 0;
        color: #1e293b;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .settings-card-header i {
        color: #3b82f6;
    }

    /* Info box */
    .info-box {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(30, 64, 175, 0.03));
        border-radius: 12px;
        padding: 1.5rem;
        border: 1px solid rgba(59, 130, 246, 0.1);
        margin-bottom: 1.5rem;
    }

    .info-box p {
        margin: 0;
        color: #475569;
        font-size: 0.95rem;
    }
</style>

<div class="settings-page">
    <!-- PAGE HEADER -->
    <div class="dashboard-header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h1>System Settings</h1>
                <p>Manage configuration values for Smart Lab Management System</p>
            </div>
            <a href="/dashboard/admin" class="btn btn-outline-secondary">
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
                                    <?= ucwords(str_replace('_', ' ', $key)) ?>
                                </label>

                                <?php if ($row['type'] === 'integer'): ?>
                                    <input type="number" 
                                           name="<?= esc($key) ?>" 
                                           id="<?= esc($key) ?>"
                                           value="<?= esc($row['value']) ?>"
                                           class="form-control form-control-glass" 
                                           required>

                                <?php elseif ($row['type'] === 'bool'): ?>
                                    <select name="<?= esc($key) ?>" 
                                            id="<?= esc($key) ?>"
                                            class="form-control form-control-glass" 
                                            required>
                                        <option value="1" <?= ($row['value'] ? 'selected' : '') ?>>Enabled</option>
                                        <option value="0" <?= (!$row['value'] ? 'selected' : '') ?>>Disabled</option>
                                    </select>

                                <?php else: ?>
                                    <input type="text" 
                                           name="<?= esc($key) ?>" 
                                           id="<?= esc($key) ?>"
                                           value="<?= esc($row['value']) ?>"
                                           class="form-control form-control-glass" 
                                           required>
                                <?php endif; ?>
                                
                                <div class="form-hint">
                                    <?php if ($row['type'] === 'integer'): ?>
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
            row.style.transition = 'all 0.3s ease';
            row.style.opacity = '0';
            row.style.transform = 'translateX(-20px)';
            
            setTimeout(() => {
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
            }, 300);
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

    // Add some visual feedback for inputs
    document.addEventListener('focusin', (e) => {
        if (e.target.classList.contains('form-control-glass')) {
            e.target.parentElement.style.transform = 'translateY(-2px)';
        }
    });

    document.addEventListener('focusout', (e) => {
        if (e.target.classList.contains('form-control-glass')) {
            e.target.parentElement.style.transform = 'translateY(0)';
        }
    });
});
</script>

<?= $this->endSection() ?>
