<?php
helper('url');
$csrfTokenName = csrf_token();
$csrfTokenHash = csrf_hash();
?>

<style>
    .slams-chatbot {
        position: fixed;
        right: 24px;
        bottom: 24px;
        z-index: 1050;
        font-family: "Inter", sans-serif;
    }

    .slams-chatbot .chatbot-toggle {
        width: 56px;
        height: 56px;
        border-radius: 14px;
        border: none;
        background: var(--blue-light);
        color: #fff;
        box-shadow: 0 12px 24px rgba(30, 58, 138, 0.25);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    .slams-chatbot .chatbot-toggle i {
        font-size: 1.3rem;
    }

    .slams-chatbot .chatbot-panel {
        position: absolute;
        right: 0;
        bottom: 72px;
        width: 380px;
        max-width: calc(100vw - 32px);
        background: var(--gray-card);
        border-radius: 12px;
        box-shadow: 0 12px 32px rgba(15, 23, 42, 0.15);
        border: 1px solid rgba(148, 163, 184, 0.15);
        opacity: 0;
        transform: translateY(12px);
        pointer-events: none;
        transition: all 0.2s ease;
    }

    .slams-chatbot.is-open .chatbot-panel {
        opacity: 1;
        transform: translateY(0);
        pointer-events: auto;
    }

    .slams-chatbot .chatbot-header {
        padding: 16px 18px 12px;
        border-bottom: 1px solid rgba(148, 163, 184, 0.2);
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: #fff;
        border-radius: 12px 12px 0 0;
        gap: 12px;
    }

    .slams-chatbot .chatbot-title {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .slams-chatbot .chatbot-title i {
        font-size: 1.2rem;
        color: var(--blue-light);
    }

    .slams-chatbot .chatbot-header h6 {
        margin: 0;
        font-weight: 600;
        color: var(--text-dark);
        font-size: 0.95rem;
    }

    .slams-chatbot .chatbot-subtitle {
        margin: 0;
        font-size: 0.75rem;
        color: var(--text-light);
    }

    .slams-chatbot .chatbot-commands {
        padding: 10px 16px 0;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
        background: var(--gray-bg);
        border-bottom: 1px solid rgba(148, 163, 184, 0.15);
    }

    .slams-chatbot .chatbot-command {
        border: 1px solid rgba(59, 130, 246, 0.2);
        background: #fff;
        color: var(--blue-primary);
        font-size: 0.72rem;
        padding: 6px 10px;
        border-radius: 999px;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .slams-chatbot .chatbot-command:hover {
        background: rgba(59, 130, 246, 0.1);
        border-color: rgba(59, 130, 246, 0.4);
    }

    .slams-chatbot .chatbot-messages {
        padding: 14px 16px;
        max-height: 300px;
        overflow-y: auto;
        background: var(--gray-bg);
    }

    .slams-chatbot .chatbot-message {
        margin-bottom: 12px;
        display: flex;
    }

    .slams-chatbot .chatbot-message.bot {
        justify-content: flex-start;
    }

    .slams-chatbot .chatbot-message.user {
        justify-content: flex-end;
    }

    .slams-chatbot .chatbot-bubble {
        padding: 10px 12px;
        border-radius: 12px;
        max-width: 250px;
        font-size: 0.85rem;
        line-height: 1.4;
        white-space: pre-line;
        box-shadow: 0 6px 12px rgba(15, 23, 42, 0.06);
    }

    .slams-chatbot .chatbot-message.bot .chatbot-bubble {
        background: #fff;
        border: 1px solid rgba(148, 163, 184, 0.25);
        color: var(--text-dark);
    }

    .slams-chatbot .chatbot-message.user .chatbot-bubble {
        background: var(--blue-light);
        color: #fff;
    }

    .slams-chatbot .chatbot-input {
        padding: 12px 16px;
        border-top: 1px solid rgba(148, 163, 184, 0.2);
        display: flex;
        gap: 8px;
        background: #fff;
        border-radius: 0 0 12px 12px;
    }

    .slams-chatbot .chatbot-input input {
        flex: 1;
        border: 1px solid rgba(148, 163, 184, 0.4);
        border-radius: 10px;
        padding: 8px 10px;
        font-size: 0.85rem;
    }

    .slams-chatbot .chatbot-input button {
        border: none;
        background: var(--blue-primary);
        color: #fff;
        border-radius: 10px;
        padding: 8px 14px;
        font-size: 0.85rem;
    }

    @media (max-width: 576px) {
        .slams-chatbot {
            right: 16px;
            bottom: 16px;
        }

        .slams-chatbot .chatbot-panel {
            width: min(360px, calc(100vw - 32px));
        }
    }
</style>

<div class="slams-chatbot" id="slamsChatbot">
    <button class="chatbot-toggle" id="chatbotToggle" type="button" aria-label="Open chatbot" aria-expanded="false">
        <i class="bi bi-chat-dots"></i>
    </button>

    <div class="chatbot-panel" id="chatbotPanel" aria-hidden="true">
        <div class="chatbot-header">
            <div class="chatbot-title">
                <i class="bi bi-cpu"></i>
                <div>
                    <h6>Smart Lab Assistant</h6>
                    <p class="chatbot-subtitle">Quick insight commands</p>
                </div>
            </div>
            <button class="btn btn-sm btn-light" type="button" id="chatbotClose">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>
        <div class="chatbot-commands" id="chatbotCommands">
            <button type="button" class="chatbot-command" data-command="Total bookings">Total bookings</button>
            <button type="button" class="chatbot-command" data-command="Top labs by bookings">Top labs</button>
            <button type="button" class="chatbot-command" data-command="Asset status summary">Asset status</button>
            <button type="button" class="chatbot-command" data-command="My upcoming bookings">My bookings</button>
            <button type="button" class="chatbot-command" data-command="Pending approvals">Pending approvals</button>
        </div>
        <div class="chatbot-messages" id="chatbotMessages">
            <div class="chatbot-message bot">
                <div class="chatbot-bubble">
                    Ask me about total bookings, top labs, asset status, or your upcoming bookings. Type "help" for more.
                </div>
            </div>
        </div>
        <form class="chatbot-input" id="chatbotForm">
            <input type="text" id="chatbotInput" placeholder="Ask about lab data..." autocomplete="off">
            <button type="submit">Send</button>
        </form>
    </div>
</div>

<script>
(function() {
    const widget = document.getElementById('slamsChatbot');
    if (!widget) {
        return;
    }

    const toggle = document.getElementById('chatbotToggle');
    const panel = document.getElementById('chatbotPanel');
    const closeBtn = document.getElementById('chatbotClose');
    const form = document.getElementById('chatbotForm');
    const input = document.getElementById('chatbotInput');
    const messages = document.getElementById('chatbotMessages');
    const commands = document.getElementById('chatbotCommands');

    const apiUrl = <?= json_encode(site_url('api/chat')) ?>;
    let csrfTokenName = <?= json_encode($csrfTokenName) ?>;
    let csrfTokenValue = <?= json_encode($csrfTokenHash) ?>;

    const openPanel = () => {
        widget.classList.add('is-open');
        toggle.setAttribute('aria-expanded', 'true');
        panel.setAttribute('aria-hidden', 'false');
        input.focus();
    };

    const closePanel = () => {
        widget.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
        panel.setAttribute('aria-hidden', 'true');
    };

    const addMessage = (text, role) => {
        const item = document.createElement('div');
        item.className = `chatbot-message ${role}`;
        item.innerHTML = `<div class="chatbot-bubble">${text}</div>`;
        messages.appendChild(item);
        messages.scrollTop = messages.scrollHeight;
    };

    const sendMessage = (text) => {
        addMessage(text, 'user');
        input.value = '';
        input.disabled = true;

        const body = new URLSearchParams({
            message: text,
            [csrfTokenName]: csrfTokenValue,
        });

        fetch(apiUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body,
        })
        .then((response) => response.json())
        .then((data) => {
            if (data && data.reply) {
                addMessage(data.reply, 'bot');
            } else if (data && (data.message || data.error)) {
                addMessage(data.message || data.error, 'bot');
            } else {
                addMessage('Sorry, I could not process that.', 'bot');
            }

            if (data && data.csrfHash) {
                csrfTokenValue = data.csrfHash;
            }
        })
        .catch(() => {
            addMessage('Something went wrong. Please try again.', 'bot');
        })
        .finally(() => {
            input.disabled = false;
            input.focus();
        });
    };

    toggle.addEventListener('click', () => {
        if (widget.classList.contains('is-open')) {
            closePanel();
        } else {
            openPanel();
        }
    });

    closeBtn.addEventListener('click', closePanel);

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        const text = input.value.trim();
        if (!text) {
            return;
        }

        sendMessage(text);
    });

    if (commands) {
        commands.addEventListener('click', (event) => {
            const button = event.target.closest('.chatbot-command');
            if (!button) {
                return;
            }
            const command = button.getAttribute('data-command');
            if (!command) {
                return;
            }
            if (!widget.classList.contains('is-open')) {
                openPanel();
            }
            sendMessage(command);
        });
    }
})();
</script>
