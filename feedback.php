<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';

// Fetch logged-in user info for pre-filling the form
$prefill_name = '';
$prefill_email = '';
$prefill_campus = '';

if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT name, email, campus FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $user_info = $stmt->fetch();
    if ($user_info) {
        $prefill_name = $user_info['name'];
        $prefill_email = $user_info['email'];
        $prefill_campus = $user_info['campus'];
    }
}
?>

<div class="max-w-6xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
    <!-- Header -->
    <div class="text-center mb-12 animate-fade-in-up">
        <h1 class="text-3xl md:text-4xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-uitmPurple to-purple-600 dark:from-purple-300 dark:to-purple-500 font-serif">
            We Value Your Feedback
        </h1>
        <p class="text-gray-655 dark:text-slate-405 max-w-2xl mx-auto mt-3 text-base md:text-lg">
            Your thoughts help us improve the platform. Tell us what you think or get in touch for technical assistance.
        </p>
    </div>

    <!-- Main Content Layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Form Column (takes 2 cols on desktop) -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-gray-100 dark:border-slate-800/80 p-8 transition-colors duration-300">
            <h2 class="text-xl font-bold mb-6 text-gray-900 dark:text-white font-serif flex items-center gap-2">
                <svg class="w-5 h-5 text-uitmPurple dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"></path>
                </svg>
                Send Feedback
            </h2>

            <form action="insert-feedback.php" method="POST" class="space-y-6">
                <input type="hidden" name="csrf_token" value="<?php echo escape($_SESSION['csrf_token'] ?? ''); ?>">

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label for="name" class="block text-gray-700 dark:text-slate-300 font-bold mb-2 text-sm">Full Name</label>
                        <input type="text" name="name" id="name" required value="<?php echo escape($prefill_name); ?>" placeholder="e.g. Ahmad bin Ali" class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all placeholder-gray-400 dark:placeholder-slate-500">
                    </div>

                    <div>
                        <label for="email" class="block text-gray-700 dark:text-slate-300 font-bold mb-2 text-sm">Student Email</label>
                        <input type="email" name="email" id="email" required value="<?php echo escape($prefill_email); ?>" placeholder="e.g. student@student.uitm.edu.my" class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all placeholder-gray-400 dark:placeholder-slate-500">
                    </div>

                    <div>
                        <label for="phone" class="block text-gray-700 dark:text-slate-300 font-bold mb-2 text-sm">Phone Number</label>
                        <input type="text" name="phone" id="phone" required placeholder="e.g. 0123456789" class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all placeholder-gray-400 dark:placeholder-slate-500">
                    </div>

                    <div>
                        <label for="campus" class="block text-gray-700 dark:text-slate-300 font-bold mb-2 text-sm">Your Campus</label>
                        <select name="campus" id="campus" required class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all">
                            <option value="" disabled <?php echo empty($prefill_campus) ? 'selected' : ''; ?>>Select your campus</option>
                            <optgroup label="Selangor">
                                <option value="UiTM Shah Alam" <?php echo ($prefill_campus === 'UiTM Shah Alam') ? 'selected' : ''; ?>>Shah Alam</option>
                                <option value="UiTM Kampus Puncak Alam" <?php echo ($prefill_campus === 'UiTM Kampus Puncak Alam') ? 'selected' : ''; ?>>Puncak Alam</option>
                                <option value="UiTM Kampus Puncak Perdana" <?php echo ($prefill_campus === 'UiTM Kampus Puncak Perdana') ? 'selected' : ''; ?>>Puncak Perdana</option>
                                <option value="UiTM Kampus Hospital Sg Buloh" <?php echo ($prefill_campus === 'UiTM Kampus Hospital Sg Buloh') ? 'selected' : ''; ?>>Hospital Sg Buloh</option>
                                <option value="UiTM Kampus Dengkil" <?php echo ($prefill_campus === 'UiTM Kampus Dengkil') ? 'selected' : ''; ?>>Dengkil</option>
                            </optgroup>
                            <optgroup label="Perlis">
                                <option value="UiTM Kampus Arau" <?php echo ($prefill_campus === 'UiTM Kampus Arau') ? 'selected' : ''; ?>>Arau</option>
                            </optgroup>
                            <optgroup label="Kedah">
                                <option value="UiTM Kampus Sungai Petani" <?php echo ($prefill_campus === 'UiTM Kampus Sungai Petani') ? 'selected' : ''; ?>>Sungai Petani</option>
                            </optgroup>
                            <optgroup label="Pulau Pinang">
                                <option value="UiTM Kampus Permatang Pauh" <?php echo ($prefill_campus === 'UiTM Kampus Permatang Pauh') ? 'selected' : ''; ?>>Permatang Pauh</option>
                                <option value="UiTM Kampus Bertam" <?php echo ($prefill_campus === 'UiTM Kampus Bertam') ? 'selected' : ''; ?>>Bertam</option>
                            </optgroup>
                            <optgroup label="Perak">
                                <option value="UiTM Kampus Seri Iskandar" <?php echo ($prefill_campus === 'UiTM Kampus Seri Iskandar') ? 'selected' : ''; ?>>Seri Iskandar</option>
                                <option value="UiTM Kampus Tapah" <?php echo ($prefill_campus === 'UiTM Kampus Tapah') ? 'selected' : ''; ?>>Tapah</option>
                            </optgroup>
                            <optgroup label="Negeri Sembilan">
                                <option value="UiTM Kampus Kuala Pilah Beting" <?php echo ($prefill_campus === 'UiTM Kampus Kuala Pilah Beting') ? 'selected' : ''; ?>>Kuala Pilah Beting</option>
                                <option value="UiTM Kampus Seremban 3" <?php echo ($prefill_campus === 'UiTM Kampus Seremban 3') ? 'selected' : ''; ?>>Seremban 3</option>
                                <option value="UiTM Kampus Rembau" <?php echo ($prefill_campus === 'UiTM Kampus Rembau') ? 'selected' : ''; ?>>Rembau</option>
                            </optgroup>
                            <optgroup label="Melaka">
                                <option value="UiTM Kampus Alor Gajah" <?php echo ($prefill_campus === 'UiTM Kampus Alor Gajah') ? 'selected' : ''; ?>>Alor Gajah</option>
                                <option value="UiTM Kampus Bandaraya Melaka" <?php echo ($prefill_campus === 'UiTM Kampus Bandaraya Melaka') ? 'selected' : ''; ?>>Bandaraya Melaka</option>
                                <option value="UiTM Kampus Jasin" <?php echo ($prefill_campus === 'UiTM Kampus Jasin') ? 'selected' : ''; ?>>Jasin</option>
                            </optgroup>
                            <optgroup label="Johor">
                                <option value="UiTM Kampus Segamat" <?php echo ($prefill_campus === 'UiTM Kampus Segamat') ? 'selected' : ''; ?>>Segamat</option>
                                <option value="UiTM Kampus Pasir Gudang" <?php echo ($prefill_campus === 'UiTM Kampus Pasir Gudang') ? 'selected' : ''; ?>>Pasir Gudang</option>
                            </optgroup>
                            <optgroup label="Pahang">
                                <option value="UiTM Kampus Jengka" <?php echo ($prefill_campus === 'UiTM Kampus Jengka') ? 'selected' : ''; ?>>Jengka</option>
                                <option value="UiTM Kampus Raub" <?php echo ($prefill_campus === 'UiTM Kampus Raub') ? 'selected' : ''; ?>>Raub</option>
                            </optgroup>
                            <optgroup label="Terengganu">
                                <option value="UiTM Kampus Dungun" <?php echo ($prefill_campus === 'UiTM Kampus Dungun') ? 'selected' : ''; ?>>Dungun</option>
                                <option value="UiTM Kampus Kuala Terengganu Cendering" <?php echo ($prefill_campus === 'UiTM Kampus Kuala Terengganu Cendering') ? 'selected' : ''; ?>>Kuala Terengganu Cendering</option>
                                <option value="UiTM Kampus Bukit Besi" <?php echo ($prefill_campus === 'UiTM Kampus Bukit Besi') ? 'selected' : ''; ?>>Bukit Besi</option>
                            </optgroup>
                            <optgroup label="Kelantan">
                                <option value="UiTM Kampus Machang" <?php echo ($prefill_campus === 'UiTM Kampus Machang') ? 'selected' : ''; ?>>Machang</option>
                                <option value="UiTM Kampus Kota Bharu" <?php echo ($prefill_campus === 'UiTM Kampus Kota Bharu') ? 'selected' : ''; ?>>Kota Bharu</option>
                            </optgroup>
                            <optgroup label="Sabah">
                                <option value="UiTM Kampus Kota Kinabalu" <?php echo ($prefill_campus === 'UiTM Kampus Kota Kinabalu') ? 'selected' : ''; ?>>Kota Kinabalu</option>
                                <option value="UiTM Kampus Tawau" <?php echo ($prefill_campus === 'UiTM Kampus Tawau') ? 'selected' : ''; ?>>Tawau</option>
                            </optgroup>
                            <optgroup label="Sarawak">
                                <option value="UiTM Kampus Samarahan" <?php echo ($prefill_campus === 'UiTM Kampus Samarahan') ? 'selected' : ''; ?>>Samarahan</option>
                                <option value="UiTM Kampus Samarahan 2" <?php echo ($prefill_campus === 'UiTM Kampus Samarahan 2') ? 'selected' : ''; ?>>Samarahan 2</option>
                                <option value="UiTM Kampus Mukah" <?php echo ($prefill_campus === 'UiTM Kampus Mukah') ? 'selected' : ''; ?>>Mukah</option>
                            </optgroup>
                        </select>
                    </div>
                </div>

                <div>
                    <label for="nature" class="block text-gray-700 dark:text-slate-300 font-bold mb-2 text-sm">Nature of Feedback</label>
                    <select name="nature" id="nature" required class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all">
                        <option value="" disabled selected>-- Select Nature --</option>
                        <option value="Complaint">Complaint</option>
                        <option value="Suggestion">Suggestion</option>
                        <option value="Compliment">Compliment</option>
                    </select>
                </div>

                <div>
                    <label for="message" class="block text-gray-700 dark:text-slate-300 font-bold mb-2 text-sm">Message</label>
                    <textarea name="message" id="message" maxlength="500" required placeholder="Write your message here... (Max 500 characters)" class="w-full h-36 px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all resize-none placeholder-gray-400 dark:placeholder-slate-500"></textarea>
                </div>

                <button type="submit" class="w-full bg-uitmPurple hover:bg-purple-900 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-purple-900/10 dark:shadow-none hover:scale-[1.01] active:scale-[0.99] transition-all flex items-center justify-center gap-2 cursor-pointer border-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    Submit Feedback
                </button>
            </form>
        </div>

        <!-- Support Info Column (takes 1 col on desktop) -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-gray-100 dark:border-slate-800/80 p-8 transition-colors duration-300 flex flex-col justify-between">
            <div class="space-y-6">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white font-serif flex items-center gap-2">
                    <svg class="w-5 h-5 text-uitmPurple dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Support Information
                </h2>

                <p class="text-sm text-gray-600 dark:text-slate-400 leading-relaxed">
                    For any technical issues, inquiries, or assistance regarding the UiTM STEP system, please contact us directly via email.
                </p>

                <div class="p-4 bg-slate-50 dark:bg-slate-800/50 rounded-xl border border-slate-100 dark:border-slate-800/50 space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-uitmPurple/10 rounded-lg text-uitmPurple dark:text-purple-400 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-gray-500 dark:text-slate-400">Support Email</span>
                            <span class="text-sm font-bold text-gray-800 dark:text-white">uitmstep@gmail.com</span>
                        </div>
                    </div>

                    <div class="flex items-start gap-3">
                        <div class="p-2 bg-uitmPurple/10 rounded-lg text-uitmPurple dark:text-purple-400 mt-0.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <span class="block text-xs font-semibold text-gray-500 dark:text-slate-400">Response Time</span>
                            <span class="text-sm font-bold text-gray-800 dark:text-white">Within 1–3 working days</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-8">
                <a href="mailto:uitmstep@gmail.com" class="w-full text-center bg-slate-50 dark:bg-slate-800 hover:bg-slate-100 dark:hover:bg-slate-750 text-slate-800 dark:text-white border border-slate-200 dark:border-slate-700 font-bold py-3.5 px-4 rounded-xl transition-all inline-block hover:scale-[1.01] active:scale-[0.99] cursor-pointer">
                    Drop Email
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>