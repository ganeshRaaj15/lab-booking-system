<?= $this->extend('layouts/main_user') ?>
<?= $this->section('content') ?>

<?php
$selectedChemicals = ($selectedChemicals ?? 'no') === 'yes' ? 'yes' : 'no';
$formErrors = is_array($formErrors ?? null) ? $formErrors : [];
$recommendationResult = is_array($recommendationResult ?? null) ? $recommendationResult : null;
$documentSummary = is_array($documentSummary ?? null) ? $documentSummary : null;
$analysis = is_array($recommendationResult['analysis'] ?? null) ? $recommendationResult['analysis'] : [];
$recommendations = is_array($recommendationResult['recommendations'] ?? null) ? $recommendationResult['recommendations'] : [];
$manualReviewRequired = (bool) ($recommendationResult['manual_review_required'] ?? false);
?>

<div class="container py-4 py-lg-5 lab-fit-page">
    <div class="lab-fit-hero card border-0 shadow-sm mb-4">
        <div class="card-body p-4 p-lg-5">
            <span class="lab-fit-kicker">Smart Intake</span>
            <h1 class="lab-fit-title mt-2 mb-3">Find Best Lab for My Work</h1>
            <p class="lab-fit-lead mb-0">
                Upload your completed SOP or SWP, and add an SDS when chemicals are involved. The system will compare your procedure against laboratory services, equipment, and safety metadata to suggest the best-fit lab options.
            </p>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-4">
                    <h2 class="h5 fw-semibold mb-3">1. Download the template</h2>
                    <p class="text-muted mb-3">Please download the suitable document.</p>

                    <div class="d-grid gap-3">
                        <a href="<?= site_url('/laboratories/find-best-lab/templates/sop') ?>" class="btn btn-outline-primary btn-lg text-start">
                            <div class="fw-semibold">Download SOP Template</div>
                            <div class="small text-muted">For standard laboratory operating procedures.</div>
                        </a>

                        <a href="<?= site_url('/laboratories/find-best-lab/templates/swp') ?>" class="btn btn-outline-primary btn-lg text-start">
                            <div class="fw-semibold">Download SWP Template</div>
                            <div class="small text-muted">For task-specific safe work procedures.</div>
                        </a>
                    </div>

                    <hr class="my-4">

                    <h2 class="h5 fw-semibold mb-3">2. Upload your documents</h2>
                    <p class="text-muted small mb-3">
                        Supported formats: DOC, DOCX, PDF, TXT, HTML, MD, and RTF. The first version works best when you keep the section headings from the provided template.
                    </p>

                    <?php if ($formErrors !== []): ?>
                        <div class="alert alert-danger small">
                            <div class="fw-semibold mb-2">Upload could not be processed.</div>
                            <ul class="mb-0 ps-3">
                                <?php foreach ($formErrors as $error): ?>
                                    <li><?= esc((string) $error) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    <?php endif; ?>

                    <form method="post" action="<?= site_url('/laboratories/find-best-lab/suggest') ?>" enctype="multipart/form-data" class="d-grid gap-3">
                        <?= csrf_field() ?>

                        <div>
                            <label class="form-label fw-semibold">Procedure document</label>
                            <input type="file"
                                   name="work_document"
                                   class="form-control"
                                   accept=".doc,.docx,.pdf,.txt,.html,.htm,.md,.rtf"
                                   required>
                            <div class="form-text">Upload the completed SOP or SWP file.</div>
                        </div>

                        <div>
                            <label class="form-label fw-semibold d-block mb-2">Are chemicals involved?</label>
                            <div class="d-flex flex-wrap gap-3">
                                <label class="form-check form-check-inline m-0">
                                    <input class="form-check-input chemicals-toggle"
                                           type="radio"
                                           name="chemicals_involved"
                                           value="no"
                                           <?= $selectedChemicals === 'no' ? 'checked' : '' ?>>
                                    <span class="form-check-label">No</span>
                                </label>
                                <label class="form-check form-check-inline m-0">
                                    <input class="form-check-input chemicals-toggle"
                                           type="radio"
                                           name="chemicals_involved"
                                           value="yes"
                                           <?= $selectedChemicals === 'yes' ? 'checked' : '' ?>>
                                    <span class="form-check-label">Yes</span>
                                </label>
                            </div>
                        </div>

                        <div id="sdsUploadPanel" class="<?= $selectedChemicals === 'yes' ? '' : 'd-none' ?>">
                            <label class="form-label fw-semibold">SDS upload</label>
                            <input type="file"
                                   name="sds_document"
                                   class="form-control"
                                   accept=".doc,.docx,.pdf,.txt,.html,.htm,.md,.rtf"
                                   <?= $selectedChemicals === 'yes' ? 'required' : '' ?>>
                            <div class="form-text">If chemicals are involved, upload the supplier SDS or a readable SDS export.</div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-stars me-1"></i>
                            Suggest Best-Fit Labs
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body p-4">
                    <h2 class="h5 fw-semibold mb-3">How the recommendation works</h2>
                    <div class="lab-fit-steps">
                        <div class="lab-fit-step">
                            <span class="lab-fit-step-no">1</span>
                            <div>
                                <div class="fw-semibold">Read the uploaded procedure</div>
                                <div class="text-muted small">The system extracts activity, equipment, hazard, and control language from the completed template.</div>
                            </div>
                        </div>
                        <div class="lab-fit-step">
                            <span class="lab-fit-step-no">2</span>
                            <div>
                                <div class="fw-semibold">Check chemical context</div>
                                <div class="text-muted small">When chemicals are involved, SDS content is used to strengthen hazard and control matching.</div>
                            </div>
                        </div>
                        <div class="lab-fit-step">
                            <span class="lab-fit-step-no">3</span>
                            <div>
                                <div class="fw-semibold">Rank laboratory fit</div>
                                <div class="text-muted small">Labs are scored against services, linked equipment, safety notes, and current equipment availability.</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($documentSummary !== null): ?>
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-body p-4">
                        <h2 class="h5 fw-semibold mb-3">Document processing summary</h2>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <div class="lab-fit-summary-tile">
                                    <div class="text-muted small text-uppercase">Procedure file</div>
                                    <div class="fw-semibold"><?= esc((string) ($documentSummary['work_document']['name'] ?? '')) ?></div>
                                    <div class="small text-muted">
                                        <?= esc(strtoupper((string) ($documentSummary['work_document']['extension'] ?? ''))) ?>
                                        · <?= esc((string) ($documentSummary['work_document']['char_count'] ?? 0)) ?> chars
                                        · <?= esc((string) ($documentSummary['work_document']['line_count'] ?? 0)) ?> lines
                                    </div>
                                </div>
                            </div>
                            <?php if (! empty($documentSummary['sds_document'])): ?>
                                <div class="col-md-6">
                                    <div class="lab-fit-summary-tile">
                                        <div class="text-muted small text-uppercase">SDS file</div>
                                        <div class="fw-semibold"><?= esc((string) ($documentSummary['sds_document']['name'] ?? '')) ?></div>
                                        <div class="small text-muted">
                                            <?= esc(strtoupper((string) ($documentSummary['sds_document']['extension'] ?? ''))) ?>
                                            · <?= esc((string) ($documentSummary['sds_document']['char_count'] ?? 0)) ?> chars
                                            · <?= esc((string) ($documentSummary['sds_document']['line_count'] ?? 0)) ?> lines
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <div class="lab-fit-summary-tile">
                                    <div class="text-muted small text-uppercase mb-2">Activity terms</div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach (array_slice($analysis['activity_keywords'] ?? [], 0, 10) as $term): ?>
                                            <span class="badge text-bg-light border"><?= esc((string) $term) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="lab-fit-summary-tile">
                                    <div class="text-muted small text-uppercase mb-2">Equipment terms</div>
                                    <div class="d-flex flex-wrap gap-2">
                                        <?php foreach (array_slice($analysis['equipment_keywords'] ?? [], 0, 10) as $term): ?>
                                            <span class="badge text-bg-light border"><?= esc((string) $term) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php if (! empty($analysis['hazard_flags']) || ! empty($analysis['parser_notes'])): ?>
                            <div class="mt-3">
                                <?php if (! empty($analysis['hazard_flags'])): ?>
                                    <div class="mb-2">
                                        <span class="text-muted small text-uppercase d-block mb-2">Detected hazard themes</span>
                                        <div class="d-flex flex-wrap gap-2">
                                            <?php foreach ($analysis['hazard_flags'] as $flag): ?>
                                                <span class="badge text-bg-warning"><?= esc(ucfirst((string) $flag)) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <?php if (! empty($analysis['parser_notes'])): ?>
                                    <div class="alert alert-secondary small mb-0 mt-3">
                                        <div class="fw-semibold mb-2">Parser notes</div>
                                        <ul class="mb-0 ps-3">
                                            <?php foreach ($analysis['parser_notes'] as $note): ?>
                                                <li><?= esc((string) $note) ?></li>
                                            <?php endforeach; ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($recommendationResult !== null): ?>
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">
                        <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <h2 class="h5 fw-semibold mb-1">Suggested laboratories</h2>
                                <p class="text-muted mb-0">Review the ranked fit, matched services, and safety context before booking.</p>
                            </div>
                            <?php if ($manualReviewRequired): ?>
                                <span class="badge text-bg-warning px-3 py-2">Manual review recommended</span>
                            <?php endif; ?>
                        </div>

                        <?php if ($recommendations === []): ?>
                            <div class="alert alert-warning mb-0">
                                No suitable laboratory could be suggested from the uploaded documents. Refine the SOP/SWP detail or ask the PIC to review the activity manually.
                            </div>
                        <?php else: ?>
                            <div class="d-grid gap-3">
                                <?php foreach ($recommendations as $recommendation): ?>
                                    <div class="lab-fit-result card border">
                                        <div class="card-body">
                                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-3">
                                                <div>
                                                    <div class="small text-muted text-uppercase">Recommended lab</div>
                                                    <h3 class="h5 mb-1"><?= esc((string) ($recommendation['lab_name'] ?? '')) ?></h3>
                                                    <div class="text-muted small">
                                                        <?php if (! empty($recommendation['room'])): ?>
                                                            Room <?= esc((string) $recommendation['room']) ?>
                                                        <?php else: ?>
                                                            Room not specified
                                                        <?php endif; ?>
                                                        · <?= esc((string) ($recommendation['available_assets'] ?? 0)) ?>/<?= esc((string) ($recommendation['total_assets'] ?? 0)) ?> asset records currently available
                                                    </div>
                                                </div>

                                                <div class="text-end">
                                                    <div class="lab-fit-score"><?= esc((string) ($recommendation['fit_score'] ?? 0)) ?>%</div>
                                                    <div class="small text-muted">Fit score</div>
                                                </div>
                                            </div>

                                            <?php if (! empty($recommendation['reasons'])): ?>
                                                <ul class="small mb-3 ps-3">
                                                    <?php foreach ($recommendation['reasons'] as $reason): ?>
                                                        <li><?= esc((string) $reason) ?></li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>

                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <div class="lab-fit-summary-tile h-100">
                                                        <div class="text-muted small text-uppercase mb-2">Matched services</div>
                                                        <?php if (! empty($recommendation['matched_services'])): ?>
                                                            <div class="d-flex flex-wrap gap-2">
                                                                <?php foreach ($recommendation['matched_services'] as $serviceName): ?>
                                                                    <span class="badge text-bg-light border"><?= esc((string) $serviceName) ?></span>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="text-muted small">No direct service match was found.</div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>

                                                <div class="col-md-6">
                                                    <div class="lab-fit-summary-tile h-100">
                                                        <div class="text-muted small text-uppercase mb-2">Matched equipment</div>
                                                        <?php if (! empty($recommendation['matched_assets'])): ?>
                                                            <div class="d-flex flex-wrap gap-2">
                                                                <?php foreach ($recommendation['matched_assets'] as $assetName): ?>
                                                                    <span class="badge text-bg-light border"><?= esc((string) $assetName) ?></span>
                                                                <?php endforeach; ?>
                                                            </div>
                                                        <?php else: ?>
                                                            <div class="text-muted small">No direct equipment match was found.</div>
                                                        <?php endif; ?>
                                                    </div>
                                                </div>
                                            </div>

                                            <?php if (! empty($recommendation['safety_excerpt']) || ! empty($recommendation['description_excerpt'])): ?>
                                                <div class="mt-3 small text-muted">
                                                    <?php if (! empty($recommendation['safety_excerpt'])): ?>
                                                        <div><span class="fw-semibold text-dark">Safety note:</span> <?= esc((string) $recommendation['safety_excerpt']) ?></div>
                                                    <?php endif; ?>
                                                    <?php if (! empty($recommendation['description_excerpt'])): ?>
                                                        <div class="mt-1"><span class="fw-semibold text-dark">Lab description:</span> <?= esc((string) $recommendation['description_excerpt']) ?></div>
                                                    <?php endif; ?>
                                                </div>
                                            <?php endif; ?>

                                            <div class="mt-3 d-flex flex-wrap gap-2">
                                                <a href="<?= site_url('/laboratories/' . (int) ($recommendation['lab_id'] ?? 0)) ?>" class="btn btn-primary btn-sm">
                                                    <i class="bi bi-eye me-1"></i>
                                                    View Lab Details
                                                </a>
                                                <?php if (! empty($recommendation['chemical_ready'])): ?>
                                                    <span class="badge text-bg-success align-self-center">Chemical-ready metadata matched</span>
                                                <?php elseif ($selectedChemicals === 'yes'): ?>
                                                    <span class="badge text-bg-warning align-self-center">Chemical work needs manual review</span>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.lab-fit-hero {
    background: linear-gradient(135deg, #f7f1e4 0%, #fffdf8 55%, #e3ece7 100%);
}
.lab-fit-kicker {
    display: inline-block;
    padding: 0.35rem 0.75rem;
    border-radius: 999px;
    background: #20362d;
    color: #fff;
    font-size: 0.78rem;
    letter-spacing: 0.08em;
    text-transform: uppercase;
}
.lab-fit-title {
    font-size: clamp(1.9rem, 3vw, 3rem);
    font-weight: 800;
    color: #16241d;
}
.lab-fit-lead {
    max-width: 62rem;
    color: #4f5f57;
    font-size: 1rem;
}
.lab-fit-steps {
    display: grid;
    gap: 1rem;
}
.lab-fit-step {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: 0.85rem;
    align-items: start;
}
.lab-fit-step-no {
    width: 2rem;
    height: 2rem;
    border-radius: 50%;
    background: #20362d;
    color: #fff;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-weight: 700;
}
.lab-fit-summary-tile {
    padding: 0.9rem 1rem;
    border-radius: 1rem;
    background: #f8faf9;
    border: 1px solid #e5ebe7;
}
.lab-fit-score {
    min-width: 4.5rem;
    padding: 0.6rem 0.8rem;
    border-radius: 1rem;
    background: #20362d;
    color: #fff;
    font-size: 1.25rem;
    font-weight: 800;
}
.lab-fit-result {
    border-radius: 1rem;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const toggles = document.querySelectorAll('.chemicals-toggle');
    const sdsPanel = document.getElementById('sdsUploadPanel');
    const sdsInput = sdsPanel ? sdsPanel.querySelector('input[name="sds_document"]') : null;

    function syncSdsPanel() {
        const selected = document.querySelector('.chemicals-toggle:checked');
        const needsSds = selected && selected.value === 'yes';

        if (!sdsPanel || !sdsInput) {
            return;
        }

        sdsPanel.classList.toggle('d-none', !needsSds);
        sdsInput.required = !!needsSds;
    }

    toggles.forEach(toggle => {
        toggle.addEventListener('change', syncSdsPanel);
    });

    syncSdsPanel();
});
</script>

<?= $this->endSection() ?>
