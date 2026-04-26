<?php
// api/google_auth.php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

if (GOOGLE_CLIENT_ID === '') {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Google sign-in is not configured']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$idToken = trim((string)($input['id_token'] ?? ''));
$mode = trim((string)($input['mode'] ?? 'login')); // login | register
$campus = trim((string)($input['campus'] ?? ''));

if ($idToken === '' || ($mode !== 'login' && $mode !== 'register')) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid request']);
    exit;
}

$payload = verify_google_id_token($idToken, GOOGLE_CLIENT_ID);
if (!$payload) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Google token verification failed']);
    exit;
}

$email = strtolower(trim((string)($payload['email'] ?? '')));
$name = trim((string)($payload['name'] ?? 'UiTM Student'));
$name = substr($name, 0, 100);

if (!is_uitm_student_email($email)) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Only UiTM student emails are allowed']);
    exit;
}

function generate_student_id_from_email(PDO $pdo, string $email): string {
    $localPart = strstr($email, '@', true);
    $base = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string)$localPart));
    if ($base === '') {
        $base = 'UITMSTUDENT';
    }
    $base = substr($base, 0, 20);

    $candidate = $base;
    $counter = 1;

    while (true) {
        $stmt = $pdo->prepare('SELECT user_id FROM users WHERE student_id = ? LIMIT 1');
        $stmt->execute([$candidate]);
        if (!$stmt->fetch()) {
            return $candidate;
        }

        $suffix = (string)$counter;
        $prefixLen = max(1, 20 - strlen($suffix));
        $candidate = substr($base, 0, $prefixLen) . $suffix;
        $counter++;

        if ($counter > 9999) {
            return 'UITM' . strtoupper(substr(bin2hex(random_bytes(8)), 0, 16));
        }
    }
}

/**
 * Normalize display names from providers
 */
function normalize_display_name(string $name): string {
    $name = trim($name);
    if ($name === '') {
        return 'UiTM Student';
    }

    $parts = preg_split('/(\s+|-|\')/', strtolower($name), -1, PREG_SPLIT_DELIM_CAPTURE);
    if (!is_array($parts)) {
        return $name;
    }

    foreach ($parts as $i => $part) {
        if ($part === '' || preg_match('/^(\s+|-|\')$/', $part)) {
            continue;
        }
        $parts[$i] = strtoupper(substr($part, 0, 1)) . substr($part, 1);
    }

    return implode('', $parts);
}

try {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($mode === 'login') {
        if (!$user) {
            echo json_encode([
                'success' => false,
                'error' => 'No account found for this email. Please sign up first.'
            ]);
            exit;
        }
    } else {
        if (!$user) {
            if ($campus === '') {
                http_response_code(400);
                echo json_encode(['success' => false, 'error' => 'Campus is required for registration']);
                exit;
            }

            $studentId = generate_student_id_from_email($pdo, $email);
            $name = normalize_display_name($name);
            $randomPasswordHash = password_hash(bin2hex(random_bytes(32)), PASSWORD_DEFAULT);

            $insert = $pdo->prepare('INSERT INTO users (student_id, name, email, password, campus, role) VALUES (?, ?, ?, ?, ?, ?)');
            $insert->execute([$studentId, $name, $email, $randomPasswordHash, $campus, 'student']);

            $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
            $stmt->execute([$email]);
            $user = $stmt->fetch();
        }
    }

    if (!$user) {
        http_response_code(500);
        echo json_encode(['success' => false, 'error' => 'Unable to authenticate user']);
        exit;
    }

    // Session fixation mitigation
    session_regenerate_id(true);

    $_SESSION['user_id'] = $user['user_id'];
    $_SESSION['role'] = $user['role'];
    $_SESSION['name'] = $user['name'];
    $_SESSION['campus'] = $user['campus'];
    $_SESSION['student_id'] = $user['student_id'];

    if ($user['role'] === 'student') {
        $_SESSION['mode'] = 'buying';
    }

    echo json_encode([
        'success' => true,
        'redirect' => 'index.php',
        'name' => $user['name']
    ]);
} catch (Exception $e) {
    error_log('Google Auth Error: ' . $e->getMessage());

    if ($e instanceof PDOException && isset($e->errorInfo[0]) && $e->errorInfo[0] === '23000') {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'error' => 'An account with this email already exists. Try Login with Google instead.'
        ]);
        exit;
    }

    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Authentication failed']);
}
