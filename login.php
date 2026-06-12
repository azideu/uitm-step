<?php
// login.php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (isset($_SESSION['user_id'])) {
    $redirect = $_GET['redirect'] ?? 'home';
    redirect($redirect);
}

$google_enabled = GOOGLE_CLIENT_ID !== '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if (isset($user['is_verified']) && $user['is_verified'] == 0) {
                $otp = $user['otp_code'];
                if (empty($otp)) {
                    $otp = (string)rand(100000, 999999);
                    $update_otp = $pdo->prepare("UPDATE users SET otp_code = ? WHERE user_id = ?");
                    $update_otp->execute([$otp, $user['user_id']]);
                }
                $_SESSION['verify_email'] = $email;
                set_toast('info', "Please verify your email. DEBUG: Your code is <b>$otp</b>");
                redirect('verify_email');
            }

            // Check if user is banned
            if ($user['role'] === 'banned') {
                $_SESSION['user_id'] = $user['user_id']; // Set session so they can appeal
                $_SESSION['role'] = $user['role'];
                $_SESSION['name'] = $user['name'];
                redirect('banned');
            }

            // Mitigate Session Fixation
            session_regenerate_id(true);


            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['campus'] = $user['campus'];
            $_SESSION['student_id'] = $user['student_id'];
            
            // Set default mode for student
            if ($user['role'] === 'student') {
                $_SESSION['mode'] = 'buying';
            }

            set_toast('success', "Welcome back, " . escape($user['name']) . "!");
            $redirect = $_GET['redirect'] ?? 'home';
            redirect($redirect);
        } else {
            set_toast('error', 'Invalid email or password.');
        }
    } else {
        set_toast('error', 'Please complete all fields.');
    }
}

require_once 'includes/header.php';
?>

<div class="max-w-md mx-auto bg-white dark:bg-slate-900 p-8 rounded-2xl shadow-xl border border-gray-100 dark:border-slate-800 transition-colors duration-300 mt-10">
    <h2 class="text-2xl font-bold mb-6 text-center text-uitmPurple dark:text-purple-300 font-serif">Login to UiTM STEP</h2>
    <form action="login<?php echo isset($_GET['redirect']) ? '?redirect=' . urlencode($_GET['redirect']) : ''; ?>" method="POST">
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-slate-300 font-bold mb-2" for="email">Student Email</label>
            <input type="email" name="email" id="email" required pattern=".*@student\.uitm\.edu\.my" title="Must be a @student.uitm.edu.my email" class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all">
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 dark:text-slate-300 font-bold mb-2" for="password">Password</label>
            <input type="password" name="password" id="password" required class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all">
        </div>
        <button type="submit" class="w-full bg-uitmPurple text-white font-bold py-3 px-4 rounded-md shadow-xl hover:bg-purple-900 transition-all">Login</button>
    </form>

    <?php if ($google_enabled): ?>
    <div class="my-5 flex items-center gap-3">
        <span class="h-px bg-gray-300 dark:bg-slate-700 flex-1"></span>
        <span class="text-sm text-gray-500 dark:text-slate-400">or</span>
        <span class="h-px bg-gray-300 dark:bg-slate-700 flex-1"></span>
    </div>

    <div id="google-auth-error" class="hidden mb-3 rounded border border-red-200 bg-red-50 text-red-700 px-3 py-2 text-sm"></div>

    <div id="g_id_onload"
         data-client_id="<?php echo escape(GOOGLE_CLIENT_ID); ?>"
         data-callback="handleGoogleAuthLogin"
         data-auto_prompt="false">
    </div>
        <div class="flex justify-center">
           <div class="g_id_signin"
               data-type="standard"
               data-shape="rectangular"
               data-theme="outline"
               data-text="continue_with"
               data-size="large"
               data-logo_alignment="left"
               data-width="320">
           </div>
    </div>
    <?php endif; ?>

    <p class="mt-4 text-center text-gray-600 dark:text-slate-400">Don't have an account? <a href="register" class="text-uitmPurple dark:text-purple-400 font-bold hover:underline">Register here</a></p>
</div>

<?php if ($google_enabled): ?>
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
function handleGoogleAuthLogin(response) {
    const errorBox = document.getElementById('google-auth-error');
    if (errorBox) {
        errorBox.classList.add('hidden');
        errorBox.textContent = '';
    }

    fetch('api/google_auth', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id_token: response.credential,
            mode: 'login'
        })
    })
    .then(function (res) {
        return res.text().then(function (text) {
            var data = null;
            try {
                data = text ? JSON.parse(text) : null;
            } catch (e) {
                data = null;
            }
            return { ok: res.ok, data: data, raw: text };
        });
    })
    .then(function (result) {
        if (result.ok && result.data && result.data.success) {
            window.location.href = result.data.redirect || 'home';
            return;
        }
        var message = (result.data && result.data.error)
            ? result.data.error
            : 'Google login failed. Server returned an invalid response.';
        throw new Error(message);
    })
    .catch(function (err) {
        if (!errorBox) {
            alert(err.message || 'Google login failed');
            return;
        }
        errorBox.textContent = err.message || 'Google login failed';
        errorBox.classList.remove('hidden');
    });
}
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
