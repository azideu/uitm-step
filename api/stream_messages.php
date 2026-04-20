<?php
// api/stream_messages.php — Server-Sent Events (SSE) Streaming Endpoint
// =========================================================================
// ARCHITECTURE NOTE:
// Instead of the client polling every 3 seconds (pull), this endpoint keeps
// an HTTP connection open and the SERVER pushes new rows to the client the
// moment they appear in the database (push). The browser's native EventSource
// API handles reconnection automatically if the connection drops.
//
// The Last-Event-ID mechanism ensures no messages are lost on reconnect:
//   • The browser remembers the last message_id it received.
//   • On reconnect it sends it back via the "Last-Event-ID" HTTP header.
//   • We use that value as the cursor for the next query.
// =========================================================================

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// --- Auth guard ---
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$my_id     = (int)$_SESSION['user_id'];
$other_id  = isset($_GET['user']) ? (int)$_GET['user'] : 0;

if ($other_id <= 0) {
    http_response_code(400);
    exit;
}

// CRITICAL: Release the PHP session lock immediately after reading session data.
// PHP sessions use an exclusive file lock — if we hold it inside the while(true)
// loop, every other request from this browser (including send_message.php) will
// block waiting for the lock, so sent messages never get inserted until a reload.
session_write_close();

// --- SSE Headers ---
// Disable output buffering at every level so data is flushed immediately.
// PHP's default zlib compression would buffer SSE frames—disable it.
@ini_set('zlib.output_compression', 0);
@ini_set('output_buffering', 'off');
if (ob_get_level()) {
    ob_end_clean();
}

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // Disable Nginx proxy buffering if present

// --- Cursor: honour browser's Last-Event-ID for reconnect resilience ---
// On the very first connection the client sends ?last_id=0.
// On reconnect the browser automatically sends the Last-Event-ID header.
$last_id = isset($_SERVER['HTTP_LAST_EVENT_ID'])
    ? (int)$_SERVER['HTTP_LAST_EVENT_ID']
    : (isset($_GET['last_id']) ? (int)$_GET['last_id'] : 0);

// Prepare the incremental query once outside the loop (performance).
// We only fetch messages that belong to THIS conversation and are NEWER
// than the last message the client already has.
$stmt = $pdo->prepare("
    SELECT message_id, sender_id, content, timestamp
    FROM   messages
    WHERE  message_id > :last_id
      AND  (
               (sender_id = :me  AND receiver_id = :other)
            OR (sender_id = :other2 AND receiver_id = :me2)
           )
    ORDER BY message_id ASC
");

// --- Streaming loop ---
// We keep this connection alive and push new rows as they arrive.
// sleep(1) keeps CPU usage negligible while still feeling real-time.
while (true) {

    // Abort if client disconnected (tab closed, navigated away, etc.)
    if (connection_aborted()) {
        break;
    }

    $stmt->execute([
        ':last_id' => $last_id,
        ':me'      => $my_id,
        ':other'   => $other_id,
        ':other2'  => $other_id,
        ':me2'     => $my_id,
    ]);

    $rows = $stmt->fetchAll();

    foreach ($rows as $row) {
        $last_id = (int)$row['message_id'];

        // Build JSON payload for the client
        $payload = json_encode([
            'id'        => $last_id,
            'content'   => escape($row['content']),  // XSS-safe via escape()
            'is_mine'   => $row['sender_id'] == $my_id,
            'timestamp' => date('H:i', strtotime($row['timestamp'])),
        ]);

        // SSE frame format:
        //   id: <message_id>      ← the cursor; browser stores this as Last-Event-ID
        //   data: <json>
        //   (blank line)          ← signals end of frame to the browser
        echo "id: {$last_id}\n";
        echo "data: {$payload}\n\n";

        ob_flush();
        flush();
    }

    sleep(1); // 1-second heartbeat — low CPU, still near-instant delivery
}
?>
