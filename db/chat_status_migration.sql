-- =============================================================================
-- UiTM STEP — Chat Module Schema Extensions
-- =============================================================================
-- Run these against uitm_step ONLY when you are ready to enable:
--   1. Message delivery receipts (sent / delivered)
--   2. Typing indicator
--
-- These are OPTIONAL. The current chat module works without them; the JS
-- already shows optimistic delivery ticks (clock → ✓) using client-side state.
-- Adding these columns allows the server to confirm delivery reliably.
-- =============================================================================

USE uitm_step;

-- 1. Delivery status column on messages
--    sent      → row exists in DB (default)
--    delivered → receiver's client has rendered the bubble
-- ---------------------------------------------------------------------------
ALTER TABLE messages
    ADD COLUMN status ENUM('sent','delivered') NOT NULL DEFAULT 'sent'
    AFTER content;

-- When a client opens a conversation (fetch_messages.php), run:
--   UPDATE messages SET status = 'delivered'
--   WHERE receiver_id = :me AND sender_id = :other AND status = 'sent'
-- Then include status in the JSON payload so the sender's SSE frame can
-- upgrade the tick from ✓ to ✓✓.


-- 2. Typing indicator — lightweight ephemeral table
--    Rows are upserted by /api/typing.php and expire after 5 seconds.
--    stream_messages.php emits a named SSE event:  event: typing\ndata:{}\n\n
--    when it detects a fresh row for the other user.
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS typing_status (
    user_id    INT         NOT NULL,
    partner_id INT         NOT NULL,
    updated_at TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP
                           ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (user_id, partner_id),
    FOREIGN KEY (user_id)    REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (partner_id) REFERENCES users(user_id) ON DELETE CASCADE
);

-- /api/typing.php would do:
--   INSERT INTO typing_status (user_id, partner_id) VALUES (:me, :other)
--   ON DUPLICATE KEY UPDATE updated_at = NOW()
-- stream_messages.php checks:
--   SELECT 1 FROM typing_status
--   WHERE user_id = :other AND partner_id = :me
--     AND updated_at > NOW() - INTERVAL 4 SECOND
-- If found, emit:  echo "event: typing\ndata: {}\n\n";
