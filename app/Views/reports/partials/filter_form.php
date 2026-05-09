<div class="card reports-filter-card">
    <div class="card-body">
        <form method="get" action="<?= esc($filterAction) ?>" class="row g-3 align-items-end">
            <?php foreach ($filterFields as $field): ?>
                <div class="col-12 col-md-6 col-xl-3">
                    <label class="form-label" for="filter_<?= esc($field['name']) ?>"><?= esc($field['label']) ?></label>
                    <?php if (($field['type'] ?? 'text') === 'select'): ?>
                        <select class="form-select" id="filter_<?= esc($field['name']) ?>" name="<?= esc($field['name']) ?>">
                            <option value="">All</option>
                            <?php foreach ($field['options'] ?? [] as $option): ?>
                                <option value="<?= esc($option['value']) ?>" <?= (string) ($filters[$field['name']] ?? '') === (string) ($option['value'] ?? '') ? 'selected' : '' ?>>
                                    <?= esc($option['label'] ?? $option['value']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    <?php else: ?>
                        <input
                            type="<?= esc($field['type'] ?? 'text') ?>"
                            class="form-control"
                            id="filter_<?= esc($field['name']) ?>"
                            name="<?= esc($field['name']) ?>"
                            value="<?= esc((string) ($filters[$field['name']] ?? '')) ?>"
                        >
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
            <div class="col-12">
                <div class="reports-filter-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-funnel me-1"></i> Apply Filters
                    </button>
                    <a href="<?= esc($filterAction) ?>" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-counterclockwise me-1"></i> Reset
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
