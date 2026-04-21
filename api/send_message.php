<?php
// api/send_message.php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'error' => 'Invalid method']);
    exit;
}

// Release session lock to prevent blocking concurrent requests
$sender_id = $_SESSION['user_id'];
session_write_close();

// Get JSON input
$data = json_decode(file_get_contents('php://input'), true);

$receiver_id = (int)($data['receiver_id'] ?? 0);
$content = trim($data['content'] ?? '');

if ($receiver_id <= 0 || empty($content)) {
    echo json_encode(['success' => false, 'error' => 'Invalid data']);
    exit;
}

// Insert message
$stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
if ($stmt->execute([$sender_id, $receiver_id, $content])) {
    echo json_encode(['success' => true, 'message_id' => (int)$pdo->lastInsertId()]);
} else {
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
