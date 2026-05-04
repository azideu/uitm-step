<?php
// register.php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (isset($_SESSION['user_id'])) {
    redirect('home');
}

$google_enabled = GOOGLE_CLIENT_ID !== '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $student_id = '';
    
    // Extract Student ID from Email (10 digits before @)
    if (strpos($email, '@') !== false) {
        $parts = explode('@', $email);
        $prefix = $parts[0];
        // Match first 10 digits
        if (preg_match('/^\d{10}/', $prefix, $matches)) {
            $student_id = $matches[0];
        }
    }

    $name = trim($_POST['name'] ?? '');
    $password = $_POST['password'] ?? '';
    $campus = trim($_POST['campus'] ?? '');

    // Validation
    $errors = [];
    if (!is_uitm_student_email($email)) {
        $allowed_domains = implode(', ', get_uitm_student_email_domains());
        $errors[] = "Email must be a UiTM student email address ({$allowed_domains}).";
    }
    if (empty($student_id)) {
        $errors[] = "Could not extract a valid 10-digit Student ID from your email.";
    }
    if (strlen($password) < 6) {
        $errors[] = "Password must be at least 6 characters.";
    }

    if (empty($errors)) {
        // Check if email or student ID exists
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ? OR student_id = ?");
        $stmt->execute([$email, $student_id]);
        if ($stmt->fetch()) {
            $errors[] = "Email or Student ID already exists.";
        } else {
            $hashed_pw = password_hash($password, PASSWORD_DEFAULT);
            $otp = (string)rand(100000, 999999);
            $stmt = $pdo->prepare("INSERT INTO users (student_id, name, email, password, campus, role, is_verified, otp_code) VALUES (?, ?, ?, ?, ?, 'student', 0, ?)");
            try {
                $stmt->execute([$student_id, $name, $email, $hashed_pw, $campus, $otp]);
                
                $_SESSION['verify_email'] = $email;

                // Always show OTP in toast for testing purposes (even on production)
                set_toast('info', "DEBUG MODE: Your verification code is <b>$otp</b> (This would normally be sent to your email).");
                
                redirect('verify_email');
            } catch (\Exception $e) {
                set_toast('error', "Registration failed.");
                error_log($e->getMessage());
            }
        }
    }

    if (!empty($errors)) {
        set_toast('error', implode("<br>", $errors));
    }
}

require_once 'includes/header.php';
?>

