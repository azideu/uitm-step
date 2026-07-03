<?php
// chat.php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

// Only students can chat
if ($_SESSION['role'] !== 'student') {
    redirect('home');
}

$user_id = $_SESSION['user_id'];
$active_chat = isset($_GET['user']) ? (int)$_GET['user'] : 0;

// Fetch unique users the current user has chatted with, ordered by latest message
$stmt_users = $pdo->prepare("
    SELECT u.user_id, u.name, u.profile_picture, MAX(m.timestamp) AS last_msg
    FROM users u
    JOIN messages m ON (u.user_id = m.sender_id OR u.user_id = m.receiver_id)
    WHERE (m.sender_id = :uid1 OR m.receiver_id = :uid2) AND u.user_id != :uid3
    GROUP BY u.user_id, u.name, u.profile_picture
    ORDER BY last_msg DESC
");
$stmt_users->execute(['uid1' => $user_id, 'uid2' => $user_id, 'uid3' => $user_id]);
$chatted_users = $stmt_users->fetchAll();


// If coming from gig details and no prior chat, ensure they are in the list
if ($active_chat > 0) {
    $found = false;
    foreach ($chatted_users as $cu) {
        if ($cu['user_id'] == $active_chat) {
            $found = true; break;
        }
    }
    if (!$found) {
        $stmt = $pdo->prepare("SELECT user_id, name, profile_picture FROM users WHERE user_id = ?");
        $stmt->execute([$active_chat]);
        $new_user = $stmt->fetch();
        if ($new_user) array_unshift($chatted_users, $new_user);
    }
}

// Fetch active chat user info
$active_user_name = '';
$active_user_pic = '';
$active_order_id = null; // order the chat partner has placed on YOUR gig (paid, awaiting delivery)
if ($active_chat > 0) {
    $stmt = $pdo->prepare("SELECT name, profile_picture FROM users WHERE user_id = ?");
    $stmt->execute([$active_chat]);
    $u = $stmt->fetch();
    if ($u) {
        $active_user_name = $u['name'];
        $active_user_pic = $u['profile_picture'];
    }

    // Check if $active_chat has a paid (undelivered) order on any of $user_id's gigs
    $stmt_order = $pdo->prepare("
        SELECT o.order_id
        FROM orders o
        JOIN gigs g ON o.gig_id = g.gig_id
        WHERE o.buyer_id = ? AND g.seller_id = ? AND o.status = 'paid'
        LIMIT 1
    ");
    $stmt_order->execute([$active_chat, $user_id]);
    $pending_order = $stmt_order->fetch();
    if ($pending_order) {
        $active_order_id = $pending_order['order_id'];
    }
}

$no_container = true;
require_once 'includes/header.php';
?>

<div class="px-4 sm:px-6 lg:px-8 py-6 w-full max-w-[96%] xl:max-w-[98%] mx-auto">
    <div class="flex h-[calc(100vh-7rem)] bg-white/80 dark:bg-slate-900/80 backdrop-blur-md rounded-lg shadow-xl border border-white/50 dark:border-slate-700/50 overflow-hidden transition-colors duration-300">
    <!-- Contacts Sidebar -->
    <div class="w-full md:w-1/4 border-r border-gray-100 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-800/50 <?php echo $active_chat > 0 ? 'hidden md:flex' : 'flex'; ?> flex-col transition-colors duration-300">
        <div class="p-5 bg-uitmPurple text-white font-bold text-lg border-b border-purple-900 dark:border-slate-700 shadow-xl flex items-center justify-between">
            <span>Chats</span>
            <svg class="w-5 h-5 text-purple-200 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
        </div>
        <div id="chat-list" class="overflow-y-auto flex-1 p-2 space-y-1">
            <?php if(count($chatted_users) > 0): ?>
                <?php foreach($chatted_users as $cu): ?>
                    <a href="chat?user=<?php echo $cu['user_id']; ?>" data-user-id="<?php echo $cu['user_id']; ?>" class="flex items-center gap-3 p-3 rounded-lg transition-all duration-300 <?php echo ($active_chat == $cu['user_id']) ? 'bg-purple-100/80 dark:bg-slate-700/80 shadow-xl border border-purple-200 dark:border-slate-600' : 'hover:bg-white dark:hover:bg-slate-700 border border-transparent'; ?>">
                        <div class="w-10 h-10 rounded-full bg-uitmPurple flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-inner overflow-hidden">
                            <?php if (!empty($cu['profile_picture'])): ?>
                                <img src="<?php echo asset_url($cu['profile_picture']); ?>" alt="" class="w-full h-full object-cover">
                            <?php else: ?>
                                <?php echo strtoupper(substr($cu['name'], 0, 1)); ?>
                            <?php endif; ?>
                        </div>
                        <div class="overflow-hidden">
                            <h4 class="font-bold text-gray-900 dark:text-white truncate text-sm"><?php echo escape($cu['name']); ?></h4>
                            <p class="text-xs text-gray-400 dark:text-slate-400 truncate mt-0.5">Tap to chat</p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-6 text-gray-400 dark:text-slate-500 text-sm text-center flex flex-col items-center justify-center h-full">
                    <svg class="w-12 h-12 mb-3 text-gray-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    No active chats.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Chat Window -->
    <div class="w-full md:w-3/4 <?php echo $active_chat > 0 ? 'flex' : 'hidden md:flex'; ?> flex-col relative bg-white dark:bg-slate-900 bg-opacity-90 dark:bg-opacity-90 transition-colors duration-300">
        <?php if($active_chat > 0 && $active_user_name): ?>
            <!-- Chat Header -->
            <div class="px-4 sm:px-6 py-4 bg-white dark:bg-slate-900 border-b border-gray-100 dark:border-slate-800 flex justify-between items-center z-10 shadow-xl transition-colors duration-300">
                <a href="<?php echo ROOT_URL; ?>profile?id=<?php echo $active_chat; ?>" class="flex items-center gap-2 sm:gap-3 min-w-0 group/profile" title="View profile">
                    <!-- Back Button on Mobile -->
                    <span onclick="event.preventDefault(); window.location='chat'" class="md:hidden p-1.5 text-gray-500 hover:text-uitmPurple dark:text-slate-400 dark:hover:text-uitmGold transition-colors mr-1" aria-label="Back to contacts">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                        </svg>
                    </span>
                    <div class="w-10 h-10 rounded-full bg-uitmPurple flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-xl overflow-hidden ring-2 ring-transparent group-hover/profile:ring-uitmPurple/40 transition-all">
                        <?php if (!empty($active_user_pic)): ?>
                            <img src="<?php echo asset_url($active_user_pic); ?>" alt="" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?php echo strtoupper(substr($active_user_name, 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0">
                        <h3 class="font-bold text-gray-900 dark:text-white truncate text-sm sm:text-base group-hover/profile:text-uitmPurple dark:group-hover/profile:text-purple-300 transition-colors"><?php echo escape($active_user_name); ?></h3>
                        <span id="chat-status-badge" class="flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-slate-400 mt-0.5">
                            <span id="chat-status-dot" class="inline-block w-2 h-2 rounded-full shrink-0 bg-yellow-400 animate-pulse"></span>
                            <span id="chat-status-text">Connecting…</span>
                        </span>
                    </div>
                </a>
                
                <!-- Quick-Action Panel -->
                <div class="flex gap-2">

                    <?php if ($active_order_id): ?>
                    <a href="<?php echo ROOT_URL; ?>dashboard?mode=selling" class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 text-xs font-bold rounded-lg transition-colors border border-emerald-200 dark:border-emerald-800">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Deliver Order
                    </a>
                    <?php endif; ?>

                    <button onclick="openChatReportModal()" class="flex items-center gap-1.5 px-3 py-1.5 bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 hover:bg-red-100 dark:hover:bg-red-900/40 text-xs font-bold rounded-lg transition-colors border border-red-200 dark:border-red-800/50">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/></svg>
                        Report
                    </button>
                </div>
            </div>
            
            <!-- Chat Messages -->
            <div id="chat-messages" class="flex-1 p-6 overflow-y-auto chat-container bg-slate-50/50 dark:bg-slate-950/50 transition-colors duration-300 relative" data-receiver="<?php echo $active_chat; ?>">
                
                <!-- Safety Tip Banner (Hidden by default) -->
                <div id="safety-tip-banner" class="hidden sticky top-0 z-30 mb-6 animate-fade-in-down">
                    <div class="bg-yellow-50/90 dark:bg-yellow-900/20 backdrop-blur-md border border-yellow-200/50 dark:border-yellow-800/50 rounded-lg p-4 shadow-xl flex items-start gap-4">
                        <div class="w-10 h-10 bg-yellow-400 dark:bg-yellow-600 rounded-lg flex items-center justify-center shrink-0 shadow-lg shadow-yellow-400/20">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 17c-.77 1.333.192 3 1.732 3z"></path></svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-bold text-yellow-800 dark:text-yellow-300 mb-1">Stay Safe on UiTM STEP!</h4>
                            <p class="text-xs text-yellow-700/80 dark:text-yellow-400/70 leading-relaxed">
                                We noticed a mention of external messaging (WhatsApp, Telegram, Discord). 
                                <span class="font-bold">Always keep transactions and chats within STEP</span> to stay protected by our Trust &amp; Safety guarantee.
                            </p>
                        </div>
                        <button onclick="document.getElementById('safety-tip-banner').remove()" class="text-yellow-600 hover:text-yellow-800 dark:text-yellow-500 transition-colors p-1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                        </button>
                    </div>
                </div>

                <!-- Messages injected via JS -->
            </div>

            <!-- Scroll-to-bottom nudge -->
            <button id="scroll-btn" onclick="document.getElementById('chat-messages').scrollTo({top:9999999,behavior:'smooth'})"
                class="absolute bottom-24 right-6 bg-white dark:bg-slate-800 border border-gray-200 dark:border-slate-700 text-uitmPurple dark:text-purple-300 w-10 h-10 rounded-full shadow-2xl flex items-center justify-center hover:bg-gray-50 dark:hover:bg-slate-700 hover:scale-105 transition-all z-20"
                title="Scroll to latest">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
            </button>
            
            <!-- Input Area -->
            <div class="p-4 bg-white dark:bg-slate-900 border-t border-gray-100 dark:border-slate-800 flex gap-3 items-end transition-colors duration-300 relative z-20 shadow-[0_-10px_40px_rgba(0,0,0,0.05)] dark:shadow-[0_-10px_40px_rgba(0,0,0,0.2)]">
                <div class="flex-1 bg-gray-50 dark:bg-slate-800 border border-gray-200 dark:border-slate-700 rounded-lg flex items-center overflow-hidden focus-within:ring-2 focus-within:ring-purple-100 dark:focus-within:ring-slate-700 focus-within:border-uitmPurple focus-within:shadow-2xl transition-all">
                    <input type="text" id="chat-input" placeholder="Message..." class="w-full bg-transparent px-4 py-3 outline-none text-sm text-gray-700 dark:text-slate-200 placeholder-gray-400 dark:placeholder-slate-500" autocomplete="off" onkeypress="if(event.key === 'Enter') sendMessage()">
                </div>
                <button onclick="sendMessage()" class="bg-uitmPurple text-white w-12 h-12 rounded-lg flex items-center justify-center flex-shrink-0 hover:bg-indigo-700 transition-all">
                    <svg class="w-5 h-5 transform rotate-90" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path></svg>
                </button>
            </div>
        <?php else: ?>
            <div class="flex-1 flex flex-col justify-center items-center text-gray-400 dark:text-slate-500 bg-slate-50/30 dark:bg-slate-950/30 transition-colors duration-300 relative overflow-hidden">
                <div class="absolute inset-0 bg-noise opacity-[0.02] mix-blend-overlay pointer-events-none z-0"></div>
                <div class="w-24 h-24 bg-white dark:bg-slate-800 rounded-full shadow-[0_10px_30px_rgba(51,0,102,0.1)] dark:shadow-[0_10px_30px_rgba(0,0,0,0.5)] border border-gray-100 dark:border-slate-700 flex items-center justify-center mb-6 animate-float relative z-10">
                    <svg class="w-12 h-12 text-gray-300 dark:text-slate-600 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-600 dark:text-slate-400 mb-2 font-serif">Your Messages</h3>
                <p class="text-sm">Select a conversation to start chatting securely.</p>
            </div>
        <?php endif; ?>
    </div>
</div>
</div>

<?php if($active_chat > 0): ?>

<!-- Sender safety confirmation modal -->
<div id="unsafe-send-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-2xl max-w-sm w-full p-6 border border-gray-100 dark:border-slate-800 animate-fade-in-down">
        <!-- Icon -->
        <div class="flex items-center justify-center w-14 h-14 bg-amber-100 dark:bg-amber-900/40 rounded-full mx-auto mb-4">
            <svg class="w-7 h-7 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 17c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>
        <!-- Title -->
        <h3 class="text-base font-bold text-gray-900 dark:text-white text-center mb-2">Safety Warning</h3>
        <!-- Body -->
        <p class="text-sm text-gray-500 dark:text-slate-400 text-center leading-relaxed mb-6">
            Your message appears to contain a <span class="font-semibold text-gray-700 dark:text-slate-300">phone number or external link</span>.
            <br><br>
            For your protection, please keep all communication and payments <span class="font-semibold text-gray-700 dark:text-slate-300">within UiTM STEP</span>.
        </p>
        <!-- Actions -->
        <div class="flex gap-3">
            <button id="unsafe-send-cancel"
                class="flex-1 px-4 py-2.5 rounded-xl border border-gray-200 dark:border-slate-700 text-sm font-semibold text-gray-700 dark:text-slate-300 hover:bg-gray-50 dark:hover:bg-slate-800 transition-colors">
                Cancel
            </button>
            <button id="unsafe-send-confirm"
                class="flex-1 px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold transition-colors">
                Send Anyway
            </button>
        </div>
    </div>
</div>

    <?php
    $chat_js_path = __DIR__ . '/assets/js/chat.js';
    $chat_js_version = is_file($chat_js_path) ? filemtime($chat_js_path) : '1';
    ?>
    <script src="assets/js/chat.js?v=<?php echo $chat_js_version; ?>"></script>
<?php endif; ?>

<?php if ($active_chat > 0): ?>
<!-- =====================================================================
     REPORT USER MODAL (Chat)
     ===================================================================== -->
<div
    id="chat-report-modal-overlay"
    onclick="if(event.target===this) closeChatReportModal()"
    class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300"
    aria-modal="true"
    role="dialog"
    aria-labelledby="chat-report-modal-title"
>
    <div
        id="chat-report-modal-panel"
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
                        <h2 id="chat-report-modal-title" class="text-lg font-extrabold text-gray-900 dark:text-white">Report User</h2>
                        <p class="text-sm text-gray-500 dark:text-slate-400">Reporting: <span class="font-bold text-gray-700 dark:text-slate-300"><?php echo escape($active_user_name); ?></span></p>
                    </div>
                </div>
                <button onclick="closeChatReportModal()" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 text-gray-400 dark:text-slate-500 hover:text-gray-700 dark:hover:text-slate-300 transition-colors" aria-label="Close">
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
            <form action="<?php echo ROOT_URL; ?>api/report_action" method="POST" id="chat-report-form">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="reported_id" value="<?php echo $active_chat; ?>">
                <input type="hidden" name="redirect_to" value="<?php echo ROOT_URL; ?>chat?user=<?php echo $active_chat; ?>">

                <!-- Reason -->
                <div class="mb-5">
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-widest mb-3">
                        Reason for report <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 gap-2" id="chat-reason-options">
                        <?php
                        $chat_reasons = [
                            'scam'                  => ['label' => 'Scam / Fraud',                  'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'],
                            'fake_payment_proof'    => ['label' => 'Fake Payment Proof',            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>'],
                            'non_delivery'          => ['label' => 'Did Not Deliver Work',          'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>'],
                            'harassment'            => ['label' => 'Harassment / Threats',          'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'],
                            'inappropriate_content' => ['label' => 'Inappropriate Content',         'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636"></path></svg>'],
                            'other'                 => ['label' => 'Other',                         'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>'],
                        ];
                        foreach ($chat_reasons as $value => $meta):
                        ?>
                        <label class="chat-reason-card flex items-center gap-3 p-3 rounded-lg border border-gray-100 dark:border-slate-800 cursor-pointer hover:border-red-300 dark:hover:border-red-700 hover:bg-red-50/50 dark:hover:bg-red-900/10 transition-all duration-200 has-[:checked]:border-red-500 has-[:checked]:bg-red-50 dark:has-[:checked]:bg-red-900/20 dark:has-[:checked]:border-red-600 group">
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
                    <label for="chat-report-details" class="block text-sm font-bold text-gray-700 dark:text-slate-300 mb-2">
                        Additional details <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <textarea
                        id="chat-report-details"
                        name="details"
                        rows="3"
                        maxlength="1000"
                        placeholder="Describe what happened in as much detail as possible..."
                        class="w-full px-4 py-3 rounded-lg border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-red-400/40 focus:border-red-400 dark:focus:ring-red-800/40 dark:focus:border-red-700 transition-all resize-none"
                    ></textarea>
                    <p class="text-xs text-gray-400 dark:text-slate-500 mt-1 text-right"><span id="chat-report-char-count">0</span>/1000</p>
                </div>

                <!-- Actions -->
                <div class="flex gap-3">
                    <button type="button" onclick="closeChatReportModal()" class="flex-1 py-3 rounded-lg border-2 border-gray-200 dark:border-slate-700 text-gray-600 dark:text-slate-400 font-bold text-sm hover:bg-gray-50 dark:hover:bg-slate-800 transition-all duration-300">
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
    const chatReportOverlay = document.getElementById('chat-report-modal-overlay');
    const chatReportPanel   = document.getElementById('chat-report-modal-panel');
    const chatReportTextarea  = document.getElementById('chat-report-details');
    const chatReportCharCount = document.getElementById('chat-report-char-count');

    function openChatReportModal() {
        chatReportOverlay.classList.remove('opacity-0', 'pointer-events-none');
        chatReportPanel.classList.remove('scale-95');
        chatReportPanel.classList.add('scale-100');
        document.body.style.overflow = 'hidden';
    }

    function closeChatReportModal() {
        chatReportOverlay.classList.add('opacity-0', 'pointer-events-none');
        chatReportPanel.classList.remove('scale-100');
        chatReportPanel.classList.add('scale-95');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeChatReportModal();
    });

    chatReportTextarea.addEventListener('input', function() {
        chatReportCharCount.textContent = this.value.length;
    });
</script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
