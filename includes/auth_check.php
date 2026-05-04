<?php
// includes/auth_check.php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    set_toast('error', 'Please log in to access this page.');
    redirect('login');
}

// Verify user still exists in DB (prevents issues after database re-seeds)
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
if (!$stmt->fetch()) {
    // Session is stale, user no longer exists
    session_destroy();
    set_toast('error', 'Your session has expired. Please log in again.');
    redirect('login');
}

// Function to enforce admin only routes
function require_admin() {
    if ($_SESSION['role'] !== 'admin') {
        set_toast('error', 'Unauthorized access.');
        redirect('home');
    }
}
?>
