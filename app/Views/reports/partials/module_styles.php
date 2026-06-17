<style>
/* ─── Analytics page tweaks ──────────────────────────────────────────────── */

/* Stat boxes in overview strip */
.rpt-stat-box {
    text-align: center;
    padding: 1rem 0.75rem;
}

.rpt-stat-label {
    font-size: 0.7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--slams-muted);
    margin-bottom: 0.3rem;
}

.rpt-stat-value {
    font-family: var(--slams-font-display, inherit);
    font-size: clamp(1.4rem, 2.8vw, 2rem);
    font-weight: 700;
    line-height: 1.1;
    color: var(--slams-primary);
}

/* Tab nav pill look */
.rpt-nav .nav-link {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    font-size: 0.83rem;
    font-weight: 600;
    color: var(--slams-muted);
    white-space: nowrap;
    padding: 0.45rem 0.9rem;
    border-radius: var(--slams-radius, 8px);
    border: 1px solid transparent;
    transition: color 0.15s, background 0.15s, border-color 0.15s;
}

.rpt-nav .nav-link:hover {
    color: var(--slams-heading);
    background: var(--slams-surface-soft);
    border-color: var(--slams-border);
}

.rpt-nav .nav-link.active {
    background: var(--slams-primary) !important;
    color: #fff !important;
    border-color: var(--slams-primary) !important;
}

/* Tab strip container */
.rpt-nav-wrap {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
    scrollbar-width: none;
    padding: 0.6rem 0.85rem;
    background: var(--slams-surface-soft);
    border: 1px solid var(--slams-border);
    border-radius: var(--slams-radius, 8px);
}

.rpt-nav-wrap::-webkit-scrollbar { display: none; }

.rpt-nav-wrap .nav { flex-wrap: nowrap; gap: 0.3rem; min-width: max-content; }

/* Section table cards */
.rpt-table-card .card-header {
    padding: 0.65rem 1rem;
    font-size: 0.87rem;
    font-weight: 700;
    border-bottom: 1px solid var(--slams-border);
}

.rpt-table-card table {
    font-size: 0.83rem;
    margin: 0;
}

.rpt-table-card thead th {
    font-size: 0.73rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--slams-muted);
    padding: 0.5rem 0.85rem;
    white-space: nowrap;
}

.rpt-table-card tbody td {
    padding: 0.5rem 0.85rem;
    vertical-align: middle;
}

.rpt-table-card tbody tr:first-child td { border-top: none; }

.rpt-empty {
    padding: 2rem 1rem;
    text-align: center;
    color: var(--slams-muted);
    font-size: 0.84rem;
}

/* Applied-filter pills */
.rpt-pill-row {
    display: flex;
    flex-wrap: wrap;
    gap: 0.4rem;
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
}

.rpt-pill-label { color: var(--slams-muted); font-weight: 700; }

/* Chart cards */
.rpt-chart-card { border: 1px solid var(--slams-border); border-radius: var(--slams-radius, 8px); padding: 1rem 1.1rem; background: var(--slams-surface); }
.rpt-chart-card h6 { margin: 0 0 0.75rem; font-size: 0.9rem; font-weight: 700; }

@media (max-width: 575.98px) {
    .rpt-stat-value { font-size: 1.35rem; }
}
</style>
