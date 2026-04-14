<div class="theme-toggle" aria-live="polite">
    <button type="button" class="theme-toggle-btn" id="themeToggle" aria-label="Toggle dark mode">
        <span class="theme-icon" data-mode="light"><i class="bi bi-sun"></i></span>
        <span class="theme-icon" data-mode="dark"><i class="bi bi-moon-stars"></i></span>
    </button>
</div>

<style>
.theme-toggle {
    position: fixed;
    bottom: 96px;
    right: 24px;
    z-index: 1100;
}

@media (max-width: 576px) {
    .theme-toggle {
        bottom: 88px;
        right: 16px;
    }
}

.theme-toggle-btn {
    width: 50px;
    height: 50px;
    border-radius: 16px;
    border: 1px solid rgba(255, 255, 255, 0.25);
    background: rgba(15, 23, 42, 0.8);
    color: #fff;
    display: grid;
    place-items: center;
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.4);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
}

.theme-toggle-btn:hover {
    transform: translateY(-3px);
    box-shadow: 0 20px 35px rgba(15, 23, 42, 0.45);
}

.theme-icon {
    position: absolute;
    opacity: 0;
    transform: scale(0.6);
    transition: all 0.2s ease;
}

:root[data-theme="dark"] .theme-toggle-btn {
    background: rgba(248, 250, 252, 0.12);
    border-color: rgba(148, 163, 184, 0.4);
}

:root[data-theme="dark"] .theme-icon[data-mode="dark"],
:root[data-theme="light"] .theme-icon[data-mode="light"] {
    opacity: 1;
    transform: scale(1);
}
/* Theme toggle contrast fixes */
.theme-icon i {
    color: currentColor;
}

:root[data-theme="light"] .theme-toggle-btn {
    background: rgba(255, 255, 255, 0.9);
    color: #1e293b;
    border-color: rgba(148, 163, 184, 0.35);
    box-shadow: 0 14px 30px rgba(15, 23, 42, 0.2);
}

:root[data-theme="dark"] .theme-toggle-btn {
    background: rgba(15, 23, 42, 0.85);
    color: #e2e8f0;
    border-color: rgba(148, 163, 184, 0.4);
    box-shadow: 0 14px 30px rgba(2, 6, 23, 0.55);
}</style>

<script>
(function() {
    const storageKey = 'slams-theme';
    const root = document.documentElement;
    const body = document.body;

    function applyTheme(theme) {
        root.setAttribute('data-theme', theme);
        body.setAttribute('data-theme', theme);
        localStorage.setItem(storageKey, theme);
    }

    const saved = localStorage.getItem(storageKey);
    applyTheme(saved || 'light');

    const toggle = document.getElementById('themeToggle');
    if (!toggle) {
        return;
    }

    toggle.addEventListener('click', () => {
        const current = root.getAttribute('data-theme') || 'light';
        const next = current === 'dark' ? 'light' : 'dark';
        applyTheme(next);
    });
})();
</script>





