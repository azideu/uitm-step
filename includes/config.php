<?php
// includes/config.php

// Set default timezone
date_default_timezone_set('Asia/Kuala_Lumpur');

// Error reporting (graceful in production ideally, but for MVP we log and display)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/../error.log');

// Start secure session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 86400,
        'path' => '/',
        'domain' => '',
        'secure' => false, // Set to true if using HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}
?>
