<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['user_id'])) {
    redirect('login');
}

$profile_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$preview_mode = (isset($_GET['preview']) && $_GET['preview'] === 'true');
$is_own_profile = ($profile_id === 0 || $profile_id === (int)$_SESSION['user_id']) && !$preview_mode;

if ($preview_mode && $profile_id === 0) {
    $profile_id = (int)$_SESSION['user_id'];
}

if ($is_own_profile) {
    $user_id = $_SESSION['user_id'];

    // Handle Form Submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Verify CSRF Token
        if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
            set_toast('error', 'Invalid security token.');
            redirect('profile');
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
            if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] !== UPLOAD_ERR_NO_FILE) {
                if ($_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    $file_tmp = $_FILES['avatar']['tmp_name'];
                    
                    // Add explicit size limit (e.g. 5MB)
                    $max_size = 5 * 1024 * 1024;
                    if ($_FILES['avatar']['size'] > $max_size) {
                        $errors[] = "Image size exceeds 5MB limit.";
                    } else {
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
                            require_once 'includes/storage.php';
                            $uploaded_path = Storage::upload($file_tmp, 'avatars/' . $file_name, $file_type);
                            
                            if ($uploaded_path) {
                                $profile_picture = $uploaded_path;
                                
                                // Delete old avatar if it was local
                                $stmt = $pdo->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
                                $stmt->execute([$user_id]);
                                $old_pic = $stmt->fetchColumn();
                                if ($old_pic && strpos($old_pic, 'http') === false) {
                                    $old_pic_path = __DIR__ . '/' . ltrim($old_pic, '/');
                                    if (file_exists($old_pic_path)) {
                                        unlink($old_pic_path);
                                    }
                                }
                            } else {
                                $errors[] = "Failed to upload avatar to storage.";
                            }
                        } else {
                            $errors[] = "Invalid image type. Only JPG, PNG, GIF, and WEBP are allowed.";
                        }
                    }
                } else {
                    // Handle PHP upload errors (like UPLOAD_ERR_INI_SIZE)
                    if ($_FILES['avatar']['error'] === UPLOAD_ERR_INI_SIZE) {
                        $errors[] = "The uploaded file exceeds the maximum allowed size (check server limits).";
                    } else {
                        $errors[] = "File upload error code: " . $_FILES['avatar']['error'];
                    }
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
                redirect('profile');
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
                    redirect('profile');
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

    $stored_pic = $user['profile_picture'] ?? '';
    $avatar_path = !empty($stored_pic) 
        ? asset_url($stored_pic) 
        : get_avatar_url($user['name']);
} else {
    // Public Seller Profile View
    $stmt = $pdo->prepare("SELECT user_id, name, campus, profile_picture, bio, created_at, role FROM users WHERE user_id = ?");
    $stmt->execute([$profile_id]);
    $profile_user = $stmt->fetch();

    if (!$profile_user || $profile_user['role'] === 'banned') {
        set_toast('error', 'User not found or is no longer active.');
        redirect('marketplace');
    }

    $stored_pic = $profile_user['profile_picture'] ?? '';
    $avatar_path = !empty($stored_pic) 
        ? asset_url($stored_pic) 
        : get_avatar_url($profile_user['name']);

    // Fetch target user's active gigs
    $gigs_stmt = $pdo->prepare("
        SELECT g.*, 
               COALESCE(AVG(r.rating), 0) as avg_rating, 
               COUNT(r.review_id) as review_count
        FROM gigs g
        LEFT JOIN reviews r ON g.gig_id = r.gig_id
        WHERE g.seller_id = ? AND g.status = 'active'
        GROUP BY g.gig_id
        ORDER BY g.created_at DESC
    ");
    $gigs_stmt->execute([$profile_id]);
    $seller_gigs = $gigs_stmt->fetchAll();

    // Fetch target user's reviews
    $reviews_stmt = $pdo->prepare("
        SELECT r.*, u.name as buyer_name, u.profile_picture as buyer_avatar, g.title as gig_title
        FROM reviews r
        JOIN users u ON r.buyer_id = u.user_id
        JOIN gigs g ON r.gig_id = g.gig_id
        WHERE r.seller_id = ?
        ORDER BY r.created_at DESC
    ");
    $reviews_stmt->execute([$profile_id]);
    $seller_reviews = $reviews_stmt->fetchAll();

    // Calculate rating stats
    $overall_rating = 0;
    $overall_reviews_count = count($seller_reviews);
    if ($overall_reviews_count > 0) {
        $sum_ratings = array_sum(array_column($seller_reviews, 'rating'));
        $overall_rating = $sum_ratings / $overall_reviews_count;
    } else {
        $overall_rating = 0;
        $overall_reviews_count = 0;
    }
}

require_once 'includes/header.php';
?>

<?php if ($is_own_profile): ?>
<div class="max-w-6xl mx-auto space-y-8 animate-fade-in-up pb-12">
    <!-- Hero Header -->
    <div class="relative bg-uitmPurple rounded-lg p-8 sm:p-12 overflow-hidden shadow-xl border border-uitmPurple/30">
        <div class="absolute inset-0 bg-noise opacity-10"></div>
        
        <div class="relative z-10 flex flex-col sm:flex-row items-center gap-8">
            <div class="relative group">
                <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-uitmGold shadow-xl bg-white flex items-center justify-center">
                    <img src="<?= $avatar_path ?>" alt="Avatar" class="w-full h-full object-cover">
                </div>
                <div class="absolute inset-0 bg-black/50 rounded-full opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center pointer-events-none">
                    <span class="text-white text-xs font-bold">Edit Below</span>
                </div>
            </div>
            <div class="text-center sm:text-left text-white flex-grow">
                <h1 class="text-3xl sm:text-4xl font-bold font-serif mb-2"><?= escape($user['name']) ?></h1>
                <p class="inline-flex items-center text-uitmGold bg-white/10 px-3 py-1 rounded-md text-sm font-medium border border-white/10">
                    <?= $_SESSION['role'] === 'admin' ? 'Admin ID' : 'Student ID' ?>: <?= escape($user['student_id']) ?>
                </p>
                <div class="mt-3 text-purple-200">
                    <?= escape($user['campus']) ?>
                </div>
            </div>
            <div class="shrink-0 w-full sm:w-auto mt-4 sm:mt-0">
                <a href="profile?preview=true" class="bg-uitmGold hover:bg-yellow-600 text-uitmPurple font-bold py-2.5 px-5 rounded-lg transition-all duration-300 shadow-lg flex items-center justify-center gap-2 transform hover:-translate-y-0.5 w-full sm:w-auto text-sm border-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    Live Preview Profile
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Profile Settings Form -->
        <div class="lg:col-span-2">
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-xl overflow-hidden border border-slate-100 dark:border-slate-800 transition-colors duration-300">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 flex items-center transition-colors duration-300">
                    <h2 class="text-xl font-bold font-serif text-uitmPurple dark:text-purple-300">Profile Settings</h2>
                </div>
                <div class="p-6 sm:p-8">
                    <form action="profile" method="POST" enctype="multipart/form-data" class="space-y-6">
                        <input type="hidden" name="action" value="update_profile">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <!-- Avatar Upload -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2">Profile Picture</label>
                            <div class="flex items-center space-x-4">
                                <span class="h-12 w-12 rounded-full overflow-hidden bg-slate-100 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shrink-0">
                                    <img id="avatar-preview" src="<?= $avatar_path ?>" class="h-full w-full object-cover" alt="Avatar preview">
                                </span>
                                <input type="file" name="avatar" id="avatar-input" accept="image/png, image/jpeg, image/gif, image/webp" class="block w-full text-sm text-slate-500 dark:text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 dark:file:bg-purple-900/30 file:text-uitmPurple dark:file:text-purple-300 hover:file:bg-purple-100 dark:hover:file:bg-purple-900/50 transition-all cursor-pointer">
                            </div>
                        </div>

                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" for="name">Full Name</label>
                            <input type="text" name="name" id="name" value="<?= escape($user['name']) ?>" required class="w-full px-4 py-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-uitmPurple dark:focus:border-purple-400 focus:ring-2 focus:ring-purple-200 dark:focus:ring-purple-900/50 outline-none transition-all placeholder:text-slate-400 dark:placeholder:text-slate-500">
                        </div>

                        <!-- Bio -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" for="bio">Bio</label>
                            <textarea name="bio" id="bio" rows="4" placeholder="Tell us a bit about yourself..." class="w-full px-4 py-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-uitmPurple dark:focus:border-purple-400 focus:ring-2 focus:ring-purple-200 dark:focus:ring-purple-900/50 outline-none transition-all placeholder:text-slate-400 dark:placeholder:text-slate-500 resize-y"><?= escape($user['bio']) ?></textarea>
                            <p class="mt-1 text-xs text-slate-500 dark:text-slate-400">Brief description for your profile.</p>
                        </div>

                        <!-- Campus -->
                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" for="campus">Campus</label>
                            <select name="campus" id="campus" required class="w-full px-4 py-3 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-uitmPurple dark:focus:border-purple-400 focus:ring-2 focus:ring-purple-200 dark:focus:ring-purple-900/50 outline-none transition-all appearance-none">
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
                            <button type="submit" class="bg-uitmPurple text-white px-8 py-3 rounded-md font-bold shadow-xl hover:bg-purple-900 transition-colors">Save Profile</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Security / Password Sidebar -->
        <div class="lg:col-span-1 space-y-8">
            <div class="bg-white dark:bg-slate-900 rounded-lg shadow-xl overflow-hidden border border-slate-100 dark:border-slate-800 transition-colors duration-300">
                <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-800 bg-slate-50 dark:bg-slate-800 transition-colors duration-300">
                    <h2 class="text-xl font-bold font-serif text-slate-800 dark:text-slate-200 flex items-center space-x-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-uitmPurple dark:text-purple-400" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                        </svg>
                        <span>Change Password</span>
                    </h2>
                </div>
                <div class="p-6">
                    <form action="profile" method="POST" class="space-y-4">
                        <input type="hidden" name="action" value="update_password">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" for="current_password">Current Password</label>
                            <input type="password" name="current_password" id="current_password" required class="w-full px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-uitmPurple dark:focus:border-purple-400 focus:ring-2 focus:ring-purple-200 dark:focus:ring-purple-900/50 outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" for="new_password">New Password</label>
                            <input type="password" name="new_password" id="new_password" required minlength="6" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-uitmPurple dark:focus:border-purple-400 focus:ring-2 focus:ring-purple-200 dark:focus:ring-purple-900/50 outline-none transition-all">
                        </div>

                        <div>
                            <label class="block text-sm font-bold text-slate-700 dark:text-slate-300 mb-2" for="confirm_password">Confirm New Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" required minlength="6" class="w-full px-4 py-2.5 rounded-lg border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-slate-900 dark:text-white focus:border-uitmPurple dark:focus:border-purple-400 focus:ring-2 focus:ring-purple-200 dark:focus:ring-purple-900/50 outline-none transition-all">
                        </div>

                        <div class="pt-2">
                            <button type="submit" class="w-full bg-slate-800 dark:bg-slate-700 text-white px-6 py-2.5 rounded-lg font-bold shadow-xl hover:bg-slate-700 dark:hover:bg-slate-600 transition-colors focus:ring-4 focus:ring-slate-300 dark:focus:ring-slate-800">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
            
            <div class="bg-yellow-50 dark:bg-yellow-900/30 rounded-lg shadow-xl p-6 border border-yellow-100 dark:border-yellow-800/50 flex items-start space-x-4 transition-colors duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-yellow-600 dark:text-yellow-400 shrink-0 mt-1" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <div>
                    <h3 class="font-bold text-yellow-800 dark:text-yellow-300">Account Security</h3>
                    <p class="text-sm text-yellow-700 dark:text-yellow-400/80 mt-1">Keep your profile updated and ensure you use a strong password for better security across the UiTM STEP platform.</p>
                </div>
            </div>

            <!-- Danger Zone -->
            <div class="bg-red-50 dark:bg-red-900/30 rounded-lg shadow-xl p-6 border border-red-100 dark:border-red-800/50 mt-8 flex flex-col items-center transition-colors duration-300">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-red-500 dark:text-red-400 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                </svg>
                <h3 class="font-bold text-red-800 dark:text-red-300 text-lg mb-2">Ready to leave?</h3>
                <p class="text-sm text-red-600 dark:text-red-400 mb-6 text-center">You can securely log out of your account to ensure no one else can access it.</p>
                <a href="logout" class="w-full text-center bg-red-600 text-white font-bold py-3 px-4 rounded-md hover:bg-red-700 transition-colors shadow-xl">
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

        // Avatar Upload Preview and Compression
        const avatarInput = document.getElementById('avatar-input');
        const avatarPreview = document.getElementById('avatar-preview');
        
        avatarInput.addEventListener('change', async function() {
            const file = this.files[0];
            if (!file) return;

            // Immediate preview for better UX
            const previewUrl = URL.createObjectURL(file);
            avatarPreview.src = previewUrl;

            // Compress if file is > 1.5MB and is an image
            const MAX_SIZE = 1.5 * 1024 * 1024;
            if (file.size > MAX_SIZE && file.type.startsWith('image/')) {
                const submitBtn = document.querySelector('form[action="profile"] button[type="submit"]');
                try {
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white inline" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Compressing...';
                    }

                    // Compress to max 800px dimension and 80% JPEG quality
                    const compressedFile = await compressImage(file, 800, 0.8);
                    
                    // Replace the file in the input
                    const dt = new DataTransfer();
                    dt.items.add(compressedFile);
                    this.files = dt.files;
                    
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Save Profile';
                    }
                } catch (e) {
                    console.error('Image compression failed:', e);
                    // Re-enable submit button to allow standard server-side limits to handle it
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Save Profile';
                    }
                }
            }
        });

        /**
         * Compress an image file using Canvas API
         */
        function compressImage(file, maxDimension, quality) {
            return new Promise((resolve, reject) => {
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = new Image();
                    img.onload = () => {
                        let width = img.width;
                        let height = img.height;

                        if (width > height) {
                            if (width > maxDimension) {
                                height = Math.round((height * maxDimension) / width);
                                width = maxDimension;
                            }
                        } else {
                            if (height > maxDimension) {
                                width = Math.round((width * maxDimension) / height);
                                height = maxDimension;
                            }
                        }

                        const canvas = document.createElement('canvas');
                        canvas.width = width;
                        canvas.height = height;

                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0, width, height);

                        canvas.toBlob(
                            (blob) => {
                                if (!blob) {
                                    reject(new Error('Canvas is empty'));
                                    return;
                                }
                                // Rename with .jpg to match the new mime type
                                const newName = file.name.replace(/\.[^/.]+$/, "") + ".jpg";
                                const newFile = new File([blob], newName, {
                                    type: 'image/jpeg',
                                    lastModified: Date.now()
                                });
                                resolve(newFile);
                            },
                            'image/jpeg',
                            quality
                        );
                    };
                    img.onerror = () => reject(new Error('Failed to load image'));
                    img.src = e.target.result;
                };
                reader.onerror = () => reject(new Error('Failed to read file'));
                reader.readAsDataURL(file);
            });
        }
    });
