<?= $this->extend('layouts/main_user') ?>

<?= $this->section('content') ?>

<?php
$mode = $mode ?? 'create';
$requestRecord = $requestRecord ?? [];
$isEdit = $mode === 'edit';
$actionUrl = $isEdit ? '/dashboard/external/request/update/' . (int) ($requestRecord['id'] ?? 0) : '/dashboard/external/request/store';
$requestModel = $requestModel ?? null;
$currentStatus = (string) ($requestRecord['status'] ?? 'pending_pic_approval');
?>

<div class="slams-page-header">
    <div class="slams-page-header-left">
        <h1 class="slams-page-title"><?= $isEdit ? 'Update External Request' : 'Request Lab Access' ?></h1>
        <p class="slams-page-subtitle">This form starts a staged approval flow. It does not directly reserve the laboratory.</p>
    </div>
    <div class="slams-page-header-actions">
        <a href="/dashboard/external" class="btn btn-glass btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Back to Requests
        </a>
    </div>
</div>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger"><?= esc(session()->getFlashdata('error')) ?></div>
<?php endif; ?>

<?php if ($isEdit && !empty($requestRecord['review_notes'])): ?>
    <div class="alert alert-warning">
        <strong>Latest reviewer note:</strong><br>
        <?= nl2br(esc($requestModel ? $requestModel->latestRequesterNote($requestRecord) : ($requestRecord['review_notes'] ?? ''))) ?>
    </div>
<?php endif; ?>

