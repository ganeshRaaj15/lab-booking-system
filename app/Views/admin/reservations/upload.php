<?= $this->extend('layouts/main_admin') ?>
<?= $this->section('content') ?>

<div class="container-fluid">
    <div class="mb-4">
        <a href="/admin/reservations" class="btn btn-sm btn-outline-secondary mb-3"><i class="bi bi-arrow-left me-1"></i>Back</a>
        <h1 class="h3 mb-1">Upload Class Schedule</h1>
        <p class="text-muted mb-0">Import recurring weekly lab reservations for the semester from a CSV file.</p>
    </div>

    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger border-0 shadow-sm"><?= esc(session()->getFlashdata('error')) ?></div>
    <?php endif; ?>

    <div class="row g-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h5 class="card-title mb-0">Select CSV File</h5>
                        <a href="/templates/class-schedule-template.csv" download class="btn btn-sm btn-outline-secondary">
                            <i class="bi bi-download me-1"></i>Download Template
                        </a>
                    </div>
                    <form method="post" action="/admin/reservations/upload/preview" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">CSV File <span class="text-danger">*</span></label>
                            <input type="file" name="csv" class="form-control" accept=".csv,text/csv" required>
                            <div class="form-text">Maximum 2 MB. First row must be the header row.</div>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="bi bi-eye me-2"></i>Preview Import</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <h5 class="card-title mb-3">CSV Format</h5>
                    <p class="small text-muted mb-2">The CSV must have the following columns in order:</p>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered small mb-3">
                            <thead class="table-light">
                                <tr><th>#</th><th>Column</th><th>Example</th></tr>
                            </thead>
                            <tbody>
                                <tr><td>1</td><td>lab_name</td><td>Makmal Fizik</td></tr>
                                <tr><td>2</td><td>day_of_week</td><td>Monday</td></tr>
                                <tr><td>3</td><td>start_time</td><td>08:00</td></tr>
                                <tr><td>4</td><td>end_time</td><td>14:00</td></tr>
                                <tr><td>5</td><td>subject_code</td><td>BDA2223</td></tr>
                                <tr><td>6</td><td>subject_name</td><td>Fluid Mechanics</td></tr>
                                <tr><td>7</td><td>valid_from <span class="text-muted">(optional)</span></td><td>01-06-2026</td></tr>
                                <tr><td>8</td><td>valid_until <span class="text-muted">(optional)</span></td><td>31-10-2026</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <p class="small text-muted mb-1"><strong>Day names accepted:</strong> Monday, Tuesday, Wednesday, Thursday, Friday, Saturday, Sunday (or Mon, Tue, Wed, etc.)</p>
                    <p class="small text-muted mb-0"><strong>Dates:</strong> DD-MM-YYYY format. Leave valid_from / valid_until blank for no semester restriction.</p>
                    <div class="mt-3">
                        <p class="small text-muted mb-1"><strong>Example row:</strong></p>
                        <code class="small">Makmal Fizik,Monday,08:00,14:00,BDA2223,Fluid Mechanics,01-06-2026,31-10-2026</code>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>
