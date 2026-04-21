/* assets/js/chat.js — Polished SSE Chat Module
 * =========================================================================
 * ARCHITECTURE: Server-Sent Events (SSE) + optimistic UI
 *
 * FLOW:
 *   1. Page load  → loadHistory() fetches full history (one-shot fetch).
 *   2. After history → startStream() opens SSE for real-time push.
 *   3. Each SSE frame appends ONE new bubble (batched if burst arrives).
 *   4. sendMessage() → optimistic bubble shown instantly, POST in background.
 *   5. SSE frame for own message is swapped with the real ID, not duplicated.
 *   6. pollNewMessages() runs every 3 s as a safety net / SSE fallback.
 *   7. beforeunload → closeStream() + closePolling() prevent zombie conns.
 *
 * POLISH APPLIED (see inline comments):
 *   • Connection resilience  — explicit SSE state machine + retry cap
 *   • Session lock           — session_write_close() is already in PHP;
 *                              fetch_messages.php also gets it added.
 *   • Smart auto-scroll      — only scroll if user is near the bottom.
 *   • Message batching       — batch DOM writes inside requestAnimationFrame.
 *   • Security (cursor)      — last_id validated as strict positive integer.
 *   • Delivery ticks         — ✓ (sent) / ✓✓ (delivered) via data-status.
 *   • Typing indicator       — sendTyping() + renderTypingIndicator() stubs.
 * =========================================================================
 */

'use strict';

// ---------------------------------------------------------------------------
// DOM references
// ---------------------------------------------------------------------------
const chatContainer = document.getElementById('chat-messages');
const inputField    = document.getElementById('chat-input');
const receiverId    = chatContainer ? parseInt(chatContainer.dataset.receiver, 10) : null;

// ---------------------------------------------------------------------------
// State
// ---------------------------------------------------------------------------
/** Highest message_id the client has seen — used as SSE cursor.
 *  Strictly coerced to a non-negative integer before every use. */
let lastMessageId = 0;

/** Active EventSource reference. */
let eventSource = null;

/** Fallback polling timer. */
let pollIntervalId = null;

/** SSE reconnect state machine:
 *    'idle' → 'connecting' → 'open' → 'error' → back to 'connecting'
 *  We cap retries to avoid hammering a dead server. */
const SSE_MAX_RETRIES = 8;
let sseRetryCount   = 0;
let sseState        = 'idle'; // 'idle' | 'connecting' | 'open' | 'error' | 'closed'

/** Typing indicator — cleared after 3 s of silence. */
let typingTimer = null;

// ---------------------------------------------------------------------------
// Utilities
// ---------------------------------------------------------------------------

/** Clamp and validate a cursor integer — prevents negative/NaN cursor bugs. */
function safeCursor(value) {
    const n = parseInt(value, 10);
    return isFinite(n) && n >= 0 ? n : 0;
}

/** True when the user is scrolled close to the bottom of the chat window.
 *  Only auto-scroll when they haven't manually scrolled up. */
function isNearBottom() {
    const threshold = 80; // px
    return chatContainer.scrollHeight - chatContainer.scrollTop - chatContainer.clientHeight <= threshold;
}

/** Smooth-scroll to the bottom. */
function scrollToBottom(force = false) {
    if (force || isNearBottom()) {
        chatContainer.scrollTo({ top: chatContainer.scrollHeight, behavior: 'smooth' });
    }
}

/** Format a JS Date as HH:MM. */
function fmtTime(date) {
    return `${String(date.getHours()).padStart(2,'0')}:${String(date.getMinutes()).padStart(2,'0')}`;
}

// ---------------------------------------------------------------------------
// DOM: connection status badge
// ---------------------------------------------------------------------------
const statusBadge = document.getElementById('chat-status-badge');
function setStatusBadge(state) {
    if (!statusBadge) return;
    const map = {
        connecting : { text: 'Connecting…', cls: 'text-yellow-400' },
        open       : { text: 'Live',        cls: 'text-green-400'  },
        error      : { text: 'Reconnecting…',cls:'text-orange-400' },
        closed     : { text: 'Offline',     cls: 'text-red-400'    },
    };
    const s = map[state] || map.closed;
    statusBadge.textContent  = s.text;
    statusBadge.className    = `text-[10px] font-medium ${s.cls} transition-colors duration-300`;
}

