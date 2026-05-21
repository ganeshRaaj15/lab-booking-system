(function () {
    "use strict";

    var pendingServiceWorker = null;
    var shouldReloadForUpdate = false;
    var updateBanner = null;
    var statusBanner = null;

    function ensureStatusBanner() {
        if (!statusBanner) {
            statusBanner = document.querySelector("[data-mobile-status-banner]");
        }

        return statusBanner;
    }

    function ensureUpdateBanner() {
        if (!updateBanner) {
            updateBanner = document.querySelector("[data-mobile-update-banner]");
        }

        return updateBanner;
    }

    function formatSyncTime(date) {
        return "Synced " + date.toLocaleTimeString([], {
            hour: "numeric",
            minute: "2-digit"
        });
    }

    function updateLastSync(now) {
        var timestamp = now instanceof Date ? now : new Date();
        try {
            localStorage.setItem("slams-mobile-last-sync", timestamp.toISOString());
        } catch (_error) {
            // Storage can be unavailable in privacy modes. The mobile shell still works.
        }

        var labels = document.querySelectorAll("[data-mobile-last-sync]");
        labels.forEach(function (label) {
            label.textContent = formatSyncTime(timestamp);
        });
    }

    function loadLastSync() {
        var value = null;

        try {
            value = localStorage.getItem("slams-mobile-last-sync");
        } catch (_error) {
            value = null;
        }

        if (!value) {
            return;
        }

        var timestamp = new Date(value);
        if (Number.isNaN(timestamp.getTime())) {
            return;
        }

        var labels = document.querySelectorAll("[data-mobile-last-sync]");
        labels.forEach(function (label) {
            label.textContent = formatSyncTime(timestamp);
        });
    }

    function showStatusBanner(kind, message, autoHide) {
        var banner = ensureStatusBanner();
        if (!banner) {
            return;
        }

        banner.hidden = false;
        banner.textContent = message;
        banner.classList.remove("is-online", "is-offline");
        banner.classList.add(kind === "offline" ? "is-offline" : "is-online");

        if (banner._hideTimer) {
            window.clearTimeout(banner._hideTimer);
            banner._hideTimer = null;
        }

        if (autoHide) {
            banner._hideTimer = window.setTimeout(function () {
                banner.hidden = true;
            }, 2600);
        }
    }

    function refreshNetworkStatus() {
        var isOnline = navigator.onLine !== false;
        var labels = document.querySelectorAll("[data-mobile-network-status]");

        labels.forEach(function (label) {
            label.textContent = isOnline ? "Online" : "Offline";
            label.classList.toggle("is-online", isOnline);
            label.classList.toggle("is-offline", !isOnline);
        });

        if (!isOnline) {
            showStatusBanner("offline", "Offline mode: cached pages and app tools are still available.", false);
        }
    }

    function showUpdateBanner(registration) {
        var banner = ensureUpdateBanner();
        if (!banner) {
            return;
        }

        pendingServiceWorker = registration && registration.waiting ? registration.waiting : null;
        if (!pendingServiceWorker) {
            return;
        }

        banner.hidden = false;
    }

    function setupUpdateButton() {
        document.querySelectorAll("[data-mobile-app-update]").forEach(function (button) {
            button.addEventListener("click", function () {
                if (pendingServiceWorker) {
                    shouldReloadForUpdate = true;
                    pendingServiceWorker.postMessage({ type: "SKIP_WAITING" });
                    return;
                }

                window.location.reload();
            });
        });
    }

    function bindServiceWorkerLifecycle(registration) {
        if (!registration) {
            return;
        }

        if (registration.waiting) {
            showUpdateBanner(registration);
        }

        registration.addEventListener("updatefound", function () {
            var installingWorker = registration.installing;
            if (!installingWorker) {
                return;
            }

            installingWorker.addEventListener("statechange", function () {
                if (installingWorker.state === "installed" && navigator.serviceWorker.controller) {
                    showUpdateBanner(registration);
                }
            });
        });
    }

    if ("serviceWorker" in navigator) {
        window.addEventListener("load", function () {
            var currentScript = document.currentScript || document.querySelector('script[src*="mobile-app.js"]');
            var scriptUrl = currentScript && currentScript.src
                ? new URL(currentScript.src, window.location.href)
                : new URL("js/mobile-app.js", window.location.href);
            var serviceWorkerUrl = new URL("../sw.js", scriptUrl);

            serviceWorkerUrl.search = scriptUrl.search;

            navigator.serviceWorker.register(serviceWorkerUrl.href, {
                updateViaCache: "none"
            }).then(function (registration) {
                bindServiceWorkerLifecycle(registration);
            }).catch(function () {
                // The app still works normally if service worker registration is unavailable.
            });
        });

        navigator.serviceWorker.addEventListener("controllerchange", function () {
            if (!shouldReloadForUpdate) {
                return;
            }

            var banner = ensureUpdateBanner();
            if (banner) {
                banner.hidden = true;
            }
            window.location.reload();
        });
    }

    window.addEventListener("online", function () {
        refreshNetworkStatus();
        updateLastSync(new Date());
        showStatusBanner("online", "Back online. SLAMS will use fresh data again.", true);
    });

    window.addEventListener("offline", function () {
        refreshNetworkStatus();
    });

    document.addEventListener("DOMContentLoaded", function () {
        setupUpdateButton();
        loadLastSync();
        refreshNetworkStatus();

        if (navigator.onLine !== false) {
            updateLastSync(new Date());
        }
    });
})();
