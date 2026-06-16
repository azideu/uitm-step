<?php
// complete-registration.php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login');
}

// If campus is already set, no need to be here
if (!empty($_SESSION['campus'])) {
    redirect('home');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'])) {
        set_toast('error', 'Invalid security token.');
        redirect('complete-registration');
    }
    $campus = trim($_POST['campus'] ?? '');
    
    if (!empty($campus)) {
        $stmt = $pdo->prepare("UPDATE users SET campus = ? WHERE user_id = ?");
        try {
            $stmt->execute([$campus, $_SESSION['user_id']]);
            $_SESSION['campus'] = $campus;
            set_toast('success', "Welcome! Your campus has been set.");
            redirect('home');
        } catch (\Exception $e) {
            set_toast('error', "Failed to update campus.");
            error_log($e->getMessage());
        }
    } else {
        set_toast('error', "Please select a campus.");
    }
}

require_once 'includes/header.php';
?>

<div class="max-w-md mx-auto bg-white dark:bg-slate-900 p-8 rounded-2xl shadow-xl border border-gray-100 dark:border-slate-800 transition-colors duration-300 mt-10 animate-fade-in-up">
    <h2 class="text-2xl font-bold mb-2 text-center text-uitmPurple dark:text-purple-300 font-serif">One Last Step!</h2>
    <p class="text-center text-gray-600 dark:text-slate-400 mb-8">Please select your campus to complete your profile.</p>

    <form action="complete-registration" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo escape($_SESSION['csrf_token'] ?? ''); ?>">

        <div class="mb-6">
            <label class="block text-gray-700 dark:text-slate-300 font-bold mb-2">Your Campus</label>
            <select name="campus" required class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all appearance-none">
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
        <button type="submit" class="w-full bg-uitmPurple text-white font-bold py-3 px-4 rounded-md shadow-xl hover:bg-purple-900 transition-all">Complete Registration</button>
    </form>
</div>

<?php require_once 'includes/footer.php'; ?>