<div class="max-w-md mx-auto bg-white dark:bg-slate-900 p-8 rounded-2xl shadow-xl border border-gray-100 dark:border-slate-800 transition-colors duration-300 mt-10">
    <h2 class="text-2xl font-bold mb-6 text-center text-uitmPurple dark:text-purple-300 font-serif">Register for UiTM STEP</h2>
    <form action="register" method="POST">
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-slate-300 font-bold mb-2">Student Email</label>
            <input type="email" name="email" id="email_input" required pattern=".*@student\.uitm\.edu\.my" title="Must be a @student.uitm.edu.my email" placeholder="xxxxxxxxxx@student.uitm.edu.my" class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-slate-300 font-bold mb-2">Student ID (Auto-extracted)</label>
            <input type="text" id="student_id_display" readonly placeholder="Enter email first..." class="w-full px-4 py-3 bg-gray-50 dark:bg-slate-800/50 text-gray-500 dark:text-slate-400 border border-gray-200 dark:border-slate-700 rounded-lg cursor-not-allowed focus:outline-none">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-slate-300 font-bold mb-2">Full Name</label>
            <input type="text" name="name" required class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 dark:text-slate-300 font-bold mb-2">Password</label>
            <input type="password" name="password" required minlength="6" class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all">
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 dark:text-slate-300 font-bold mb-2">Campus</label>
            <select name="campus" required class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all">
                <option value="" disabled selected>Select your campus</option>
                <optgroup label="Selangor">
                    <option value="UiTM Shah Alam">Shah Alam</option>
                    <option value="UiTM Kampus Puncak Alam">Puncak Alam</option>
                    <option value="UiTM Kampus Puncak Perdana">Puncak Perdana</option>
                    <option value="UiTM Kampus Hospital Sg Buloh">Hospital Sg Buloh</option>
                    <option value="UiTM Kampus Dengkil">Dengkil</option>
                </optgroup>
                <optgroup label="Perlis">
                    <option value="UiTM Kampus Arau">Arau</option>
                </optgroup>
                <optgroup label="Kedah">
                    <option value="UiTM Kampus Sungai Petani">Sungai Petani</option>
                </optgroup>
                <optgroup label="Pulau Pinang">
                    <option value="UiTM Kampus Permatang Pauh">Permatang Pauh</option>
                    <option value="UiTM Kampus Bertam">Bertam</option>
                </optgroup>
                <optgroup label="Perak">
                    <option value="UiTM Kampus Seri Iskandar">Seri Iskandar</option>
                    <option value="UiTM Kampus Tapah">Tapah</option>
                </optgroup>
                <optgroup label="Negeri Sembilan">
                    <option value="UiTM Kampus Kuala Pilah Beting">Kuala Pilah Beting</option>
                    <option value="UiTM Kampus Seremban 3">Seremban 3</option>
                    <option value="UiTM Kampus Rembau">Rembau</option>
                </optgroup>
                <optgroup label="Melaka">
                    <option value="UiTM Kampus Alor Gajah">Alor Gajah</option>
                    <option value="UiTM Kampus Bandaraya Melaka">Bandaraya Melaka</option>
                    <option value="UiTM Kampus Jasin">Jasin</option>
                </optgroup>
                <optgroup label="Johor">
                    <option value="UiTM Kampus Segamat">Segamat</option>
                    <option value="UiTM Kampus Pasir Gudang">Pasir Gudang</option>
                </optgroup>
                <optgroup label="Pahang">
                    <option value="UiTM Kampus Jengka">Jengka</option>
                    <option value="UiTM Kampus Raub">Raub</option>
                </optgroup>
                <optgroup label="Terengganu">
                    <option value="UiTM Kampus Dungun">Dungun</option>
                    <option value="UiTM Kampus Kuala Terengganu Cendering">Kuala Terengganu Cendering</option>
                    <option value="UiTM Kampus Bukit Besi">Bukit Besi</option>
                </optgroup>
                <optgroup label="Kelantan">
                    <option value="UiTM Kampus Machang">Machang</option>
                    <option value="UiTM Kampus Kota Bharu">Kota Bharu</option>
                </optgroup>
                <optgroup label="Sabah">
                    <option value="UiTM Kampus Kota Kinabalu">Kota Kinabalu</option>
                    <option value="UiTM Kampus Tawau">Tawau</option>
                </optgroup>
                <optgroup label="Sarawak">
                    <option value="UiTM Kampus Samarahan">Samarahan</option>
                    <option value="UiTM Kampus Samarahan 2">Samarahan 2</option>
                    <option value="UiTM Kampus Mukah">Mukah</option>
                </optgroup>
            </select>
        </div>
        <button type="submit" class="w-full bg-uitmPurple text-white font-bold py-3 px-4 rounded-md shadow-xl hover:bg-purple-900 transition-all">Register</button>
    </form>

    <?php if ($google_enabled): ?>
    <div class="my-5 flex items-center gap-3">
        <span class="h-px bg-gray-300 dark:bg-slate-700 flex-1"></span>
        <span class="text-sm text-gray-500 dark:text-slate-400">or</span>
        <span class="h-px bg-gray-300 dark:bg-slate-700 flex-1"></span>
    </div>

    <p class="mb-2 text-sm text-gray-600 dark:text-slate-400">Continue with Google to sign up quickly.</p>
    <div id="google-auth-error" class="hidden mb-3 rounded border border-red-200 bg-red-50 text-red-700 px-3 py-2 text-sm"></div>

    <div id="g_id_onload"
         data-client_id="<?php echo escape(GOOGLE_CLIENT_ID); ?>"
         data-callback="handleGoogleAuthRegister"
         data-auto_prompt="false">
    </div>
        <div class="flex justify-center">
           <div class="g_id_signin"
               data-type="standard"
               data-shape="rectangular"
               data-theme="outline"
               data-text="signup_with"
               data-size="large"
               data-logo_alignment="left"
               data-width="320">
           </div>
    </div>
    <?php endif; ?>

    <p class="mt-4 text-center text-gray-600 dark:text-slate-400">Already have an account? <a href="login" class="text-uitmPurple dark:text-purple-400 font-bold hover:underline">Login here</a></p>
</div>

<?php if ($google_enabled): ?>
<script src="https://accounts.google.com/gsi/client" async defer></script>
<?php endif; ?>

<script>
document.getElementById('email_input').addEventListener('input', function(e) {
    const email = e.target.value;
    const studentIdDisplay = document.getElementById('student_id_display');
    
    // Extract first 10 digits before @
    const match = email.split('@')[0].match(/^\d{10}/);
    if (match) {
        studentIdDisplay.value = match[0];
    } else {
        studentIdDisplay.value = '';
    }
});

<?php if ($google_enabled): ?>
function handleGoogleAuthRegister(response) {
    const campus = '';

    fetch('api/google_auth', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            id_token: response.credential,
            mode: 'register',
            campus: campus
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
            : 'Google sign up failed. Server returned an invalid response.';
        throw new Error(message);
    })
    .catch(function (err) {
        if (!errorBox) {
            alert(err.message || 'Google sign up failed');
            return;
        }
        errorBox.textContent = err.message || 'Google sign up failed';
        errorBox.classList.remove('hidden');
    });
}
<?php endif; ?>
</script>

<?php require_once 'includes/footer.php'; ?>
