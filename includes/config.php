<?php
// includes/config.php
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
if (strpos($base_dir, '/admin') !== false || strpos($base_dir, '/api') !== false || strpos($base_dir, '/gigs') !== false) {
    $base_dir = dirname($base_dir);
}
define('ROOT_URL', rtrim($base_dir, '/') . '/');

// Set default timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

// Error reporting (graceful in production ideally, but for MVP we log and display)
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error.log');

// 1. Load Database and Session Handler
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/session_db.php';

// 2. Start secure session if not already started
if (session_status() === PHP_SESSION_NONE) {
    // Register Database Session Handler
    $handler = new DatabaseSessionHandler($pdo);
    session_set_save_handler($handler, true);

    // Detect if we are on HTTPS
    $is_secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') 
                 || ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';

    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => '',
        'secure' => $is_secure,
        'httponly' => true,
        'samesite' => 'Lax'
    ]);
    
    session_start();
}

// Generate CSRF token if not exists
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Google Sign-In settings (set GOOGLE_CLIENT_ID in environment for production)
if (!defined('GOOGLE_CLIENT_ID')) {
    define('GOOGLE_CLIENT_ID', trim((string)(getenv('GOOGLE_CLIENT_ID') ?: '')));
}

// Comma-separated list of allowed student email domains for auth.
// Example: student.uitm.edu.my,siswa.uitm.edu.my
if (!defined('UITM_STUDENT_EMAIL_DOMAINS')) {
    define('UITM_STUDENT_EMAIL_DOMAINS', trim((string)(getenv('UITM_STUDENT_EMAIL_DOMAINS') ?: 'student.uitm.edu.my')));
}
?>