// ---------------------------------------------------------------------------
// appendMessage — render a single bubble, with delivery tick support
// ---------------------------------------------------------------------------
/**
 * @param {object} msg
 * @param {number}  msg.id         — real DB id, or negative temp id
 * @param {string}  msg.content    — already XSS-escaped by server
 * @param {boolean} msg.is_mine
 * @param {string}  msg.timestamp  — "HH:MM"
 * @param {string}  [msg.status]   — 'sending' | 'sent' | 'delivered' (optional)
 */
function appendMessage(msg) {
    // Dedup guard — never render the same real ID twice
    if (msg.id > 0 && document.querySelector(`[data-msg-id="${msg.id}"]`)) return;

    const msgDiv = document.createElement('div');
    msgDiv.dataset.msgId = msg.id;
    msgDiv.className = [
        'chat-bubble w-fit max-w-[75%] px-4 py-2.5 mb-3 shadow-sm text-sm',
        'leading-relaxed whitespace-pre-wrap break-words',
        msg.is_mine
            ? 'chat-bubble-outgoing chat-bubble-mine bg-uitmPurple text-white ml-auto rounded-t-2xl rounded-bl-2xl rounded-br-sm'
            : 'chat-bubble-incoming chat-bubble-theirs bg-gray-100 text-gray-900 mr-auto rounded-t-2xl rounded-br-2xl rounded-bl-sm border border-gray-200',
    ].join(' ');

    // Delivery tick icons (own messages only) — updated later by updateTick()
    const tick = msg.is_mine ? buildTick(msg.status || 'sending') : '';

    msgDiv.innerHTML = `
        <div class="mb-1">${msg.content}</div>
        <div class="flex items-center justify-end gap-1">
            <span class="text-[10px] ${msg.is_mine ? 'text-purple-200' : 'text-gray-400'}">${msg.timestamp}</span>
            ${tick}
        </div>
    `;

    chatContainer.appendChild(msgDiv);
}

/** Build the SVG tick span for a given status. */
function buildTick(status) {
    // 'sending' → clock icon, 'sent' → single ✓, 'delivered' → double ✓✓
    const icons = {
        sending: `<svg class="tick-icon w-3 h-3 text-purple-300" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M8 1a7 7 0 1 0 0 14A7 7 0 0 0 8 1zm.5 3.5v4l2.793 2.793-.707.707L7.5 8.914V4.5h1z"/>
                  </svg>`,
        sent    : `<svg class="tick-icon w-3 h-3 text-purple-300" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M10.97 4.97a.75.75 0 0 1 1.07 1.05l-3.99 4.99a.75.75 0 0 1-1.08.02L4.324 8.384a.75.75 0 1 1 1.06-1.06l2.094 2.093 3.473-4.425z"/>
                  </svg>`,
        delivered:`<svg class="tick-icon w-3 h-3 text-purple-200" viewBox="0 0 16 16" fill="currentColor">
                    <path d="M12.354 4.354a.5.5 0 0 0-.708-.708L5 11.293 1.854 8.146a.5.5 0 1 0-.708.708l3.5 3.5a.5.5 0 0 0 .708 0zm-3.5 0a.5.5 0 0 0-.708-.708L4 8.293 2.854 7.146a.5.5 0 1 0-.708.708l1.5 1.5a.5.5 0 0 0 .708 0l5-5z"/>
                  </svg>`,
    };
    return `<span class="tick-wrap">${icons[status] || icons.sent}</span>`;
}

/** Update the tick icon on an already-rendered bubble after server confirms. */
function updateTick(msgId, status) {
    const bubble = document.querySelector(`[data-msg-id="${msgId}"]`);
    if (!bubble) return;
    const wrap = bubble.querySelector('.tick-wrap');
    if (wrap) wrap.innerHTML = buildTick(status).replace(/<span[^>]*>|<\/span>/g, '');
}

// ---------------------------------------------------------------------------
// Typing indicator
// ---------------------------------------------------------------------------
const TYPING_INDICATOR_ID = 'chat-typing-indicator';

