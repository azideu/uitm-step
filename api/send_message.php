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

// CSRF Validation
$csrf_token = '';
if (isset($_SERVER['HTTP_X_CSRF_TOKEN'])) {
    $csrf_token = $_SERVER['HTTP_X_CSRF_TOKEN'];
} elseif (isset($_SERVER['X_CSRF_TOKEN'])) {
    $csrf_token = $_SERVER['X_CSRF_TOKEN'];
} elseif (function_exists('getallheaders')) {
    $headers = getallheaders();
    foreach ($headers as $key => $val) {
        if (strcasecmp($key, 'X-CSRF-Token') === 0) {
            $csrf_token = $val;
            break;
        }
    }
}

if ($csrf_token === '' || !hash_equals($_SESSION['csrf_token'] ?? '', $csrf_token)) {
    echo json_encode(['success' => false, 'error' => 'Invalid security token']);
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
try {
    $stmt = $pdo->prepare("INSERT INTO messages (sender_id, receiver_id, content) VALUES (?, ?, ?)");
    $stmt->execute([$sender_id, $receiver_id, $content]);
    echo json_encode(['success' => true, 'message_id' => (int)$pdo->lastInsertId()]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Database error: ' . $e->getMessage()]);
}
?>
