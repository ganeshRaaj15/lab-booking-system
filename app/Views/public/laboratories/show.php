<?= $this->extend('layouts/main_user') ?>
<?= $this->section('content') ?>

<?php
$picName = trim((string)($lab['pic_name'] ?? ''));
$picEmail = trim((string)($lab['pic_email'] ?? ''));
$picPhone = trim((string)($lab['pic_phone'] ?? ''));
$services = is_array($services ?? null) ? $services : [];

if ($picName === '') {
    $picName = 'Not Assigned';
}
if ($picEmail === '') {
    $picEmail = '';
}
if ($picPhone === '') {
    $picPhone = '';
}
?>

<!-- ============================================================
     LABORATORY DETAIL PAGE CONTENT
     ============================================================ -->
<div class="lab-detail-page">
    <div class="container">
        
        <!-- Breadcrumb -->
        <div class="lab-breadcrumb">
            <a href="<?= site_url('/laboratories') ?>" class="back-link">
                <i class="bi bi-arrow-left"></i>
                Back to Laboratory Directory
            </a>
        </div>

        <!-- Lab Header — compact info bar -->
        <div class="lab-header-compact-card">
            <?php if (!empty($lab['image'])): ?>
                <div class="lab-thumb"
                     data-bs-toggle="modal" data-bs-target="#labImgModal"
                     title="Click to view full image">
                    <img src="<?= base_url($lab['image']) ?>"
                         alt="<?= esc($lab['name']) ?>"
                         onerror="this.onerror=null;this.src='<?= base_url('images/assets/placeholder_asset.png') ?>';">
                </div>
            <?php else: ?>
                <div class="lab-thumb lab-thumb--placeholder">
                    <i class="bi bi-building"></i>
                </div>
            <?php endif; ?>

            <div class="lab-header-body">
                <div class="lab-header-title-row">
                    <h1 class="lab-title-compact"><?= esc($lab['name']) ?></h1>
                    <?php if (!empty($lab['room'])): ?>
                        <span class="lab-room-tag">
                            <i class="bi bi-door-open"></i> Room <?= esc($lab['room']) ?>
                        </span>
                    <?php endif; ?>
                </div>
                <div class="lab-header-pic-row">
                    <?php if (!empty($lab['pic_image'])): ?>
                        <img src="<?= base_url($lab['pic_image']) ?>"
                             alt="<?= esc($picName) ?>"
                             class="lab-pic-tiny-avatar"
                             onerror="this.style.display='none'">
                    <?php else: ?>
                        <i class="bi bi-person-circle lab-pic-tiny-icon"></i>
                    <?php endif; ?>
                    <span class="lab-pic-name-inline"><?= esc($picName) ?></span>
                    <?php if ($picEmail !== 'null'): ?>
                        <span class="lab-meta-sep">·</span>
                        <a href="mailto:<?= esc($picEmail) ?>" class="lab-meta-link"><?= esc($picEmail) ?></a>
                    <?php endif; ?>
                    <?php if ($picPhone !== 'null'): ?>
                        <span class="lab-meta-sep">·</span>
                        <span class="lab-meta-phone"><i class="bi bi-telephone me-1"></i><?= esc($picPhone) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="lab-header-access-note">
                <i class="bi bi-info-circle mb-1" style="font-size:1.1rem;display:block;text-align:center;"></i>
                <?php if ($bookingMode === 'uthm'): ?>
                    Select a service below, then use the booking wizard to reserve a slot.
                <?php elseif ($bookingMode === 'external'): ?>
                    Login and submit an access request. The PIC will review it.
                <?php else: ?>
                    Login with an external account or contact the PIC directly.
                <?php endif; ?>
            </div>
        </div>

        <!-- Service selection status — updated by JS -->
        <div id="selectedServiceSummary" class="slams-service-status-bar">
            <i class="bi bi-info-circle me-1"></i>
            Choose a service below to activate the correct equipment set for booking.
        </div>

        <div class="slams-service-section mt-4">
            <div class="slams-service-section-head">
                <div>
                    <h2 class="slams-service-section-title">
                        <i class="bi bi-list-check"></i>
                        Available Services &amp; Equipment
                    </h2>
                    <p class="slams-service-section-sub">Choose a service to activate booking. Equipment inventory linked to each service is shown below.</p>
                </div>
                <span class="calib-badge calib-badge--count">
                    <?= count($services) ?> <?= count($services) === 1 ? 'Service' : 'Services' ?>
                </span>
            </div>

            <?php if ($services === []): ?>
                <div class="text-center py-4" style="color: var(--slams-muted)">
                    No services have been imported for this laboratory yet.
                </div>
            <?php else: ?>
                <div class="slams-service-list">
                    <?php foreach ($services as $service): ?>
                        <?php
                        $serviceId = (int)($service['id'] ?? 0);
                        $serviceCalibration = strtolower(trim((string) ($service['calibration_status'] ?? 'unknown')));
                        $calibrationClass = $serviceCalibration === 'valid'
                            ? 'calib-badge calib-badge--valid'
                            : ($serviceCalibration === 'expired' ? 'calib-badge calib-badge--expired' : 'calib-badge calib-badge--unknown');
                        $equipmentModels = trim((string) ($service['equipment_models'] ?? ''));
                        $criteriaText = trim((string) ($service['acceptance_criteria'] ?? ''));
                        $serviceAssets = $assetsByService[$serviceId] ?? [];
                        ?>
                        <div class="slams-service-item">
                            <div class="slams-service-item-head">
                                <div class="slams-service-item-meta">
                                    <div class="slams-service-name"><?= esc($service['service_name'] ?? '') ?></div>
                                    <?php if (!empty($service['field_name'])): ?>
                                        <div class="slams-service-field">
                                            <i class="bi bi-diagram-3"></i>
                                            <?= esc($service['field_name']) ?>
                                        </div>
                                    <?php endif; ?>
                                    <?php if ($equipmentModels !== ''): ?>
                                        <div class="slams-service-detail mt-1">
                                            <span class="slams-service-detail-label">Models:</span>
                                            <?= esc(str_replace(' | ', ', ', $equipmentModels)) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <span class="<?= esc($calibrationClass) ?>">
                                    Calibration: <?= esc(ucfirst($serviceCalibration)) ?>
                                </span>
                            </div>

                            <?php if ($criteriaText !== ''): ?>
                                <div class="slams-service-detail">
                                    <span class="slams-service-detail-label">Acceptance criteria:</span>
                                    <?= esc($criteriaText) ?>
                                </div>
                            <?php endif; ?>

                            <!-- Equipment inventory cards -->
                            <div class="service-assets-grid mt-3">
                                <?php if (!empty($serviceAssets)): ?>
                                    <?php foreach ($serviceAssets as $a): ?>
                                        <?php
                                        $assetStatus = strtolower(trim((string)($a['status'] ?? 'unknown')));
                                        $isAvailable = $assetStatus === 'available' && (int)($a['quantity'] ?? 0) > 0;
                                        $assetQty = (int)($a['quantity'] ?? 0);
                                        ?>
                                        <div class="service-asset-card<?= !$isAvailable ? ' service-asset-card--unavailable' : '' ?>"
                                             data-asset-id="<?= esc((string)($a['id'] ?? '')) ?>"
                                             data-service-id="<?= esc((string)$serviceId) ?>"
                                             data-status="<?= esc($assetStatus) ?>"
                                             data-quantity="<?= $assetQty ?>"
                                             data-asset-name="<?= esc($a['name'] ?? '') ?>"
                                             data-asset-category="<?= esc($a['category'] ?? '') ?>"
                                             data-asset-img="<?= !empty($a['image']) ? esc(base_url($a['image'])) : '' ?>"
                                             data-service-name="<?= esc($service['service_name'] ?? '') ?>"
                                             title="Click to view details"
                                             role="button">

                                            <div class="service-asset-img-wrap">
                                                <?php if (!empty($a['image'])): ?>
                                                    <img src="<?= base_url($a['image']) ?>"
                                                         alt="<?= esc($a['name'] ?? '') ?>"
                                                         class="service-asset-img"
                                                         onerror="this.onerror=null;this.src='<?= base_url('images/assets/placeholder_asset.png') ?>';">
                                                <?php else: ?>
                                                    <div class="asset-img-placeholder">
                                                        <i class="bi bi-cpu"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </div>

                                            <div class="service-asset-info">
                                                <div class="service-asset-name"><?= esc($a['name'] ?? '') ?></div>
                                                <?php if (!empty($a['category'])): ?>
                                                    <div class="service-asset-meta"><?= esc($a['category']) ?></div>
                                                <?php endif; ?>
                                                <div class="service-asset-status-row">
                                                    <?php if ($assetStatus === 'available'): ?>
                                                        <span class="asset-status-badge asset-status-badge--available">Available</span>
                                                    <?php elseif ($assetStatus === 'maintenance'): ?>
                                                        <span class="asset-status-badge asset-status-badge--maintenance">Maintenance</span>
                                                    <?php elseif ($assetStatus === 'faulty'): ?>
                                                        <span class="asset-status-badge asset-status-badge--faulty">Faulty</span>
                                                    <?php else: ?>
                                                        <span class="asset-status-badge"><?= esc(ucfirst($assetStatus)) ?></span>
                                                    <?php endif; ?>
                                                    <span class="service-asset-qty-text">&times;<?= $assetQty ?></span>
                                                </div>
                                            </div>

                                            <?php if ($bookingMode === 'uthm'): ?>
                                                <div class="service-asset-booking">
                                                    <label class="service-asset-check-label<?= !$isAvailable ? ' opacity-50' : '' ?>">
                                                        <input type="checkbox"
                                                               class="asset-checkbox"
                                                               data-asset-id="<?= esc((string)($a['id'] ?? '')) ?>"
                                                               <?= !$isAvailable ? 'disabled' : '' ?>>
                                                        <span class="small">Select</span>
                                                    </label>
                                                    <input type="number"
                                                           class="asset-qty form-control form-control-sm"
                                                           data-asset-id="<?= esc((string)($a['id'] ?? '')) ?>"
                                                           min="1"
                                                           max="<?= $assetQty ?: 1 ?>"
                                                           value="<?= $isAvailable ? 1 : 0 ?>"
                                                           style="width:64px;"
                                                           <?= !$isAvailable ? 'disabled' : '' ?>>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <div class="service-assets-empty">
                                        <i class="bi bi-inbox me-1"></i>
                                        No inventory items linked to this service.
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="mt-3 d-flex justify-content-between align-items-center gap-2 flex-wrap">
                                <button type="button"
                                        class="btn btn-outline-primary btn-sm select-service-btn"
                                        data-service-id="<?= esc((string)$serviceId) ?>"
                                        data-service-name="<?= esc($service['service_name'] ?? '') ?>"
                                        data-service-calibration="<?= esc(ucfirst($serviceCalibration)) ?>"
                                        data-service-equipment="<?= esc(str_replace(' | ', ', ', $equipmentModels)) ?>"
                                        data-service-criteria="<?= esc($criteriaText) ?>">
                                    <i class="bi bi-check2-square me-1"></i>
                                    Choose Service
                                </button>
                                <div class="small text-muted service-state-label">
                                    This service will drive equipment and slot selection.
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Booking CTA Card -->
        <div class="booking-card">
            <h2 class="booking-title">
                <i class="bi bi-calendar-check"></i>
                <?php if ($bookingMode === 'uthm'): ?>
                    Ready to Book?
                <?php else: ?>
                    How to Book This Laboratory
                <?php endif; ?>
            </h2>
            
                <p class="booking-description">
                    <?php if ($bookingMode === 'uthm'): ?>
                        Start by choosing a service. The system will load the linked equipment, check availability,
                        and guide you into the booking wizard with the correct context.
                        <span class="text-danger fw-semibold d-block mt-1">Note: Only equipment marked as "Available" and linked to the chosen service can be booked.</span>
                    <?php elseif ($bookingMode === 'external'): ?>
                        Review the laboratory resources, then submit an external access request. The PIC will review your request and decide whether it can move forward for scheduling.
                    <?php else: ?>
                        You can browse equipment and view availability, but direct booking is reserved for UTHM users. Login with an external account to submit a request, or contact the PIC first.
                    <?php endif; ?>
                </p>
            
            <div class="booking-alert">
                <i class="bi bi-info-circle"></i>
                <p>Availability is calculated from the selected service and its linked equipment, and may vary between timeslots.</p>
            </div>
            
            <div class="booking-actions">
                <?php if ($bookingMode === 'uthm'): ?>
                    <button id="openBookingWizardBtn"
                            class="btn booking-btn"
                            data-lab-id="<?= esc($lab['id']) ?>"
                            disabled>
                        <i class="bi bi-magic me-1"></i>
                        Launch Booking Wizard (Select Service First)
                    </button>
                <?php elseif ($bookingMode === 'external'): ?>
                    <a href="/dashboard/external/request?lab_id=<?= esc($lab['id']) ?>" class="btn booking-btn">
                        <i class="bi bi-clipboard-check me-1"></i>
                        Request Lab Access
                    </a>
                <?php else: ?>
                    <a href="/login" class="btn booking-btn booking-btn-outline">
                        <i class="bi bi-box-arrow-in-right me-1"></i>
                        Login to Submit a Request
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Calendar Section -->
        <div class="calendar-card">
            <div class="calendar-header">
                <h2 class="calendar-title">
                    <i class="bi bi-calendar3"></i>
                    Laboratory Availability
                </h2>
            <div class="calendar-note">
                Choose a service above to load its linked equipment and real-time availability. Click on any date to see available timeslots.
                <span class="d-block text-danger small mt-1">Note: Equipment can still be booked when some units remain available, even if other units are under maintenance.</span>
            </div>
        </div>
            <div id="labCalendar"></div>
            <div id="daySlotPanel" class="d-none mt-4 pt-3 border-top"></div>
        </div>

    </div>