</script>
<?php else: ?>
<style>
    @keyframes gradient-flow {
        0% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
        100% { background-position: 0% 50%; }
    }
    @keyframes shimmer {
        100% { transform: translateX(100%); }
    }
    .animated-hero-bg {
        background-size: 200% 200%;
        animation: gradient-flow 12s ease infinite;
    }
    .spring-transition {
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.15);
    }
    .tab-content-panel {
        opacity: 0;
        transform: translateY(12px);
        transition: opacity 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94), transform 0.35s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    }
    .tab-content-panel.tab-active {
        opacity: 1;
        transform: translateY(0);
    }
</style>
<!-- Public Seller Profile HTML -->
<div class="max-w-6xl mx-auto space-y-8 animate-fade-in-up pb-12">
    <?php if (isset($_GET['preview']) && $_GET['preview'] === 'true'): ?>
        <div class="bg-indigo-600 dark:bg-indigo-950/80 text-white font-semibold py-3 px-6 rounded-xl flex flex-col sm:flex-row items-center justify-between gap-3 shadow-lg border border-indigo-500/20">
            <div class="flex items-center gap-2 text-sm">
                <svg class="w-5 h-5 text-indigo-200 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span>You are viewing a live preview of your own profile as buyers see it.</span>
            </div>
            <a href="profile" class="bg-white hover:bg-slate-100 text-indigo-700 dark:text-indigo-900 font-bold py-1.5 px-4 rounded-lg text-xs transition-all shadow-md shrink-0 border-0">
                Exit Preview / Edit Profile
            </a>
        </div>
    <?php endif; ?>
    <!-- Hero Header -->
    <div class="relative bg-gradient-to-r from-uitmPurple via-purple-950 to-purple-900 rounded-lg p-8 sm:p-12 overflow-hidden shadow-2xl border border-uitmPurple/30 animated-hero-bg transition-colors duration-300">
        <div class="absolute inset-0 bg-noise opacity-15"></div>
        <div class="absolute -right-24 -top-24 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -left-24 -bottom-24 w-96 h-96 bg-uitmGold/5 rounded-full blur-3xl pointer-events-none"></div>
        
        <div class="relative z-10 flex flex-col sm:flex-row items-center gap-8">
            <div class="relative group">
                <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-uitmGold shadow-2xl bg-white flex items-center justify-center relative transition-transform duration-500 group-hover:scale-105">
                    <img src="<?= $avatar_path ?>" alt="Avatar" class="w-full h-full object-cover">
                </div>
                <div class="absolute bottom-1 right-2 w-5 h-5 rounded-full bg-green-500 border-2 border-white dark:border-slate-900 shadow-md flex items-center justify-center" title="Online">
                    <span class="w-2.5 h-2.5 rounded-full bg-green-200 animate-ping absolute"></span>
                    <span class="w-2 h-2 rounded-full bg-green-400"></span>
                </div>
            </div>
            <div class="text-center sm:text-left text-white flex-grow">
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 mb-2">
                    <h1 class="text-3xl sm:text-4xl font-extrabold font-serif tracking-tight leading-none"><?= escape($profile_user['name']) ?></h1>
                    <span class="bg-uitmGold/20 text-uitmGold text-[10px] uppercase font-bold tracking-widest px-2.5 py-1 rounded-md border border-uitmGold/30">Verified Seller</span>
                </div>
                <p class="text-purple-200 text-sm sm:text-base flex items-center justify-center sm:justify-start gap-1.5 font-medium">
                    <svg class="w-4 h-4 text-uitmGold/80 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    <?= escape($profile_user['campus']) ?>
                </p>
                <div class="mt-4 flex flex-wrap justify-center sm:justify-start gap-4 text-xs text-purple-200/80">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        Joined <?= date('F Y', strtotime($profile_user['created_at'])) ?>
                    </span>
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4 text-purple-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"></path></svg>
                        Active Account
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 items-start">
        <!-- Sidebar Column (Left) -->
        <div class="lg:col-span-1 space-y-6 lg:sticky lg:top-24">
            <!-- About Card -->
            <div class="bg-white dark:bg-slate-900 rounded-lg p-6 shadow-xl border border-slate-100 dark:border-slate-800 transition-colors duration-300">
                <h3 class="text-lg font-bold font-serif text-slate-900 dark:text-white mb-4 border-b border-slate-100 dark:border-slate-800 pb-2">About Seller</h3>
                
                <div class="space-y-4">
                    <div>
                        <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Biography</span>
                        <p class="text-sm text-slate-600 dark:text-slate-300 leading-relaxed mt-1 whitespace-pre-wrap"><?= !empty($profile_user['bio']) ? escape($profile_user['bio']) : 'This seller hasn\'t written a bio yet.' ?></p>
                    </div>
                    
                    <div>
                        <span class="text-xs font-bold text-slate-400 dark:text-slate-500 uppercase tracking-widest">Location</span>
                        <p class="text-sm text-slate-600 dark:text-slate-300 font-medium mt-0.5"><?= escape($profile_user['campus']) ?></p>
                    </div>
                </div>
            </div>

            <!-- Stats & Feedback Card -->
            <div class="bg-white dark:bg-slate-900 rounded-lg p-6 shadow-xl border border-slate-100 dark:border-slate-800 transition-colors duration-300">
                <h3 class="text-lg font-bold font-serif text-slate-900 dark:text-white mb-4 border-b border-slate-100 dark:border-slate-800 pb-2">Seller Rating</h3>
                
                <div class="flex items-center gap-4">
                    <div class="text-center bg-purple-50 dark:bg-purple-950/20 px-4 py-3 rounded-lg border border-purple-100 dark:border-purple-950/30">
                        <span class="block text-4xl font-extrabold font-serif text-uitmPurple dark:text-purple-300"><?= number_format($overall_rating, 1) ?></span>
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Out of 5</span>
                    </div>
                    <div>
                        <div class="flex gap-1 mb-1">
                            <?php 
                            $rounded_rating = round($overall_rating);
                            for ($i = 1; $i <= 5; $i++): 
                            ?>
                                <svg class="w-5 h-5 <?= $i <= $rounded_rating ? 'fill-amber-400 text-amber-400' : 'fill-gray-200 dark:fill-slate-800 text-gray-200 dark:text-slate-800' ?>" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                </svg>
                            <?php endfor; ?>
                        </div>
                        <p class="text-xs text-slate-500 dark:text-slate-400 font-medium"><?= $overall_reviews_count ?> customer reviews</p>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mt-6 pt-4 border-t border-slate-100 dark:border-slate-800">
                    <div class="text-center p-3 rounded-lg bg-slate-50 dark:bg-slate-800/40 font-semibold text-slate-800 dark:text-slate-200">
                        <span class="block text-xl font-bold text-slate-800 dark:text-slate-200"><?= count($seller_gigs) ?></span>
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Active Gigs</span>
                    </div>
                    <div class="text-center p-3 rounded-lg bg-slate-50 dark:bg-slate-800/40 font-semibold text-slate-800 dark:text-slate-200">
                        <span class="block text-xl font-bold text-slate-800 dark:text-slate-200"><?= $overall_reviews_count ?></span>
                        <span class="text-[10px] font-bold text-slate-400 dark:text-slate-500 uppercase tracking-wider">Total Sales</span>
                    </div>
                </div>

                <!-- Chat CTA -->
                <a href="<?= ROOT_URL ?>chat?user=<?= $profile_user['user_id'] ?>" class="group/chat relative w-full mt-6 border-2 border-uitmPurple/30 dark:border-purple-500/30 text-uitmPurple dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20 font-bold py-3 px-6 rounded-lg flex items-center justify-center gap-2 hover:scale-[1.02] active:scale-[0.98] spring-transition overflow-hidden focus:outline-none">
                    <span class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-purple-500/5 to-transparent -translate-x-full group-hover/chat:animate-[shimmer_1.5s_infinite]"></span>
                    <svg class="w-5 h-5 transition-transform duration-300 group-hover/chat:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                    <span>Contact Seller</span>
                </a>

                <?php if (!$is_own_profile && isset($_SESSION['user_id']) && $_SESSION['role'] === 'student'): ?>
                <button
                    onclick="openProfileReportModal()"
                    class="w-full mt-2 flex items-center justify-center gap-2 text-xs text-gray-400 dark:text-slate-500 hover:text-red-500 dark:hover:text-red-400 font-medium py-2 rounded-lg hover:bg-red-50 dark:hover:bg-red-900/20 border border-transparent hover:border-red-200 dark:hover:border-red-800/50 transition-all duration-300 group"
                >
                    <svg class="w-3.5 h-3.5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                    </svg>
                    Report this user
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- Main Column (Right) -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Tabs Navigation -->
            <div class="flex border-b border-slate-200 dark:border-slate-800 mb-6 gap-6 relative">
                <button onclick="switchTab('gigs')" id="tab-btn-gigs" class="pb-3 text-base sm:text-lg font-bold border-b-2 border-uitmPurple dark:border-purple-400 text-uitmPurple dark:text-purple-300 transition-all font-serif flex items-center gap-2 focus:outline-none">
                    Active Gigs
                    <span class="text-xs bg-uitmPurple/10 dark:bg-purple-950/30 text-uitmPurple dark:text-purple-300 font-bold px-2 py-0.5 rounded-full"><?= count($seller_gigs) ?></span>
                </button>
                <button onclick="switchTab('reviews')" id="tab-btn-reviews" class="pb-3 text-base sm:text-lg font-bold border-b-2 border-transparent text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-all font-serif flex items-center gap-2 focus:outline-none">
                    Reviews
                    <span class="text-xs bg-slate-100 dark:bg-slate-800 text-slate-400 font-bold px-2 py-0.5 rounded-full"><?= count($seller_reviews) ?></span>
                </button>
            </div>

            <!-- Tab: Gigs -->
            <div id="tab-content-gigs" class="tab-content-panel tab-active">
                <?php if (empty($seller_gigs)): ?>
                    <div class="bg-white dark:bg-slate-900 rounded-lg p-12 text-center border border-slate-100 dark:border-slate-800">
                        <svg class="w-16 h-16 text-slate-200 dark:text-slate-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                        <h4 class="text-lg font-bold text-slate-700 dark:text-slate-300 font-serif">No Services Listed</h4>
                        <p class="text-slate-400 dark:text-slate-500 text-sm mt-1">This seller doesn't have any active services listed on the marketplace.</p>
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <?php foreach ($seller_gigs as $gig): ?>
                            <div class="bg-white dark:bg-slate-900 rounded-lg border border-slate-100 dark:border-slate-800/80 overflow-hidden flex flex-col spring-transition group hover:-translate-y-1.5 hover:shadow-2xl hover:shadow-purple-950/10 hover:border-purple-500/20 dark:hover:border-purple-500/30 relative">
                                <!-- Media / Slideshow Container -->
                                <div class="aspect-video w-full relative overflow-hidden bg-slate-100 dark:bg-slate-800 shrink-0">
                                    <?php
                                        $media_items = [];
                                        if (!empty($gig['youtube_url'])) {
                                            if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $gig['youtube_url'], $matches)) {
                                                $media_items[] = ['type' => 'youtube', 'content' => 'https://www.youtube.com/embed/' . $matches[1]];
                                            }
                                        }
                                        if (!empty($gig['image_url'])) {
                                            $media_items[] = ['type' => 'image', 'content' => asset_url($gig['image_url'])];
                                        }

                                        // Default fallback if no media
                                        if (empty($media_items)) {
                                            $cat = strtolower($gig['category']);
                                            $thumb = 'assets/img/cat_programming.jpg'; 
                                            if (strpos($cat, 'design') !== false) $thumb = 'assets/img/cat_design.jpg';
                                            elseif (strpos($cat, 'video') !== false) $thumb = 'assets/img/cat_video.jpg';
                                            elseif (strpos($cat, 'writing') !== false) $thumb = 'assets/img/cat_writing.jpg';
                                            $media_items[] = ['type' => 'image', 'content' => asset_url($thumb)];
                                        }
                                    ?>
                                    
                                    <div id="slider-<?php echo $gig['gig_id']; ?>" class="h-full w-full relative">
                                        <?php foreach ($media_items as $index => $item): ?>
                                            <div class="card-slide absolute inset-0 transition-opacity duration-300 <?php echo $index === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0'; ?>" data-index="<?php echo $index; ?>">
                                                <?php if ($item['type'] === 'youtube'): ?>
                                                    <iframe class="w-full h-full pointer-events-none" src="<?php echo $item['content']; ?>?controls=0&mute=1&loop=1" frameborder="0"></iframe>
                                                <?php else: ?>
                                                    <img src="<?php echo $item['content']; ?>" alt="<?php echo escape($gig['title']); ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                                <?php endif; ?>
                                            </div>
                                        <?php endforeach; ?>

                                        <?php if (count($media_items) > 1): ?>
                                            <!-- Small Dots for Card Slider -->
                                            <div class="absolute bottom-2 left-1/2 -translate-x-1/2 z-20 flex gap-1.5 opacity-0 group-hover:opacity-100 transition-opacity">
                                                <?php foreach ($media_items as $index => $item): ?>
                                                    <div class="w-1.5 h-1.5 rounded-full bg-white/80 shadow-xl <?php echo $index === 0 ? 'w-3' : ''; ?>"></div>
                                                <?php endforeach; ?>
                                            </div>
                                            <!-- Hover overlays to switch slides and link to details -->
                                            <div class="absolute inset-0 z-20 flex">
                                                <a class="flex-1 cursor-pointer" onmouseenter="setCardSlide(<?php echo $gig['gig_id']; ?>, 0)" href="<?php echo ROOT_URL; ?>gigs/details?id=<?php echo $gig['gig_id']; ?>"></a>
                                                <a class="flex-1 cursor-pointer" onmouseenter="setCardSlide(<?php echo $gig['gig_id']; ?>, 1)" href="<?php echo ROOT_URL; ?>gigs/details?id=<?php echo $gig['gig_id']; ?>"></a>
                                            </div>
                                        <?php else: ?>
                                            <!-- Overlay link to details -->
                                            <a class="absolute inset-0 z-20" href="<?php echo ROOT_URL; ?>gigs/details?id=<?php echo $gig['gig_id']; ?>"></a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <!-- Info / Card Body -->
                                <div class="p-3 sm:p-4 flex-grow flex flex-col justify-between">
                                    <div>
                                        <!-- Gig Title -->
                                        <a href="<?php echo ROOT_URL; ?>gigs/details?id=<?php echo $gig['gig_id']; ?>" class="text-xs sm:text-sm font-medium text-gray-800 dark:text-slate-200 line-clamp-2 hover:underline hover:text-uitmPurple dark:hover:text-purple-300 leading-snug mb-1 h-[32px] sm:h-[40px] block transition-colors duration-200">
                                            <?php echo escape($gig['title']); ?>
                                        </a>

                                        <!-- Star Rating (using calculated averages / real db reviews) -->
                                        <?php
                                            $actual_rating = (float)$gig['avg_rating'];
                                            $actual_reviews = (int)$gig['review_count'];
                                            if ($actual_reviews > 0) {
                                                $rating_val = number_format($actual_rating, 1);
                                                $review_cnt = $actual_reviews;
                                            } else {
                                                $rating_val = "0.0";
                                                $review_cnt = 0;
                                            }
                                        ?>
                                        <div class="flex items-center gap-1 text-xs sm:text-sm font-bold mt-1 text-gray-900 dark:text-white">
                                            <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 fill-current text-gray-900 dark:text-white shrink-0" viewBox="0 0 20 20">
                                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                            </svg>
                                            <span class="text-gray-900 dark:text-white"><?php echo $rating_val; ?></span>
                                            <span class="text-gray-400 dark:text-slate-400 font-normal text-[10px] sm:text-xs">(<?php echo $review_cnt; ?>)</span>
                                        </div>
                                    </div>

                                    <div>
                                        <!-- Starting Price -->
                                        <div class="mt-2 text-xs sm:text-sm text-gray-900 dark:text-slate-100 font-extrabold">
                                            From RM<?php echo number_format($gig['price']); ?>
                                        </div>

                                        <!-- Footer: Campus & Category tag -->
                                        <div class="mt-2.5 pt-2.5 border-t border-gray-100 dark:border-slate-800 flex items-center justify-between text-[10px] sm:text-xs text-gray-500 dark:text-slate-400 font-medium gap-1">
                                            <span class="flex items-center min-w-0">
                                                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5 text-gray-400 dark:text-slate-500 mr-1 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path>
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                                </svg>
                                                <span class="truncate"><?php echo escape(str_replace(['UiTM Kampus ', 'UiTM '], '', $gig['campus'])); ?></span>
                                            </span>
                                            <span class="text-[8px] sm:text-[10px] font-bold text-uitmPurple dark:text-purple-300 uppercase tracking-widest shrink-0 bg-uitmPurple/5 dark:bg-purple-950/20 px-1.5 py-0.5 rounded">
                                                <?php echo escape($gig['category']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Tab: Reviews -->
            <div id="tab-content-reviews" class="tab-content-panel hidden">
                <?php if (empty($seller_reviews)): ?>
                    <div class="bg-white dark:bg-slate-900 rounded-lg p-12 text-center border border-slate-100 dark:border-slate-800">
                        <svg class="w-16 h-16 text-slate-200 dark:text-slate-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                        <h4 class="text-lg font-bold text-slate-700 dark:text-slate-300 font-serif">No Reviews Yet</h4>
                        <p class="text-slate-400 dark:text-slate-500 text-sm mt-1">This seller hasn't received any buyer feedback yet.</p>
                    </div>
                <?php else: ?>
                    <div class="space-y-6">
                        <?php foreach ($seller_reviews as $review): ?>
                            <div class="p-6 bg-white dark:bg-slate-900 border border-slate-100 dark:border-slate-800 rounded-lg hover:shadow-xl hover:border-purple-500/20 dark:hover:border-purple-500/30 spring-transition">
                                <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4 mb-4">
                                    <div class="flex items-center gap-3">
                                        <?php
                                        $reviewer_avatar = !empty($review['buyer_avatar']) 
                                            ? asset_url($review['buyer_avatar']) 
                                            : get_avatar_url($review['buyer_name']);
                                        ?>
                                        <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-white dark:border-slate-800 shadow-md shrink-0 bg-uitmPurple flex items-center justify-center">
                                            <img src="<?= $reviewer_avatar ?>" alt="<?= escape($review['buyer_name']) ?>" class="w-full h-full object-cover">
                                        </div>
                                        <div>
                                            <p class="font-bold text-slate-900 dark:text-white text-sm"><?= escape($review['buyer_name']) ?></p>
                                            <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium">for: <span class="font-semibold text-uitmPurple dark:text-purple-400"><?= escape($review['gig_title']) ?></span></p>
                                        </div>
                                    </div>
                                    <div class="flex flex-row sm:flex-col sm:items-end justify-between items-center shrink-0">
                                        <div class="flex gap-0.5 mb-1">
                                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                                <svg class="w-3.5 h-3.5 <?= $i <= $review['rating'] ? 'fill-amber-400 text-amber-400' : 'fill-gray-200 dark:fill-slate-800 text-gray-200 dark:text-slate-800' ?>" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path>
                                                </svg>
                                            <?php endfor; ?>
                                        </div>
                                        <p class="text-[10px] text-slate-400 dark:text-slate-500 font-medium"><?= date('M d, Y', strtotime($review['created_at'])) ?></p>
                                    </div>
                                </div>
                                <p class="text-slate-600 dark:text-slate-300 text-sm leading-relaxed whitespace-pre-wrap"><?= escape($review['review_text']) ?></p>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<script>
    function switchTab(tabId) {
        const gigsTab = document.getElementById('tab-content-gigs');
        const reviewsTab = document.getElementById('tab-content-reviews');
        const gigsBtn = document.getElementById('tab-btn-gigs');
        const reviewsBtn = document.getElementById('tab-btn-reviews');

        if (tabId === 'gigs') {
            reviewsTab.classList.remove('tab-active');
            setTimeout(() => {
                reviewsTab.classList.add('hidden');
                gigsTab.classList.remove('hidden');
                setTimeout(() => gigsTab.classList.add('tab-active'), 20);
            }, 200);
            
            gigsBtn.classList.add('border-uitmPurple', 'dark:border-purple-400', 'text-uitmPurple', 'dark:text-purple-300');
            gigsBtn.classList.remove('border-transparent', 'text-slate-400');
            
            reviewsBtn.classList.remove('border-uitmPurple', 'dark:border-purple-400', 'text-uitmPurple', 'dark:text-purple-300');
            reviewsBtn.classList.add('border-transparent', 'text-slate-400');
        } else {
            gigsTab.classList.remove('tab-active');
            setTimeout(() => {
                gigsTab.classList.add('hidden');
                reviewsTab.classList.remove('hidden');
                setTimeout(() => reviewsTab.classList.add('tab-active'), 20);
            }, 200);
            
            reviewsBtn.classList.add('border-uitmPurple', 'dark:border-purple-400', 'text-uitmPurple', 'dark:text-purple-300');
            reviewsBtn.classList.remove('border-transparent', 'text-slate-400');
            
            gigsBtn.classList.remove('border-uitmPurple', 'dark:border-purple-400', 'text-uitmPurple', 'dark:text-purple-300');
            gigsBtn.classList.add('border-transparent', 'text-slate-400');
        }
    }

    function setCardSlide(gigId, index) {
        const slider = document.getElementById('slider-' + gigId);
        if (!slider) return;
        
        const slides = slider.querySelectorAll('.card-slide');
        const dots = slider.querySelectorAll('.w-1\\.5');
        
        slides.forEach((slide, i) => {
            if (i === index) {
                slide.classList.remove('opacity-0', 'z-0');
                slide.classList.add('opacity-100', 'z-10');
            } else {
                slide.classList.add('opacity-0', 'z-0');
                slide.classList.remove('opacity-100', 'z-10');
            }
        });
        
        dots.forEach((dot, i) => {
            if (i === index) {
                dot.classList.add('w-3');
            } else {
                dot.classList.remove('w-3');
            }
        });
    }
</script>
<?php endif; ?>
<?php if (!$is_own_profile && isset($_SESSION['user_id']) && $_SESSION['role'] === 'student'): ?>
<!-- =====================================================================
     REPORT USER MODAL (Profile)
     ===================================================================== -->
<div
    id="profile-report-modal-overlay"
    onclick="if(event.target===this) closeProfileReportModal()"
    class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300"
    aria-modal="true"
    role="dialog"
    aria-labelledby="profile-report-modal-title"
>
    <div
        id="profile-report-modal-panel"
        class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-lg shadow-2xl border-x border-b border-gray-100 dark:border-slate-800 overflow-hidden transform scale-95 transition-all duration-300"
    >
        <div class="h-1.5 bg-gradient-to-r from-red-500 to-rose-600 rounded-t-2xl border-x border-gray-100 dark:border-slate-800"></div>

        <div class="p-8">
            <!-- Header -->
            <div class="flex items-start justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                        </svg>
                    </div>
                    <div>
                        <h2 id="profile-report-modal-title" class="text-lg font-extrabold text-gray-900 dark:text-white">Report User</h2>
                        <p class="text-sm text-gray-500 dark:text-slate-400">Reporting: <span class="font-bold text-gray-700 dark:text-slate-300"><?= escape($profile_user['name']) ?></span></p>
                    </div>
                </div>
                <button onclick="closeProfileReportModal()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 text-gray-400 dark:text-slate-500 hover:text-gray-700 dark:hover:text-slate-300 transition-colors" aria-label="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Info banner -->
            <div class="mb-5 flex gap-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 rounded-lg p-4">
                <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-xs text-amber-700 dark:text-amber-300 leading-relaxed font-medium">
                    Reports are reviewed by UiTM STEP admins within 24–48 hours. False or malicious reports may result in action against your account.
                </p>
            </div>

            <!-- Form -->
            <form action="<?php echo ROOT_URL; ?>api/report_action" method="POST" id="profile-report-form">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="reported_id" value="<?= $profile_user['user_id'] ?>">
                <input type="hidden" name="redirect_to" value="<?php echo ROOT_URL; ?>profile?id=<?= $profile_user['user_id'] ?>">

                <!-- Reason -->
                <div class="mb-5">
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-widest mb-3">
                        Reason for report <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 gap-2">
                        <?php
                        $profile_reasons = [
                            'scam'                  => ['label' => 'Scam / Fraud',                  'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'],
                            'fake_payment_proof'    => ['label' => 'Fake Payment Proof',            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>'],
                            'non_delivery'          => ['label' => 'Did Not Deliver Work',          'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>'],
                            'harassment'            => ['label' => 'Harassment / Threats',          'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'],
                            'inappropriate_content' => ['label' => 'Inappropriate Content',         'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636"></path></svg>'],
                            'other'                 => ['label' => 'Other',                         'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>'],
                        ];
                        foreach ($profile_reasons as $value => $meta):
                        ?>
                        <label class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 dark:border-slate-800 cursor-pointer hover:border-red-300 dark:hover:border-red-700 hover:bg-red-50/50 dark:hover:bg-red-900/10 transition-all duration-200 has-[:checked]:border-red-500 has-[:checked]:bg-red-50 dark:has-[:checked]:bg-red-900/20 dark:has-[:checked]:border-red-600 group">
                            <input type="radio" name="reason" value="<?php echo $value; ?>" class="sr-only" required>
                            <span class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-slate-800 flex items-center justify-center text-gray-400 group-hover:text-red-500 transition-colors group-has-[:checked]:bg-red-100 dark:group-has-[:checked]:bg-red-900/40 group-has-[:checked]:text-red-600">
                                <?php echo $meta['icon']; ?>
                            </span>
                            <span class="text-sm font-bold text-gray-700 dark:text-slate-300"><?php echo $meta['label']; ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Details -->
                <div class="mb-6">
                    <label for="profile-report-details" class="block text-sm font-bold text-gray-700 dark:text-slate-300 mb-2">
                        Additional details <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <textarea
                        id="profile-report-details"
                        name="details"
                        rows="3"
                        maxlength="1000"
                        placeholder="Describe what happened in as much detail as possible..."
                        class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-red-400/40 focus:border-red-400 dark:focus:ring-red-800/40 dark:focus:border-red-700 transition-all resize-none"
                    ></textarea>
                    <p class="text-xs text-gray-400 dark:text-slate-500 mt-1 text-right"><span id="profile-report-char-count">0</span>/1000</p>
                </div>

                <!-- Actions -->
                <div class="flex gap-3">
                    <button type="button" onclick="closeProfileReportModal()" class="flex-1 py-3 rounded-lg border-2 border-gray-200 dark:border-slate-700 text-gray-600 dark:text-slate-400 font-bold text-sm hover:bg-gray-50 dark:hover:bg-slate-800 transition-all duration-300">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 py-3 rounded-lg bg-red-500 hover:bg-red-600 text-white font-bold text-sm shadow-lg hover:shadow-red-200 dark:hover:shadow-red-900/30 transition-all duration-300 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                        </svg>
                        Submit Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const profileReportOverlay   = document.getElementById('profile-report-modal-overlay');
    const profileReportPanel     = document.getElementById('profile-report-modal-panel');
    const profileReportTextarea  = document.getElementById('profile-report-details');
    const profileReportCharCount = document.getElementById('profile-report-char-count');

    function openProfileReportModal() {
        profileReportOverlay.classList.remove('opacity-0', 'pointer-events-none');
        profileReportPanel.classList.remove('scale-95');
        profileReportPanel.classList.add('scale-100');
        document.body.style.overflow = 'hidden';
    }

    function closeProfileReportModal() {
        profileReportOverlay.classList.add('opacity-0', 'pointer-events-none');
        profileReportPanel.classList.remove('scale-100');
        profileReportPanel.classList.add('scale-95');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeProfileReportModal();
    });

    profileReportTextarea.addEventListener('input', function() {
        profileReportCharCount.textContent = this.value.length;
    });
</script>
<?php endif; ?>
<?php require_once 'includes/footer.php'; ?>
