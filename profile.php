<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login.php');
}

$user_id = $_SESSION['user_id'];

// Handle Form Submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        set_toast('error', 'Invalid security token.');
        redirect('profile.php');
    }

    if (isset($_POST['action']) && $_POST['action'] === 'update_profile') {
        $name = trim($_POST['name'] ?? '');
        $bio = trim($_POST['bio'] ?? '');
        $campus = trim($_POST['campus'] ?? '');
        
        $errors = [];
        
        if (empty($name) || empty($campus)) {
            $errors[] = "Name and Campus are required.";
        }

        // Handle Avatar Upload
        $profile_picture = null;
        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $file_tmp = $_FILES['avatar']['tmp_name'];
            $file_type = mime_content_type($file_tmp);
            
            if (in_array($file_type, $allowed_types)) {
                $ext_map = [
                    'image/jpeg' => 'jpg',
                    'image/png'  => 'png',
                    'image/gif'  => 'gif',
                    'image/webp' => 'webp'
                ];
                $ext = $ext_map[$file_type];
                $file_name = 'avatar_' . $user_id . '_' . time() . '.' . $ext;
                $upload_dir = __DIR__ . '/assets/uploads/avatars';
                $upload_path = $upload_dir . '/' . $file_name;
                
                if (!is_dir($upload_dir) && !mkdir($upload_dir, 0755, true) && !is_dir($upload_dir)) {
                    $errors[] = "Failed to prepare avatar upload directory.";
                } elseif (move_uploaded_file($file_tmp, $upload_path)) {
                    $profile_picture = 'assets/uploads/avatars/' . $file_name;
                    
                    // Delete old avatar if exists
                    $stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
                    $stmt->execute([$user_id]);
                    $old_pic = $stmt->fetchColumn();
                    $old_pic_path = $old_pic ? __DIR__ . '/' . ltrim($old_pic, '/') : null;
                    if ($old_pic_path && file_exists($old_pic_path)) {
                        unlink($old_pic_path);
                    }
                } else {
                    $errors[] = "Failed to upload avatar.";
                }
            } else {
                $errors[] = "Invalid image type. Only JPG, PNG, GIF, and WEBP are allowed.";
            }
        }

        if (empty($errors)) {
            if ($profile_picture) {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, bio = ?, campus = ?, profile_picture = ? WHERE user_id = ?");
                $stmt->execute([$name, $bio, $campus, $profile_picture, $user_id]);
                $_SESSION['name'] = $name; // Update session name
            } else {
                $stmt = $pdo->prepare("UPDATE users SET name = ?, bio = ?, campus = ? WHERE user_id = ?");
                $stmt->execute([$name, $bio, $campus, $user_id]);
                $_SESSION['name'] = $name;
            }
            $_SESSION['campus'] = $campus;
            set_toast('success', "Profile updated successfully.");
            redirect('profile.php');
        } else {
            set_toast('error', implode('<br>', $errors));
        }
    } elseif (isset($_POST['action']) && $_POST['action'] === 'update_password') {
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        $errors = [];
        
        if (strlen($new_password) < 6) {
            $errors[] = "New password must be at least 6 characters.";
        }
        if ($new_password !== $confirm_password) {
            $errors[] = "New passwords do not match.";
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
            $stmt->execute([$user_id]);
            $db_password = $stmt->fetchColumn();

            if (password_verify($current_password, $db_password)) {
                $hashed_pw = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
                $stmt->execute([$hashed_pw, $user_id]);
                set_toast('success', "Password updated successfully.");
                redirect('profile.php');
            } else {
                set_toast('error', "Incorrect current password.");
            }
        } else {
            set_toast('error', implode('<br>', $errors));
        }
    }
}