<div class="card shadow-sm border-0">
    <div class="card-body p-4">
        <form action="<?= esc($actionUrl) ?>" method="post" class="row g-4" id="externalRequestForm">
            <?= csrf_field() ?>

            <div class="col-md-6">
                <label class="form-label">Laboratory *</label>
                <select name="lab_id" class="form-select" id="externalLabId" required>
                    <option value="">Select a laboratory</option>
                    <?php foreach (($labs ?? []) as $lab): ?>
                        <option value="<?= esc($lab['id']) ?>" <?= (string) old('lab_id', $requestRecord['lab_id'] ?? '') === (string) $lab['id'] ? 'selected' : '' ?>>
                            <?= esc($lab['name']) ?><?= !empty($lab['room']) ? ' (' . esc($lab['room']) . ')' : '' ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="col-md-6">
                <label class="form-label">Service Bundle *</label>
                <input type="hidden" name="service_id" id="externalServiceId" value="<?= esc(old('service_id', $requestRecord['service_id'] ?? '')) ?>">
                <input type="hidden" name="selected_assets" id="externalSelectedAssets" value="<?= esc(old('selected_assets', $requestRecord['selected_assets'] ?? '')) ?>">
                <div id="externalServiceChoices" class="d-flex flex-wrap gap-2 mt-1"></div>
                <div id="externalServiceHint" class="small text-muted mt-1">Select a laboratory to see configured services. If the lab has services, choosing one is required.</div>
                <div id="externalEquipmentInfo" class="mt-2"></div>
            </div>

            <?php if ($isEdit): ?>
            <div class="col-md-6">
                <label class="form-label">Current Status</label>
                <input type="text" class="form-control" value="<?= esc($requestModel ? $requestModel->statusLabel($currentStatus) : ucfirst($currentStatus)) ?>" readonly>
                <div class="form-text">You will receive a notification and email whenever the PIC or Lab Manager updates this request.</div>
            </div>
            <?php endif; ?>

            <div class="col-md-6">
                <label class="form-label">Organization / Institution *</label>
                <input type="text" name="organization_name" class="form-control" maxlength="255" value="<?= esc(old('organization_name', $requestRecord['organization_name'] ?? '')) ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Contact Name *</label>
                <input type="text" name="contact_name" class="form-control" maxlength="255" value="<?= esc(old('contact_name', $requestRecord['contact_name'] ?? '')) ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Contact Email *</label>
                <input type="email" name="contact_email" class="form-control" maxlength="255" value="<?= esc(old('contact_email', $requestRecord['contact_email'] ?? '')) ?>" required>
            </div>

            <div class="col-md-6">
                <label class="form-label">Contact Phone *</label>
                <input type="text" name="contact_phone" class="form-control" maxlength="50" value="<?= esc(old('contact_phone', $requestRecord['contact_phone'] ?? '')) ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Participant Count *</label>
                <input type="number" name="participant_count" class="form-control" min="1" value="<?= esc(old('participant_count', $requestRecord['participant_count'] ?? 1)) ?>" required>
            </div>

            <div class="col-md-4">
                <label class="form-label">Preferred Date *</label>
                <input type="date" name="preferred_date" id="externalPreferredDate" class="form-control" min="<?= esc(date('Y-m-d')) ?>" value="<?= esc(old('preferred_date', $requestRecord['preferred_date'] ?? '')) ?>" required>
            </div>

            <div class="col-md-8">
                <label class="form-label">Preferred Slot *</label>
                <input type="hidden" name="preferred_start_time" id="externalPreferredStartTime" value="<?= esc(old('preferred_start_time', $requestRecord['preferred_start_time'] ?? '')) ?>">
                <input type="hidden" name="preferred_end_time" id="externalPreferredEndTime" value="<?= esc(old('preferred_end_time', $requestRecord['preferred_end_time'] ?? '')) ?>">
                <div class="form-control bg-light d-flex align-items-center" id="externalSlotSummary" style="min-height: 48px;">
                    Choose a laboratory and date to load the configured booking slots.
                </div>
                <div id="externalSlotFeedback" class="mt-2"></div>
                <div id="externalSlotChoices" class="d-flex flex-wrap gap-2 mt-2"></div>
                <div class="form-text">External requests use the same configured booking sessions as student bookings. The slot is only reserved after final approval.</div>
            </div>

            <div class="col-12">
                <label class="form-label">Purpose of Use *</label>
                <textarea name="purpose" id="purposeField" class="form-control" rows="5" minlength="10" required><?= esc(old('purpose', $requestRecord['purpose'] ?? '')) ?></textarea>
                <div class="d-flex justify-content-between align-items-start mt-1">
                    <div class="form-text">Explain what you need to do in the laboratory, why the lab is required, and any timing constraints.</div>
                    <small id="purposeCounter" class="text-muted ms-2 flex-shrink-0"></small>
                </div>
            </div>

            <div class="col-12">
                <label class="form-label">Setup Notes</label>
                <textarea name="equipment_notes" class="form-control" rows="4"><?= esc(old('equipment_notes', $requestRecord['equipment_notes'] ?? '')) ?></textarea>
                <div class="form-text">Describe any workstation setup, environmental requirements, or other notes for the PIC.</div>
            </div>

            <div class="col-12 d-flex justify-content-end gap-2">
                <a href="/dashboard/external" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary"><?= $isEdit ? 'Update Request' : 'Submit Request' ?></button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const labField = document.getElementById("externalLabId");
    const serviceIdField = document.getElementById("externalServiceId");
    const dateField = document.getElementById("externalPreferredDate");
    const startField = document.getElementById("externalPreferredStartTime");
    const endField = document.getElementById("externalPreferredEndTime");
    const summaryEl = document.getElementById("externalSlotSummary");
    const feedbackEl = document.getElementById("externalSlotFeedback");
    const slotChoicesEl = document.getElementById("externalSlotChoices");

    if (!labField || !serviceIdField || !dateField || !startField || !endField || !summaryEl || !feedbackEl || !slotChoicesEl) {
        return;
    }

    let selectedStart = startField.value || "";
    let selectedEnd = endField.value || "";

    function renderFeedback(message, type = "info") {
        if (!message) {
            feedbackEl.innerHTML = "";
            return;
        }

        const className = type === "warning" ? "alert-warning" : type === "success" ? "alert-success" : "alert-info";
        feedbackEl.innerHTML = `<div class="alert ${className} small mb-0">${message}</div>`;
    }

    function renderSummary(label) {
        summaryEl.textContent = label;
    }

    function setSelectedSlot(slot) {
        selectedStart = slot?.start || "";
        selectedEnd = slot?.end || "";
        startField.value = selectedStart;
        endField.value = selectedEnd;

        if (!slot) {
            renderSummary("Choose one of the available booking slots for the selected date.");
            return;
        }

        renderSummary(`${slot.label || `${slot.start}-${slot.end}`} | ${slot.start}-${slot.end}`);
    }

    function renderSlotChoices(slots) {
        if (!slots.length) {
            slotChoicesEl.innerHTML = "";
            renderFeedback("No configured booking slots are available for this date.", "warning");
            return;
        }

        slotChoicesEl.innerHTML = slots.map((slot) => {
            const isSelected = selectedStart === slot.start && selectedEnd === slot.end;
            const buttonClass = slot.can_book
                ? (isSelected ? "btn-primary" : "btn-outline-success")
                : "btn-outline-secondary";
            const disabledAttr = slot.can_book ? "" : "disabled";
            const meta = slot.can_book ? `${slot.start}-${slot.end}` : (slot.reason || "Unavailable");

            return `
                <button
                    type="button"
                    class="btn ${buttonClass} text-start external-slot-choice"
                    data-start="${slot.start}"
                    data-end="${slot.end}"
                    data-label="${slot.label || `${slot.start}-${slot.end}`}"
                    ${disabledAttr}
                >
                    <div class="fw-semibold">${slot.label || `${slot.start}-${slot.end}`}</div>
                    <div class="small">${meta}</div>
                </button>
            `;
        }).join("");
    }

    async function loadSlots(preserveCurrentSelection = false) {
        const labId = labField.value;
        const preferredDate = dateField.value;

        if (!labId || !preferredDate) {
            slotChoicesEl.innerHTML = "";
            if (!preserveCurrentSelection) {
                setSelectedSlot(null);
            }
            renderFeedback("");
            renderSummary("Choose a laboratory and date to load the configured booking slots.");
            return;
        }

        if (!preserveCurrentSelection) {
            setSelectedSlot(null);
        }

        renderFeedback("Loading configured booking slots...");
        slotChoicesEl.innerHTML = "";

        try {
            const params = new URLSearchParams();
            if (serviceIdField.value) {
                params.set("service_id", serviceIdField.value);
            }

            const response = await fetch(`/dashboard/external/request/slots/${encodeURIComponent(labId)}/${encodeURIComponent(preferredDate)}?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await response.json();
            const slots = Array.isArray(data.slots) ? data.slots : [];

            if ((selectedStart || selectedEnd) && !preserveCurrentSelection) {
                selectedStart = "";
                selectedEnd = "";
            }

            const matchingSlot = slots.find((slot) => slot.start === selectedStart && slot.end === selectedEnd);
            if (matchingSlot && matchingSlot.can_book) {
                setSelectedSlot(matchingSlot);
                renderFeedback("Selected slot is available.", "success");
            } else if (matchingSlot && !matchingSlot.can_book) {
                setSelectedSlot(null);
                renderFeedback(matchingSlot.reason || "Selected slot is no longer available. Please choose another slot.", "warning");
            } else if (selectedStart || selectedEnd) {
                setSelectedSlot(null);
                renderFeedback("Please choose one of the configured booking slots for this date.", "warning");
            } else {
                renderFeedback("Choose one of the available booking slots below.");
            }

            renderSlotChoices(slots);
        } catch (_error) {
            slotChoicesEl.innerHTML = "";
            renderFeedback("Could not load booking slots right now. Please try again.", "warning");
            renderSummary("Choose a laboratory and date to load the configured booking slots.");
        }
    }

    labField.addEventListener("change", () => {
        selectedStart = "";
        selectedEnd = "";
        loadSlots();
    });

    dateField.addEventListener("change", () => {
        selectedStart = "";
        selectedEnd = "";
        loadSlots();
    });

    serviceIdField.addEventListener("change", () => {
        selectedStart = "";
        selectedEnd = "";
        loadSlots();
    });

    slotChoicesEl.addEventListener("click", (event) => {
        const button = event.target.closest(".external-slot-choice");
        if (!button || button.disabled) {
            return;
        }

        setSelectedSlot({
            label: button.dataset.label || "",
            start: button.dataset.start || "",
            end: button.dataset.end || "",
        });

        renderFeedback("Selected slot is available.", "success");
        loadSlots(true);
    });

    if (selectedStart && selectedEnd && labField.value && dateField.value) {
        renderSummary("Validating the preselected booking slot...");
        loadSlots(true);
        return;
    }

    renderSummary("Choose a laboratory and date to load the configured booking slots.");
    if (labField.value && dateField.value) {
        loadSlots();
    }
});
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const labField          = document.getElementById("externalLabId");
    const serviceIdField    = document.getElementById("externalServiceId");
    const serviceChoicesEl  = document.getElementById("externalServiceChoices");
    const serviceHintEl     = document.getElementById("externalServiceHint");
    const selectedAssetsField = document.getElementById("externalSelectedAssets");
    const equipmentInfoEl   = document.getElementById("externalEquipmentInfo");

    if (!labField || !serviceIdField || !serviceChoicesEl) {
        return;
    }

    let currentServiceId = serviceIdField.value || "";

    function showEquipmentInfo(bundleSummary, equipmentModels, isBookable) {
        if (!equipmentInfoEl) return;
        if (!bundleSummary && !equipmentModels) {
            equipmentInfoEl.innerHTML = "";
            return;
        }

        const availabilityBadge = isBookable
            ? '<span class="badge bg-success-subtle text-success border border-success-subtle">Bundle available</span>'
            : '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">Bundle unavailable</span>';

        equipmentInfoEl.innerHTML = `
            <div class="alert alert-secondary py-2 mb-0 small">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <span class="fw-semibold">Bundle:</span>
                    ${availabilityBadge}
                </div>
                <div>${bundleSummary || equipmentModels}</div>
                ${equipmentModels && bundleSummary !== equipmentModels ? `<div class="text-muted mt-1">Models: ${equipmentModels}</div>` : ""}
            </div>`;
    }

    function renderServiceChoices(services) {
        if (!services.length) {
            serviceChoicesEl.innerHTML = "";
            serviceHintEl.textContent = "No services configured for this laboratory.";
            showEquipmentInfo("", "", false);
            return;
        }
        serviceHintEl.textContent = "";
        serviceChoicesEl.innerHTML = services.map((s) => {
            const selected = String(s.id) === currentServiceId;
            return `
                <button type="button"
                        class="btn btn-sm ${selected ? "btn-primary" : "btn-outline-secondary"} service-choice-btn"
                        data-id="${s.id}"
                        data-bundle-summary="${s.bundle_summary || ""}"
                        data-equipment="${s.equipment_models || ""}"
                        data-bookable="${s.is_bookable ? "1" : "0"}">
                    ${s.service_name}
                </button>`;
        }).join("");

        const selectedBtn = serviceChoicesEl.querySelector(`.service-choice-btn[data-id="${currentServiceId}"]`);
        showEquipmentInfo(
            selectedBtn ? (selectedBtn.dataset.bundleSummary || "") : "",
            selectedBtn ? (selectedBtn.dataset.equipment || "") : "",
            selectedBtn ? selectedBtn.dataset.bookable === "1" : false
        );
    }

    async function loadServices(labId, preserveService = false) {
        if (!labId) {
            serviceChoicesEl.innerHTML = "";
            serviceHintEl.textContent = "Select a laboratory to see available services.";
            if (!preserveService) {
                currentServiceId = "";
                serviceIdField.value = "";
                if (selectedAssetsField) selectedAssetsField.value = "";
                serviceIdField.dispatchEvent(new Event("change"));
            }
            showEquipmentInfo("", "", false);
            return;
        }

        serviceChoicesEl.innerHTML = '<span class="text-muted small">Loading services...</span>';
        serviceHintEl.textContent = "";

        try {
            const res = await fetch(`/dashboard/external/request/lab-services/${encodeURIComponent(labId)}`, {
                headers: { "X-Requested-With": "XMLHttpRequest" },
            });
            const data = await res.json();
            const services = Array.isArray(data.services) ? data.services : [];

            if (!preserveService) {
                currentServiceId = "";
                serviceIdField.value = "";
                if (selectedAssetsField) selectedAssetsField.value = "";
                serviceIdField.dispatchEvent(new Event("change"));
            }

            renderServiceChoices(services);
        } catch {
            serviceChoicesEl.innerHTML = "";
            serviceHintEl.textContent = "Could not load services right now.";
        }
    }

    serviceChoicesEl.addEventListener("click", (e) => {
        const btn = e.target.closest(".service-choice-btn");
        if (!btn) return;

        const clickedId = btn.dataset.id || "";
        const isSame = clickedId === currentServiceId;

        currentServiceId = isSame ? "" : clickedId;
        serviceIdField.value = currentServiceId;
        if (selectedAssetsField) {
            selectedAssetsField.value = "";
        }
        serviceIdField.dispatchEvent(new Event("change"));

        serviceChoicesEl.querySelectorAll(".service-choice-btn").forEach((b) => {
            b.className = (b === btn && !isSame)
                ? "btn btn-sm btn-primary service-choice-btn"
                : "btn btn-sm btn-outline-secondary service-choice-btn";
        });

        showEquipmentInfo(
            isSame ? "" : (btn.dataset.bundleSummary || ""),
            isSame ? "" : (btn.dataset.equipment || ""),
            !isSame && btn.dataset.bookable === "1"
        );
    });

    labField.addEventListener("change", () => {
        currentServiceId = "";
        serviceIdField.value = "";
        if (selectedAssetsField) selectedAssetsField.value = "";
        loadServices(labField.value);
    });

    if (labField.value) {
        loadServices(labField.value, Boolean(currentServiceId));
    }
});
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const purposeField = document.getElementById("purposeField");
    const purposeCounter = document.getElementById("purposeCounter");
    const MIN_CHARS = 10;

    if (!purposeField || !purposeCounter) return;

    function updateCounter() {
        const len = purposeField.value.length;
        const remaining = MIN_CHARS - len;

        if (remaining > 0) {
            purposeCounter.textContent = `${remaining} more character${remaining === 1 ? "" : "s"} required`;
            purposeCounter.className = "text-danger ms-2 flex-shrink-0";
        } else {
            purposeCounter.textContent = `${len} characters`;
            purposeCounter.className = "text-muted ms-2 flex-shrink-0";
        }
    }

    purposeField.addEventListener("input", updateCounter);
    updateCounter();
});
</script>

<?= $this->endSection() ?>
