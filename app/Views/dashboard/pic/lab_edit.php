<?php
/** @var array $lab */
/** @var \CodeIgniter\Shield\Entities\User $user */
$lab    = $lab ?? [];
$errors = session()->getFlashdata('errors') ?? [];
?>
<?= $this->extend('layouts/main_user') ?>
<?= $this->section('content') ?>

<div class="container py-4">

    <div class="slams-page-header">
        <div class="slams-page-header-left">
            <h1 class="slams-page-title">Edit Laboratory Details</h1>
            <p class="slams-page-subtitle">
                Update description, availability notes, safety notes, capacity, and images for
                <strong><?= esc($lab['name'] ?? '') ?></strong>.
            </p>
        </div>
        <div class="slams-page-header-actions">
            <a href="/dashboard/pic" class="btn btn-glass btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
            </a>
        </div>
    </div>

    <?php if (! empty($errors)): ?>
        <div class="alert alert-danger border-0 shadow-sm">
            <ul class="mb-0 ps-3">
                <?php foreach ($errors as $e): ?><li><?= esc($e) ?></li><?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0"><?= esc($lab['name'] ?? '') ?> — Room <?= esc($lab['room'] ?? '') ?></h5>
                </div>
                <div class="card-body">
                    <form method="post" action="/dashboard/pic/lab/update/<?= (int) $lab['id'] ?>" enctype="multipart/form-data" class="row g-3">
                        <?= csrf_field() ?>

                        <div class="col-md-4">
                            <label class="form-label">Capacity</label>
                            <input type="number" min="1" name="capacity" class="form-control"
                                   value="<?= esc(old('capacity', $lab['capacity'] ?? '')) ?>"
                                   placeholder="Number of workstations">
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Availability Note</label>
                            <input type="text" name="availability_note" class="form-control"
                                   value="<?= esc(old('availability_note', $lab['availability_note'] ?? '')) ?>"
                                   placeholder="e.g. Weekday access only, shared use note...">
                        </div>

                        <div class="col-12">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="4"><?= esc(old('description', $lab['description'] ?? '')) ?></textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Safety Note</label>
                            <textarea name="safety_note" class="form-control" rows="3"
                                      placeholder="PPE rules, access warnings, required supervision..."><?= esc(old('safety_note', $lab['safety_note'] ?? '')) ?></textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Laboratory Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <?php if (! empty($lab['image_url'])): ?>
                                <div class="mt-2">
                                    <img src="<?= esc($lab['image_url']) ?>" alt="Lab image"
                                         style="width:180px;border-radius:10px;object-fit:cover;">
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="remove_image" id="remove_image">
                                    <label class="form-check-label text-danger small" for="remove_image">Remove current image</label>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">PIC Photo</label>
                            <input type="file" name="pic_image" class="form-control" accept="image/*">
                            <?php if (! empty($lab['pic_image_url'])): ?>
                                <div class="mt-2">
                                    <img src="<?= esc($lab['pic_image_url']) ?>" alt="PIC photo"
                                         style="width:100px;height:100px;object-fit:cover;border-radius:50%;">
                                </div>
                                <div class="form-check mt-2">
                                    <input class="form-check-input" type="checkbox" name="remove_pic_image" id="remove_pic_image">
                                    <label class="form-check-label text-danger small" for="remove_pic_image">Remove current PIC photo</label>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 d-flex justify-content-end gap-2">
                            <a href="/dashboard/pic" class="btn btn-outline-secondary">Cancel</a>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-circle me-1"></i> Save Changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white"><h6 class="mb-0">Read-Only Information</h6></div>
                <div class="card-body small">
                    <div class="mb-2">
                        <span class="text-muted">Laboratory Name</span>
                        <div class="fw-semibold"><?= esc($lab['name'] ?? '—') ?></div>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted">Room</span>
                        <div class="fw-semibold"><?= esc($lab['room'] ?? '—') ?></div>
                    </div>
                    <div class="mb-2">
                        <span class="text-muted">PIC Name</span>
                        <div class="fw-semibold"><?= esc($lab['pic_name'] ?? '—') ?></div>
                    </div>
                    <div class="mb-0">
                        <span class="text-muted">PIC Email</span>
                        <div class="fw-semibold"><?= esc($lab['pic_email'] ?? '—') ?></div>
                    </div>
                </div>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body small text-muted">
                    Laboratory name, room, and PIC assignment are managed by the administrator.
                    To update those fields, contact the system administrator.
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
