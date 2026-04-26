<?php
// api/fetch_messages.php — one-shot history loader (called once on page load)
// =========================================================================
// POLISH:
//   • session_write_close() immediately after reading user_id — concurrent
//     AJAX requests (send_message.php) are not blocked.
//   • ORDER BY message_id ASC (not timestamp) avoids ties from same-second
//     inserts causing wrong display order.
//   • Explicit column list instead of SELECT *.
// =========================================================================

require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Cache-Control: post-check=0, pre-check=0', false);
header('Pragma: no-cache');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id    = (int)$_SESSION['user_id'];
$other_user = isset($_GET['user']) ? (int)$_GET['user'] : 0;

// Release session lock ASAP — this endpoint is called in parallel with
// others and must not block the send_message.php POST.
session_write_close();

if ($other_user <= 0 || $other_user === $user_id) {
    echo json_encode(['error' => 'Invalid user']);
    exit;
}

$stmt = $pdo->prepare("
    SELECT message_id, sender_id, content, status, is_read, timestamp
    FROM   messages
    WHERE  (sender_id = :me  AND receiver_id = :other)
       OR  (sender_id = :other2 AND receiver_id = :me2)
    ORDER BY message_id ASC
");
$stmt->execute([
    ':me'     => $user_id,
    ':other'  => $other_user,
    ':other2' => $other_user,
    ':me2'    => $user_id,
]);
$messages = $stmt->fetchAll();

$escaped = array_map(function ($msg) use ($user_id) {
    return [
        'id'        => (int)$msg['message_id'],
        'content'   => escape($msg['content']),
        'is_mine'   => $msg['sender_id'] == $user_id,
        'status'    => $msg['status'],
        'is_read'   => (bool)$msg['is_read'],
        'timestamp' => date('H:i', strtotime($msg['timestamp'])),
    ];
}, $messages);

echo json_encode(['messages' => $escaped], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
