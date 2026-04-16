<?php
// includes/auth_check.php

require_once 'config.php';
require_once 'functions.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    set_toast('error', 'Please log in to access this page.');
    redirect('login.php');
}

// Function to enforce admin only routes
function require_admin() {
    if ($_SESSION['role'] !== 'admin') {
        set_toast('error', 'Unauthorized access.');
        redirect('index.php');
    }
}
?>