// Fetch user data
$stmt = $pdo->prepare("SELECT * FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

$avatar_path = $user['profile_picture'] && file_exists($user['profile_picture']) 
    ? escape($user['profile_picture']) 
    : 'https://ui-avatars.com/api/?name=' . urlencode($user['name']) . '&background=330066&color=FFD700';

require_once 'includes/header.php';
?>

<div class="max-w-6xl mx-auto space-y-8 animate-fade-in-up pb-12">
    <!-- Hero Header -->
    <div class="relative bg-gradient-to-br from-uitmPurple to-purple-900 rounded-3xl p-8 sm:p-12 overflow-hidden shadow-2xl">
        <div class="absolute -right-20 -top-20 w-64 h-64 bg-uitmGold rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse-slow"></div>
        <div class="absolute -left-20 -bottom-20 w-64 h-64 bg-pink-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-pulse-slow font-serif"></div>
        
        <div class="relative z-10 flex flex-col sm:flex-row items-center gap-8">
            <div class="relative group">
                <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-uitmGold shadow-xl bg-white flex items-center justify-center">
                    <img src="<?= $avatar_path ?>" alt="Avatar" class="w-full h-full object-cover">
                </div>
                <div class="absolute inset-0 bg-black/50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none">
                    <span class="text-white text-xs font-bold">Edit Below</span>
                </div>
            </div>
            <div class="text-center sm:text-left text-white">
                <h1 class="text-3xl sm:text-4xl font-bold font-serif mb-2"><?= escape($user['name']) ?></h1>
                <p class="inline-flex items-center text-uitmGold bg-white/10 px-3 py-1 rounded-full text-sm font-medium backdrop-blur-sm">
                    <?= $_SESSION['role'] === 'admin' ? 'Admin ID' : 'Student ID' ?>: <?= escape($user['student_id']) ?>
                </p>
                <div class="mt-3 text-purple-200">
                    <?= escape($user['campus']) ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Profile Settings Form -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-100">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex items-center">
                    <h2 class="text-xl font-bold font-serif text-uitmPurple">Profile Settings</h2>
                </div>
                <div class="p-6 sm:p-8">
                    <form action="profile.php" method="POST" enctype="multipart/form-data" class="space-y-6">
                        <input type="hidden" name="action" value="update_profile">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <!-- Avatar Upload -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Profile Picture</label>
                            <div class="flex items-center space-x-4">
                                <span class="h-12 w-12 rounded-full overflow-hidden bg-slate-100 border border-slate-200 shrink-0">
                                    <img id="avatar-preview" src="<?= $avatar_path ?>" class="h-full w-full object-cover" alt="Avatar preview">
                                </span>
                                <input type="file" name="avatar" id="avatar-input" accept="image/png, image/jpeg, image/gif, image/webp" class="block w-full text-sm text-slate-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-uitmPurple hover:file:bg-purple-100 transition-all cursor-pointer">
                            </div>
                        </div>

                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2" for="name">Full Name</label>
                            <input type="text" name="name" id="name" value="<?= escape($user['name']) ?>" required class="w-full px-4 py-3 rounded-lg border border-slate-200 focus:border-uitmPurple focus:ring-2 focus:ring-purple-200 outline-none transition-all placeholder:text-slate-400">
                        </div>

                        <!-- Bio -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2" for="bio">Bio</label>
                            <textarea name="bio" id="bio" rows="4" placeholder="Tell us a bit about yourself..." class="w-full px-4 py-3 rounded-lg border border-slate-200 focus:border-uitmPurple focus:ring-2 focus:ring-purple-200 outline-none transition-all placeholder:text-slate-400 resize-y"><?= escape($user['bio']) ?></textarea>
                            <p class="mt-1 text-xs text-slate-500">Brief description for your profile.</p>
                        </div>

                        <!-- Campus -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2" for="campus">Campus</label>
                            <select name="campus" id="campus" required class="w-full px-4 py-3 rounded-lg border border-slate-200 focus:border-uitmPurple focus:ring-2 focus:ring-purple-200 outline-none transition-all appearance-none bg-white">
                                <!-- Reusing campus optgroups -->
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

                        <div class="pt-4 flex justify-end">
                            <button type="submit" class="bg-uitmPurple text-white px-8 py-3 rounded-full font-bold shadow-md hover:bg-purple-900 transition-colors hover:shadow-lg focus:outline-none focus:ring-4 focus:ring-uitmPurple/30">Save Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Security / Password Sidebar -->
        <div class="lg:col-span-1 space-y-8">
            <div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-slate-100">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h2 class="text-xl font-bold font-serif text-slate-800 flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-uitmPurple" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Change Password</span>
                    </h2>
                </div>
                <div class="p-6">
                    <form action="profile.php" method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="update_password">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2" for="current_password">Current Password</label>
                            <input type="password" name="current_password" id="current_password" required class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-uitmPurple focus:ring-2 focus:ring-purple-200 outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2" for="new_password">New Password</label>
                            <input type="password" name="new_password" id="new_password" required minlength="6" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-uitmPurple focus:ring-2 focus:ring-purple-200 outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2" for="confirm_password">Confirm New Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" required minlength="6" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 focus:border-uitmPurple focus:ring-2 focus:ring-purple-200 outline-none transition-all">
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full bg-slate-800 text-white px-6 py-2.5 rounded-lg font-bold shadow hover:bg-slate-700 transition-colors focus:ring-4 focus:ring-slate-300">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="bg-yellow-50 rounded-2xl shadow p-6 border border-yellow-100 flex items-start space-x-4">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-600 shrink-0 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="font-bold text-yellow-800">Account Security</h3>
                    <p class="text-sm text-yellow-700 mt-1">Keep your profile updated and ensure you use a strong password for better security across the UiTM STEP platform.</p>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="bg-red-50 rounded-2xl shadow p-6 border border-red-100 mt-8 flex flex-col items-center">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-red-500 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <h3 class="font-bold text-red-800 text-lg mb-2">Ready to leave?</h3>
                <p class="text-sm text-red-600 mb-6 text-center">You can securely log out of your account to ensure no one else can access it.</p>
                <a href="logout.php" class="w-full text-center bg-red-600 text-white font-bold py-3 px-4 rounded-xl hover:bg-red-700 transition-colors shadow-md hover:shadow-lg focus:ring-4 focus:ring-red-300">
                    Logout of UiTM STEP
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    // Pre-select campus value
    document.addEventListener("DOMContentLoaded", () => {
        const campusSelect = document.getElementById('campus');
        const userCampus = "<?= escape($user['campus']) ?>";
        if (userCampus) {
            campusSelect.value = userCampus;
        }

        // Avatar Upload Preview
        const avatarInput = document.getElementById('avatar-input');
        const avatarPreview = document.getElementById('avatar-preview');
        
        avatarInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.addEventListener('load', function() {
                    avatarPreview.src = this.result;
                });
                reader.readAsDataURL(file);
            }
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>
