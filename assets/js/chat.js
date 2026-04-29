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
let maxKnownReadId = 0;
const CHAT_DEBUG = true;


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
const statusDot  = document.getElementById('chat-status-dot');
const statusText = document.getElementById('chat-status-text');
function setStatusBadge(state) {
    if (!statusDot || !statusText) return;
    const map = {
        connecting: { dot: 'bg-yellow-400 animate-pulse', label: 'Connecting…'  },
        open      : { dot: 'bg-green-400',                label: 'Live'          },
        error     : { dot: 'bg-orange-400 animate-pulse', label: 'Reconnecting…' },
        closed    : { dot: 'bg-red-400',                  label: 'Offline'       },
    };
    const s = map[state] || map.closed;
    statusDot.className  = `inline-block w-2 h-2 rounded-full shrink-0 transition-colors duration-300 ${s.dot}`;
    statusText.textContent = s.label;
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
    if (msg.id > 0 && document.querySelector(`[data-msg-id="${msg.id}"]`)) return;

    // Alignment wrapper — flex row so justify-end/start places the bubble
    // correctly without relying on ml-auto/mr-auto on a w-fit block element.
    const wrapDiv = document.createElement('div');
    wrapDiv.className = `flex ${msg.is_mine ? 'justify-end' : 'justify-start'} mb-2`;

    const msgDiv = document.createElement('div');
    msgDiv.dataset.msgId = msg.id;

    // The bubble itself is a SINGLE flex row (not two stacked divs).
    // items-end  — timestamp aligns to the bottom of the last text line.
    // gap-2      — breathing room between content and meta.
    msgDiv.className = [
        'chat-bubble flex items-end gap-2',
        'max-w-[75%] px-3.5 py-2 shadow-sm text-sm leading-normal',
        msg.is_mine
            ? 'chat-bubble-outgoing chat-bubble-mine bg-uitmPurple text-white rounded-t-2xl rounded-bl-2xl rounded-br-sm'
            : 'chat-bubble-incoming chat-bubble-theirs bg-gray-100 text-gray-900 rounded-t-2xl rounded-br-2xl rounded-bl-sm border border-gray-200',
    ].join(' ');

    const metaColor = msg.is_mine ? 'text-purple-200' : 'text-gray-400';
    const tick = msg.is_mine ? buildTick(msg.status || 'sending') : '';

    // flex-1 min-w-0: content grows to fill available space AND allows text
    // to wrap correctly inside a flex item (min-w-0 is the key — without it
    // the flex item never shrinks below its min-content width).
    // shrink-0 self-end: meta never wraps and sits at the bottom of the row.
    msgDiv.innerHTML = `
        <span class="flex-1 min-w-0 whitespace-pre-wrap break-words">${msg.content}</span>
        <span class="chat-meta shrink-0 self-end flex items-center gap-0.5 text-[10px] leading-none ${metaColor} whitespace-nowrap">
            <span class="chat-timestamp">${msg.timestamp}</span>
            ${tick}
        </span>
    `;

    wrapDiv.appendChild(msgDiv);
    chatContainer.appendChild(wrapDiv);
}

/**
 * Build a delivery tick using Unicode characters — reliably visible at any size,
 * no SVG rendering inconsistencies.
 *   sending  → · (dimmed dot — in-flight)
 *   sent     → ✓ (single tick — server ACK received)
 *   delivered→ ✓✓ (double tick — rendered by recipient's client)
 */
function buildTick(status) {
    const map = { sending: '·', sent: '✓', delivered: '✓✓' };
    const symbol = map[status] || '✓';
    const opacityCls = status === 'sending' ? 'opacity-50' : 'opacity-90';
    return `<span class="tick-wrap text-[11px] font-semibold leading-none text-purple-200 ${opacityCls}">${symbol}</span>`;
}

/** Swap the tick symbol and opacity on an already-rendered bubble. */
function updateTick(msgId, status) {
    const bubble = document.querySelector(`[data-msg-id="${msgId}"]`);
    if (!bubble) return;
    const wrap = bubble.querySelector('.tick-wrap');
    if (!wrap) return;
    const map = { sending: '·', sent: '✓', delivered: '✓✓' };
    wrap.textContent = map[status] || '✓';
    // delivered gets full opacity; sending stays dimmed
    wrap.classList.toggle('opacity-50', status === 'sending');
    wrap.classList.toggle('opacity-90', status !== 'sending');
}

/**
 * Apply the best-known delivery state for an outgoing bubble.
 * This is safe to call repeatedly as the message id becomes known.
 */
function syncOutgoingBubbleState(msgId) {
    const bubble = document.querySelector(`[data-msg-id="${msgId}"]`);
    if (!bubble) return false;

    updateTick(msgId, msgId <= maxKnownReadId ? 'delivered' : 'sent');
    return true;
}

// ---------------------------------------------------------------------------
// Typing indicator
// ---------------------------------------------------------------------------
const TYPING_INDICATOR_ID = 'chat-typing-indicator';

function showTypingIndicator() {
    if (document.getElementById(TYPING_INDICATOR_ID + '-wrap')) return;

    const wrapDiv = document.createElement('div');
    wrapDiv.id = TYPING_INDICATOR_ID + '-wrap';
    wrapDiv.className = 'flex justify-start mb-2';

    const el = document.createElement('div');
    el.id = TYPING_INDICATOR_ID;
    el.className = 'chat-bubble-incoming flex gap-1.5 items-center px-4 py-3 bg-gray-100 border border-gray-200 rounded-t-2xl rounded-br-2xl rounded-bl-sm';
    el.innerHTML = `
        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce [animation-delay:-0.3s]"></span>
        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce [animation-delay:-0.15s]"></span>
        <span class="w-2 h-2 bg-gray-400 rounded-full animate-bounce"></span>
    `;

    wrapDiv.appendChild(el);
    chatContainer.appendChild(wrapDiv);
    scrollToBottom();
}

