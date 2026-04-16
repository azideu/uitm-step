<?php
// register.php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_id = trim($_POST['student_id'] ?? '');
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $campus = trim($_POST['campus'] ?? '');

    // Validation
    $errors = [];
    if (!preg_match('/^.+@student\.uitm\.edu\.my$/', $email)) {
        $errors[] = "Email must be a @student.uitm.edu.my address.";
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
            <input type="email" name="email" required pattern=".*@student\.uitm\.edu\.my" title="Must be a @student.uitm.edu.my email" placeholder="2021xxxxxx@student.uitm.edu.my" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-uitmPurple">
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
                    <option value="UiTM Shah Alam">UiTM Shah Alam</option>
                    <option value="UiTM Kampus Puncak Alam">UiTM Kampus Puncak Alam</option>
                    <option value="UiTM Kampus Puncak Perdana">UiTM Kampus Puncak Perdana</option>
                    <option value="UiTM Kampus Hospital Sg Buloh">UiTM Kampus Hospital Sg Buloh</option>
                    <option value="UiTM Kampus Dengkil">UiTM Kampus Dengkil</option>
                </optgroup>
                <optgroup label="Perlis">
                    <option value="UiTM Kampus Arau">UiTM Kampus Arau</option>
                </optgroup>
                <optgroup label="Kedah">
                    <option value="UiTM Kampus Sungai Petani">UiTM Kampus Sungai Petani</option>
                </optgroup>
                <optgroup label="Pulau Pinang">
                    <option value="UiTM Kampus Permatang Pauh">UiTM Kampus Permatang Pauh</option>
                    <option value="UiTM Kampus Bertam">UiTM Kampus Bertam</option>
                </optgroup>
                <optgroup label="Perak">
                    <option value="UiTM Kampus Seri Iskandar">UiTM Kampus Seri Iskandar</option>
                    <option value="UiTM Kampus Tapah">UiTM Kampus Tapah</option>
                </optgroup>
                <optgroup label="Negeri Sembilan">
                    <option value="UiTM Kampus Kuala Pilah Beting">UiTM Kampus Kuala Pilah Beting</option>
                    <option value="UiTM Kampus Seremban 3">UiTM Kampus Seremban 3</option>
                    <option value="UiTM Kampus Rembau">UiTM Kampus Rembau</option>
                </optgroup>
                <optgroup label="Melaka">
                    <option value="UiTM Kampus Alor Gajah">UiTM Kampus Alor Gajah</option>
                    <option value="UiTM Kampus Bandaraya Melaka">UiTM Kampus Bandaraya Melaka</option>
                    <option value="UiTM Kampus Jasin">UiTM Kampus Jasin</option>
                </optgroup>
                <optgroup label="Johor">
                    <option value="UiTM Kampus Segamat">UiTM Kampus Segamat</option>
                    <option value="UiTM Kampus Pasir Gudang">UiTM Kampus Pasir Gudang</option>
                </optgroup>
                <optgroup label="Pahang">
                    <option value="UiTM Kampus Jengka">UiTM Kampus Jengka</option>
                    <option value="UiTM Kampus Raub">UiTM Kampus Raub</option>
                </optgroup>
                <optgroup label="Terengganu">
                    <option value="UiTM Kampus Dungun">UiTM Kampus Dungun</option>
                    <option value="UiTM Kampus Kuala Terengganu Cendering">UiTM Kampus Kuala Terengganu Cendering</option>
                    <option value="UiTM Kampus Bukit Besi">UiTM Kampus Bukit Besi</option>
                </optgroup>
                <optgroup label="Kelantan">
                    <option value="UiTM Kampus Machang">UiTM Kampus Machang</option>
                    <option value="UiTM Kampus Kota Bharu">UiTM Kampus Kota Bharu</option>
                </optgroup>
                <optgroup label="Sabah">
                    <option value="UiTM Kampus Kota Kinabalu">UiTM Kampus Kota Kinabalu</option>
                    <option value="UiTM Kampus Tawau">UiTM Kampus Tawau</option>
                </optgroup>
                <optgroup label="Sarawak">
                    <option value="UiTM Kampus Samarahan">UiTM Kampus Samarahan</option>
                    <option value="UiTM Kampus Samarahan 2">UiTM Kampus Samarahan 2</option>
                    <option value="UiTM Kampus Mukah">UiTM Kampus Mukah</option>
                </optgroup>
            </select>
        </div>
        <button type="submit" class="w-full bg-uitmPurple text-white font-bold py-2 px-4 rounded hover:bg-purple-900 transition-colors">Register</button>
    </form>
    <p class="mt-4 text-center">Already have an account? <a href="login.php" class="text-uitmPurple hover:underline">Login here</a></p>
</div>

<?php require_once 'includes/footer.php'; ?>
