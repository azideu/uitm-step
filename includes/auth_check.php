<?php
// includes/auth_check.php

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    set_toast('error', 'Please log in to access this page.');
    redirect('login');
}

// Verify user still exists in DB and is not banned
$stmt = $pdo->prepare("SELECT user_id, role FROM users WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

if (!$user) {
    // Session is stale, user no longer exists
    session_destroy();
    set_toast('error', 'Your session has expired. Please log in again.');
    redirect('login');
}

// Sync session role with database in case of updates (like banning)
$_SESSION['role'] = $user['role'];

if ($user['role'] === 'banned') {
    // User was banned mid-session
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page !== 'banned.php' && $current_page !== 'logout.php') {
        set_toast('error', 'Your account has been suspended.');
        redirect('banned');
    }
}


// Function to enforce admin only routes
function require_admin() {
    if ($_SESSION['role'] !== 'admin') {
        set_toast('error', 'Unauthorized access.');
        redirect('home');
    }
}
?>
