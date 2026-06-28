<?php
// banned.php — Notice of Account Suspension & Appeal Form
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

// If not logged in, they shouldn't be here.
// But we allow it if we have a temporary session from a failed login attempt.
if (!isset($_SESSION['user_id'])) {
    redirect('login');
}

$user_id = $_SESSION['user_id'];

// Fetch user data to confirm they are actually banned
$stmt = $pdo->prepare("SELECT name, role FROM users WHERE user_id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user || $user['role'] !== 'banned') {
    redirect('home'); // Not banned? Go home.
}

// Check if they already have a pending or rejected appeal
$stmt = $pdo->prepare("SELECT * FROM ban_appeals WHERE user_id = ? ORDER BY created_at DESC LIMIT 1");
$stmt->execute([$user_id]);
$existing_appeal = $stmt->fetch();

$no_container = true; // Use full-width layout for this notice
require_once 'includes/header.php';
?>

<div class="min-h-[80vh] flex items-center justify-center p-4">
    <div class="max-w-2xl w-full bg-white dark:bg-slate-900 rounded-3xl shadow-2xl border border-gray-100 dark:border-slate-800 overflow-hidden animate-fade-in-up">
        <!-- Serious accent bar -->
        <div class="h-2 bg-gradient-to-r from-slate-700 via-red-600 to-slate-700"></div>

        <div class="p-8 md:p-12">
            <!-- Icon & Header -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-red-100 dark:bg-red-900/30 rounded-3xl flex items-center justify-center mx-auto mb-6">
                    <svg class="w-10 h-10 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white font-serif mb-2">Account Suspended</h1>
                <p class="text-gray-500 dark:text-slate-400">Notice for <span class="font-bold text-gray-800 dark:text-slate-200"><?php echo escape($user['name']); ?></span></p>
            </div>

            <div class="bg-slate-50 dark:bg-slate-800/50 rounded-lg p-6 mb-8 border border-slate-100 dark:border-slate-700/50">
                <h2 class="text-sm font-bold text-slate-700 dark:text-slate-300 uppercase tracking-widest mb-3">Why am I seeing this?</h2>
                <p class="text-slate-600 dark:text-slate-400 text-sm leading-relaxed mb-4">
                    Your UiTM STEP account has been suspended by an administrator for violating our community guidelines or trust protocols. This usually involves reports of scams, fake payment proof, or repeated non-delivery of services.
                </p>
                <div class="flex items-center gap-2 text-xs font-bold text-red-600 dark:text-red-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Your active gigs have been deactivated and hidden from the marketplace.
                </div>
            </div>

            <!-- Appeal Logic -->
            <?php if (!$existing_appeal): ?>
                <div class="space-y-6">
                    <div>
                        <h2 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Submit an Appeal</h2>
                        <p class="text-sm text-gray-500 dark:text-slate-400">If you believe this suspension was a mistake, you may submit a one-time appeal for review.</p>
                    </div>

                    <form action="api/appeal_action" method="POST" class="space-y-4">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                        <div>
                            <textarea
                                name="content"
                                rows="5"
                                required
                                maxlength="2000"
                                placeholder="Explain clearly why your account should be reinstated. Provide evidence if applicable..."
                                class="w-full px-4 py-4 rounded-lg border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 transition-all resize-none"
                            ></textarea>
                            <p class="text-xs text-gray-400 mt-2 text-right">Max 2000 characters</p>
                        </div>
                        <button type="submit" class="w-full bg-uitmPurple text-white font-bold py-4 rounded-lg shadow-xl hover:bg-purple-900 transition-all flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
                            Send Appeal to Administrators
                        </button>
                    </form>
                </div>
            <?php elseif ($existing_appeal['status'] === 'pending'): ?>
                <div class="text-center py-6 bg-indigo-50 dark:bg-indigo-900/20 rounded-lg border border-indigo-100 dark:border-indigo-800">
                    <div class="w-12 h-12 bg-indigo-100 dark:bg-indigo-900/40 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="font-bold text-indigo-900 dark:text-indigo-300">Appeal Under Review</h3>
                    <p class="text-sm text-indigo-700/70 dark:text-indigo-400/70 mt-1 px-8">Your appeal was submitted on <?php echo date('d M Y', strtotime($existing_appeal['created_at'])); ?>. Our team will review it shortly. Please check back later.</p>
                </div>
            <?php else: ?>
                <div class="text-center py-6 bg-red-50 dark:bg-red-900/20 rounded-lg border border-red-100 dark:border-red-800">
                    <div class="w-12 h-12 bg-red-100 dark:bg-red-900/40 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="font-bold text-red-900 dark:text-red-300">Appeal Rejected</h3>
                    <p class="text-sm text-red-700/70 dark:text-red-400/70 mt-1 px-8">Your final appeal has been rejected. This decision is permanent.</p>
                    <?php if ($existing_appeal['admin_note']): ?>
                        <div class="mt-4 pt-4 border-t border-red-100 dark:border-red-900/50 mx-8">
                            <p class="text-xs font-bold text-red-500 uppercase tracking-wider mb-1">Admin Feedback</p>
                            <p class="text-sm italic text-red-800/80 dark:text-red-300/80">"<?php echo escape($existing_appeal['admin_note']); ?>"</p>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div class="mt-10 pt-6 border-t border-gray-100 dark:border-slate-800 text-center">
                <a href="logout" class="text-gray-400 hover:text-uitmPurple text-sm font-bold flex items-center justify-center gap-2 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4-4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    Logout & Return to Home
                </a>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
