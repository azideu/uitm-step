<?php
// includes/functions.php

/**
 * XSS Protection wrapper function
 * Escapes HTML entities to prevent Cross-Site Scripting (XSS)
 */
function escape($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

/**
 * Redirect and exit
 */
function redirect($url) {
    header("Location: $url");
    exit;
}

/**
 * Set a toast notification message in session
 */
function set_toast($type, $message) {
    $_SESSION['toast'] = [
        'type' => $type, // 'success', 'error', 'info'
        'message' => $message
    ];
}

/**
 * Display toast notification if exists in session
 */
function display_toast() {
    if (isset($_SESSION['toast'])) {
        $toast = $_SESSION['toast'];
        $type = $toast['type'];
        $msg = escape($toast['message']);
        
        $color = 'bg-blue-500';
        if ($type === 'success') {
            $color = 'bg-green-500';
        } elseif ($type === 'error') {
            $color = 'bg-red-500';
        }
        
        echo "<div id='toast' class='fixed bottom-5 right-5 text-white px-6 py-3 rounded shadow-lg $color transition-opacity duration-300'>$msg</div>";
        echo "<script>setTimeout(() => { document.getElementById('toast').style.opacity = '0'; setTimeout(()=> {document.getElementById('toast').remove();}, 300); }, 3000);</script>";
        
        unset($_SESSION['toast']);
    }
}
?>