function showTypingIndicator() {
    if (document.getElementById(TYPING_INDICATOR_ID)) return;
    const el = document.createElement('div');
    el.id = TYPING_INDICATOR_ID;
    el.className = 'chat-bubble-incoming flex gap-1 items-center px-4 py-3 mb-3 bg-gray-100 border border-gray-200 w-fit rounded-t-2xl rounded-br-2xl rounded-bl-sm mr-auto';
    el.innerHTML = `
        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce [animation-delay:-0.3s]"></span>
        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce [animation-delay:-0.15s]"></span>
        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></span>
    `;
    chatContainer.appendChild(el);
    scrollToBottom();
}

function hideTypingIndicator() {
    const el = document.getElementById(TYPING_INDICATOR_ID);
    if (el) el.remove();
}

/**
 * Call this from the SSE onmessage handler when a "typing" event frame arrives.
 * The server would send: event: typing\ndata: {}\n\n
 * (Requires a /api/typing.php endpoint + schema column — see schema notes.)
 */
function handleTypingEvent() {
    showTypingIndicator();
    clearTimeout(typingTimer);
    typingTimer = setTimeout(hideTypingIndicator, 3000);
}

// ---------------------------------------------------------------------------
// Batch DOM writer — groups rapid successive messages into one rAF tick
// ---------------------------------------------------------------------------
let pendingBatch = [];
let batchScheduled = false;

function enqueueBubble(msg) {
    pendingBatch.push(msg);
    if (!batchScheduled) {
        batchScheduled = true;
        requestAnimationFrame(flushBatch);
    }
}

function flushBatch() {
    const wasNearBottom = isNearBottom();
    pendingBatch.forEach(appendMessage);
    pendingBatch  = [];
    batchScheduled = false;
    if (wasNearBottom) scrollToBottom();
}

// ---------------------------------------------------------------------------
// loadHistory — one-shot fetch of full history on page load
// ---------------------------------------------------------------------------
function loadHistory() {
    if (!receiverId) return;

    // Release session lock before a long-lived AJAX call
    // (session_write_close is already called in stream_messages.php;
    //  fetch_messages.php closes it too after reading user_id)
    fetch(`api/fetch_messages.php?user=${receiverId}`)
        .then(res => {
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return res.json();
        })
        .then(data => {
            chatContainer.innerHTML = '';
            const msgs = data.messages || [];
            msgs.forEach(msg => {
                appendMessage({ ...msg, status: 'delivered' });
                if (msg.id > lastMessageId) lastMessageId = safeCursor(msg.id);
            });
            // Force-scroll on initial load regardless of position
            scrollToBottom(true);
            startStream();
            startPolling();
        })
        .catch(err => {
            console.error('[chat] History fetch error:', err);
            startStream();
            startPolling();
        });
}

// ---------------------------------------------------------------------------
// SSE: startStream — explicit state machine, capped retry
// ---------------------------------------------------------------------------
function startStream() {
    if (!receiverId) return;
    closeStream(); // clean up any zombie connection

    // Validate cursor before embedding in URL
    const cursor = safeCursor(lastMessageId);

    sseState      = 'connecting';
    sseRetryCount = 0;
    setStatusBadge('connecting');

    const url = `api/stream_messages.php?user=${receiverId}&last_id=${cursor}`;
    eventSource = new EventSource(url);

    eventSource.onopen = function () {
        sseState      = 'open';
        sseRetryCount = 0;
        setStatusBadge('open');
        console.info('[chat] SSE connected.');
    };

    // Default event (unnamed) — new message pushed by server
    eventSource.onmessage = function (event) {
        let msg;
        try {
            msg = JSON.parse(event.data);
        } catch (e) {
            console.warn('[chat] Malformed SSE frame:', event.data);
            return;
        }

        // Strict cursor validation — id must be a positive integer
        const msgId = safeCursor(msg.id);
        if (msgId <= 0) return; // reject invalid frames

        // Skip if we already rendered this id (optimistic bubble or dupe)
        if (document.querySelector(`[data-msg-id="${msgId}"]`)) {
            // Still advance cursor in case it was an optimistic bubble swap
            if (msgId > lastMessageId) lastMessageId = msgId;
            return;
        }

        if (msgId > lastMessageId) lastMessageId = msgId;

        // Hide typing indicator if the other person just sent a real message
        if (!msg.is_mine) hideTypingIndicator();

        enqueueBubble({ ...msg, id: msgId, status: 'delivered' });
    };

    // Named "typing" event — requires server to emit: event: typing\ndata: {}\n\n
    eventSource.addEventListener('typing', handleTypingEvent);

    eventSource.onerror = function () {
        sseState = 'error';
        setStatusBadge('error');

        if (sseRetryCount >= SSE_MAX_RETRIES) {
            console.warn('[chat] SSE max retries reached — falling back to polling only.');
            closeStream();
            sseState = 'closed';
            setStatusBadge('closed');
            return;
        }

        // The browser will auto-reconnect; we just track the retry count.
        sseRetryCount++;
        console.warn(`[chat] SSE error — browser will reconnect (attempt ${sseRetryCount}/${SSE_MAX_RETRIES}).`);
    };
}

