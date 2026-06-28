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

// Fetch unique users the current user has chatted with
$stmt_users = $pdo->prepare("
    SELECT DISTINCT u.user_id, u.name, u.profile_picture 
    FROM users u 
    JOIN messages m ON (u.user_id = m.sender_id OR u.user_id = m.receiver_id) 
    WHERE (m.sender_id = :uid1 OR m.receiver_id = :uid2) AND u.user_id != :uid3
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
if ($active_chat > 0) {
    $stmt = $pdo->prepare("SELECT name, profile_picture FROM users WHERE user_id = ?");
    $stmt->execute([$active_chat]);
    $u = $stmt->fetch();
    if ($u) {
        $active_user_name = $u['name'];
        $active_user_pic = $u['profile_picture'];
    }
}

$no_container = true;
require_once 'includes/header.php';
?>

<div class="px-4 sm:px-6 lg:px-8 py-6 w-full max-w-[96%] xl:max-w-[98%] mx-auto">
    <div class="flex h-[calc(100vh-7rem)] bg-white/80 dark:bg-slate-900/80 backdrop-blur-md rounded-lg shadow-xl border border-white/50 dark:border-slate-700/50 overflow-hidden transition-colors duration-300">
    <!-- Contacts Sidebar -->
    <div class="w-1/3 md:w-1/4 border-r border-gray-100 dark:border-slate-800 bg-gray-50/50 dark:bg-slate-800/50 flex flex-col transition-colors duration-300">
        <div class="p-5 bg-uitmPurple text-white font-bold text-lg border-b border-purple-900 dark:border-slate-700 shadow-xl flex items-center justify-between">
            <span>Chats</span>
            <svg class="w-5 h-5 text-purple-200 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
        </div>
        <div class="overflow-y-auto flex-1 p-2 space-y-1">
            <?php if(count($chatted_users) > 0): ?>
                <?php foreach($chatted_users as $cu): ?>
                    <a href="chat?user=<?php echo $cu['user_id']; ?>" class="flex items-center gap-3 p-3 rounded-lg transition-all duration-300 <?php echo ($active_chat == $cu['user_id']) ? 'bg-purple-100/80 dark:bg-slate-700/80 shadow-xl border border-purple-200 dark:border-slate-600' : 'hover:bg-white dark:hover:bg-slate-700 border border-transparent'; ?>">
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
    <div class="w-2/3 md:w-3/4 flex flex-col relative bg-white dark:bg-slate-900 bg-opacity-90 dark:bg-opacity-90 transition-colors duration-300">
        <?php if($active_chat > 0 && $active_user_name): ?>
            <!-- Chat Header -->
            <div class="px-6 py-4 bg-white dark:bg-slate-900 border-b border-gray-100 dark:border-slate-800 flex justify-between items-center z-10 shadow-xl transition-colors duration-300">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-uitmPurple flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-xl overflow-hidden">
                        <?php if (!empty($active_user_pic)): ?>
                            <img src="<?php echo asset_url($active_user_pic); ?>" alt="" class="w-full h-full object-cover">
                        <?php else: ?>
                            <?php echo strtoupper(substr($active_user_name, 0, 1)); ?>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white"><?php echo escape($active_user_name); ?></h3>
                        <span id="chat-status-badge" class="flex items-center gap-1.5 text-xs font-medium text-gray-500 dark:text-slate-400 mt-0.5">
                            <span id="chat-status-dot" class="inline-block w-2 h-2 rounded-full shrink-0 bg-yellow-400 animate-pulse"></span>
                            <span id="chat-status-text">Connecting…</span>
                        </span>
                    </div>
                </div>
                
                <!-- Quick-Action Panel -->
                <div class="flex gap-2">
                    <a href="dashboard?mode=buying" class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 hover:bg-indigo-100 dark:hover:bg-indigo-900/50 text-xs font-bold rounded-lg transition-colors border border-indigo-200 dark:border-indigo-800">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Upload Proof
                    </a>
                    <a href="dashboard?mode=selling" class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 hover:bg-emerald-100 dark:hover:bg-emerald-900/50 text-xs font-bold rounded-lg transition-colors border border-emerald-200 dark:border-emerald-800">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Deliver Order
                    </a>
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
    <?php
    $chat_js_path = __DIR__ . '/assets/js/chat.js';
    $chat_js_version = is_file($chat_js_path) ? filemtime($chat_js_path) : '1';
    ?>
    <script src="assets/js/chat.js?v=<?php echo $chat_js_version; ?>"></script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
