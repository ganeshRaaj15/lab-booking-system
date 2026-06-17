<style>
/* ─── Shell ─────────────────────────────────────────────────────────────── */
.rpt-shell {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

/* ─── Filter collapse ───────────────────────────────────────────────────── */
.rpt-filter-collapse {
    display: grid;
    grid-template-rows: 0fr;
    transition: grid-template-rows 0.28s ease;
    overflow: hidden;
}

.rpt-filter-collapse > div {
    min-height: 0;
}

.rpt-filter-collapse.rpt-filter-open {
    grid-template-rows: 1fr;
}

/* ─── Applied-filter pills ───────────────────────────────────────────────── */
.rpt-pill-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.45rem;
    align-items: center;
}

.rpt-pill {
    display: inline-flex;
    gap: 0.3rem;
    align-items: center;
    padding: 0.28rem 0.7rem;
    border: 1px solid var(--slams-border);
    border-radius: 999px;
    background: var(--slams-surface-soft);
    font-size: 0.8rem;
    color: var(--slams-heading);
}

.rpt-pill-label {
    color: var(--slams-muted);
    font-weight: 700;
}

/* ─── KPI grid (wraps slams-kpi cards) ─────────────────────────────────── */
.rpt-kpi-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(170px, 1fr));
    gap: 0.85rem;
}

/* ─── Tab bar ────────────────────────────────────────────────────────────── */
.rpt-tabs-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    padding-bottom: 2px;
}

.rpt-tabs-wrap::-webkit-scrollbar { display: none; }

.rpt-tabs-bar {
    display: flex;
    gap: 0.35rem;
    min-width: max-content;
    padding: 0.6rem 0.75rem;
    background: var(--slams-surface-soft);
    border: 1px solid var(--slams-border);
    border-radius: var(--slams-radius);
}

.rpt-tab {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.42rem 0.85rem;
    border: 1px solid transparent;
    border-radius: calc(var(--slams-radius) - 2px);
    background: transparent;
    color: var(--slams-muted);
    font-size: 0.83rem;
    font-weight: 600;
    cursor: pointer;
    white-space: nowrap;
    transition: color 0.15s, background 0.15s, border-color 0.15s;
}

.rpt-tab:hover {
    background: var(--slams-surface);
    color: var(--slams-heading);
    border-color: var(--slams-border);
}

.rpt-tab.active {
    background: var(--slams-primary);
    color: #fff;
    border-color: var(--slams-primary);
}

/* ─── Tab panels ─────────────────────────────────────────────────────────── */
.rpt-tab-panel {
    display: none;
    flex-direction: column;
    gap: 1rem;
    animation: rptFadeIn 0.2s ease;
}

.rpt-tab-panel.rpt-tab-panel--active {
    display: flex;
}

@keyframes rptFadeIn {
    from { opacity: 0; transform: translateY(4px); }
    to   { opacity: 1; transform: translateY(0); }
}

/* ─── Overview: stat strip ────────────────────────────────────────────────── */
.rpt-stat-strip {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
    gap: 0.85rem;
}

.rpt-stat-box {
    padding: 1rem 1.1rem;
    background: var(--slams-surface-soft);
    border: 1px solid var(--slams-border);
    border-radius: var(--slams-radius);
    text-align: center;
}

.rpt-stat-box-label {
    font-size: 0.72rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--slams-muted);
}

.rpt-stat-box-value {
    margin-top: 0.35rem;
    font-family: var(--slams-font-display);
    font-size: clamp(1.45rem, 3vw, 2rem);
    line-height: 1.1;
    color: var(--slams-primary);
    font-weight: 700;
}

/* ─── Charts: two-column responsive pair ─────────────────────────────────── */
.rpt-chart-pair {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
    gap: 1rem;
}

.rpt-chart-card {
    background: var(--slams-surface);
    border: 1px solid var(--slams-border);
    border-radius: var(--slams-radius);
    padding: 1rem 1.1rem;
}

.rpt-chart-card h4 {
    margin: 0 0 0.75rem;
    font-size: 0.93rem;
    font-weight: 700;
    color: var(--slams-heading);
}

.rpt-canvas-wrap {
    position: relative;
    width: 100%;
}

/* ─── Section layout: two-col grid ─────────────────────────────────────── */
.rpt-section-header {
    padding: 0.1rem 0 0.5rem;
    border-bottom: 1px solid var(--slams-border);
}

.rpt-section-header h3 {
    margin: 0 0 0.25rem;
    font-size: 1rem;
    font-weight: 700;
}

.rpt-section-header p {
    margin: 0;
    font-size: 0.85rem;
    color: var(--slams-muted);
}

.rpt-section-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1rem;
    align-items: start;
}

.rpt-table-block {
    background: var(--slams-surface);
    border: 1px solid var(--slams-border);
    border-radius: var(--slams-radius);
    overflow: hidden;
}

.rpt-table-block.rpt-full-width {
    grid-column: 1 / -1;
}

.rpt-table-block-head {
    padding: 0.7rem 1rem 0.6rem;
    border-bottom: 1px solid var(--slams-border);
    background: var(--slams-surface-soft);
}

.rpt-table-block-head h4 {
    margin: 0;
    font-size: 0.88rem;
    font-weight: 700;
    color: var(--slams-heading);
}

.rpt-table-block-head p {
    margin: 0.2rem 0 0;
    font-size: 0.78rem;
    color: var(--slams-muted);
}

.rpt-table-body {
    padding: 0;
}

.rpt-table-body .table {
    margin: 0;
    font-size: 0.83rem;
}

.rpt-table-body .table thead th {
    font-size: 0.75rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    padding: 0.55rem 0.85rem;
    background: transparent;
    border-bottom: 1px solid var(--slams-border);
    color: var(--slams-muted);
    white-space: nowrap;
}

.rpt-table-body .table td {
    padding: 0.5rem 0.85rem;
    vertical-align: middle;
}

.rpt-table-body .table td:first-child {
    font-weight: 500;
    color: var(--slams-heading);
}

.rpt-empty {
    padding: 1.5rem 1rem;
    text-align: center;
    color: var(--slams-muted);
    font-size: 0.85rem;
}

/* ─── Governance table card ──────────────────────────────────────────────── */
.rpt-gov-card {
    background: var(--slams-surface);
    border: 1px solid var(--slams-border);
    border-radius: var(--slams-radius);
    overflow: hidden;
}

.rpt-gov-card-head {
    padding: 0.8rem 1rem;
    border-bottom: 1px solid var(--slams-border);
    background: var(--slams-surface-soft);
}

.rpt-gov-card-head h4 {
    margin: 0;
    font-size: 0.93rem;
    font-weight: 700;
}

/* ─── Responsive ─────────────────────────────────────────────────────────── */
@media (max-width: 767.98px) {
    .rpt-section-grid {
        grid-template-columns: 1fr;
    }

    .rpt-table-block.rpt-full-width {
        grid-column: auto;
    }

    .rpt-kpi-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>
