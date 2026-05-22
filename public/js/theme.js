(function () {
    "use strict";

    const storageKey = "slams-theme";
    const root = document.documentElement;
    const prefersDark = window.matchMedia && window.matchMedia("(prefers-color-scheme: dark)").matches;

    function savedTheme() {
        let stored = null;
        try {
            stored = localStorage.getItem(storageKey);
        } catch (error) {
            stored = null;
        }
        if (stored === "light" || stored === "dark") {
            return stored;
        }
        return prefersDark ? "dark" : "light";
    }

    function applyTheme(theme) {
        root.setAttribute("data-theme", theme);
        if (document.body) {
            document.body.setAttribute("data-theme", theme);
        }
        try {
            localStorage.setItem(storageKey, theme);
        } catch (error) {
            // Theme still applies for this page even when storage is unavailable.
        }
    }

    applyTheme(savedTheme());

    function bindThemeToggle() {
        const toggle = document.getElementById("themeToggle");
        if (!toggle) {
            return;
        }

        toggle.addEventListener("click", function () {
            const current = root.getAttribute("data-theme") || "light";
            applyTheme(current === "dark" ? "light" : "dark");
        });
    }

    function bindSidebar() {
        const body = document.body;
        const overlay = document.getElementById("sidebarOverlay");
        const toggle = document.getElementById("sidebarToggle");
        const wide = window.matchMedia("(min-width: 992px)");

        if (toggle) {
            toggle.addEventListener("click", function () {
                body.classList.toggle("sidebar-open");
            });
        }

        if (overlay) {
            overlay.addEventListener("click", function () {
                body.classList.remove("sidebar-open");
            });
        }

        function syncSidebar() {
            if (wide.matches) {
                body.classList.remove("sidebar-open");
            }
        }

        syncSidebar();
        if (wide.addEventListener) {
            wide.addEventListener("change", syncSidebar);
        }
    }

    function bindNavbar() {
        const navbars = document.querySelectorAll(".glass-navbar, .admin-glass-navbar");
        const syncScrolled = function () {
            navbars.forEach(function (navbar) {
                navbar.classList.toggle("scrolled", window.scrollY > 24);
            });
        };

        syncScrolled();
        window.addEventListener("scroll", syncScrolled, { passive: true });

        const current = window.location.pathname || "/";
        document.querySelectorAll(".glass-navbar .nav-link[href]").forEach(function (link) {
            const href = link.getAttribute("href");
            if (!href || href === "#") {
                return;
            }
            const active = (current === "/" && href === "/") || (href !== "/" && current.startsWith(href));
            link.classList.toggle("active", active);
        });
    }

    function ensureModalRoot(modalOrSelector) {
        const modal = typeof modalOrSelector === "string"
            ? document.querySelector(modalOrSelector)
            : modalOrSelector;

        if (!(modal instanceof HTMLElement) || !modal.classList.contains("modal")) {
            return modal;
        }

        if (document.body && modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        return modal;
    }

    function hoistStaticModals() {
        document.querySelectorAll([
            ".slams-main .modal",
            ".content-area .modal",
            ".admin-layout .modal",
            ".technician-layout .modal"
        ].join(",")).forEach(function (modal) {
            ensureModalRoot(modal);
        });
    }

    function initReveal() {
        const candidates = document.querySelectorAll([
            ".dashboard-header",
            ".page-header",
            ".section-header",
            ".home-hero-panel",
            ".home-section-header",
            ".home-stat",
            ".feature-card",
            ".home-flow-card",
            ".cta-section",
            ".kpi-glass-card",
            ".widget-card",
            ".stats-card",
            ".quick-stat",
            ".admin-dashboard > .glass-card",
            ".lab-hero",
            ".asset-hero",
            ".contact-header",
            ".lab-header-card",
            ".pic-card",
            ".equipment-card",
            ".booking-card",
            ".calendar-card",
            ".filter-bar",
            ".auth-card",
            ".login-card",
            ".map-section"
        ].join(","));

        candidates.forEach(function (node) {
            if (!node.closest(".modal") && !node.classList.contains("slams-reveal")) {
                node.classList.add("slams-reveal");
            }
        });

        const revealNodes = document.querySelectorAll(".slams-reveal");
        const staggerGroups = [
            ".home-stat-grid",
            ".home-feature-grid",
            ".home-flow-grid",
            ".dashboard-grid",
            ".row"
        ];

        revealNodes.forEach(function (node) {
            node.style.setProperty("--slams-reveal-delay", "0ms");
        });

        staggerGroups.forEach(function (selector) {
            document.querySelectorAll(selector).forEach(function (group) {
                group.querySelectorAll(".slams-reveal").forEach(function (node, index) {
                    node.style.setProperty("--slams-reveal-delay", Math.min((index % 4) * 30, 90) + "ms");
                });
            });
        });

        // Home page grids get a more cinematic stagger (80ms per item)
        [".home-stat-grid", ".home-feature-grid", ".home-flow-grid"].forEach(function (selector) {
            document.querySelectorAll(selector).forEach(function (group) {
                group.querySelectorAll(".slams-reveal").forEach(function (node, index) {
                    node.style.setProperty("--slams-reveal-delay", (index * 80) + "ms");
                });
            });
        });

        if (!("IntersectionObserver" in window) || window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
            revealNodes.forEach(function (node) {
                node.classList.add("is-visible");
            });
            return;
        }

        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add("is-visible");
                    observer.unobserve(entry.target);
                }
            });
        }, { rootMargin: "0px 0px 12% 0px", threshold: 0.04 });

        revealNodes.forEach(function (node) {
            observer.observe(node);
        });
    }

    function initCounters() {
        const counters = document.querySelectorAll(".home-stat-value");
        if (!counters.length || !("IntersectionObserver" in window)) return;
        if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) return;

        const observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (!entry.isIntersecting) return;
                observer.unobserve(entry.target);
                const el = entry.target;
                const target = parseInt(el.textContent.replace(/\D/g, ""), 10);
                if (isNaN(target) || target === 0) return;
                const duration = 1600;
                const start = performance.now();
                function tick(now) {
                    const progress = Math.min((now - start) / duration, 1);
                    // easeInOutCubic — starts slow so the user sees it begin from near 0,
                    // accelerates through the middle, then decelerates into the final value
                    const eased = progress < 0.5
                        ? 4 * progress * progress * progress
                        : 1 - Math.pow(-2 * progress + 2, 3) / 2;
                    el.textContent = Math.round(eased * target);
                    if (progress < 1) requestAnimationFrame(tick);
                }
                requestAnimationFrame(tick);
            });
        }, { rootMargin: "0px 0px 10% 0px", threshold: 0.4 });

        counters.forEach(function (el) { observer.observe(el); });
    }

    function initScrollProgress() {
        const bar = document.querySelector(".slams-scroll-progress");
        if (!bar) return;
        function update() {
            const scrollable = document.documentElement.scrollHeight - window.innerHeight;
            bar.style.transform = "scaleX(" + (scrollable > 0 ? window.scrollY / scrollable : 0) + ")";
        }
        window.addEventListener("scroll", update, { passive: true });
        update();
    }

    function initHeroVideo() {
        const heroVideo = document.querySelector(".hero-video");
        if (!(heroVideo instanceof HTMLVideoElement)) {
            return;
        }

        const videoBackground = heroVideo.closest(".video-background");

        let playAttempted = false;
        let frameCallbackRequested = false;

        const setVideoReady = function (ready) {
            if (videoBackground) {
                videoBackground.classList.toggle("video-ready", ready);
            }
        };

        const watchForRenderedFrame = function () {
            if (frameCallbackRequested || typeof heroVideo.requestVideoFrameCallback !== "function") {
                return;
            }

            frameCallbackRequested = true;
            heroVideo.requestVideoFrameCallback(function () {
                setVideoReady(true);
            });
        };

        const attemptPlay = function () {
            if (document.visibilityState === "hidden") {
                return;
            }

            heroVideo.muted = true;
            heroVideo.defaultMuted = true;
            heroVideo.playsInline = true;
            heroVideo.setAttribute("muted", "");
            heroVideo.setAttribute("playsinline", "");
            heroVideo.setAttribute("webkit-playsinline", "");

            const playPromise = heroVideo.play();
            playAttempted = true;
            watchForRenderedFrame();

            if (playPromise && typeof playPromise.catch === "function") {
                playPromise.catch(function () {
                    // Leave the CSS fallback image visible when autoplay is denied.
                    setVideoReady(false);
                });
            }
        };

        heroVideo.addEventListener("playing", function () {
            watchForRenderedFrame();
        });

        heroVideo.addEventListener("timeupdate", function () {
            if (heroVideo.currentTime > 0) {
                setVideoReady(true);
            }
        });

        heroVideo.addEventListener("loadeddata", function () {
            if (!heroVideo.paused && heroVideo.currentTime > 0) {
                setVideoReady(true);
            }
            setVideoReady(true);
        });

        heroVideo.addEventListener("error", function () {
            setVideoReady(false);
        });

        heroVideo.addEventListener("emptied", function () {
            frameCallbackRequested = false;
            setVideoReady(false);
        });

        heroVideo.addEventListener("pause", function () {
            if (heroVideo.currentTime === 0) {
                setVideoReady(false);
            }
        });

        heroVideo.addEventListener("canplay", attemptPlay, { once: true });
        heroVideo.addEventListener("loadeddata", attemptPlay, { once: true });

        document.addEventListener("visibilitychange", function () {
            if (document.visibilityState === "visible" && heroVideo.paused) {
                attemptPlay();
            }
        });

        if (heroVideo.readyState >= 2) {
            attemptPlay();
            return;
        }

        heroVideo.load();

        window.setTimeout(function () {
            if (!playAttempted && heroVideo.paused) {
                attemptPlay();
            }
            if (!heroVideo.paused && heroVideo.currentTime > 0) {
                setVideoReady(true);
            }
        }, 250);
    }

    document.addEventListener("DOMContentLoaded", function () {
        applyTheme(root.getAttribute("data-theme") || savedTheme());
        bindThemeToggle();
        bindSidebar();
        bindNavbar();
        hoistStaticModals();
        initReveal();
        initCounters();
        initScrollProgress();
        initHeroVideo();
    });

    window.slamsPrepareModal = ensureModalRoot;
})();
