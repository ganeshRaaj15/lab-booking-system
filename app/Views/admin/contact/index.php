<?= $this->extend('layouts/main_admin') ?>
<?= $this->section('content') ?>

<div class="settings-page">
    <!-- PAGE HEADER -->
    <div class="dashboard-header">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4 gap-3">
            <div>
                <h1>Contact Page Settings</h1>
                <p>Manage the information displayed on the public Contact Us page</p>
            </div>
            <div class="d-flex gap-2">
                <a href="/contact" target="_blank" class="btn btn-outline-secondary">
                    <i class="bi bi-box-arrow-up-right me-1"></i> Preview Contact Page
                </a>
                <a href="/dashboard/admin" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </div>

    <!-- FLASH MESSAGES -->
    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success alert-glass mb-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-check-circle-fill fs-5 me-2"></i>
                <div class="flex-grow-1"><?= esc(session()->getFlashdata('message')) ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-glass mb-4">
            <div class="d-flex align-items-center">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                <div class="flex-grow-1"><?= esc(session()->getFlashdata('error')) ?></div>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('errors')): ?>
        <div class="alert alert-danger alert-glass mb-4">
            <div class="d-flex">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                <div class="flex-grow-1">
                    <div class="fw-semibold mb-1">Please fix the following errors:</div>
                    <ul class="mb-0 ps-3">
                        <?php foreach (session()->getFlashdata('errors') as $err): ?>
                            <li><?= esc($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('personnel_errors')): ?>
        <div class="alert alert-danger alert-glass mb-4">
            <div class="d-flex">
                <i class="bi bi-exclamation-triangle-fill fs-5 me-2"></i>
                <div class="flex-grow-1">
                    <div class="fw-semibold mb-1">Personnel form errors:</div>
                    <ul class="mb-0 ps-3">
                        <?php foreach (session()->getFlashdata('personnel_errors') as $err): ?>
                            <li><?= esc($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- ================================================================ -->
    <!-- FACULTY INFORMATION                                                -->
    <!-- ================================================================ -->
    <div class="glass-card mb-5">
        <div class="settings-card-header">
            <h5><i class="bi bi-building"></i> Faculty Information</h5>
        </div>
        <div class="card-body p-4">
            <form action="/admin/contact-settings/update" method="post">
                <?= csrf_field() ?>

                <div class="info-box mb-4">
                    <p class="d-flex align-items-center mb-0">
                        <i class="bi bi-info-circle-fill me-2" style="color:#3b82f6;"></i>
                        These details appear in the left column of the Contact Us page.
                    </p>
                </div>

                <div class="row g-4">
                    <!-- Faculty Name -->
                    <div class="col-md-6">
                        <div class="form-group-glass">
                            <label for="faculty_name"><i class="bi bi-building"></i> Faculty Name</label>
                            <input type="text" name="faculty_name" id="faculty_name"
                                   value="<?= esc(old('faculty_name', $contactSettings['faculty_name'])) ?>"
                                   class="form-control form-control-glass" required>
                        </div>
                    </div>

                    <!-- University Name -->
                    <div class="col-md-6">
                        <div class="form-group-glass">
                            <label for="university_name"><i class="bi bi-mortarboard"></i> University Name</label>
                            <input type="text" name="university_name" id="university_name"
                                   value="<?= esc(old('university_name', $contactSettings['university_name'])) ?>"
                                   class="form-control form-control-glass" required>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="col-md-12">
                        <div class="form-group-glass">
                            <label for="address"><i class="bi bi-geo-alt"></i> Address</label>
                            <input type="text" name="address" id="address"
                                   value="<?= esc(old('address', $contactSettings['address'])) ?>"
                                   class="form-control form-control-glass" required>
                        </div>
                    </div>

                    <!-- Phone -->
                    <div class="col-md-4">
                        <div class="form-group-glass">
                            <label for="phone"><i class="bi bi-telephone"></i> Phone</label>
                            <input type="text" name="phone" id="phone"
                                   value="<?= esc(old('phone', $contactSettings['phone'])) ?>"
                                   class="form-control form-control-glass"
                                   placeholder="+607 XXXXXXX">
                        </div>
                    </div>

                    <!-- Fax -->
                    <div class="col-md-4">
                        <div class="form-group-glass">
                            <label for="fax"><i class="bi bi-printer"></i> Fax</label>
                            <input type="text" name="fax" id="fax"
                                   value="<?= esc(old('fax', $contactSettings['fax'])) ?>"
                                   class="form-control form-control-glass"
                                   placeholder="+607 XXXXXXX">
                        </div>
                    </div>

                    <!-- Location -->
                    <div class="col-md-4">
                        <div class="form-group-glass">
                            <label for="location"><i class="bi bi-pin-map"></i> Location Label</label>
                            <input type="text" name="location" id="location"
                                   value="<?= esc(old('location', $contactSettings['location'])) ?>"
                                   class="form-control form-control-glass"
                                   placeholder="e.g. Main Campus, Parit Raja">
                        </div>
                    </div>

                    <!-- Operating Hours -->
                    <div class="col-md-12">
                        <div class="form-group-glass">
                            <label for="operating_hours"><i class="bi bi-clock"></i> Operating Hours</label>
                            <input type="text" name="operating_hours" id="operating_hours"
                                   value="<?= esc(old('operating_hours', $contactSettings['operating_hours'])) ?>"
                                   class="form-control form-control-glass"
                                   placeholder="e.g. 8:00 AM - 5:00 PM (Monday - Friday)">
                        </div>
                    </div>

                    <!-- General Note -->
                    <div class="col-md-6">
                        <div class="form-group-glass">
                            <label for="general_note"><i class="bi bi-info-circle"></i> General Inquiry Note</label>
                            <textarea name="general_note" id="general_note" rows="3"
                                      class="form-control form-control-glass"
                                      placeholder="Short note displayed below faculty contact details"><?= esc(old('general_note', $contactSettings['general_note'])) ?></textarea>
                            <div class="form-hint">Shown in the info box below faculty details.</div>
                        </div>
                    </div>

                    <!-- Personnel Note -->
                    <div class="col-md-6">
                        <div class="form-group-glass">
                            <label for="personnel_note"><i class="bi bi-shield-check"></i> Personnel / SLAMS Note</label>
                            <textarea name="personnel_note" id="personnel_note" rows="3"
                                      class="form-control form-control-glass"
                                      placeholder="Short note displayed below the personnel list"><?= esc(old('personnel_note', $contactSettings['personnel_note'])) ?></textarea>
                            <div class="form-hint">Shown in the info box below the Key Personnel list.</div>
                        </div>
                    </div>
                </div>

                <!-- ============================================================ -->
                <!-- MAP SETTINGS                                                   -->
                <!-- ============================================================ -->
                <hr class="my-4">
                <h6 class="fw-semibold mb-3"><i class="bi bi-map me-2"></i>Map &amp; Location Links</h6>

                <div class="row g-4">
                    <!-- Map Embed Src -->
                    <div class="col-md-12">
                        <div class="form-group-glass">
                            <label for="map_embed_src"><i class="bi bi-code-square"></i> Google Maps Embed URL</label>
                            <textarea name="map_embed_src" id="map_embed_src" rows="3"
                                      class="form-control form-control-glass font-monospace"
                                      placeholder="Paste the src URL from a Google Maps embed iframe"><?= esc(old('map_embed_src', $contactSettings['map_embed_src'])) ?></textarea>
                            <div class="form-hint">Go to Google Maps → Share → Embed a map → copy only the <code>src="..."</code> URL.</div>
                        </div>
                    </div>

                    <!-- Directions URL -->
                    <div class="col-md-4">
                        <div class="form-group-glass">
                            <label for="directions_url"><i class="bi bi-signpost"></i> Get Directions URL</label>
                            <input type="text" name="directions_url" id="directions_url"
                                   value="<?= esc(old('directions_url', $contactSettings['directions_url'])) ?>"
                                   class="form-control form-control-glass">
                        </div>
                    </div>

                    <!-- Google Maps URL -->
                    <div class="col-md-4">
                        <div class="form-group-glass">
                            <label for="google_maps_url"><i class="bi bi-google"></i> Open in Google Maps URL</label>
                            <input type="text" name="google_maps_url" id="google_maps_url"
                                   value="<?= esc(old('google_maps_url', $contactSettings['google_maps_url'])) ?>"
                                   class="form-control form-control-glass">
                        </div>
                    </div>

                    <!-- Waze URL -->
                    <div class="col-md-4">
                        <div class="form-group-glass">
                            <label for="waze_url"><i class="bi bi-signpost-split"></i> Open in Waze URL</label>
                            <input type="text" name="waze_url" id="waze_url"
                                   value="<?= esc(old('waze_url', $contactSettings['waze_url'])) ?>"
                                   class="form-control form-control-glass">
                        </div>
                    </div>

                    <!-- Coordinates -->
                    <div class="col-md-4">
                        <div class="form-group-glass">
                            <label for="coordinates"><i class="bi bi-crosshair"></i> Exact Coordinates</label>
                            <input type="text" name="coordinates" id="coordinates"
                                   value="<?= esc(old('coordinates', $contactSettings['coordinates'])) ?>"
                                   class="form-control form-control-glass"
                                   placeholder="e.g. 1.8564176 N, 103.0881125 E">
                        </div>
                    </div>

                    <!-- Parking Info -->
                    <div class="col-md-4">
                        <div class="form-group-glass">
                            <label for="parking_info"><i class="bi bi-p-square"></i> Parking Info</label>
                            <input type="text" name="parking_info" id="parking_info"
                                   value="<?= esc(old('parking_info', $contactSettings['parking_info'])) ?>"
                                   class="form-control form-control-glass">
                        </div>
                    </div>

                    <!-- Transport Info -->
                    <div class="col-md-4">
                        <div class="form-group-glass">
                            <label for="transport_info"><i class="bi bi-bus-front"></i> Public Transport Info</label>
                            <input type="text" name="transport_info" id="transport_info"
                                   value="<?= esc(old('transport_info', $contactSettings['transport_info'])) ?>"
                                   class="form-control form-control-glass">
                        </div>
                    </div>
                </div>

                <div class="border-top pt-4 mt-4">
                    <button type="submit" class="btn btn-primary-glass px-4">
                        <i class="bi bi-save me-2"></i> Save Contact Settings
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- ================================================================ -->
    <!-- KEY PERSONNEL                                                      -->
    <!-- ================================================================ -->
    <div class="glass-card" id="personnel">
        <div class="settings-card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0"><i class="bi bi-people-fill"></i> Key Personnel Contacts</h5>
            <button type="button" class="btn btn-primary-glass btn-sm" data-bs-toggle="modal" data-bs-target="#addPersonnelModal">
                <i class="bi bi-plus-circle me-1"></i> Add Personnel
            </button>
        </div>

        <div class="card-body p-4">
            <div class="info-box mb-4">
                <p class="d-flex align-items-center mb-0">
                    <i class="bi bi-info-circle-fill me-2" style="color:#3b82f6;"></i>
                    These staff members are listed in the Key Personnel section of the Contact Us page. Use Sort Order to control display sequence (lower = first).
                </p>
            </div>

            <?php if (!empty($personnel)): ?>
                <div class="table-responsive">
                    <table class="table table-glass align-middle">
                        <thead>
                            <tr>
                                <th style="width:40px;">Order</th>
                                <th>Photo</th>
                                <th>Name &amp; Role</th>
                                <th>Phone</th>
                                <th>Email</th>
                                <th class="text-center" style="width:100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($personnel as $person): ?>
                                <tr>
                                    <td class="text-muted small"><?= esc($person['sort_order']) ?></td>
                                    <td>
                                        <?php if (!empty($person['photo_path'])): ?>
                                            <img src="<?= esc(base_url($person['photo_path'])) ?>"
                                                 alt="<?= esc($person['name']) ?>"
                                                 class="rounded-circle"
                                                 style="width:40px;height:40px;object-fit:cover;"
                                                 onerror="this.style.display='none'">
                                        <?php else: ?>
                                            <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center"
                                                 style="width:40px;height:40px;">
                                                <i class="bi bi-person text-white"></i>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-semibold"><?= esc($person['name']) ?></div>
                                        <div class="small text-muted"><?= esc($person['role']) ?></div>
                                    </td>
                                    <td class="small"><?= esc($person['phone'] ?? '—') ?></td>
                                    <td class="small"><?= esc($person['email'] ?? '—') ?></td>
                                    <td class="text-center">
                                        <button type="button"
                                                class="btn btn-sm btn-glass me-1"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editPersonnelModal"
                                                data-id="<?= esc($person['id']) ?>"
                                                data-name="<?= esc($person['name']) ?>"
                                                data-role="<?= esc($person['role']) ?>"
                                                data-phone="<?= esc($person['phone'] ?? '') ?>"
                                                data-email="<?= esc($person['email'] ?? '') ?>"
                                                data-photo="<?= esc($person['photo_path'] ?? '') ?>"
                                                data-sort="<?= esc($person['sort_order']) ?>">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button type="button"
                                                class="btn btn-sm btn-outline-danger"
                                                data-bs-toggle="modal"
                                                data-bs-target="#deletePersonnelModal"
                                                data-id="<?= esc($person['id']) ?>"
                                                data-name="<?= esc($person['name']) ?>">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center text-muted py-5">
                    <i class="bi bi-people fs-1 d-block mb-3"></i>
                    No personnel added yet. Click <strong>Add Personnel</strong> to get started.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- ADD PERSONNEL MODAL                                                -->
<!-- ================================================================ -->
<div class="modal fade" id="addPersonnelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="/admin/contact-settings/personnel/add" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-person-plus me-2"></i>Add Personnel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control form-control-glass" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Role / Title <span class="text-danger">*</span></label>
                            <input type="text" name="role" class="form-control form-control-glass" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" name="phone" class="form-control form-control-glass" placeholder="+607 XXXXXXX">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" class="form-control form-control-glass">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Photo Path / URL</label>
                            <input type="text" name="photo_path" class="form-control form-control-glass" placeholder="images/staff/photo.jpg">
                            <div class="form-text">Relative path from public/ or a full URL.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Sort Order</label>
                            <input type="number" name="sort_order" class="form-control form-control-glass" value="0" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-glass"><i class="bi bi-save me-1"></i> Add</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- EDIT PERSONNEL MODAL                                               -->
<!-- ================================================================ -->
<div class="modal fade" id="editPersonnelModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="editPersonnelForm" action="" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title"><i class="bi bi-pencil me-2"></i>Edit Personnel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="edit_name" class="form-control form-control-glass" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold">Role / Title <span class="text-danger">*</span></label>
                            <input type="text" name="role" id="edit_role" class="form-control form-control-glass" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Phone</label>
                            <input type="text" name="phone" id="edit_phone" class="form-control form-control-glass" placeholder="+607 XXXXXXX">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Email</label>
                            <input type="email" name="email" id="edit_email" class="form-control form-control-glass">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label fw-semibold">Photo Path / URL</label>
                            <input type="text" name="photo_path" id="edit_photo_path" class="form-control form-control-glass" placeholder="images/staff/photo.jpg">
                            <div class="form-text">Relative path from public/ or a full URL.</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Sort Order</label>
                            <input type="number" name="sort_order" id="edit_sort_order" class="form-control form-control-glass" value="0" min="0">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary-glass"><i class="bi bi-save me-1"></i> Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ================================================================ -->
<!-- DELETE PERSONNEL MODAL                                             -->
<!-- ================================================================ -->
<div class="modal fade" id="deletePersonnelModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form id="deletePersonnelForm" action="" method="post">
                <?= csrf_field() ?>
                <div class="modal-header">
                    <h5 class="modal-title text-danger"><i class="bi bi-trash me-2"></i>Remove Personnel</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p>Remove <strong id="deletePersonnelName"></strong> from the contact page?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger"><i class="bi bi-trash me-1"></i> Remove</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {

    // Populate Edit modal
    document.getElementById('editPersonnelModal').addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        document.getElementById('editPersonnelForm').action = '/admin/contact-settings/personnel/update/' + btn.dataset.id;
        document.getElementById('edit_name').value       = btn.dataset.name;
        document.getElementById('edit_role').value       = btn.dataset.role;
        document.getElementById('edit_phone').value      = btn.dataset.phone;
        document.getElementById('edit_email').value      = btn.dataset.email;
        document.getElementById('edit_photo_path').value = btn.dataset.photo;
        document.getElementById('edit_sort_order').value = btn.dataset.sort;
    });

    // Populate Delete modal
    document.getElementById('deletePersonnelModal').addEventListener('show.bs.modal', function (e) {
        const btn = e.relatedTarget;
        document.getElementById('deletePersonnelForm').action = '/admin/contact-settings/personnel/delete/' + btn.dataset.id;
        document.getElementById('deletePersonnelName').textContent = btn.dataset.name;
    });

});
</script>

<?= $this->endSection() ?>