</div>

<!-- Lab image fullsize modal -->
<div class="modal fade" id="labImgModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width:min(720px,92vw);">
        <div class="modal-content" style="background:transparent;border:none;box-shadow:none;">
            <div class="modal-body p-0 position-relative">
                <button type="button"
                        class="btn-close position-absolute top-0 end-0 m-2"
                        data-bs-dismiss="modal"
                        style="background-color:rgba(255,255,255,0.9);border-radius:50%;padding:7px;z-index:10;">
                </button>
                <?php if (!empty($lab['image'])): ?>
                    <img src="<?= base_url($lab['image']) ?>"
                         alt="<?= esc($lab['name']) ?>"
                         style="width:100%;max-height:80vh;object-fit:contain;border-radius:16px;display:block;"
                         onerror="this.onerror=null;this.src='<?= base_url('images/assets/placeholder_asset.png') ?>';">
                <?php else: ?>
                    <div style="height:260px;background:var(--slams-primary-soft);border-radius:16px;display:flex;align-items:center;justify-content:center;">
                        <i class="bi bi-building" style="font-size:5rem;color:var(--slams-primary);"></i>
                    </div>
                <?php endif; ?>
                <div class="text-center mt-2" style="color:#fff;font-size:0.85rem;opacity:0.8;">
                    <?= esc($lab['name']) ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Equipment detail modal -->
