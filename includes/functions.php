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
        
        echo "<div id='toast' class='fixed bottom-5 right-5 text-white px-6 py-3 rounded shadow-lg $color transition-opacity duration-300 z-[9999]'>$msg</div>";
        echo "<script>setTimeout(() => { document.getElementById('toast').style.opacity = '0'; setTimeout(()=> {document.getElementById('toast').remove();}, 300); }, 3000);</script>";
        
        unset($_SESSION['toast']);
    }
}

/**
 * Return allowed UiTM student email domains from config.
 * @return array<int, string>
 */
function get_uitm_student_email_domains() {
    $raw = defined('UITM_STUDENT_EMAIL_DOMAINS') ? UITM_STUDENT_EMAIL_DOMAINS : 'student.uitm.edu.my';
    $parts = array_filter(array_map('trim', explode(',', strtolower($raw))));
    return !empty($parts) ? array_values(array_unique($parts)) : ['student.uitm.edu.my'];
}

/**
 * Strictly validate UiTM student email by domain.
 */
function is_uitm_student_email($email) {
    $email = strtolower(trim((string)$email));
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    $atPos = strrpos($email, '@');
    if ($atPos === false) {
        return false;
    }
    
    $localPart = substr($email, 0, $atPos);
    $domain = substr($email, $atPos + 1);

    // Check domain
    if (!in_array($domain, get_uitm_student_email_domains(), true)) {
        return false;
    }

    // Strict 10-digit check
    if (!preg_match('/^\d{10}$/', $localPart)) {
        return false;
    }

    // DNS MX Check
    if (!checkdnsrr($domain, 'MX')) {
        return false;
    }

    return true;
}

/**
 * Verify Google ID token via Google's tokeninfo endpoint.
 * Returns decoded payload array on success, null on failure.
 *
 * @return array<string, mixed>|null
 */
function verify_google_id_token($idToken, $expectedAud) {
    $idToken = trim((string)$idToken);
    $expectedAud = trim((string)$expectedAud);
    if ($idToken === '' || $expectedAud === '') {
        return null;
    }

    $url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . rawurlencode($idToken);
    $response = false;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        $response = curl_exec($ch);
        $httpCode = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($response === false || $httpCode !== 200) {
            return null;
        }
    } else {
        $context = stream_context_create([
            'http' => ['timeout' => 10],
        ]);
        $response = @file_get_contents($url, false, $context);
        if ($response === false) {
            return null;
        }
    }

    $payload = json_decode($response, true);
    if (!is_array($payload)) {
        return null;
    }

    if (($payload['aud'] ?? '') !== $expectedAud) {
        return null;
    }

    if (($payload['email_verified'] ?? 'false') !== 'true') {
        return null;
    }

    if (!empty($payload['exp']) && ((int)$payload['exp'] < time())) {
        return null;
    }

    return $payload;
}
?>
