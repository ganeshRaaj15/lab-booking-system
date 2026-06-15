<?= $this->extend('layouts/main_user') ?>
<?= $this->section('content') ?>

<?php
$bookingId  = (int) $booking['id'];
$labId      = (int) $booking['lab_id'];
$serviceId  = (int) ($booking['service_id'] ?? 0);
$assetIds   = implode(',', array_column($assets ?? [], 'asset_id'));
$dashUrl    = auth()->user()->inGroup('staff') ? '/dashboard/staff' : '/dashboard/student';
$requiresSupervisor = auth()->user()->inGroup('student') && ! auth()->user()->inGroup('staff');
$currentPdf = basename((string) ($booking['pdf_path'] ?? ''));
?>

<div class="dashboard-header">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold text-primary mb-0">Edit Booking</h2>
            <p class="text-muted small mb-0">Update your booking details. Lab and service cannot be changed.</p>
        </div>
        <a href="<?= esc($dashUrl) ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>
</div>

<div class="card mb-4 border-0 shadow-sm">
    <div class="card-body d-flex align-items-center gap-3 py-3">
        <i class="bi bi-building fs-4 text-primary"></i>
        <div>
            <div class="fw-semibold">
                <?= esc($booking['lab_name'] ?? '') ?>
                <?= !empty($booking['lab_room']) ? '&mdash; Room ' . esc($booking['lab_room']) : '' ?>
            </div>
            <?php if (!empty($booking['service_name'])): ?>
                <div class="small text-muted"><?= esc($booking['service_name']) ?></div>
            <?php endif; ?>
        </div>
        <span class="ms-auto badge bg-secondary px-3 py-2">Read-only</span>
    </div>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">

        <div class="mb-4">
            <div class="progress" style="height: 8px;">
                <div id="wizardProgress" class="progress-bar bg-primary" style="width: <?= $requiresSupervisor ? '33' : '50' ?>%;"></div>
            </div>
            <div id="wizardStepLabel" class="text-center mt-2 small fw-semibold text-primary">
                Step 1 of <?= $requiresSupervisor ? '3' : '2' ?> - Applicant Details
            </div>
        </div>

        <?php if (!empty($booking['approved_by_pic'])): ?>
            <div class="alert alert-info">
                Editing this pending booking will send it back through the approval flow from the PIC stage.
            </div>
        <?php endif; ?>

        <div id="wizardErrorArea"></div>

        <form id="editBookingForm"
              action="<?= esc($dashUrl) ?>/booking-edit/<?= $bookingId ?>"
              method="post"
              enctype="multipart/form-data">
            <?= csrf_field() ?>

            <div id="wizardViewport" class="wizard-viewport">

                <!-- STEP 1: Applicants -->
                <div id="step1" class="wizard-step">
                    <h5 class="fw-semibold mb-3">Applicant Information</h5>

                    <div id="applicantContainer">
                        <?php foreach ($applicants as $i => $a): ?>
                        <div class="card p-3 mb-3 position-relative applicant-block">
                            <button class="btn btn-sm btn-danger remove-applicant-btn"
                                    type="button"
                                    style="position:absolute; top:10px; right:10px; <?= $i === 0 ? 'display:none;' : '' ?>">
                                <i class="bi bi-x"></i>
                            </button>
                            <h6 class="fw-semibold small mb-3 applicant-title">Applicant <?= $i + 1 ?></h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="small mb-1">Name *</label>
                                    <input type="text" name="applicant_name[]"
                                           class="form-control"
                                           value="<?= esc($a['name'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="small mb-1">Matric / Staff ID *</label>
                                    <input type="text" name="applicant_id[]"
                                           class="form-control"
                                           value="<?= esc($a['matric_id'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="small mb-1">Email *</label>
                                    <input type="email" name="applicant_email[]"
                                           class="form-control"
                                           value="<?= esc($a['email'] ?? '') ?>">
                                </div>
                                <div class="col-md-6">
                                    <label class="small mb-1">Phone *</label>
                                    <input type="text" name="applicant_phone[]"
                                           class="form-control"
                                           value="<?= esc($a['phone'] ?? '') ?>">
                                </div>
                                <div class="col-12">
                                    <label class="small mb-1">Faculty / Organisation *</label>
                                    <select name="applicant_faculty[]" class="form-control">
                                        <option value="">Select Faculty</option>
                                        <?php foreach ($faculties as $f): ?>
                                            <option value="<?= esc($f['id']) ?>"
                                                <?= (string) ($a['faculty'] ?? '') === (string) $f['id'] ? 'selected' : '' ?>>
                                                <?= esc($f['code']) ?> - <?= esc($f['name_en']) ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <button type="button" id="addApplicant" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-person-plus me-1"></i> Add Applicant
                    </button>
                </div>

                <!-- STEP 2: Date & Session -->
                <div id="step2" class="wizard-step d-none">
                    <h5 class="fw-semibold mb-3"><?= $requiresSupervisor ? 'Date &amp; Session' : 'Date, Session &amp; Documents' ?></h5>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="small mb-1">Date *</label>
                            <input type="date" id="selectedDate" name="date"
                                   class="form-control"
                                   min="<?= esc(date('Y-m-d')) ?>"
                                   value="<?= esc($booking['date'] ?? '') ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="small mb-1">Selected Session *</label>
                            <input type="text" id="selectedSessionLabel"
                                   class="form-control"
                                   placeholder="Choose one of the available sessions below"
                                   readonly>
                        </div>
                    </div>

                    <div id="daySlotChoices" class="mb-2"></div>
                    <div id="slotStatusMsg"></div>
                    <input type="hidden" id="startTime" name="start_time"
                           value="<?= esc(substr((string) ($booking['start_time'] ?? ''), 0, 5)) ?>">
                    <input type="hidden" id="endTime"   name="end_time"
                           value="<?= esc(substr((string) ($booking['end_time']   ?? ''), 0, 5)) ?>">

                    <?php if (! $requiresSupervisor): ?>
                    <div class="mt-4">
                        <label class="small mb-1 fw-semibold">Activity Description *</label>
                        <textarea name="activity" rows="4" class="form-control mb-3"><?= esc($booking['activity'] ?? '') ?></textarea>

                        <label class="small mb-1 fw-semibold">Upload PDF (SOP/SWP/SDS)</label>
                        <?php if ($currentPdf): ?>
                            <div class="small text-muted mb-2">
                                <i class="bi bi-file-earmark-pdf me-1 text-danger"></i>
                                Current: <?= esc($currentPdf) ?> &mdash; upload a new file to replace it.
                            </div>
                        <?php endif; ?>
                        <input type="file" name="pdf" accept=".pdf" class="form-control">
                        <div class="form-text">Leave blank to keep the existing PDF.</div>
                    </div>
                    <?php endif; ?>
                </div>

                <?php if ($requiresSupervisor): ?>
                <!-- STEP 3: Activity & PDF -->
                <div id="step3" class="wizard-step d-none">
                    <h5 class="fw-semibold mb-3">Activity &amp; Supervisor</h5>

                    <div class="card p-3 mb-3">
                        <h6 class="fw-semibold small mb-2">Supervisor<?= $requiresSupervisor ? ' *' : ' (Students Only)' ?></h6>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="small mb-1">Supervisor Name<?= $requiresSupervisor ? ' *' : '' ?></label>
                                <input type="text" name="supervisor_name" class="form-control<?= $requiresSupervisor ? ' student-supervisor-field' : '' ?>"
                                       <?= $requiresSupervisor ? 'required' : '' ?>
                                       value="<?= esc($booking['supervisor_name'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Supervisor Email<?= $requiresSupervisor ? ' *' : '' ?></label>
                                <input type="email" name="supervisor_email" class="form-control<?= $requiresSupervisor ? ' student-supervisor-field' : '' ?>"
                                       <?= $requiresSupervisor ? 'required' : '' ?>
                                       value="<?= esc($booking['supervisor_email'] ?? '') ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="small mb-1">Supervisor Phone<?= $requiresSupervisor ? ' *' : '' ?></label>
                                <input type="text" name="supervisor_phone" class="form-control<?= $requiresSupervisor ? ' student-supervisor-field' : '' ?>"
                                       <?= $requiresSupervisor ? 'required' : '' ?>
                                       value="<?= esc($booking['supervisor_phone'] ?? '') ?>">
                            </div>
                        </div>
                    </div>

                    <label class="small mb-1 fw-semibold">Activity Description *</label>
                    <textarea name="activity" rows="4" class="form-control mb-3"><?= esc($booking['activity'] ?? '') ?></textarea>

                    <label class="small mb-1 fw-semibold">Upload PDF (SOP/SWP/SDS)</label>
                    <?php if ($currentPdf): ?>
                        <div class="small text-muted mb-2">
                            <i class="bi bi-file-earmark-pdf me-1 text-danger"></i>
                            Current: <?= esc($currentPdf) ?> &mdash; upload a new file to replace it.
                        </div>
                    <?php endif; ?>
                    <input type="file" name="pdf" accept=".pdf" class="form-control">
                    <div class="form-text">Leave blank to keep the existing PDF.</div>
                </div>
                <?php endif; ?>

            </div><!-- end wizardViewport -->
        </form>

        <div class="d-flex justify-content-between mt-4">
            <button id="prevBtn" class="btn btn-outline-secondary d-none" type="button">
                <i class="bi bi-arrow-left me-1"></i> Back
            </button>
            <div class="ms-auto d-flex gap-2">
                <button id="nextBtn" class="btn btn-primary" type="button">
                    Next <i class="bi bi-arrow-right ms-1"></i>
                </button>
                <button id="submitBtn" class="btn btn-success d-none" form="editBookingForm">
                    <i class="bi bi-check-circle me-1"></i> Save Changes
                </button>
            </div>
        </div>

    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    let currentStep = 1;
    let isAnimating = false;

    const form        = document.getElementById("editBookingForm");
    const prevBtn     = document.getElementById("prevBtn");
    const nextBtn     = document.getElementById("nextBtn");
    const submitBtn   = document.getElementById("submitBtn");
    const wizardLabel = document.getElementById("wizardStepLabel");
    const wizardProg  = document.getElementById("wizardProgress");
    const errorArea   = document.getElementById("wizardErrorArea");

    const dateField         = document.getElementById("selectedDate");
    const startField        = document.getElementById("startTime");
    const endField          = document.getElementById("endTime");
    const sessionLabelField = document.getElementById("selectedSessionLabel");
    const daySlotsEl        = document.getElementById("daySlotChoices");

    const labId     = <?= json_encode((string) $labId) ?>;
    const serviceId = <?= json_encode((string) $serviceId) ?>;
    const assetStr  = <?= json_encode($assetIds) ?>;
    const bookingId = <?= json_encode((string) $bookingId) ?>;
    const totalSteps = <?= $requiresSupervisor ? '3' : '2' ?>;
    const finalStep = totalSteps;

    const labels = totalSteps === 3
        ? {
            1: "Step 1 of 3 - Applicant Details",
            2: "Step 2 of 3 - Date & Session",
            3: "Step 3 of 3 - Activity & Supervisor"
        }
        : {
            1: "Step 1 of 2 - Applicant Details",
            2: "Step 2 of 2 - Date, Session & Documents"
        };
    const widths = totalSteps === 3
        ? { 1: 33, 2: 66, 3: 100 }
        : { 1: 50, 2: 100 };

    function showError(msg) {
        errorArea.innerHTML = `<div class="alert alert-danger small mb-2"><i class="bi bi-exclamation-triangle me-1"></i>${msg}</div>`;
    }
    function clearError() { errorArea.innerHTML = ""; }

    function formatDateLabel(dateStr) {
        const d = new Date(dateStr + "T00:00:00");
        return d.toLocaleDateString("en-US", { weekday: "short", month: "short", day: "numeric" });
    }

    function renderSelectedSessionLabel() {
        if (!sessionLabelField) return;
        if (!dateField.value || !startField.value || !endField.value) {
            sessionLabelField.value = "";
            return;
        }
        sessionLabelField.value = `${formatDateLabel(dateField.value)} | ${startField.value}-${endField.value}`;
    }

    function setSelectedSession(start, end) {
        startField.value = start || "";
        endField.value   = end   || "";
        renderSelectedSessionLabel();
    }

    async function refreshDaySlots() {
        if (!daySlotsEl || !dateField.value) {
            if (daySlotsEl) daySlotsEl.innerHTML = "";
            return;
        }

        daySlotsEl.innerHTML = `
            <div class="alert alert-info small mb-2">
                <i class="bi bi-hourglass-split me-1"></i>
                Loading booking sessions for the selected date...
            </div>`;

        try {
            const url = `/api/bookings/day-with-assets/${encodeURIComponent(labId)}/${encodeURIComponent(dateField.value)}`
                + `?service_id=${encodeURIComponent(serviceId)}`
                + `&assets=${encodeURIComponent(assetStr)}`
                + `&exclude_booking_id=${encodeURIComponent(bookingId)}`;

            const res   = await fetch(url);
            const data  = await res.json();
            const slots = data.slots || [];

            if (!slots.length) {
                daySlotsEl.innerHTML = `
                    <div class="alert alert-warning small mb-2">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        No booking sessions are available for this date.
                    </div>`;
                return;
            }

            const currentStart = startField.value;
            const currentEnd   = endField.value;

            const buttons = slots.map(slot => {
                const selected    = currentStart === slot.start && currentEnd === slot.end;
                const buttonClass = slot.can_book
                    ? (selected ? "btn-primary" : "btn-outline-success")
                    : "btn-outline-secondary";
                const disabled = slot.can_book ? "" : "disabled";
                const caption  = slot.can_book ? `${slot.start} - ${slot.end}` : (slot.reason || "Unavailable");

                return `
                    <button type="button"
                            class="btn ${buttonClass} btn-sm me-2 mb-2 day-slot-btn"
                            data-start="${slot.start}"
                            data-end="${slot.end}"
                            ${disabled}>
                        <div class="fw-semibold">${slot.label || `${slot.start}-${slot.end}`}</div>
                        <div class="small">${caption}</div>
                    </button>`;
            }).join("");

            daySlotsEl.innerHTML = `
                <div class="small fw-semibold text-muted mb-2">Available sessions for ${formatDateLabel(dateField.value)}:</div>
                ${buttons}`;

        } catch {
            daySlotsEl.innerHTML = `
                <div class="alert alert-warning small mb-2">
                    <i class="bi bi-exclamation-triangle me-1"></i>
                    Unable to load booking sessions for this date right now.
                </div>`;
        }
    }

    function updateWizardNav(step) {
        wizardLabel.textContent = labels[step];
        wizardProg.style.width  = widths[step] + "%";
        prevBtn.classList.toggle("d-none", step === 1);
        nextBtn.classList.toggle("d-none", step === finalStep);
        submitBtn.classList.toggle("d-none", step !== finalStep);
        clearError();
    }

    function onStepShown(step) {
        if (step === 2) {
            refreshDaySlots();
            renderSelectedSessionLabel();
        }
    }

    function showStep(step) {
        document.querySelectorAll(".wizard-step").forEach(s => s.classList.add("d-none"));
        const stepEl = document.getElementById("step" + step);
        if (stepEl) stepEl.classList.remove("d-none");
        updateWizardNav(step);
        onStepShown(step);
    }

    function transitionToStep(fromStep, toStep) {
        const fromEl = document.getElementById("step" + fromStep);
        const toEl   = document.getElementById("step" + toStep);
        if (!fromEl || !toEl || isAnimating) return;
        isAnimating = true;

        const forward = toStep > fromStep;
        updateWizardNav(toStep);

        if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
            fromEl.classList.add("d-none");
            toEl.classList.remove("d-none");
            isAnimating = false;
            onStepShown(toStep);
            return;
        }

        const viewport = document.getElementById("wizardViewport");
        if (viewport) {
            viewport.style.minHeight = fromEl.offsetHeight + "px";
            viewport.style.overflow  = "hidden";
        }

        const exitCls  = forward ? "wiz-exit-fwd"  : "wiz-exit-back";
        const enterCls = forward ? "wiz-enter-fwd" : "wiz-enter-back";

        fromEl.classList.add(exitCls);
        toEl.classList.remove("d-none");
        toEl.classList.add(enterCls);

        setTimeout(() => {
            fromEl.classList.remove(exitCls);
            fromEl.classList.add("d-none");
            toEl.classList.remove(enterCls);
            if (viewport) {
                viewport.style.overflow  = "";
                viewport.style.minHeight = "";
            }
            isAnimating = false;
            onStepShown(toStep);
        }, 340);
    }

    // Applicant add / remove
    const applicantContainer = document.getElementById("applicantContainer");
    const addApplicantBtn    = document.getElementById("addApplicant");

    function renumberApplicants() {
        const blocks = applicantContainer.querySelectorAll(".applicant-block");
        blocks.forEach((block, index) => {
            const title = block.querySelector(".applicant-title");
            if (title) title.textContent = "Applicant " + (index + 1);
            const removeBtn = block.querySelector(".remove-applicant-btn");
            if (removeBtn) removeBtn.style.display = (blocks.length > 1 ? "inline-flex" : "none");
        });
    }

    if (addApplicantBtn && applicantContainer) {
        addApplicantBtn.addEventListener("click", () => {
            const blocks = applicantContainer.querySelectorAll(".applicant-block");
            if (!blocks.length) return;
            const clone = blocks[0].cloneNode(true);
            clone.querySelectorAll("input").forEach(el => { el.value = ""; });
            clone.querySelectorAll("select").forEach(el => { el.selectedIndex = 0; });
            applicantContainer.appendChild(clone);
            renumberApplicants();
        });

        applicantContainer.addEventListener("click", (e) => {
            if (e.target.closest(".remove-applicant-btn")) {
                const block = e.target.closest(".applicant-block");
                if (!block) return;
                const all = applicantContainer.querySelectorAll(".applicant-block");
                if (all.length <= 1) return;
                block.remove();
                renumberApplicants();
            }
        });

        renumberApplicants();
    }

    // Validation
    function validateStep(step) {
        clearError();
        if (step === 1) {
            let ok = true;
            applicantContainer.querySelectorAll("input, select").forEach(field => {
                if (!field.value.trim()) ok = false;
            });
            if (!ok) { showError("Please complete all applicant fields."); return false; }
        }
        if (step === 2) {
            if (!dateField.value || !startField.value || !endField.value) {
                showError("Please choose a date and one of the available booking sessions.");
                return false;
            }
            if (startField.value >= endField.value) {
                showError("End time must be after start time.");
                return false;
            }
        }
        if (step === finalStep) {
            const activityField = form.querySelector(totalSteps === 3 ? "#step3 textarea[name='activity']" : "#step2 textarea[name='activity']");
            if (!activityField || !activityField.value.trim()) {
                showError("Please fill in the activity description.");
                return false;
            }

            const supervisorFields = totalSteps === 3 ? form.querySelectorAll(".student-supervisor-field") : [];
            if (supervisorFields.length) {
                const supervisorValues = {};
                supervisorFields.forEach(field => {
                    supervisorValues[field.name] = field.value.trim();
                });

                if (!supervisorValues.supervisor_name || !supervisorValues.supervisor_email || !supervisorValues.supervisor_phone) {
                    showError("Supervisor name, email, and phone are required for student bookings.");
                    return false;
                }

                const emailField = form.querySelector("input[name='supervisor_email']");
                if (emailField && emailField.value.trim() && !emailField.checkValidity()) {
                    showError("Supervisor email address is invalid.");
                    return false;
                }
            }
        }
        return true;
    }

    nextBtn.addEventListener("click", () => {
        if (isAnimating) return;
        if (!validateStep(currentStep)) return;
        if (currentStep < finalStep) {
            const from = currentStep++;
            transitionToStep(from, currentStep);
        }
    });

    prevBtn.addEventListener("click", () => {
        if (isAnimating) return;
        if (currentStep > 1) {
            const from = currentStep--;
            transitionToStep(from, currentStep);
        }
    });

    if (dateField) {
        dateField.addEventListener("change", () => {
            setSelectedSession("", "");
            refreshDaySlots();
        });
    }

    if (daySlotsEl) {
        daySlotsEl.addEventListener("click", (e) => {
            const btn = e.target.closest(".day-slot-btn");
            if (!btn || btn.disabled) return;
            setSelectedSession(btn.dataset.start || "", btn.dataset.end || "");
            refreshDaySlots();
        });
    }

    form.addEventListener("submit", (e) => {
        if (!validateStep(finalStep)) {
            e.preventDefault();
        }
    });

    showStep(1);
});
</script>

<?= $this->endSection() ?>