// ---------------------------------------------------------------------------
// SSE: closeStream
// ---------------------------------------------------------------------------
function closeStream() {
    if (eventSource) {
        eventSource.close();
        eventSource = null;
        sseState    = 'idle';
    }
}

// ---------------------------------------------------------------------------
// Fallback polling — safety net if SSE drops silently
// ---------------------------------------------------------------------------
function pollNewMessages() {
    if (!receiverId) return;

    fetch(`api/fetch_messages.php?user=${receiverId}`)
        .then(res => res.json())
        .then(data => {
            const msgs = data.messages || [];
            let hasNew = false;
            msgs.forEach(msg => {
                const id = safeCursor(msg.id);
                if (id > lastMessageId && !document.querySelector(`[data-msg-id="${id}"]`)) {
                    enqueueBubble({ ...msg, id, status: 'delivered' });
                    if (id > lastMessageId) lastMessageId = id;
                    hasNew = true;
                }
            });
            // flushBatch auto-scrolls if near bottom; nothing extra needed
        })
        .catch(err => console.error('[chat] Poll error:', err));
}

function startPolling() {
    if (pollIntervalId) clearInterval(pollIntervalId);
    pollIntervalId = setInterval(pollNewMessages, 3000);
}

function closePolling() {
    if (pollIntervalId) {
        clearInterval(pollIntervalId);
        pollIntervalId = null;
    }
}

// ---------------------------------------------------------------------------
// sendMessage — optimistic UI + delivery tick lifecycle
// ---------------------------------------------------------------------------
function sendMessage() {
    const content = inputField.value.trim();
    if (!content || !receiverId) return;

    inputField.value = '';
    inputField.focus();

    const tempId   = -(Date.now()); // negative sentinel
    const nowStamp = fmtTime(new Date());

    // 1. Show bubble immediately (status: 'sending' → clock icon)
    appendMessage({ id: tempId, content, is_mine: true, timestamp: nowStamp, status: 'sending' });
    scrollToBottom(true);

    // 2. POST to server
    fetch('api/send_message.php', {
        method : 'POST',
        headers: { 'Content-Type': 'application/json' },
        body   : JSON.stringify({ receiver_id: receiverId, content }),
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            // Roll back optimistic bubble, restore input
            document.querySelector(`[data-msg-id="${tempId}"]`)?.remove();
            inputField.value = content;
            alert(data.error || 'Message failed to send. Please try again.');
            return;
        }

        // 3. Swap temp id → real message_id so SSE dedup guard fires correctly
        const realId = safeCursor(data.message_id);
        const bubble = document.querySelector(`[data-msg-id="${tempId}"]`);
        if (bubble) {
            bubble.dataset.msgId = realId;
            updateTick(realId, 'sent'); // upgrade clock → single ✓
        }
        if (realId > lastMessageId) lastMessageId = realId;
    })
    .catch(err => {
        console.error('[chat] Send error:', err);
        document.querySelector(`[data-msg-id="${tempId}"]`)?.remove();
        inputField.value = content;
    });
}

// ---------------------------------------------------------------------------
// Cleanup on navigation
// ---------------------------------------------------------------------------
window.addEventListener('beforeunload', () => {
    closeStream();
    closePolling();
});

// ---------------------------------------------------------------------------
// Bootstrap
// ---------------------------------------------------------------------------
if (chatContainer) {
    loadHistory();

    // Show/hide scroll-to-bottom FAB based on scroll position
    const scrollBtn = document.getElementById('scroll-btn');
    if (scrollBtn) {
        chatContainer.addEventListener('scroll', () => {
            scrollBtn.classList.toggle('visible', !isNearBottom());
        }, { passive: true });
    }
}
