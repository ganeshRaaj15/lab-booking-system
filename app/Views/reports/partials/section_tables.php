<?php foreach (($sectionGroups ?? []) as $group): ?>
    <section class="card reports-section-card">
        <div class="card-body">
            <div class="reports-section-header">
                <div>
                    <h3><?= esc($group['title'] ?? 'Analytics Section') ?></h3>
                    <?php if (! empty($group['description'])): ?>
                        <p class="text-muted mb-0"><?= esc($group['description']) ?></p>
                    <?php endif; ?>
                </div>
            </div>

            <div class="reports-table-stack">
                <?php foreach (($group['tables'] ?? []) as $table): ?>
                    <div class="reports-table-block">
                        <div class="reports-table-title-row">
                            <h4><?= esc($table['title'] ?? 'Table') ?></h4>
                            <?php if (! empty($table['description'])): ?>
                                <span class="text-muted small"><?= esc($table['description']) ?></span>
                            <?php endif; ?>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover reports-mini-table">
                                <thead>
                                    <tr>
                                        <?php foreach (($table['columns'] ?? []) as $column): ?>
                                            <th><?= esc($column['label'] ?? $column['key'] ?? 'Column') ?></th>
                                        <?php endforeach; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (($table['rows'] ?? []) === []): ?>
                                        <tr>
                                            <td colspan="<?= esc((string) count($table['columns'] ?? [])) ?>" class="text-center text-muted">
                                                <?= esc($table['emptyMessage'] ?? 'No data available.') ?>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach (($table['rows'] ?? []) as $row): ?>
                                            <tr>
                                                <?php foreach (($table['columns'] ?? []) as $column): ?>
                                                    <td><?= esc((string) ($row[$column['key']] ?? '')) ?></td>
                                                <?php endforeach; ?>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endforeach; ?>
