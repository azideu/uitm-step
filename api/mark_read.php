<?php
// api/mark_read.php
require_once '../includes/config.php';
require_once '../includes/db.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'error' => 'Unauthorized']);
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

$input = json_decode(file_get_contents('php://input'), true);
$other_user = isset($input['user']) ? (int)$input['user'] : 0;
$me = (int)$_SESSION['user_id'];

if ($other_user <= 0 || $other_user === $me) {
    echo json_encode(['success' => false, 'error' => 'Invalid user']);
    exit;
}

try {
    $stmt = $pdo->prepare("
        UPDATE messages 
        SET is_read = 1, status = 'delivered'
        WHERE receiver_id = :me AND sender_id = :other AND (is_read = 0 OR is_read IS NULL)
    ");
    $stmt->execute([
        ':me' => $me,
        ':other' => $other_user
    ]);
    
    echo json_encode(['success' => true, 'updated' => $stmt->rowCount()]);
} catch (Exception $e) {
    error_log("Mark Read Error: " . $e->getMessage());
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
