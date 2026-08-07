<style>
    /* ── TVIRS AI CHAT SUPPORT WIDGET ── */
    .tvrs-chat-trigger {
        position: fixed;
        bottom: 1.5rem;
        right: 1.5rem;
        z-index: 1045;
        width: 54px;
        height: 54px;
        border-radius: 50%;
        background: linear-gradient(135deg, #1d4ed8, #2563eb);
        color: #fff;
        border: 2px solid #ffffff;
        box-shadow: 0 8px 24px rgba(29, 78, 216, 0.35), 0 2px 8px rgba(0, 0, 0, 0.15);
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
        transition: transform 0.2s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.2s;
    }
    .tvrs-chat-trigger:hover {
        transform: scale(1.08);
        box-shadow: 0 12px 28px rgba(29, 78, 216, 0.45);
    }
    .tvrs-chat-badge-pulse {
        position: absolute;
        top: 0;
        right: 0;
        width: 13px;
        height: 13px;
        background: #22c55e;
        border: 2.5px solid #ffffff;
        border-radius: 50%;
    }

    /* ── CHAT DRAWER CARD ── */
    .tvrs-chat-card {
        position: fixed;
        bottom: 5.25rem;
        right: 1.5rem;
        z-index: 1050;
        width: 380px;
        max-width: calc(100vw - 2rem);
        height: 520px;
        max-height: calc(100vh - 7rem);
        background: #ffffff;
        border-radius: 16px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 16px 40px rgba(15, 23, 42, 0.18), 0 4px 12px rgba(0,0,0,0.06);
        display: none;
        flex-direction: column;
        overflow: hidden;
        animation: tvrsChatIn 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards;
    }
    .tvrs-chat-card.show {
        display: flex;
    }

    @keyframes tvrsChatIn {
        from { opacity: 0; transform: translateY(16px) scale(0.95); }
        to   { opacity: 1; transform: translateY(0) scale(1); }
    }

    /* Header */
    .tvrs-chat-header {
        background: linear-gradient(135deg, #1d4ed8, #1e40af);
        color: #ffffff;
        padding: 0.85rem 1rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }

    /* Body */
    .tvrs-chat-body {
        flex: 1;
        overflow-y: auto;
        padding: 1rem;
        background: #f8fafc;
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }

    /* Message Bubbles */
    .tvrs-msg {
        display: flex;
        flex-direction: column;
        max-width: 86%;
        font-size: 0.82rem;
        line-height: 1.5;
    }
    .tvrs-msg.user {
        align-self: flex-end;
    }
    .tvrs-msg.bot {
        align-self: flex-start;
    }
    .tvrs-msg-bubble {
        padding: 0.65rem 0.85rem;
        border-radius: 12px;
        word-wrap: break-word;
    }
    .tvrs-msg.user .tvrs-msg-bubble {
        background: #1d4ed8;
        color: #ffffff;
        border-bottom-right-radius: 2px;
    }
    .tvrs-msg.bot .tvrs-msg-bubble {
        background: #ffffff;
        color: #1e293b;
        border: 1px solid #e2e8f0;
        border-bottom-left-radius: 2px;
        box-shadow: 0 1px 3px rgba(0,0,0,0.04);
    }
    .tvrs-msg-bubble ul, .tvrs-msg-bubble ol {
        margin-bottom: 0;
        padding-left: 1.1rem;
    }

    /* Quick FAQ Pills */
    .tvrs-faq-pills {
        display: flex;
        flex-wrap: wrap;
        gap: 0.35rem;
        margin-top: 0.25rem;
    }
    .tvrs-faq-pill {
        background: #eff6ff;
        color: #1d4ed8;
        border: 1px solid #bfdbfe;
        border-radius: 20px;
        padding: 0.3rem 0.65rem;
        font-size: 0.72rem;
        font-weight: 600;
        cursor: pointer;
        transition: background 0.15s, border-color 0.15s;
    }
    .tvrs-faq-pill:hover {
        background: #dbeafe;
        border-color: #93c5fd;
    }

    /* Footer / Input */
    .tvrs-chat-footer {
        padding: 0.65rem 0.85rem;
        background: #ffffff;
        border-top: 1px solid #f1f5f9;
    }
    .tvrs-chat-input-group {
        display: flex;
        align-items: center;
        gap: 0.4rem;
        background: #f8fafc;
        border: 1px solid #cbd5e1;
        border-radius: 24px;
        padding: 0.2rem 0.3rem 0.2rem 0.8rem;
    }
    .tvrs-chat-input-group:focus-within {
        border-color: #2563eb;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
    }
    .tvrs-chat-input {
        flex: 1;
        border: none;
        background: transparent;
        font-size: 0.82rem;
        color: #0f172a;
        outline: none;
    }
    .tvrs-chat-send-btn {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        background: #1d4ed8;
        color: #ffffff;
        border: none;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.85rem;
        cursor: pointer;
        transition: background 0.15s;
    }
    .tvrs-chat-send-btn:hover { background: #1e40af; }
    .tvrs-chat-send-btn:disabled { background: #cbd5e1; cursor: not-allowed; }

    /* Typing Dots */
    .tvrs-typing {
        display: flex;
        align-items: center;
        gap: 4px;
        padding: 0.4rem 0.6rem;
    }
    .tvrs-typing-dot {
        width: 6px;
        height: 6px;
        background: #94a3b8;
        border-radius: 50%;
        animation: tvrsBounce 1.2s infinite ease-in-out;
    }
    .tvrs-typing-dot:nth-child(2) { animation-delay: 0.2s; }
    .tvrs-typing-dot:nth-child(3) { animation-delay: 0.4s; }
    @keyframes tvrsBounce {
        0%, 80%, 100% { transform: translateY(0); }
        40% { transform: translateY(-5px); }
    }
</style>

@php
    $currentUser = auth()->user();
    $persona = \App\Services\TvrsKnowledgeBase::getRolePersona($currentUser);
    $quickFaqs = \App\Services\TvrsKnowledgeBase::getQuickFaqsForUser($currentUser);
@endphp

<!-- Floating Trigger Button -->
<button type="button" class="tvrs-chat-trigger" id="tvrsChatTrigger" title="{{ $persona['trigger_title'] }}" aria-label="Open AI Chat Support">
    <i class="bi bi-robot"></i>
    <span class="tvrs-chat-badge-pulse"></span>
</button>

<!-- Chat Card Window -->
<div class="tvrs-chat-card" id="tvrsChatCard">
    <div class="tvrs-chat-header">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-circle bg-white text-primary d-flex align-items-center justify-content-center" style="width:30px;height:30px;font-size:.9rem;">
                <i class="bi bi-robot"></i>
            </div>
            <div>
                <div class="fw-700" id="tvrsAssistantName" style="font-size:.85rem;line-height:1.2;">{{ $persona['assistant_name'] }}</div>
                <div class="d-flex align-items-center gap-1" style="font-size:.65rem;color:#bfdbfe;">
                    <span class="rounded-circle bg-success" style="width:6px;height:6px;display:inline-block;"></span>
                    <span id="tvrsAssistantSubtitle">{{ $persona['subtitle'] }}</span>
                </div>
            </div>
        </div>
        <button type="button" class="btn-close btn-close-white" id="tvrsChatClose" aria-label="Close"></button>
    </div>

    <div class="tvrs-chat-body" id="tvrsChatBody">
        <!-- Initial Bot Greeting -->
        <div class="tvrs-msg bot">
            <div class="tvrs-msg-bubble">
                {!! \Illuminate\Support\Str::markdown($persona['greeting']) !!}
                <div class="mt-2 text-muted" style="font-size:.74rem;">Tap a common question below or type your custom query:</div>
                <div class="tvrs-faq-pills mt-2" id="tvrsFaqPills">
                    @foreach($quickFaqs as $faq)
                        <button class="tvrs-faq-pill" onclick="sendFaq('{{ $faq['id'] }}')">{{ $faq['pill_label'] ?? $faq['question'] }}</button>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="tvrs-chat-footer">
        <form id="tvrsChatForm" onsubmit="handleChatSubmit(event)">
            <div class="tvrs-chat-input-group">
                <input type="text" class="tvrs-chat-input" id="tvrsChatInput" placeholder="Ask anything about TVIRS..." autocomplete="off">
                <button type="submit" class="tvrs-chat-send-btn" id="tvrsSendBtn">
                    <i class="bi bi-send-fill"></i>
                </button>
            </div>
        </form>
        <div class="d-flex align-items-center justify-content-between mt-1 px-1" style="font-size:.65rem;color:#64748b;">
            <span id="tvrsAssistantBadge"><i class="bi bi-shield-check text-success me-1"></i>{{ $persona['badge'] }}</span>
            <span id="tvrsQuotaText">Daily Free Limit: 1,500</span>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const trigger = document.getElementById('tvrsChatTrigger');
    const card = document.getElementById('tvrsChatCard');
    const closeBtn = document.getElementById('tvrsChatClose');

    trigger.addEventListener('click', function() {
        card.classList.toggle('show');
        if (card.classList.contains('show')) {
            document.getElementById('tvrsChatInput').focus();
        }
    });

    closeBtn.addEventListener('click', function() {
        card.classList.remove('show');
    });
});

function appendUserMessage(text) {
    const body = document.getElementById('tvrsChatBody');
    const msgDiv = document.createElement('div');
    msgDiv.className = 'tvrs-msg user';
    msgDiv.innerHTML = `<div class="tvrs-msg-bubble">${escapeHtml(text)}</div>`;
    body.appendChild(msgDiv);
    body.scrollTop = body.scrollHeight;
}

function appendBotMessage(htmlContent) {
    const body = document.getElementById('tvrsChatBody');
    const msgDiv = document.createElement('div');
    msgDiv.className = 'tvrs-msg bot';
    msgDiv.innerHTML = `<div class="tvrs-msg-bubble">${formatMarkdown(htmlContent)}</div>`;
    body.appendChild(msgDiv);
    body.scrollTop = body.scrollHeight;
}

function showTypingIndicator() {
    const body = document.getElementById('tvrsChatBody');
    const typingDiv = document.createElement('div');
    typingDiv.id = 'tvrsTypingIndicator';
    typingDiv.className = 'tvrs-msg bot';
    typingDiv.innerHTML = `
        <div class="tvrs-msg-bubble py-2">
            <div class="tvrs-typing">
                <div class="tvrs-typing-dot"></div>
                <div class="tvrs-typing-dot"></div>
                <div class="tvrs-typing-dot"></div>
            </div>
        </div>
    `;
    body.appendChild(typingDiv);
    body.scrollTop = body.scrollHeight;
}

function hideTypingIndicator() {
    const indicator = document.getElementById('tvrsTypingIndicator');
    if (indicator) indicator.remove();
}

function sendFaq(faqId) {
    const faqMap = {
        'faq_add_user': 'How to add a new user or enforcer account?',
        'faq_sms_setup': 'How to setup free Android SIM Gateway for SMS?',
        'faq_gcash_claim': 'How do Cashiers verify online GCash payment claims?',
        'faq_settle_ticket': 'How to settle a violation ticket at Cashier counter?',
        'faq_motorist_setup': 'How to add or search motorists?',
        'faq_incidents_info': 'How to check or log road incidents?',
        'faq_ocr_scanner': 'How does the Officer License OCR Scanner work?',
        'faq_thermal_printer': 'How to print on Bluetooth thermal printer?',
        'faq_collection_report': 'How to generate Treasury Collection Reports?'
    };

    const questionText = faqMap[faqId] || 'Quick FAQ';
    appendUserMessage(questionText);
    showTypingIndicator();

    fetch('{{ route("chat-support.query") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ message: questionText, faq_id: faqId })
    })
    .then(async res => {
        if (res.status === 419) {
            return { success: false, message: 'Your session expired. Please refresh the page.' };
        }
        const text = await res.text();
        try { return JSON.parse(text); } catch(e) { return { success: false, message: 'Server error. Please try again.' }; }
    })
    .then(data => {
        hideTypingIndicator();
        if (data.success && data.answer) {
            appendBotMessage(data.answer);
            updateQuotaDisplay(data.daily_remaining);
        } else {
            appendBotMessage(data.message || 'Sorry, I encountered an issue retrieving the FAQ. Please try again.');
        }
    })
    .catch(err => {
        hideTypingIndicator();
        appendBotMessage('Network connection issue. Please check your internet connection.');
    });
}

function handleChatSubmit(e) {
    e.preventDefault();
    const input = document.getElementById('tvrsChatInput');
    const message = input.value.trim();
    if (!message) return;

    input.value = '';
    appendUserMessage(message);
    showTypingIndicator();

    fetch('{{ route("chat-support.query") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({ message: message })
    })
    .then(async res => {
        if (res.status === 419) {
            return { success: false, message: 'Your session expired. Please refresh the page to continue chatting.' };
        }
        const text = await res.text();
        try { return JSON.parse(text); } catch(e) { return { success: false, message: 'Unable to process AI response. Please try tapping a Quick FAQ.' }; }
    })
    .then(data => {
        hideTypingIndicator();
        if (data.success && data.answer) {
            appendBotMessage(data.answer);
            updateQuotaDisplay(data.daily_remaining);
        } else {
            appendBotMessage(data.message || 'Unable to process your query at the moment. Please try selecting a Quick FAQ above.');
        }
    })
    .catch(err => {
        hideTypingIndicator();
        appendBotMessage('Network connection issue. Please refresh or try again.');
    });
}

function updateQuotaDisplay(remaining) {
    if (typeof remaining !== 'undefined') {
        document.getElementById('tvrsQuotaText').innerText = `Daily AI Limit: ${remaining}/1,500 left`;
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.innerText = text;
    return div.innerHTML;
}

function formatMarkdown(text) {
    if (!text) return '';
    let formatted = text
        .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
        .replace(/\*(.*?)\*/g, '<em>$1</em>')
        .replace(/`([^`]+)`/g, '<code style="background:#f1f5f9;padding:1px 4px;border-radius:4px;font-size:.78rem;">$1</code>')
        .replace(/\[(.*?)\]\((.*?)\)/g, '<a href="$2" target="_blank" style="color:#1d4ed8;font-weight:600;">$1</a>')
        .replace(/\n\n/g, '<br><br>')
        .replace(/\n/g, '<br>');

    return formatted;
}
</script>
