<?php
// includes/config.php
$base_dir = dirname($_SERVER['SCRIPT_NAME']);
if (strpos($base_dir, '/admin') !== false || strpos($base_dir, '/api') !== false || strpos($base_dir, '/gigs') !== false || strpos($base_dir, '/guides') !== false) {
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

// Rate limiting for all POST requests (applies to all input fields/forms)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rate_limit = 10; // max requests
    $time_window = 10; // in seconds
    $current_time = time();

    $ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
    $ip_hash = md5($ip);
    $limit_dir = sys_get_temp_dir() . '/step_rate_limits';
    $file_path = $limit_dir . '/' . $ip_hash . '.json';
    $timestamps = [];
    $file_based_success = false;

    // Attempt file-based IP rate limiting
    try {
        if (!is_dir($limit_dir)) {
            @mkdir($limit_dir, 0700, true);
        }
        if (is_dir($limit_dir) && is_writable($limit_dir)) {
            if (file_exists($file_path)) {
                $content = @file_get_contents($file_path);
                $timestamps = json_decode($content, true) ?: [];
            }

            // Remove timestamps older than the time window
            $timestamps = array_filter($timestamps, function($timestamp) use ($current_time, $time_window) {
                return ($current_time - $timestamp) < $time_window;
            });

            if (count($timestamps) >= $rate_limit) {
                http_response_code(429);
                
                // Check if the request expects JSON (like API calls)
                $is_json = (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) || 
                           (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
                           
                if ($is_json) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => 'Too many requests. Please slow down.']);
                } else {
                    // For regular form submissions, display a styled message or just a simple die
                    die('<div style="font-family: sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; text-align: center;"><h2>Too Many Requests</h2><p>You are submitting forms too quickly. Please slow down and try again in a few seconds.</p><button onclick="window.history.back()" style="padding: 10px 20px; background: #330066; color: white; border: none; border-radius: 5px; cursor: pointer;">Go Back</button></div>');
                }
                exit;
            }

            $timestamps[] = $current_time;
            if (@file_put_contents($file_path, json_encode(array_values($timestamps))) !== false) {
                $file_based_success = true;
            }
        }
    } catch (\Exception $e) {
        // Fall back to session-based rate limiting below
        error_log("File-based rate limit error: " . $e->getMessage());
    }

    // Fallback: session-based rate limiting if file-based failed
    if (!$file_based_success) {
        if (!isset($_SESSION['post_timestamps'])) {
            $_SESSION['post_timestamps'] = [];
        }

        $_SESSION['post_timestamps'] = array_filter($_SESSION['post_timestamps'], function($timestamp) use ($current_time, $time_window) {
            return ($current_time - $timestamp) < $time_window;
        });

        if (count($_SESSION['post_timestamps']) >= $rate_limit) {
            http_response_code(429);
            
            // Check if the request expects JSON (like API calls)
            $is_json = (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false) || 
                       (isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
                       
            if ($is_json) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Too many requests. Please slow down.']);
            } else {
                // For regular form submissions, display a styled message or just a simple die
                die('<div style="font-family: sans-serif; max-width: 500px; margin: 50px auto; padding: 20px; border: 1px solid #ccc; border-radius: 8px; text-align: center;"><h2>Too Many Requests</h2><p>You are submitting forms too quickly. Please slow down and try again in a few seconds.</p><button onclick="window.history.back()" style="padding: 10px 20px; background: #330066; color: white; border: none; border-radius: 5px; cursor: pointer;">Go Back</button></div>');
            }
            exit;
        }

        $_SESSION['post_timestamps'][] = $current_time;
    }
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
