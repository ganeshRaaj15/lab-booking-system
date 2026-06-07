(function () {
    "use strict";

    const shell = document.querySelector("[data-auth-shell]");

    if (!shell) {
        return;
    }

    const panels = Array.from(shell.querySelectorAll("[data-auth-panel]"));
    const modeLinks = Array.from(shell.querySelectorAll("[data-auth-mode-link]"));
    const modeTabs = Array.from(shell.querySelectorAll("[data-auth-mode-tab]"));
    const heroTitle = shell.querySelector("[data-auth-hero-title]");
    const heroCopy = shell.querySelector("[data-auth-hero-copy]");
    const loginUrl = shell.dataset.loginUrl || "/login";
    const registerUrl = shell.dataset.registerUrl || "/register";
    const modeTitles = {
        login: shell.dataset.loginTitle || "Sign in to SLAMS",
        register: shell.dataset.registerTitle || "Create your account",
    };
    const modeCopy = {
        login: shell.dataset.loginCopy || "",
        register: shell.dataset.registerCopy || "",
    };
    const pageTitles = {
        login: "SLAMS | Sign in",
        register: "SLAMS | Create your account",
    };

    function normalizedPath(pathname) {
        const path = pathname.replace(/\/+$/, "");
        return path === "" ? "/" : path;
    }

    function resolveMode(pathname) {
        return normalizedPath(pathname) === normalizedPath(registerUrl) ? "register" : "login";
    }

    function modeUrl(mode) {
        return mode === "register" ? registerUrl : loginUrl;
    }

    function focusActiveField(mode) {
        const panel = shell.querySelector('[data-auth-panel="' + mode + '"]');
        const field = panel
            ? panel.querySelector("input:not([type='hidden']), textarea, select")
            : null;

        if (field instanceof HTMLElement) {
            field.focus({ preventScroll: true });
        }
    }

    function applyMode(mode, options) {
        const settings = Object.assign({ pushState: false, focus: false }, options);

        shell.dataset.authMode = mode;

        panels.forEach(function (panel) {
            const active = panel.dataset.authPanel === mode;
            panel.hidden = !active;
            panel.setAttribute("aria-hidden", active ? "false" : "true");
        });

        modeTabs.forEach(function (tab) {
            const active = tab.dataset.authModeLink === mode;
            tab.classList.toggle("is-active", active);
            tab.setAttribute("aria-selected", active ? "true" : "false");
            tab.setAttribute("tabindex", active ? "0" : "-1");
        });

        if (heroTitle) {
            heroTitle.textContent = modeTitles[mode];
        }

        if (heroCopy) {
            heroCopy.textContent = modeCopy[mode];
        }

        document.title = pageTitles[mode];

        if (settings.pushState && normalizedPath(window.location.pathname) !== normalizedPath(modeUrl(mode))) {
            window.history.pushState({ authMode: mode }, "", modeUrl(mode));
        }

        if (settings.focus) {
            window.requestAnimationFrame(function () {
                focusActiveField(mode);
            });
        }
    }

    function transitionTo(mode, focusField) {
        const shouldFocusField = focusField !== false;

        if (typeof document.startViewTransition === "function") {
            document.startViewTransition(function () {
                applyMode(mode, { pushState: true, focus: shouldFocusField });
            });
            return;
        }

        applyMode(mode, { pushState: true, focus: shouldFocusField });
    }

    modeLinks.forEach(function (link) {
        link.addEventListener("click", function (event) {
            const mode = link.dataset.authModeLink === "register" ? "register" : "login";

            event.preventDefault();

            if (shell.dataset.authMode === mode) {
                focusActiveField(mode);
                return;
            }

            transitionTo(mode, true);
        });
    });

    modeTabs.forEach(function (tab, index) {
        tab.addEventListener("keydown", function (event) {
            const key = event.key;
            let nextIndex = null;

            if (key === "ArrowRight" || key === "ArrowDown") {
                nextIndex = (index + 1) % modeTabs.length;
            } else if (key === "ArrowLeft" || key === "ArrowUp") {
                nextIndex = (index - 1 + modeTabs.length) % modeTabs.length;
            } else if (key === "Home") {
                nextIndex = 0;
            } else if (key === "End") {
                nextIndex = modeTabs.length - 1;
            }

            if (nextIndex === null) {
                return;
            }

            event.preventDefault();
            const nextTab = modeTabs[nextIndex];
            const nextMode = nextTab.dataset.authModeLink === "register" ? "register" : "login";

            nextTab.focus();

            if (shell.dataset.authMode !== nextMode) {
                transitionTo(nextMode, false);
            }
        });
    });

    window.addEventListener("popstate", function () {
        applyMode(resolveMode(window.location.pathname), { focus: false });
    });

    window.history.replaceState({ authMode: resolveMode(window.location.pathname) }, "", window.location.href);
    applyMode(resolveMode(window.location.pathname), { focus: false });

    shell.querySelectorAll("[data-password-toggle]").forEach(function (toggle) {
        const targetId = toggle.getAttribute("data-password-target");
        const input = targetId ? document.getElementById(targetId) : null;
        const icon = toggle.querySelector("i");

        if (!(input instanceof HTMLInputElement) || !(icon instanceof HTMLElement)) {
            return;
        }

        toggle.addEventListener("click", function () {
            const isHidden = input.type === "password";
            input.type = isHidden ? "text" : "password";
            toggle.setAttribute("aria-label", isHidden ? "Hide password" : "Show password");
            icon.classList.toggle("bi-eye", isHidden);
            icon.classList.toggle("bi-eye-slash", !isHidden);
            input.focus({ preventScroll: true });
        });
    });

    const registerPassword = document.getElementById("registerPassword");
    const criteriaBox = shell.querySelector("[data-password-criteria]");

    if (registerPassword instanceof HTMLInputElement && criteriaBox instanceof HTMLElement) {
        const rules = {
            length: function (value) { return value.length >= 8; },
            upper: function (value) { return /[A-Z]/.test(value); },
            lower: function (value) { return /[a-z]/.test(value); },
            number: function (value) { return /[0-9]/.test(value); },
            special: function (value) { return /[^A-Za-z0-9]/.test(value); },
        };

        registerPassword.addEventListener("input", function () {
            const value = registerPassword.value;

            if (value.length === 0) {
                criteriaBox.classList.add("d-none");
                return;
            }

            criteriaBox.classList.remove("d-none");

            criteriaBox.querySelectorAll(".pw-rule").forEach(function (ruleElement) {
                const ruleName = ruleElement.getAttribute("data-rule");
                const met = !!(ruleName && rules[ruleName] && rules[ruleName](value));
                const icon = ruleElement.querySelector("i");

                ruleElement.classList.toggle("met", met);

                if (icon instanceof HTMLElement) {
                    icon.className = met ? "bi bi-check-circle-fill me-1" : "bi bi-circle me-1";
                }
            });
        });

        if (registerPassword.value.length > 0) {
            registerPassword.dispatchEvent(new Event("input"));
        }
    }
})();
