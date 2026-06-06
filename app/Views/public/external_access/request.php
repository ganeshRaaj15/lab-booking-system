<?= $this->extend('layouts/main_user') ?>
<?= $this->section('content') ?>

<div class="container py-5" style="max-width:680px">
    <div class="mb-4 text-center">
        <h2 class="fw-bold text-primary">Request External Access</h2>
        <p class="text-muted">Fill in your details to request an external user account. An administrator will review your request and notify you by email.</p>
    </div>

    <?php if (! empty(session()->getFlashdata('errors'))): ?>
        <div class="alert alert-danger border-0 shadow-sm">
            <ul class="mb-0 ps-3">
                <?php foreach (session()->getFlashdata('errors') as $e): ?>
                    <li><?= esc($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="card border-0 shadow-sm">
        <div class="card-body p-4">
            <form method="post" action="/external-access/submit" class="row g-3">
                <?= csrf_field() ?>

                <div class="col-md-6">
                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                    <input type="text" name="full_name" class="form-control"
                           value="<?= esc(old('full_name')) ?>" required placeholder="Your full legal name">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control"
                           value="<?= esc(old('email')) ?>" required placeholder="you@example.com">
                    <div class="form-text">Your login credentials will be sent to this address.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Phone Number</label>
                    <input type="text" name="phone" class="form-control"
                           value="<?= esc(old('phone')) ?>" placeholder="+60 1x-xxx xxxx">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Organization / Institution <span class="text-danger">*</span></label>
                    <input type="text" name="organization" class="form-control"
                           value="<?= esc(old('organization')) ?>" required placeholder="Your company or university name">
                </div>
                <div class="col-12">
                    <label class="form-label">Purpose of Access <span class="text-danger">*</span></label>
                    <textarea name="purpose" class="form-control" rows="4" required
                              placeholder="Briefly describe why you need access to the FKMP Smart Lab system and what you intend to do…"><?= esc(old('purpose')) ?></textarea>
                </div>
                <div class="col-12">
                    <label class="form-label">Additional Notes</label>
                    <textarea name="notes" class="form-control" rows="2"
                              placeholder="Any supporting information — reference person, project name, etc."><?= esc(old('notes')) ?></textarea>
                </div>
                <div class="col-12 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="bi bi-send me-2"></i>Submit Request
                    </button>
                </div>
            </form>
        </div>
    </div>

    <p class="text-center text-muted small mt-4">
        Already have an account? <a href="/login">Sign in here</a>.
    </p>
</div>

<?= $this->endSection() ?>
