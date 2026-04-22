<?php
// api/stream_messages.php — Server-Sent Events (SSE) Streaming Endpoint
// =========================================================================
// ARCHITECTURE: push model — server holds connection and emits new rows.
//
// POLISH APPLIED:
//   • Strict last_id validation (positive integer, prevents cursor injection)
//   • Self-close after MAX_STREAM_SECONDS so PHP/Apache reclaim the process
//   • Heartbeat "comment" frame every ~15 s keeps proxies/CDNs from timing out
//   • Named "typing" event reserved for future /api/typing.php integration
//   • session_write_close() releases file lock BEFORE the long-running loop
//   • No SELECT * — only the columns we actually need
// =========================================================================

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// --- Auth guard (read session, then immediately release the lock) ----------
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit;
}

$my_id    = (int)$_SESSION['user_id'];
$other_id = isset($_GET['user']) ? (int)$_GET['user'] : 0;

if ($other_id <= 0 || $other_id === $my_id) {
    http_response_code(400);
    exit;
}

// CRITICAL: Release the PHP session lock immediately.
// Without this, every concurrent AJAX from the same browser
// (e.g. send_message.php) blocks waiting for the lock — messages
// never insert until the SSE connection dies.
session_write_close();

// --- SSE headers -----------------------------------------------------------
@ini_set('zlib.output_compression', 0);
@ini_set('output_buffering', 'off');
if (ob_get_level()) ob_end_clean();

header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');
header('Connection: keep-alive');
header('X-Accel-Buffering: no'); // disable Nginx proxy buffering

// --- Cursor: validated strictly as a non-negative integer ------------------
// Priority: browser's Last-Event-ID header (set automatically on reconnect)
//           → ?last_id query param (set by JS on first connect)
//           → 0 (fallback: stream from beginning)
$raw_cursor = $_SERVER['HTTP_LAST_EVENT_ID'] ?? ($_GET['last_id'] ?? '0');
$last_id    = max(0, (int)$raw_cursor); // clamp negative / NaN to 0

// Sanity-check: if the cursor is impossibly large (e.g. 2^31) reset it.
// This prevents an attacker from passing INT_MAX to leak a gap or spin forever.
$last_id = min($last_id, PHP_INT_MAX);

// --- Prepared query (compiled once, executed in a loop) --------------------
$stmt = $pdo->prepare("
    SELECT message_id, sender_id, content, is_read, timestamp
    FROM   messages
    WHERE  message_id > :last_id
      AND  (
               (sender_id = :me   AND receiver_id = :other )
            OR (sender_id = :other2 AND receiver_id = :me2)
           )
    ORDER BY message_id ASC
");

// --- Stream configuration --------------------------------------------------
/** Maximum seconds before we close and let the browser reconnect cleanly.
 *  Prevents PHP worker exhaustion on shared hosts / Apache MaxClients. */
const MAX_STREAM_SECONDS = 55;

/** Emit a heartbeat SSE comment every N loop ticks (each tick ≈ 1 s). */
const HEARTBEAT_TICKS    = 15;

$started_at  = time();
$tick_count  = 0;

$stmt_read = $pdo->prepare("
    SELECT MAX(message_id) FROM messages
    WHERE sender_id = :me AND receiver_id = :other AND is_read = 1
");
$last_read_id_sent = 0;

// ---------------------------------------------------------------------------
// Streaming loop
// ---------------------------------------------------------------------------
while (true) {

    // Abort if client has disconnected (tab closed, navigated away)
    if (connection_aborted()) break;

    // Self-close: let the browser transparently reconnect with Last-Event-ID.
    // This reclaims the PHP worker before Apache/PHP-FPM hits its own limits.
    if ((time() - $started_at) >= MAX_STREAM_SECONDS) {
        // Send a named "reconnect" event so the JS can log it (optional)
        echo "event: reconnect\ndata: {}\n\n";
        ob_flush(); flush();
        break;
    }

    // Execute incremental query
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

        $payload = json_encode([
            'id'        => $last_id,
            'content'   => escape($row['content']),   // XSS-safe
            'is_mine'   => ($row['sender_id'] == $my_id),
            'is_read'   => (bool)$row['is_read'],
            'timestamp' => date('H:i', strtotime($row['timestamp'])),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        // SSE frame: id line allows browser to restore cursor on reconnect
        echo "id: {$last_id}\n";
        echo "data: {$payload}\n\n";

        ob_flush();
        flush();
    }

    // Check for read receipts updates
    $stmt_read->execute([':me' => $my_id, ':other' => $other_id]);
    $current_read_id = (int)$stmt_read->fetchColumn();
    if ($current_read_id > $last_read_id_sent) {
        $last_read_id_sent = $current_read_id;
        $read_payload = json_encode(['last_read_id' => $last_read_id_sent]);
        echo "event: read_receipt\ndata: {$read_payload}\n\n";
        ob_flush(); flush();
    }

    // Periodic heartbeat — a SSE comment (:) keeps the connection alive
    // through proxies/load-balancers that terminate idle connections.
    $tick_count++;
    if ($tick_count % HEARTBEAT_TICKS === 0) {
        echo ": heartbeat\n\n";
        ob_flush();
        flush();
    }

    sleep(1);
}
?>
