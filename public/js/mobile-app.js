(function () {
    "use strict";

    var serviceWorkerRegistrationPromise = null;
    var pendingServiceWorker = null;
    var shouldReloadForUpdate = false;
    var updateBanner = null;
    var statusBanner = null;
    var pushControls = [];
    var mobilePushPills = [];
    var appConfig = {
        loggedIn: false,
        pushConfigured: false,
        pushPublicKey: "",
        subscribeUrl: "",
        unsubscribeUrl: "",
        testUrl: ""
    };

    function readAppConfig() {
        var body = document.body;
        if (!body) {
            return;
        }

        appConfig.loggedIn = body.dataset.userLoggedIn === "1";
        appConfig.pushConfigured = body.dataset.pushConfigured === "1";
        appConfig.pushPublicKey = body.dataset.pushPublicKey || "";
        appConfig.subscribeUrl = body.dataset.pushSubscribeUrl || "";
        appConfig.unsubscribeUrl = body.dataset.pushUnsubscribeUrl || "";
        appConfig.testUrl = body.dataset.pushTestUrl || "";
    }

    function csrfHeaders() {
        var meta = document.getElementById("slams-csrf-meta");
        if (!meta) {
            return {};
        }

        var headers = {};
        headers[meta.name] = meta.content;
        return headers;
    }

    function postJson(url, payload) {
        var headers = csrfHeaders();
        headers["Content-Type"] = "application/json";
        headers["X-Requested-With"] = "XMLHttpRequest";

        return fetch(url, {
            method: "POST",
            headers: headers,
            credentials: "same-origin",
            body: JSON.stringify(payload || {})
        }).then(function (response) {
            return response.json().catch(function () {
                return {};
            }).then(function (data) {
                if (!response.ok) {
                    var error = new Error(data.message || "Request failed.");
                    error.payload = data;
                    throw error;
                }

                return data;
            });
        });
    }

    function urlBase64ToUint8Array(base64String) {
        var padding = "=".repeat((4 - base64String.length % 4) % 4);
        var base64 = (base64String + padding)
            .replace(/-/g, "+")
            .replace(/_/g, "/");

        var rawData = window.atob(base64);
        var outputArray = new Uint8Array(rawData.length);

        for (var i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }

        return outputArray;
    }

    function supportedContentEncoding() {
        if (window.PushManager && Array.isArray(window.PushManager.supportedContentEncodings)) {
            if (window.PushManager.supportedContentEncodings.indexOf("aes128gcm") !== -1) {
                return "aes128gcm";
            }

            if (window.PushManager.supportedContentEncodings.indexOf("aesgcm") !== -1) {
                return "aesgcm";
            }
        }

        return "aes128gcm";
    }

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

    function setPushStatus(text, state) {
        mobilePushPills.forEach(function (pill) {
            pill.textContent = text;
            pill.classList.remove("is-enabled", "is-blocked");
            if (state === "enabled") {
                pill.classList.add("is-enabled");
            } else if (state === "blocked") {
                pill.classList.add("is-blocked");
            }
        });
    }

    function updatePushButtons(mode) {
        var iconClass = "bi-bell";
        var label = "Enable push notifications";
        var title = "Enable push notifications";
        var state = "default";

        if (mode === "enabled") {
            iconClass = "bi-bell-fill";
            label = "Disable push notifications";
            title = "Disable push notifications";
            state = "enabled";
            setPushStatus("Push on", "enabled");
        } else if (mode === "blocked") {
            iconClass = "bi-bell-slash-fill";
            label = "Push notifications blocked by browser";
            title = "Push notifications blocked by browser";
            state = "blocked";
            setPushStatus("Push blocked", "blocked");
        } else if (mode === "unsupported") {
            label = "Push notifications unavailable on this browser";
            title = "Push notifications unavailable on this browser";
            setPushStatus("Push unsupported", "blocked");
        } else if (mode === "unavailable") {
            label = "Push notifications are not configured on the server";
            title = "Push notifications are not configured on the server";
            setPushStatus("Push unavailable", "blocked");
        } else {
            setPushStatus("Push off", "default");
        }

        pushControls.forEach(function (button) {
            button.hidden = mode === "unsupported";
            button.setAttribute("aria-label", label);
            button.setAttribute("title", title);
            button.classList.toggle("is-enabled", state === "enabled");
            button.classList.toggle("is-blocked", state === "blocked");
            if (button.querySelector("i")) {
                button.querySelector("i").className = "bi " + iconClass;
            }
        });
    }

    function pushSupported() {
        return !!(window.isSecureContext && "serviceWorker" in navigator && "PushManager" in window && "Notification" in window);
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

    function syncExistingPushSubscription() {
        if (!pushSupported() || !appConfig.loggedIn || !appConfig.pushConfigured || Notification.permission !== "granted" || !serviceWorkerRegistrationPromise) {
            return Promise.resolve();
        }

        return serviceWorkerRegistrationPromise.then(function (registration) {
            if (!registration || !registration.pushManager) {
                return;
            }

            return registration.pushManager.getSubscription().then(function (subscription) {
                if (!subscription) {
                    updatePushButtons("default");
                    return;
                }

                return postJson(appConfig.subscribeUrl, Object.assign({}, subscription.toJSON(), {
                    contentEncoding: supportedContentEncoding()
                })).then(function () {
                    updatePushButtons("enabled");
                }).catch(function () {
                    updatePushButtons("default");
                });
            });
        });
    }

    function handlePushToggle() {
        if (!appConfig.loggedIn) {
            showStatusBanner("offline", "Sign in first before enabling push notifications.", true);
            return;
        }

        if (!pushSupported()) {
            updatePushButtons("unsupported");
            showStatusBanner("offline", "This browser does not support web push notifications.", true);
            return;
        }

        if (!appConfig.pushConfigured || !appConfig.pushPublicKey) {
            updatePushButtons("unavailable");
            showStatusBanner("offline", "Web push is not configured on the server yet.", true);
            return;
        }

        if (Notification.permission === "denied") {
            updatePushButtons("blocked");
            showStatusBanner("offline", "Push notifications are blocked in your browser settings.", true);
            return;
        }

        if (!serviceWorkerRegistrationPromise) {
            showStatusBanner("offline", "The mobile worker is still starting. Try again in a moment.", true);
            return;
        }

        serviceWorkerRegistrationPromise.then(function (registration) {
            if (!registration || !registration.pushManager) {
                throw new Error("Push manager is unavailable.");
            }

            return registration.pushManager.getSubscription().then(function (existingSubscription) {
                if (existingSubscription) {
                    if (!window.confirm("Disable push notifications for this device?")) {
                        return null;
                    }

                    return postJson(appConfig.unsubscribeUrl, {
                        endpoint: existingSubscription.endpoint
                    }).catch(function () {
                        return {};
                    }).then(function () {
                        return existingSubscription.unsubscribe().catch(function () {
                            return false;
                        }).then(function () {
                            updatePushButtons("default");
                            showStatusBanner("online", "Push notifications disabled for this device.", true);
                            return null;
                        });
                    });
                }

                return Notification.requestPermission().then(function (permission) {
                    if (permission !== "granted") {
                        updatePushButtons(permission === "denied" ? "blocked" : "default");
                        throw new Error(permission === "denied"
                            ? "Push notifications were blocked by the browser."
                            : "Push permission was not granted.");
                    }

                    return registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(appConfig.pushPublicKey)
                    }).then(function (subscription) {
                        var payload = Object.assign({}, subscription.toJSON(), {
                            contentEncoding: supportedContentEncoding()
                        });

                        return postJson(appConfig.subscribeUrl, payload).then(function () {
                            updatePushButtons("enabled");
                            showStatusBanner("online", "Push notifications enabled for this device.", true);
                            if (appConfig.testUrl) {
                                return postJson(appConfig.testUrl, {}).catch(function () {
                                    return {};
                                });
                            }
                            return {};
                        });
                    });
                });
            });
        }).catch(function (error) {
            updatePushButtons(Notification.permission === "denied" ? "blocked" : "default");
            showStatusBanner("offline", error && error.message ? error.message : "Push notifications could not be changed.", true);
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

            serviceWorkerRegistrationPromise = navigator.serviceWorker.register(serviceWorkerUrl.href, {
                updateViaCache: "none"
            }).then(function (registration) {
                bindServiceWorkerLifecycle(registration);
                syncExistingPushSubscription();
                return registration;
            }).catch(function () {
                // The app still works normally if service worker registration is unavailable.
                return null;
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
        readAppConfig();
        setupUpdateButton();
        loadLastSync();
        refreshNetworkStatus();
        pushControls = Array.prototype.slice.call(document.querySelectorAll("[data-push-toggle], [data-mobile-push-toggle]"));
        mobilePushPills = Array.prototype.slice.call(document.querySelectorAll("[data-mobile-push-status]"));

        if (!pushSupported()) {
            updatePushButtons("unsupported");
        } else if (!appConfig.loggedIn) {
            updatePushButtons("unsupported");
        } else if (!appConfig.pushConfigured) {
            updatePushButtons("unavailable");
        } else if (Notification.permission === "denied") {
            updatePushButtons("blocked");
            pushControls.forEach(function (button) {
                button.hidden = false;
            });
        } else {
            updatePushButtons("default");
            pushControls.forEach(function (button) {
                button.hidden = false;
            });
        }

        pushControls.forEach(function (button) {
            button.addEventListener("click", handlePushToggle);
        });

        if (navigator.onLine !== false) {
            updateLastSync(new Date());
        }

    });
})();