<div class="modal fade" id="equipmentDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content" style="border-radius:16px;overflow:hidden;">
            <div class="modal-header border-0 pb-0 pt-3 px-3">
                <h6 class="modal-title fw-bold" id="edModalName"></h6>
                <button type="button" class="btn-close btn-close-sm" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body pt-2 px-3 pb-3">
                <div id="edModalImg" class="ed-modal-img-wrap mb-3"></div>
                <div class="text-muted small mb-2" id="edModalService"></div>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span id="edModalStatus" class="asset-status-badge"></span>
                    <span class="small text-muted" id="edModalQty"></span>
                </div>
                <div class="text-muted small" id="edModalCategory"></div>
            </div>
        </div>
    </div>
</div>

<!-- Include booking modal (adapts to bookingMode inside) -->
<?= $this->include('public/booking/booking_modal', [
    'lab'          => $lab,
    'faculties'    => $faculties,
    'bookingMode'  => $bookingMode,
    'userProfile'  => $userProfile ?? null
]) ?>

<script>
document.addEventListener("DOMContentLoaded", function () {

    const LAB_ID      = <?= (int) $lab['id'] ?>;
    const BOOKING_MODE = "<?= esc($bookingMode) ?>";

    const calendarEl       = document.getElementById("labCalendar");
    const assetCheckboxes  = document.querySelectorAll(".asset-checkbox");
    const openWizardBtn    = document.getElementById("openBookingWizardBtn");
    const hiddenAssetField = document.getElementById("asset_selection_modal");
    const hiddenLabIdInput = document.getElementById("labIdInput");
    const hiddenServiceInput = document.getElementById("service_id_modal");
    const selectedServiceSummary = document.getElementById("selectedServiceSummary");
    const serviceButtons = document.querySelectorAll(".select-service-btn");
    const todayDate = new Date();
    todayDate.setHours(0, 0, 0, 0);
    let selectedService = null;

    // -------------------------------
    // Check if equipment is available based on status
    // -------------------------------
    function isEquipmentAvailable(assetId) {
        const row = document.querySelector(`.service-asset-card[data-asset-id="${assetId}"]`);
        if (!row) return false;

        const status = row.dataset.status || '';
        const qty = parseInt(row.dataset.quantity || '0', 10);

        return status !== 'maintenance' && status !== 'faulty' && qty > 0;
    }

    function getSelectedServiceId() {
        return selectedService && selectedService.id ? String(selectedService.id) : "";
    }

    function buildServiceInfo(button) {
        if (!button) return null;

        const id = button.dataset.serviceId || "";
        if (!id) return null;

        return {
            id,
            name: button.dataset.serviceName || "Selected Service",
            calibrationStatus: button.dataset.serviceCalibration || "",
            equipmentModels: button.dataset.serviceEquipment || "",
            acceptanceCriteria: button.dataset.serviceCriteria || "",
        };
    }

    function syncServiceContextToModal() {
        const serviceId = getSelectedServiceId();
        if (hiddenServiceInput) {
            hiddenServiceInput.value = serviceId;
        }
        if (typeof window.updateBookingServiceContext === "function") {
            window.updateBookingServiceContext(selectedService);
        }
    }

    function updateServiceButtonStates(serviceId) {
        serviceButtons.forEach(button => {
            const isSelected = serviceId !== "" && button.dataset.serviceId === serviceId;
            button.classList.toggle("btn-primary", isSelected);
            button.classList.toggle("btn-outline-primary", !isSelected);
            button.innerHTML = isSelected
                ? '<i class="bi bi-check2-circle me-1"></i>Selected Service'
                : '<i class="bi bi-check2-square me-1"></i>Choose Service';

            const stateLabel = button.parentElement?.querySelector(".service-state-label");
            if (stateLabel) {
                stateLabel.textContent = isSelected
                    ? "This service is active for availability and booking."
                    : "This service will drive equipment and slot selection.";
            }
        });
    }

    function updateSelectedServiceSummary(linkedCount = 0, availableCount = 0) {
        if (!selectedServiceSummary) return;

        if (!selectedService) {
            selectedServiceSummary.className = "slams-service-status-bar";
            selectedServiceSummary.innerHTML = `
                <i class="bi bi-info-circle me-1"></i>
                Choose a service below to activate the correct equipment set for booking.
            `;
            return;
        }

        const meta = [];
        if (selectedService.calibrationStatus) {
            meta.push(`Calibration: ${selectedService.calibrationStatus}`);
        }
        if (selectedService.acceptanceCriteria) {
            meta.push(`Criteria: ${selectedService.acceptanceCriteria}`);
        }

        const isReady = availableCount > 0;
        selectedServiceSummary.className = `slams-service-status-bar ${isReady ? "slams-ssb--ready" : "slams-ssb--warn"}`;
        selectedServiceSummary.innerHTML = `
            <i class="bi bi-${isReady ? "check-circle" : "exclamation-circle"} me-1"></i>
            <span class="fw-semibold">${selectedService.name}</span>
            <span class="mx-2 opacity-50">·</span>${linkedCount} item(s), ${availableCount} bookable
            ${meta.length ? `<span class="opacity-75 ms-1">${meta.join(" · ")}</span>` : ""}
        `;
    }

    function applyRowSelectionState(row, enabled, checked) {
        const checkbox = row.querySelector(".asset-checkbox");
        const qtyInput = row.querySelector(".asset-qty");
        const assetId = row.dataset.assetId || "";

        if (!checkbox || !qtyInput) return;

        if (!enabled) {
            checkbox.checked = false;
            checkbox.disabled = true;
            qtyInput.disabled = true;
            qtyInput.style.borderColor = "#e2e8f0";
            qtyInput.value = isEquipmentAvailable(assetId) ? 1 : 0;
            return;
        }

        checkbox.disabled = false;
        checkbox.checked = checked;

        if (checked && isEquipmentAvailable(assetId)) {
            qtyInput.disabled = false;
            qtyInput.style.borderColor = "#3b82f6";
            if (!qtyInput.value || parseInt(qtyInput.value, 10) < 1) {
                qtyInput.value = 1;
            }
        } else {
            qtyInput.disabled = true;
            qtyInput.style.borderColor = "#e2e8f0";
        }
    }

    function filterAssetsForService(serviceId) {
        let linkedCount = 0;
        let availableCount = 0;

        document.querySelectorAll(".service-asset-card[data-asset-id]").forEach(row => {
            const rowServiceId = row.dataset.serviceId || "";
            const matches = serviceId !== "" && rowServiceId === serviceId;
            const assetId = row.dataset.assetId || "";
            const available = isEquipmentAvailable(assetId);

            if (!matches) {
                applyRowSelectionState(row, false, false);
                return;
            }

            linkedCount++;
            if (available) {
                availableCount++;
            }

            applyRowSelectionState(row, available, available);
        });

        return { linkedCount, availableCount };
    }

    function selectService(service) {
        selectedService = service && service.id ? service : null;
        const serviceId = getSelectedServiceId();

        updateServiceButtonStates(serviceId);

        const { linkedCount, availableCount } = filterAssetsForService(serviceId);
        updateSelectedServiceSummary(linkedCount, availableCount);
        updateBookingButton();
        syncAssetSelectionToModal();
        syncServiceContextToModal();
        refreshCalendar();
    }

    function selectServiceById(serviceId) {
        if (!serviceId) return false;
        const button = document.querySelector(`.select-service-btn[data-service-id="${serviceId}"]`);
        if (!button) return false;

        selectService(buildServiceInfo(button));
        return true;
    }

    // -------------------------------
    // Build asset selection string
    // -------------------------------
    function buildAssetSelectionString() {
        const parts = [];
        document.querySelectorAll(".asset-checkbox").forEach(cb => {
            const id = cb.dataset.assetId;
            
            // Skip if equipment is not available
            if (!isEquipmentAvailable(id)) return;
            
            // Skip if not checked
            if (!cb.checked) return;
            
            const qtyInput = document.querySelector(`.asset-qty[data-asset-id="${id}"]`);
            if (qtyInput && qtyInput.value) {
                const qty = parseInt(qtyInput.value, 10);
                if (!isNaN(qty) && qty > 0) {
                    parts.push(`${id}:${qty}`);
                }
            }
        });
        return parts.join(",");
    }

    function syncAssetSelectionToModal() {
        if (!hiddenAssetField) return;
        hiddenAssetField.value = buildAssetSelectionString();
        window.dispatchEvent(new Event("assetSelectionUpdated"));
    }

    // -------------------------------
    // Get available assets count
    // -------------------------------
    function getAvailableAssetsCount() {
        let count = 0;
        document.querySelectorAll(".asset-checkbox").forEach(cb => {
            const id = cb.dataset.assetId;
            if (isEquipmentAvailable(id) && cb.checked) {
                count++;
            }
        });
        return count;
    }

    // -------------------------------
    // Update booking button state
    // -------------------------------
    function updateBookingButton() {
        if (!openWizardBtn) return;

        const availableCount = getAvailableAssetsCount();

        if (BOOKING_MODE === 'uthm') {
            if (!getSelectedServiceId()) {
                openWizardBtn.disabled = true;
                openWizardBtn.innerHTML = '<i class="bi bi-magic me-1"></i>Launch Booking Wizard (Select Service First)';
            } else if (availableCount > 0) {
                openWizardBtn.disabled = false;
                openWizardBtn.innerHTML = '<i class="bi bi-magic me-1"></i>Launch Booking Wizard';
            } else {
                openWizardBtn.disabled = true;
                openWizardBtn.innerHTML = '<i class="bi bi-magic me-1"></i>No Bookable Equipment for Selected Service';
            }
        }
    }

    // -------------------------------
    // Enable/disable quantity inputs with animations
    // -------------------------------
    assetCheckboxes.forEach(cb => {
        cb.addEventListener("change", () => {
            const id = cb.dataset.assetId;
            const qtyInput = document.querySelector(`.asset-qty[data-asset-id="${id}"]`);
            const row = cb.closest('.service-asset-card');

            if (!qtyInput || !row) return;

            if (cb.checked && isEquipmentAvailable(id)) {
                qtyInput.disabled = false;
                qtyInput.style.borderColor = '#3b82f6';
                if (!qtyInput.value || parseInt(qtyInput.value, 10) < 1) {
                    qtyInput.value = 1;
                }
            } else {
                qtyInput.disabled = true;
                qtyInput.style.borderColor = '#e2e8f0';
                if (!isEquipmentAvailable(id)) {
                    qtyInput.value = 0;
                }
            }

            updateBookingButton();
            refreshCalendar();
            syncAssetSelectionToModal();
        });
    });

    serviceButtons.forEach(button => {
        button.addEventListener("click", () => {
            selectService(buildServiceInfo(button));
        });
    });

    // Quantity input event listeners
    document.querySelectorAll(".asset-qty").forEach(input => {
        input.addEventListener("change", () => {
            const assetId = input.dataset.assetId;

            if (!isEquipmentAvailable(assetId)) {
                input.value = 0;
                return;
            }
            
            if (parseInt(input.value, 10) < 1) input.value = 1;
            const max = parseInt(input.max, 10);
            if (parseInt(input.value, 10) > max) input.value = max;
            
            refreshCalendar();
            syncAssetSelectionToModal();
        });
        
        input.addEventListener("input", () => {
            const assetId = input.dataset.assetId;
            if (!isEquipmentAvailable(assetId)) {
                input.value = 0;
            }
        });
    });

    // -------------------------------------------------------
    // Custom month-grid calendar (no external dependencies)
    // -------------------------------------------------------
    const MonthCal = {
        el: null,
        onDateClick: null,
        onMonthChange: null,
        today: null,
        year: 0,
        month: 0,
        eventMap: {},

        init(container, opts) {
            this.el = container;
            this.onDateClick = opts.onDateClick || (() => {});
            this.onMonthChange = opts.onMonthChange || (() => {});
            this.today = new Date();
            this.today.setHours(0, 0, 0, 0);
            this.year = this.today.getFullYear();
            this.month = this.today.getMonth();
            this.render();
        },

        _pad(n) { return String(n).padStart(2, '0'); },

        _dateStr(y, m, d) {
            return `${y}-${this._pad(m + 1)}-${this._pad(d)}`;
        },

        currentRange() {
            const firstDay = new Date(this.year, this.month, 1);
            const lastDay  = new Date(this.year, this.month + 1, 0);

            return {
                start: this._dateStr(firstDay.getFullYear(), firstDay.getMonth(), firstDay.getDate()),
                end: this._dateStr(lastDay.getFullYear(), lastDay.getMonth(), lastDay.getDate()),
            };
        },

        render() {
            if (!this.el) return;

            const firstDay = new Date(this.year, this.month, 1);
            const lastDay  = new Date(this.year, this.month + 1, 0);
            const label    = firstDay.toLocaleDateString('en-MY', { month: 'long', year: 'numeric' });
            const dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

            let cells = dayNames.map(d =>
                `<div class="slams-cal-hdr">${d}</div>`
            ).join('');

            for (let i = 0; i < firstDay.getDay(); i++) {
                cells += `<div class="slams-cal-cell slams-cal-empty"></div>`;
            }

            for (let d = 1; d <= lastDay.getDate(); d++) {
                const cellDate = new Date(this.year, this.month, d);
                cellDate.setHours(0, 0, 0, 0);
                const ds      = this._dateStr(this.year, this.month, d);
                const isPast  = cellDate < this.today;
                const isToday = cellDate.getTime() === this.today.getTime();
                const ev      = this.eventMap[ds] || null;

                let cls = 'slams-cal-cell';
                cls += isPast ? ' slams-cal-past' : ' slams-cal-future';
                if (isToday) cls += ' slams-cal-today';
                if (!isPast && ev === 'unavailable') cls += ' slams-cal-unavail';

                const dateAttr = !isPast ? `data-date="${ds}"` : '';
                cells += `<div class="${cls}" ${dateAttr}><span class="slams-cal-day-num">${d}</span></div>`;
            }

            this.el.innerHTML = `
                <div class="slams-cal-wrap">
                    <div class="slams-cal-nav">
                        <button type="button" class="slams-cal-nav-btn" id="slamsCaPrev">
                            <i class="bi bi-chevron-left"></i> Prev
                        </button>
                        <span class="slams-cal-month-label">${label}</span>
                        <button type="button" class="slams-cal-nav-btn" id="slamsCaNext">
                            Next <i class="bi bi-chevron-right"></i>
                        </button>
                    </div>
                    <div class="slams-cal-grid">${cells}</div>
                    <div class="slams-cal-legend">
                        <span class="slams-cal-legend-item">
                            <span class="slams-cal-dot cal-dot-past"></span> Past
                        </span>
                        <span class="slams-cal-legend-item">
                            <span class="slams-cal-dot cal-dot-open"></span> Open — click to see sessions
                        </span>
                        <span class="slams-cal-legend-item">
                            <span class="slams-cal-dot cal-dot-unavail"></span> Fully booked
                        </span>
                    </div>
                </div>
            `;

            this.el.querySelector('#slamsCaPrev')?.addEventListener('click', () => {
                this.month--;
                if (this.month < 0) { this.month = 11; this.year--; }
                this.render();
                this.onMonthChange();
            });
            this.el.querySelector('#slamsCaNext')?.addEventListener('click', () => {
                this.month++;
                if (this.month > 11) { this.month = 0; this.year++; }
                this.render();
                this.onMonthChange();
            });

            this.el.querySelectorAll('[data-date]').forEach(cell => {
                cell.addEventListener('click', () => {
                    if (cell.classList.contains('slams-cal-unavail')) return;
                    this.el.querySelectorAll('.slams-cal-selected').forEach(c =>
                        c.classList.remove('slams-cal-selected')
                    );
                    cell.classList.add('slams-cal-selected');
                    this.onDateClick(cell.dataset.date);
                });
            });
        },

        setEvents(unavailableDates) {
            this.eventMap = {};
            (unavailableDates || []).forEach(d => { this.eventMap[d] = 'unavailable'; });
            this.render();
        },

        removeAllEvents() { this.setEvents([]); },
        updateSize() {}
    };

    let calendar = null;
    if (calendarEl) {
        MonthCal.init(calendarEl, {
            onDateClick: (ds) => loadDaySlots(ds),
            onMonthChange: () => refreshCalendar()
        });
        calendar = MonthCal;
    }

    // -------------------------------
    // Refresh availability on calendar
    // -------------------------------
    function refreshCalendar() {
        if (!calendar) return;
        const serviceId = getSelectedServiceId();
        const assets = buildAssetSelectionString();
        if (!serviceId || !assets) {
            calendar.removeAllEvents();
            hideDaySlotPanel();
            return;
        }

        const range = typeof calendar.currentRange === "function"
            ? calendar.currentRange()
            : null;
        const params = new URLSearchParams({
            service_id: serviceId,
            assets,
        });

        if (range?.start) params.set("start", range.start);
        if (range?.end) params.set("end", range.end);

        fetch(`/api/calendar-with-assets/${LAB_ID}?${params.toString()}`)
            .then(r => r.json())
            .then(data => {
                calendar.setEvents(data.unavailableDates || []);
            })
            .catch(() => {
                calendar.removeAllEvents();
            });
    }

    // -------------------------------
    // Day slot inline panel
    // -------------------------------
    const daySlotPanel = document.getElementById("daySlotPanel");

    function buildDaySlotSkeleton(formattedDate) {
        return `
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-calendar-event" style="font-size:1.2rem;color:var(--slams-primary)"></i>
                <span style="font-size:1rem;font-weight:800;color:var(--slams-heading);letter-spacing:-0.02em;">${formattedDate}</span>
            </div>
            <div class="slams-skeleton-inline-grid">
                <div class="slams-skeleton-inline-card">
                    <div class="skeleton-row skeleton-row-60"></div>
                    <div class="skeleton-row skeleton-row-80"></div>
                    <div class="skeleton-row skeleton-row-full"></div>
                    <div class="skeleton-row skeleton-row-60"></div>
                </div>
                <div class="slams-skeleton-inline-card">
                    <div class="skeleton-row skeleton-row-60"></div>
                    <div class="skeleton-row skeleton-row-80"></div>
                    <div class="skeleton-row skeleton-row-full"></div>
                    <div class="skeleton-row skeleton-row-60"></div>
                </div>
                <div class="slams-skeleton-inline-card">
                    <div class="skeleton-row skeleton-row-60"></div>
                    <div class="skeleton-row skeleton-row-80"></div>
                    <div class="skeleton-row skeleton-row-full"></div>
                    <div class="skeleton-row skeleton-row-60"></div>
                </div>
            </div>
        `;
    }

    function hideDaySlotPanel() {
        if (!daySlotPanel) return;
        daySlotPanel.classList.add("d-none");
        daySlotPanel.innerHTML = "";
        if (calendarEl) {
            calendarEl.querySelectorAll('.slams-cal-selected').forEach(c =>
                c.classList.remove('slams-cal-selected')
            );
        }
    }

    function loadDaySlots(dateStr) {
        const serviceId = getSelectedServiceId();
        if (!serviceId) {
            showAlert("Please choose a service first.", "warning");
            return;
        }

        const assets = buildAssetSelectionString();
        if (!assets) {
            showAlert("No bookable equipment is linked to the selected service right now.", "warning");
            return;
        }

        if (!daySlotPanel) return;

        const formattedDate = new Date(dateStr + "T00:00:00").toLocaleDateString('en-MY', {
            weekday: 'long', year: 'numeric', month: 'long', day: 'numeric'
        });

        daySlotPanel.innerHTML = buildDaySlotSkeleton(formattedDate);
        daySlotPanel.classList.remove("d-none");
        daySlotPanel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });

        fetch(`/api/bookings/day-with-assets/${LAB_ID}/${dateStr}?service_id=${encodeURIComponent(serviceId)}&assets=${encodeURIComponent(assets)}`)
            .then(r => r.json())
            .then(data => renderDaySlots(dateStr, formattedDate, data.slots || []))
            .catch(() => {
                daySlotPanel.innerHTML = `
                    <div class="alert alert-warning small mb-0">
                        <i class="bi bi-exclamation-triangle me-1"></i>
                        Unable to load sessions for this date.
                    </div>`;
            });
    }

    function renderDaySlots(dateStr, formattedDate, slots) {
        if (!daySlotPanel) return;

        let slotsHtml = '';

        if (!slots.length) {
            slotsHtml = `
                <div class="text-center py-4">
                    <i class="bi bi-calendar-x fs-2 mb-2" style="color:var(--slams-muted)"></i>
                    <p class="text-muted small mb-0">No sessions are configured for this date.</p>
                </div>`;
        } else {
            slotsHtml = `<div class="row g-3">`;
            slots.forEach(slot => {
                const isPast   = !slot.can_book && (slot.reason || '').includes('past');
                const isBooked = !slot.can_book && !isPast;

                let cardBg, badgeHtml, actionHtml = '';

                if (isPast) {
                    cardBg   = 'background:var(--slams-surface-soft);border:1px solid var(--slams-border);opacity:0.72;';
                    badgeHtml = `<span class="slams-slot-badge slams-slot-badge--past">
                        <i class="bi bi-clock-history"></i> Past
                    </span>`;
                } else if (isBooked) {
                    cardBg   = 'background:var(--slams-danger-soft);border:1px solid color-mix(in srgb,var(--slams-danger) 22%,transparent);';
                    badgeHtml = `<span class="slams-slot-badge slams-slot-badge--full">
                        <i class="bi bi-x-circle"></i> Full
                    </span>`;
                } else {
                    cardBg   = 'background:var(--slams-success-soft);border:1px solid color-mix(in srgb,var(--slams-success) 22%,transparent);';
                    badgeHtml = `<span class="slams-slot-badge slams-slot-badge--open">
                        <i class="bi bi-check-circle"></i> Open
                    </span>`;

                    if (BOOKING_MODE === "uthm") {
                        actionHtml = `
                            <button type="button"
                                    class="slams-slot-book-btn book-slot-btn"
                                    data-date="${dateStr}"
                                    data-start="${slot.start}"
                                    data-end="${slot.end}">
                                <i class="bi bi-calendar-plus"></i> Book This Session
                            </button>`;
                    } else if (BOOKING_MODE === "external") {
                        actionHtml = `
                            <a class="slams-slot-book-btn"
                               style="text-decoration:none;background:var(--slams-primary);"
                               href="/dashboard/external/request?lab_id=${LAB_ID}&preferred_date=${dateStr}&preferred_start_time=${slot.start}&preferred_end_time=${slot.end}">
                                <i class="bi bi-clipboard-plus"></i> Request Slot
                            </a>`;
                    }
                }

                // Deduplicate label vs time (slot.label is often just the time range)
                const timeStr = `${slot.start} – ${slot.end}`;
                const showLabel = slot.label && !slot.label.replace(/[:\s\-–]/g, '').includes(
                    slot.start.replace(/[:\s]/g, '')
                );

                const assetRows = (slot.assets || []).map(a => {
                    const ok = a.remaining >= a.requested;
                    return `<div class="slams-slot-asset slams-slot-asset--${ok ? 'ok' : 'err'}">
                        <i class="bi bi-${ok ? 'check-circle-fill' : 'x-circle-fill'}"></i>
                        <span>${a.name}: ${a.remaining} avail.</span>
                    </div>`;
                }).join('');

                slotsHtml += `
                    <div class="col-sm-6 col-lg-4">
                        <div class="slams-slot-card" style="${cardBg}">
                            <div class="slams-slot-card-head">
                                <span class="slams-slot-time">${timeStr}</span>
                                ${badgeHtml}
                            </div>
                            ${showLabel ? `<div class="slams-slot-name">${slot.label}</div>` : ''}
                            ${assetRows ? `<div class="slams-slot-assets">${assetRows}</div>` : ''}
                            ${actionHtml}
                        </div>
                    </div>`;
            });
            slotsHtml += `</div>`;
        }

        daySlotPanel.innerHTML = `
            <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-calendar-event" style="font-size:1.2rem;color:var(--slams-primary)"></i>
                    <span style="font-size:1rem;font-weight:800;color:var(--slams-heading);letter-spacing:-0.02em;">${formattedDate}</span>
                </div>
                <button type="button" class="slams-cal-nav-btn" id="closeDayPanel" style="padding:5px 12px;">
                    <i class="bi bi-x"></i> Close
                </button>
            </div>
            ${slotsHtml}
        `;

        daySlotPanel.querySelector('#closeDayPanel')?.addEventListener('click', hideDaySlotPanel);

        daySlotPanel.querySelectorAll('.book-slot-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                window.selectSlot(btn.dataset.date, btn.dataset.start, btn.dataset.end);
            });
        });
    }

    function showAlert(message, type = "info") {
        const alertClass = {
            info: "alert-info",
            warning: "alert-warning",
            error: "alert-danger",
            success: "alert-success"
        }[type];

        const alert = document.createElement("div");
        alert.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
        alert.style.cssText = `
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            border-radius: 12px;
            border: none;
        `;
        alert.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bi ${type === 'warning' ? 'bi-exclamation-triangle' : type === 'error' ? 'bi-x-circle' : 'bi-info-circle'} me-2"></i>
                <div class="flex-grow-1">${message}</div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        `;

        document.body.appendChild(alert);
        
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 5000);
    }

    function showBookingModal() {
        const modalEl = document.getElementById("bookingModal");
        if (!modalEl || typeof bootstrap === "undefined") {
            return false;
        }
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
        return true;
    }

    // -------------------------------------------------
    // Global function: pre-fill booking wizard from slot
    // -------------------------------------------------
    window.selectSlot = function(dateStr, start, end) {
        if (BOOKING_MODE !== "uthm") {
            showAlert("Online booking is only available for UTHM users.", "warning");
            return;
        }

        const bookingModalEl = document.getElementById("bookingModal");
        const dateField      = document.getElementById("selectedDate");
        const startField     = document.getElementById("startTime");
        const endField       = document.getElementById("endTime");
        const serviceId      = getSelectedServiceId();

        if (!serviceId) {
            showAlert("Please choose a service before booking a slot.", "warning");
            return;
        }

        const assetsString = buildAssetSelectionString();
        if (!assetsString) {
            showAlert("No bookable equipment is linked to the selected service right now.", "warning");
            return;
        }

        if (hiddenAssetField) hiddenAssetField.value = assetsString;
        if (hiddenLabIdInput) hiddenLabIdInput.value = LAB_ID;
        if (hiddenServiceInput) hiddenServiceInput.value = serviceId;
        syncServiceContextToModal();
        syncAssetSelectionToModal();

        if (dateField)  dateField.value  = dateStr;
        if (startField) startField.value = start;
        if (endField)   endField.value   = end;

        if (window.resetBookingWizard) {
            window.resetBookingWizard();
        }

        showBookingModal();
    };

    // -------------------------------------------------
    // "Launch Booking Wizard" button behaviour
    // -------------------------------------------------
    if (openWizardBtn) {
        openWizardBtn.addEventListener("click", () => {
            // Non-UTHM: show simple PIC info modal
            if (BOOKING_MODE !== "uthm") {
                const modalEl = document.getElementById("bookingModal");
                if (modalEl) {
                    showBookingModal();
                } else {
                    showAlert("For external bookings, please contact the Person in Charge shown above.", "info");
                }
                return;
            }

            // UTHM: normal booking wizard flow
            const serviceId = getSelectedServiceId();
            if (!serviceId) {
                showAlert("Please choose a service before proceeding.", "warning");
                return;
            }

            const assetsString = buildAssetSelectionString();
            if (!assetsString) {
                showAlert("No bookable equipment is linked to the selected service right now.", "warning");
                return;
            }

            if (hiddenAssetField) hiddenAssetField.value = assetsString;
            if (hiddenLabIdInput) hiddenLabIdInput.value = LAB_ID;
            if (hiddenServiceInput) hiddenServiceInput.value = serviceId;
            syncServiceContextToModal();
            syncAssetSelectionToModal();

            if (window.resetBookingWizard) {
                window.resetBookingWizard();
            }

            showBookingModal();
        });
    }

    function applyQrSelectionFromQuery() {
        const params = new URLSearchParams(window.location.search);
        const assetParam = params.get("asset");
        if (!assetParam) return false;

        const assetId = assetParam.replace(/[^0-9]/g, "");
        if (!assetId) return false;

        const row = document.querySelector(`.service-asset-card[data-asset-id="${assetId}"]`);
        const checkbox = document.querySelector(`.asset-checkbox[data-asset-id="${assetId}"]`);
        const qtyInput = document.querySelector(`.asset-qty[data-asset-id="${assetId}"]`);

        if (!checkbox) {
            showAlert("Selected equipment is not listed in this laboratory.", "warning");
            return false;
        }

        const rowServiceId = row?.dataset.serviceId || params.get("service") || "";
        if (!rowServiceId || !selectServiceById(rowServiceId)) {
            showAlert("Selected equipment is not linked to a bookable service.", "warning");
            return false;
        }

        if (!isEquipmentAvailable(assetId)) {
            showAlert("Selected equipment is not available for booking right now.", "warning");
            return false;
        }

        checkbox.checked = true;

        if (qtyInput) {
            const maxRaw = parseInt(qtyInput.max || "1", 10);
            const requestedRaw = parseInt(params.get("qty") || "1", 10);
            const max = Number.isNaN(maxRaw) ? 1 : maxRaw;
            const requested = Number.isNaN(requestedRaw) ? 1 : requestedRaw;
            const safeQty = Math.min(Math.max(requested, 1), max);

            qtyInput.disabled = false;
            qtyInput.value = safeQty;
        }

        updateBookingButton();
        refreshCalendar();
        syncAssetSelectionToModal();

        const openWizard = params.get("open") === "1";
        if (!openWizard) return true;

        if (BOOKING_MODE === "external") {
            window.location.href = `/dashboard/external/request?lab_id=${LAB_ID}`;
            return true;
        }

        if (BOOKING_MODE === "guest") {
            showAlert("Login with an external account to submit a lab access request.", "warning");
            return true;
        }

        const assetsString = buildAssetSelectionString();
        if (hiddenAssetField) hiddenAssetField.value = assetsString;
        if (hiddenLabIdInput) hiddenLabIdInput.value = LAB_ID;
        if (hiddenServiceInput) hiddenServiceInput.value = rowServiceId;
        syncServiceContextToModal();

        if (window.resetBookingWizard) {
            window.resetBookingWizard();
        }

        showBookingModal();

        return true;
    }

    // -------------------------------------------------------
    // Equipment detail modal
    // -------------------------------------------------------
    const equipDetailModal = document.getElementById('equipmentDetailModal');
    if (equipDetailModal && typeof bootstrap !== 'undefined') {
        const bsEquipModal = bootstrap.Modal.getOrCreateInstance(equipDetailModal);
        const placeholderImg = '<?= base_url('images/assets/placeholder_asset.png') ?>';

        document.querySelector('.slams-service-section')?.addEventListener('click', (e) => {
            const card = e.target.closest('.service-asset-card');
            if (!card) return;
            if (e.target.closest('.service-asset-booking')) return;

            const name      = card.dataset.assetName     || 'Equipment';
            const category  = card.dataset.assetCategory || '';
            const status    = card.dataset.status        || 'unknown';
            const qty       = parseInt(card.dataset.quantity || '0', 10);
            const img       = card.dataset.assetImg      || '';
            const svcName   = card.dataset.serviceName   || '';

            document.getElementById('edModalName').textContent     = name;
            document.getElementById('edModalCategory').textContent = category;
            document.getElementById('edModalQty').textContent      = `Qty: ${qty}`;
            document.getElementById('edModalService').innerHTML    = svcName
                ? `<i class="bi bi-list-check me-1"></i>${svcName}` : '';

            const statusEl = document.getElementById('edModalStatus');
            statusEl.className = 'asset-status-badge ' + (
                status === 'available'   ? 'asset-status-badge--available'   :
                status === 'maintenance' ? 'asset-status-badge--maintenance' :
                status === 'faulty'      ? 'asset-status-badge--faulty'      : ''
            );
            statusEl.textContent = status.charAt(0).toUpperCase() + status.slice(1);

            const imgWrap = document.getElementById('edModalImg');
            if (img) {
                imgWrap.innerHTML = `<img src="${img}" alt="${name}" onerror="this.onerror=null;this.src='${placeholderImg}'">`;
            } else {
                imgWrap.innerHTML = `<div class="ed-modal-img-placeholder"><i class="bi bi-cpu"></i></div>`;
            }

            bsEquipModal.show();
        });
    }

    const qrApplied = applyQrSelectionFromQuery();
    // Initial calendar load
    if (!qrApplied) {
        if (serviceButtons.length === 1) {
            selectService(buildServiceInfo(serviceButtons[0]));
        } else {
            filterAssetsForService("");
            updateSelectedServiceSummary(0, 0);
            syncServiceContextToModal();
        }
        refreshCalendar();
        updateBookingButton();
    }
});
</script>

<?= $this->endSection() ?>
