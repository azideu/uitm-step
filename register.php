<?php
// register.php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

$google_enabled = GOOGLE_CLIENT_ID !== '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $campus = trim($_POST['campus'] ?? '');

    // Validation
    $errors = [];
    if (!is_uitm_student_email($email)) {
        $allowed_domains = implode(', ', get_uitm_student_email_domains());
        $errors[] = "Email must be a UiTM student email address ({$allowed_domains}).";
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
            $stmt = $pdo->prepare("INSERT INTO users (student_id, name, email, password, campus, role) VALUES (?, ?, ?, ?, ?, 'student')");
            try {
                $stmt->execute([$student_id, $name, $email, $hashed_pw, $campus]);
                set_toast('success', "Registration successful. Please login.");
                redirect('login.php');
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

<div class="max-w-md mx-auto bg-white p-8 rounded shadow-lg mt-10">
    <h2 class="text-2xl font-bold mb-6 text-center text-uitmPurple">Register for UiTM STEP</h2>
    <form action="register.php" method="POST">
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Student ID</label>
            <input type="text" name="student_id" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-uitmPurple">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Full Name</label>
            <input type="text" name="name" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-uitmPurple">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Student Email</label>
            <input type="email" name="email" required pattern=".*@student\.uitm\.edu\.my" title="Must be a @student.uitm.edu.my email" placeholder="xxxxxxxxxx@student.uitm.edu.my" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-uitmPurple">
        </div>
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2">Password</label>
            <input type="password" name="password" required minlength="6" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-uitmPurple">
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 font-bold mb-2">Campus</label>
            <select name="campus" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-uitmPurple">
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
        <button type="submit" class="w-full bg-uitmPurple text-white font-bold py-2 px-4 rounded hover:bg-purple-900 transition-colors">Register</button>
    </form>

    <?php if ($google_enabled): ?>
    <div class="my-5 flex items-center gap-3">
        <span class="h-px bg-gray-300 flex-1"></span>
        <span class="text-sm text-gray-500">or</span>
        <span class="h-px bg-gray-300 flex-1"></span>
    </div>

    <p class="mb-2 text-sm text-gray-600">Select your campus above, then continue with Google.</p>
    <div id="google-auth-error" class="hidden mb-3 rounded border border-red-200 bg-red-50 text-red-700 px-3 py-2 text-sm"></div>

    <div id="g_id_onload"
         data-client_id="<?php echo escape(GOOGLE_CLIENT_ID); ?>"
         data-callback="handleGoogleAuthRegister"
         data-auto_prompt="false">
    </div>
        <div class="flex justify-center">
           <div class="g_id_signin"
               data-type="standard"
               data-shape="pill"
               data-theme="outline"
               data-text="signup_with"
               data-size="large"
               data-logo_alignment="left"
               data-width="320">
           </div>
    </div>
    <?php endif; ?>

    <p class="mt-4 text-center">Already have an account? <a href="login.php" class="text-uitmPurple hover:underline">Login here</a></p>
</div>

<?php if ($google_enabled): ?>
<script src="https://accounts.google.com/gsi/client" async defer></script>
<script>
function handleGoogleAuthRegister(response) {
    const campusField = document.querySelector('select[name="campus"]');
    const campus = (campusField && campusField.value) ? campusField.value : '';
    const errorBox = document.getElementById('google-auth-error');

    if (errorBox) {
        errorBox.classList.add('hidden');
        errorBox.textContent = '';
    }

    if (!campus) {
        if (errorBox) {
            errorBox.textContent = 'Please select your campus before Google sign up.';
            errorBox.classList.remove('hidden');
        } else {
            alert('Please select your campus before Google sign up.');
        }
        return;
    }

    fetch('api/google_auth.php', {
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
            window.location.href = result.data.redirect || 'index.php';
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
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
