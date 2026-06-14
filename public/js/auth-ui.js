(function () {
    "use strict";

    const passwordRules = {
        length: function (value) { return value.length >= 8; },
        upper: function (value) { return /[A-Z]/.test(value); },
        lower: function (value) { return /[a-z]/.test(value); },
        number: function (value) { return /[0-9]/.test(value); },
        special: function (value) { return /[^A-Za-z0-9]/.test(value); }
    };

    function setPasswordState(button, input, visible) {
        input.type = visible ? "text" : "password";
        button.setAttribute("aria-label", visible ? "Hide password" : "Show password");
        const icon = button.querySelector("i");
        if (icon) {
            icon.classList.toggle("bi-eye", visible);
            icon.classList.toggle("bi-eye-slash", !visible);
        }
    }

    function bindPasswordToggles(scope) {
        scope.querySelectorAll("[data-password-toggle]").forEach(function (button) {
            const input = document.getElementById(button.getAttribute("data-password-toggle"));
            if (!input) {
                return;
            }

            button.addEventListener("click", function () {
                const visible = input.type === "password";
                setPasswordState(button, input, visible);
                input.focus();
            });
        });
    }

    function updateCriteria(input, criteria) {
        if (!input || !criteria) {
            return;
        }

        const value = input.value || "";
        criteria.classList.toggle("d-none", value.length === 0);

        criteria.querySelectorAll("[data-rule]").forEach(function (rule) {
            const test = passwordRules[rule.dataset.rule];
            const met = Boolean(test && test(value));
            rule.classList.toggle("met", met);

            const icon = rule.querySelector("i");
            if (icon) {
                icon.className = met ? "bi bi-check-circle-fill" : "bi bi-circle";
            }
        });
    }

    function bindPasswordCriteria(scope, onResize) {
        const input = scope.querySelector("[data-password-criteria-input]");
        const criteria = scope.querySelector("[data-password-criteria]");
        if (!input || !criteria) {
            return;
        }

        const sync = function () {
            updateCriteria(input, criteria);
            if (typeof onResize === "function") {
                onResize();
            }
        };

        input.addEventListener("input", sync);
        sync();
    }

    function syncStageHeight(stage, activeView) {
        if (!stage || !activeView) {
            return;
        }

        stage.style.height = activeView.scrollHeight + "px";
    }

    function bindAuthShell(shell) {
        const stage = shell.querySelector("[data-auth-stage]");
        const views = {
            login: shell.querySelector('[data-auth-view="login"]'),
            register: shell.querySelector('[data-auth-view="register"]')
        };
        const buttons = shell.querySelectorAll("[data-auth-target]");
        const links = shell.querySelectorAll("[data-auth-toggle]");
        const loginUrl = shell.getAttribute("data-login-url") || "/login";
        const registerUrl = shell.getAttribute("data-register-url") || "/register";

        if (!stage || !views.login || !views.register) {
            return;
        }

        let mode = shell.getAttribute("data-auth-mode") === "register" ? "register" : "login";
        if (window.location.hash === "#register") {
            mode = "register";
        }

        const applyMode = function (nextMode, updateUrl) {
            mode = nextMode === "register" ? "register" : "login";
            shell.setAttribute("data-auth-mode", mode);

            Object.keys(views).forEach(function (key) {
                const active = key === mode;
                views[key].classList.toggle("is-active", active);
                views[key].setAttribute("aria-hidden", active ? "false" : "true");
                if ("inert" in views[key]) {
                    views[key].inert = !active;
                }
            });

            buttons.forEach(function (button) {
                const active = button.getAttribute("data-auth-target") === mode;
                button.classList.toggle("is-active", active);
                button.setAttribute("aria-selected", active ? "true" : "false");
            });

            syncStageHeight(stage, views[mode]);

            if (updateUrl && window.history && typeof window.history.replaceState === "function") {
                window.history.replaceState(null, "", mode === "register" ? registerUrl : loginUrl);
            }
        };

        buttons.forEach(function (button) {
            button.addEventListener("click", function () {
                applyMode(button.getAttribute("data-auth-target"), true);
            });
        });

        links.forEach(function (link) {
            link.addEventListener("click", function (event) {
                event.preventDefault();
                applyMode(link.getAttribute("data-auth-toggle"), true);
            });
        });

        bindPasswordToggles(shell);
        bindPasswordCriteria(shell, function () {
            syncStageHeight(stage, views[mode]);
        });

        if ("ResizeObserver" in window) {
            const observer = new ResizeObserver(function () {
                syncStageHeight(stage, views[mode]);
            });
            observer.observe(views.login);
            observer.observe(views.register);
        } else {
            window.addEventListener("resize", function () {
                syncStageHeight(stage, views[mode]);
            });
        }

        applyMode(mode, false);
        window.setTimeout(function () {
            syncStageHeight(stage, views[mode]);
        }, 0);
    }

    document.addEventListener("DOMContentLoaded", function () {
        document.querySelectorAll("[data-auth-shell]").forEach(bindAuthShell);
    });
})();
