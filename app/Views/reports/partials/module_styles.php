<style>
    .reports-shell {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .reports-hero {
        display: flex;
        justify-content: space-between;
        gap: 1rem;
        align-items: flex-start;
        padding: 1rem 1.1rem;
    }

    .reports-hero p {
        max-width: 720px;
        margin-bottom: 0;
    }

    .reports-export-group {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
        align-items: center;
    }

    .reports-nav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
        padding: 0.85rem 1rem;
    }

    .reports-nav-link {
        display: inline-flex;
        align-items: center;
        gap: 0.45rem;
        min-height: 38px;
        padding: 0.45rem 0.8rem;
        border: 1px solid var(--slams-border);
        border-radius: var(--slams-radius);
        background: var(--slams-surface-soft);
        color: var(--slams-heading);
        text-decoration: none;
        font-weight: 700;
    }

    .reports-nav-link.active {
        background: var(--slams-primary);
        border-color: var(--slams-primary);
        color: #ffffff;
    }

    .reports-filter-card .card-body,
    .reports-table-card .card-body,
    .reports-chart-card .card-body {
        padding: 1rem;
    }

    .reports-filter-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 0.65rem;
    }

    .reports-pill-row {
        display: flex;
        flex-wrap: wrap;
        gap: 0.55rem;
    }

    .reports-pill {
        display: inline-flex;
        gap: 0.35rem;
        align-items: center;
        padding: 0.32rem 0.6rem;
        border: 1px solid var(--slams-border);
        border-radius: 999px;
        background: var(--slams-surface-soft);
        color: var(--slams-heading);
        font-size: 0.82rem;
    }

    .reports-pill-label {
        color: var(--slams-muted);
        font-weight: 700;
    }

    .reports-summary-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 0.9rem;
    }

    .reports-summary-card {
        padding: 1rem;
        border-radius: var(--slams-radius);
        border: 1px solid var(--slams-border);
        background:
            linear-gradient(135deg, color-mix(in srgb, var(--slams-primary) 8%, transparent), transparent 56%),
            var(--slams-surface-soft);
        min-height: 120px;
    }

    .reports-summary-card small {
        display: block;
        color: var(--slams-muted);
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.76rem;
    }

    .reports-summary-value {
        margin-top: 0.4rem;
        font-family: var(--slams-font-display);
        font-size: clamp(1.55rem, 3vw, 2.1rem);
        line-height: 1.05;
    }

    .reports-tone-primary {
        border-color: color-mix(in srgb, var(--slams-primary) 30%, var(--slams-border));
    }

    .reports-tone-success {
        border-color: color-mix(in srgb, var(--slams-success) 38%, var(--slams-border));
    }

    .reports-tone-warning {
        border-color: color-mix(in srgb, var(--slams-warning) 40%, var(--slams-border));
    }

    .reports-tone-danger {
        border-color: color-mix(in srgb, var(--slams-danger) 40%, var(--slams-border));
    }

    .reports-tone-info {
        border-color: color-mix(in srgb, var(--slams-info) 38%, var(--slams-border));
    }

    .reports-chart-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1rem;
    }

    .reports-chart-card h3,
    .reports-table-card h3 {
        margin-bottom: 0.85rem;
        font-size: 1.05rem;
    }

    .reports-canvas-wrap {
        position: relative;
        width: 100%;
    }

    .reports-mini-table td,
    .reports-mini-table th {
        white-space: nowrap;
    }

    .reports-mini-table td:first-child,
    .reports-mini-table th:first-child {
        white-space: normal;
    }

    .reports-empty {
        padding: 1rem;
        border: 1px dashed var(--slams-border);
        border-radius: var(--slams-radius);
        background: var(--slams-surface-soft);
        color: var(--slams-muted);
        text-align: center;
    }

    @media (max-width: 767.98px) {
        .reports-hero {
            flex-direction: column;
        }

        .reports-export-group {
            width: 100%;
        }

        .reports-export-group .btn {
            flex: 1 1 auto;
        }
    }
</style>
