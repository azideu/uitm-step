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
 * Resolves a path to a full URL using ROOT_URL if relative.
 */
function asset_url($path) {
    if (empty($path)) return '';
    if (str_starts_with($path, 'http') || str_starts_with($path, 'data:') || str_starts_with($path, '/')) {
        return $path;
    }
    return ROOT_URL . ltrim($path, '/');
}

/**
 * Redirect and exit
 */
function redirect($url) {
    header("Location: " . asset_url($url));
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
        $msg = $toast['message'];
        
        $bgColor = 'bg-sky-600/95 dark:bg-sky-900/95 border-sky-500/30';
        $iconSvg = '<svg class="w-5 h-5 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        
        if ($type === 'success') {
            $bgColor = 'bg-emerald-600/95 dark:bg-emerald-900/95 border-emerald-500/30';
            $iconSvg = '<svg class="w-5 h-5 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>';
        } elseif ($type === 'error') {
            $bgColor = 'bg-rose-600/95 dark:bg-rose-900/95 border-rose-500/30';
            $iconSvg = '<svg class="w-5 h-5 shrink-0 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 17c-.77 1.333.192 3 1.732 3z"></path></svg>';
        }
        
        echo "
        <div id='toast' class='fixed bottom-5 right-5 flex items-center gap-3 text-white px-5 py-3.5 rounded-2xl shadow-2xl border backdrop-blur-md $bgColor translate-x-[120%] transition-transform duration-500 ease-out z-[9999] max-w-sm sm:max-w-md'>
            $iconSvg
            <div class='text-sm font-semibold flex-1 leading-snug'>$msg</div>
            <button onclick='dismissToast()' class='text-white/70 hover:text-white hover:scale-110 active:scale-95 transition-all p-1 rounded-lg hover:bg-white/10 shrink-0 cursor-pointer border-0 bg-transparent'>
                <svg class='w-4 h-4' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12'></path></svg>
            </button>
        </div>
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                const toast = document.getElementById('toast');
                if (!toast) return;
                
                // Animate entrance
                requestAnimationFrame(() => {
                    toast.style.transform = 'translateX(0)';
                });
                
                let timeoutId;
                let startTime = Date.now();
                let remainingTime = 4000;
                
                function startTimer() {
                    startTime = Date.now();
                    timeoutId = setTimeout(dismissToast, remainingTime);
                }
                
                function pauseTimer() {
                    clearTimeout(timeoutId);
                    remainingTime -= Date.now() - startTime;
                }
                
                toast.addEventListener('mouseenter', pauseTimer);
                toast.addEventListener('mouseleave', () => {
                    if (remainingTime > 0) {
                        startTimer();
                    }
                });
                
                startTimer();
            });

            function dismissToast() {
                const toast = document.getElementById('toast');
                if (!toast) return;
                toast.style.transform = 'translateX(120%)';
                setTimeout(() => { toast.remove(); }, 500);
            }
        </script>
        ";
        
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
