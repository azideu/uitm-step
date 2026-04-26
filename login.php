<?php
// login.php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (isset($_SESSION['user_id'])) {
    redirect('index.php');
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
            redirect('index.php');
        } else {
            set_toast('error', 'Invalid email or password.');
        }
    } else {
        set_toast('error', 'Please complete all fields.');
    }
}

require_once 'includes/header.php';
?>

<div class="max-w-md mx-auto bg-white p-8 rounded shadow-lg mt-10">
    <h2 class="text-2xl font-bold mb-6 text-center text-uitmPurple">Login to UiTM STEP</h2>
    <form action="login.php" method="POST">
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2" for="email">Student Email</label>
            <input type="email" name="email" id="email" required pattern=".*@student\.uitm\.edu\.my" title="Must be a @student.uitm.edu.my email" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-uitmPurple">
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 font-bold mb-2" for="password">Password</label>
            <input type="password" name="password" id="password" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-uitmPurple">
        </div>
        <button type="submit" class="w-full bg-uitmPurple text-white font-bold py-2 px-4 rounded hover:bg-purple-900 transition-colors">Login</button>
    </form>

    <?php if ($google_enabled): ?>
    <div class="my-5 flex items-center gap-3">
        <span class="h-px bg-gray-300 flex-1"></span>
        <span class="text-sm text-gray-500">or</span>
        <span class="h-px bg-gray-300 flex-1"></span>
    </div>

    <div id="google-auth-error" class="hidden mb-3 rounded border border-red-200 bg-red-50 text-red-700 px-3 py-2 text-sm"></div>

    <div id="g_id_onload"
         data-client_id="<?php echo escape(GOOGLE_CLIENT_ID); ?>"
         data-callback="handleGoogleAuthLogin"
         data-auto_prompt="false">
    </div>
    <div class="g_id_signin"
         data-type="standard"
         data-shape="pill"
         data-theme="outline"
         data-text="continue_with"
         data-size="large"
         data-logo_alignment="left"
         data-width="320">
    </div>
    <?php endif; ?>

    <p class="mt-4 text-center">Don't have an account? <a href="register.php" class="text-uitmPurple hover:underline">Register here</a></p>
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

    fetch('api/google_auth.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id_token: response.credential,
            mode: 'login'
        })
    })
    .then(function (res) {
        return res.json().then(function (data) {
            return { ok: res.ok, data: data };
        });
    })
    .then(function (result) {
        if (result.ok && result.data.success) {
            window.location.href = result.data.redirect || 'index.php';
            return;
        }
        throw new Error(result.data.error || 'Google login failed');
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
