/* assets/js/chat.js — Refactored to Server-Sent Events (SSE)
 * =========================================================================
 * ARCHITECTURE CHANGE (Polling → SSE):
 *   OLD: setInterval(fetchMessages, 3000) — client asks every 3 s, wastes
 *        bandwidth even when there are no new messages.
 *   NEW: EventSource — the server holds the connection and PUSHES frames
 *        only when new rows appear in the database. The browser handles
 *        reconnection automatically (with exponential back-off).
 *
 * FLOW:
 *   1. Page loads → fetch full history via api/fetch_messages.php (one shot).
 *   2. Open EventSource → api/stream_messages.php?user=X&last_id=Y.
 *   3. Each SSE frame appends ONE new message bubble (not a full re-render).
 *   4. On send → POST to api/send_message.php; SSE picks up the new row
 *      on the server's next 1-second tick.
 *   5. On beforeunload or contact switch → EventSource.close() prevents
 *      multiple open streams / zombie connections.
 * =========================================================================
 */

const chatContainer = document.getElementById('chat-messages');
const inputField    = document.getElementById('chat-input');
const receiverId    = chatContainer ? chatContainer.dataset.receiver : null;

// Tracks the highest message_id the client has received.
// Passed to the SSE endpoint so it starts streaming from where we left off.
let lastMessageId = 0;

// Reference to the active EventSource so we can close it cleanly.
let eventSource = null;

// -------------------------------------------------------------------------
// appendMessage — render a SINGLE message bubble
// (used by both history render and SSE real-time push)
// -------------------------------------------------------------------------
function appendMessage(msg) {
    const msgDiv = document.createElement('div');
    msgDiv.dataset.msgId = msg.id;
    msgDiv.className = [
        'max-w-[75%] rounded px-4 py-2 mb-3 shadow text-sm',
        msg.is_mine
            ? 'bg-uitmPurple text-white ml-auto rounded-br-none'
            : 'bg-gray-100 text-gray-800 mr-auto rounded-bl-none',
    ].join(' ');

    msgDiv.innerHTML = `
        <div class="mb-1">${msg.content}</div>
        <div class="text-[10px] text-right ${msg.is_mine ? 'text-purple-200' : 'text-gray-400'}">${msg.timestamp}</div>
    `;

    chatContainer.appendChild(msgDiv);
}

// -------------------------------------------------------------------------
// scrollToBottom — smooth-scroll to the latest message
// -------------------------------------------------------------------------
function scrollToBottom() {
    chatContainer.scrollTo({ top: chatContainer.scrollHeight, behavior: 'smooth' });
}

// -------------------------------------------------------------------------
// loadHistory — one-shot fetch of full conversation history on page load
// Sets lastMessageId so the SSE stream starts from the correct cursor.
// -------------------------------------------------------------------------
function loadHistory() {
    if (!receiverId) return;

    fetch(`api/fetch_messages.php?user=${receiverId}`)
        .then(res => res.json())
        .then(data => {
            if (!data.messages || data.messages.length === 0) return;

            chatContainer.innerHTML = ''; // clear any placeholder

            data.messages.forEach(msg => {
                appendMessage(msg);
                // Track the highest id we've seen so SSE starts after this
                if (msg.id > lastMessageId) lastMessageId = msg.id;
            });

            scrollToBottom();

            // Open the SSE stream AFTER history is loaded so the cursor is set
            startStream();
        })
        .catch(err => {
            console.error('[chat] History fetch error:', err);
            // Even if history fails, still try to open the stream
            startStream();
        });
}

// -------------------------------------------------------------------------
// startStream — open the EventSource connection to the SSE endpoint.
// Closes any existing stream first to prevent zombie connections.
// -------------------------------------------------------------------------
function startStream() {
    if (!receiverId) return;

    // Clean up any previous connection (e.g., user switched contacts)
    closeStream();

    const url = `api/stream_messages.php?user=${receiverId}&last_id=${lastMessageId}`;
    eventSource = new EventSource(url);

    // onmessage fires for every SSE frame the server pushes
    eventSource.onmessage = function (event) {
        const msg = JSON.parse(event.data);

        // Guard: skip if we already rendered this message (shouldn't happen,
        // but the cursor could overlap if reconnect timing is tight)
        if (msg.id <= lastMessageId && document.querySelector(`[data-msg-id="${msg.id}"]`)) {
            return;
        }

        appendMessage(msg);

        // Advance the cursor — the browser also stores this as Last-Event-ID
        // automatically, which the SSE endpoint reads on reconnect
        if (msg.id > lastMessageId) lastMessageId = msg.id;

        scrollToBottom();
    };

    eventSource.onerror = function () {
        // The browser will automatically attempt to reconnect using
        // Last-Event-ID, so we don't need to do anything here.
        // Log for debugging in development only.
        console.warn('[chat] SSE connection lost — browser will auto-reconnect.');
    };
}

// -------------------------------------------------------------------------
// closeStream — close the EventSource gracefully
// -------------------------------------------------------------------------
function closeStream() {
    if (eventSource) {
        eventSource.close();
        eventSource = null;
    }
}

// -------------------------------------------------------------------------
// sendMessage — POST the message, then let SSE deliver it back
// -------------------------------------------------------------------------
function sendMessage() {
    const content = inputField.value.trim();
    if (!content || !receiverId) return;

    inputField.value = ''; // clear input immediately for responsiveness

    fetch('api/send_message.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ receiver_id: receiverId, content: content }),
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success) {
            console.error('[chat] Send failed:', data.error);
            alert(data.error || 'Failed to send message. Please try again.');
        }
        // No need to manually fetch — the SSE loop will pick up the new
        // row within ~1 second and push it back automatically.
    })
    .catch(err => console.error('[chat] Send error:', err));
}

// -------------------------------------------------------------------------
// Cleanup on navigation — prevents multiple open SSE connections when the
// user clicks a contact link (chat.php reloads the page anyway, but this
// fires before the unload to close cleanly on the client side).
// -------------------------------------------------------------------------
window.addEventListener('beforeunload', closeStream);

// -------------------------------------------------------------------------
// Bootstrap
// -------------------------------------------------------------------------
if (chatContainer) {
    loadHistory(); // loads history then opens SSE stream
}
