<?php
// api/fetch_messages.php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$user_id = $_SESSION['user_id'];
$other_user = isset($_GET['user']) ? (int)$_GET['user'] : 0;

if ($other_user <= 0) {
    echo json_encode(['error' => 'Invalid user']);
    exit;
}

// Fetch messages between current user and other_user
$stmt = $pdo->prepare("
    SELECT * FROM messages 
    WHERE (sender_id = ? AND receiver_id = ?) 
       OR (sender_id = ? AND receiver_id = ?) 
    ORDER BY timestamp ASC
");
$stmt->execute([$user_id, $other_user, $other_user, $user_id]);
$messages = $stmt->fetchAll();

// Escape content before sending to frontend since it will be injected into HTML
$escaped_msgs = array_map(function($msg) use ($user_id) {
    return [
        'id' => $msg['message_id'],
        'content' => escape($msg['content']),
        'is_mine' => $msg['sender_id'] == $user_id,
        'timestamp' => date('H:i', strtotime($msg['timestamp']))
    ];
}, $messages);

echo json_encode(['messages' => $escaped_msgs]);
?>