function hideTypingIndicator() {
    const wrap = document.getElementById(TYPING_INDICATOR_ID + '-wrap');
    if (wrap) { wrap.remove(); return; }
    // Fallback for legacy references
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
    fetch(`api/fetch_messages?user=${receiverId}`)
        .then(res => {
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            return res.json();
        })
        .then(data => {
            chatContainer.innerHTML = '';
            const msgs = data.messages || [];
            msgs.forEach(msg => {
                const status = msg.is_mine
                    ? ((msg.is_read || msg.status === 'delivered') ? 'delivered' : 'sent')
                    : 'delivered';
                appendMessage({ ...msg, status: status });
                if (msg.id > lastMessageId) lastMessageId = safeCursor(msg.id);
            });
            // Force-scroll on initial load regardless of position
            scrollToBottom(true);
            startStream();
            startPolling();
            markRead();
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

    const url = `api/stream_messages?user=${receiverId}&last_id=${cursor}`;
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

        if (msgId > lastMessageId) lastMessageId = msgId;

        // Race-condition guard for own messages:
        // If we sent a message optimistically (tempId < 0) and the SSE frame
        // arrives BEFORE the POST response has swapped the temp ID to realId,
        // the dedup querySelector won't find it (it's stored as a negative id).
        // → Claim the orphaned temp bubble here to prevent a duplicate render.
        if (msg.is_mine) {
            const tempBubble = chatContainer.querySelector('[data-msg-id^="-"]');
            if (tempBubble) {
                tempBubble.dataset.msgId = msgId;
                // SSE ack means server persisted the message, not that it was read.
                // Keep it as sent and wait for read_receipt/poll updates for delivered.
                syncOutgoingBubbleState(msgId);
                return; // claimed — don't render a second bubble
            }
        }

        // Standard dedup: skip if this id is already in the DOM
        if (document.querySelector(`[data-msg-id="${msgId}"]`)) return;

        // Hide typing indicator when receiver sends a real message
        if (!msg.is_mine) hideTypingIndicator();

        const status = msg.is_mine
            ? ((msg.is_read || msg.status === 'delivered') ? 'delivered' : 'sent')
            : 'delivered';
        enqueueBubble({ ...msg, id: msgId, status: status });
        if (!msg.is_mine) markRead();
    };

    // Named "typing" event — requires server to emit: event: typing\ndata: {}\n\n
    eventSource.addEventListener('typing', handleTypingEvent);

    eventSource.addEventListener('read_receipt', function (event) {
        let data;
        try { data = JSON.parse(event.data); } catch(e) {
            if (CHAT_DEBUG) console.warn('[chat] Invalid read_receipt payload:', event.data);
            return;
        }
        if (data.last_read_id) {
            maxKnownReadId = Math.max(maxKnownReadId, data.last_read_id);
            if (CHAT_DEBUG) {
                console.debug('[chat] read_receipt received', {
                    partnerId: receiverId,
                    lastReadId: data.last_read_id,
                    maxKnownReadId
                });
            }
            document.querySelectorAll('.chat-bubble-mine').forEach(bubble => {
                const id = parseInt(bubble.dataset.msgId, 10);
                if (id > 0 && id <= maxKnownReadId) {
                    updateTick(id, 'delivered');
                }
            });
        } else if (CHAT_DEBUG) {
            console.debug('[chat] read_receipt received without last_read_id', data);
        }
    });

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

    fetch(`api/fetch_messages?user=${receiverId}`)
        .then(res => res.json())
        .then(data => {
            const msgs = data.messages || [];
            let hasNew = false;
            msgs.forEach(msg => {
                const id = safeCursor(msg.id);
                
                // Update read status for existing bubbles
                if (msg.is_mine && (msg.is_read || msg.status === 'delivered')) {
                    maxKnownReadId = Math.max(maxKnownReadId, id);
                    updateTick(id, 'delivered');
                }

                if (id > lastMessageId && !document.querySelector(`[data-msg-id="${id}"]`)) {
                    const status = msg.is_mine
                        ? ((msg.is_read || msg.status === 'delivered') ? 'delivered' : 'sent')
                        : 'delivered';
                    enqueueBubble({ ...msg, id, status: status });
                    if (!msg.is_mine) markRead();
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
    // Escape HTML locally to match the server's escape() behavior and prevent self-XSS via innerHTML
    const escapedContent = content
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    appendMessage({ id: tempId, content: escapedContent, is_mine: true, timestamp: nowStamp, status: 'sending' });
    scrollToBottom(true);

    // 2. POST to server
    fetch('api/send_message', {
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
        const bubble = document.querySelector(`[data-msg-id="${tempId}"]`) || document.querySelector(`[data-msg-id="${realId}"]`);
        if (bubble) bubble.dataset.msgId = realId;
        syncOutgoingBubbleState(realId);
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
function markRead() {
    if (!receiverId) return;
    fetch('api/mark_read', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ user: receiverId })
    })
    .then(res => {
        if (!res.ok) {
            throw new Error(`HTTP ${res.status}`);
        }
        return res.json();
    })
    .then(data => {
        if (CHAT_DEBUG) {
            console.debug('[chat] markRead response', {
                partnerId: receiverId,
                success: !!data.success,
                updated: data.updated || 0,
                error: data.error || null
            });
        }
    })
    .catch(err => console.error('[chat] markRead error:', err));
}

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
