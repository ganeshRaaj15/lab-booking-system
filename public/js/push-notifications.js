(function () {
    var button = document.getElementById("pushToggleBtn");
    if (!button) {
        return;
    }

    var publicKey = button.dataset.pushPublicKey || "";
    var csrfMeta = document.getElementById("slams-csrf-meta");
    var label = button.querySelector("[data-push-label]");
    var statusCopy = document.querySelector("[data-push-status-copy]");
    var busy = false;

    function showToast(message, type) {
        var toast = document.createElement("div");
        toast.className = "alert alert-" + (type || "info") + " shadow-sm";
        toast.style.position = "fixed";
        toast.style.right = "16px";
        toast.style.bottom = "16px";
        toast.style.zIndex = "1080";
        toast.style.maxWidth = "320px";
        toast.innerHTML = message;
        document.body.appendChild(toast);
        window.setTimeout(function () {
            toast.remove();
        }, 3200);
    }

    function setButtonState(enabled) {
        var icon = button.querySelector("[data-push-icon]");
        button.setAttribute("aria-pressed", enabled ? "true" : "false");
        button.title = enabled ? "Disable push notifications" : "Enable push notifications";
        button.classList.remove("is-blocked");
        button.classList.toggle("is-enabled", enabled);
        if (icon) {
            icon.className = enabled ? "bi bi-bell-fill" : "bi bi-bell";
        }
        if (label) {
            label.textContent = enabled ? "Disable" : "Enable";
        }
        if (statusCopy) {
            statusCopy.textContent = enabled ? "On for this device." : "Off for this device.";
        }
    }

    function setUnavailableState(message) {
        button.disabled = true;
        button.classList.remove("is-enabled");
        button.classList.add("is-blocked");
        button.title = message;
        if (label) {
            label.textContent = "Unavailable";
        }
        if (statusCopy) {
            statusCopy.textContent = message;
        }
    }

    function setBusyState(active) {
        busy = active;
        button.disabled = active;
        button.classList.toggle("opacity-75", active);
    }

    function urlBase64ToUint8Array(base64String) {
        var padding = "=".repeat((4 - (base64String.length % 4)) % 4);
        var base64 = (base64String + padding).replace(/-/g, "+").replace(/_/g, "/");
        var rawData = window.atob(base64);
        var outputArray = new Uint8Array(rawData.length);

        for (var i = 0; i < rawData.length; ++i) {
            outputArray[i] = rawData.charCodeAt(i);
        }

        return outputArray;
    }

    async function postJson(url, payload) {
        var headers = {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        };

        if (csrfMeta && csrfMeta.content) {
            headers["X-CSRF-TOKEN"] = csrfMeta.content;
        }

        var response = await fetch(url, {
            method: "POST",
            credentials: "same-origin",
            headers: headers,
            body: JSON.stringify(payload)
        });

        var data = null;
        try {
            data = await response.json();
        } catch (_error) {
            data = null;
        }

        if (!response.ok || !data || data.status !== "success") {
            throw new Error((data && data.message) || ("Request failed with HTTP " + response.status + "."));
        }

        return data;
    }

    async function serviceWorkerRegistration() {
        if (!("serviceWorker" in navigator)) {
            throw new Error("This browser does not support service workers.");
        }

        try {
            return await navigator.serviceWorker.ready;
        } catch (_error) {
            return navigator.serviceWorker.register("/sw.js", { scope: "/" });
        }
    }

    async function currentSubscription() {
        var registration = await serviceWorkerRegistration();
        if (!registration.pushManager) {
            throw new Error("Push notifications are not supported on this device.");
        }

        return registration.pushManager.getSubscription();
    }

    async function refreshState() {
        if (!("Notification" in window) || !("PushManager" in window)) {
            setUnavailableState("Push notifications are not supported on this device.");
            return;
        }

        try {
            var subscription = await currentSubscription();
            setButtonState(!!subscription);
        } catch (_error) {
            setUnavailableState("Push notifications are not available right now.");
        }
    }

    async function subscribe() {
        if (!("Notification" in window) || !("PushManager" in window)) {
            throw new Error("Push notifications are not supported on this device.");
        }

        if (Notification.permission === "denied") {
            throw new Error("Browser notifications are blocked for this site.");
        }

        if (Notification.permission !== "granted") {
            var permission = await Notification.requestPermission();
            if (permission !== "granted") {
                throw new Error("Browser notification permission was not granted.");
            }
        }

        var registration = await serviceWorkerRegistration();
        var subscription = await registration.pushManager.getSubscription();
        if (!subscription) {
            subscription = await registration.pushManager.subscribe({
                userVisibleOnly: true,
                applicationServerKey: urlBase64ToUint8Array(publicKey)
            });
        }

        await postJson("/dashboard/push/subscribe", subscription.toJSON());
        setButtonState(true);
        showToast("Push notifications enabled.", "success");
    }

    async function unsubscribe() {
        var subscription = await currentSubscription();
        if (!subscription) {
            setButtonState(false);
            return;
        }

        await postJson("/dashboard/push/unsubscribe", {
            endpoint: subscription.endpoint
        });
        await subscription.unsubscribe().catch(function () {});
        setButtonState(false);
        showToast("Push notifications disabled.", "secondary");
    }

    button.addEventListener("click", async function () {
        if (busy) {
            return;
        }

        setBusyState(true);

        try {
            var enabled = button.getAttribute("aria-pressed") === "true";
            if (enabled) {
                await unsubscribe();
            } else {
                await subscribe();
            }
        } catch (error) {
            showToast((error && error.message) || "Push notification settings could not be updated.", "danger");
        } finally {
            setBusyState(false);
            refreshState();
        }
    });

    refreshState();
}());
