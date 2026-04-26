<?php
// chat.php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

// Only students can chat
if ($_SESSION['role'] !== 'student') {
    redirect('index.php');
}

$user_id = $_SESSION['user_id'];
$active_chat = isset($_GET['user']) ? (int)$_GET['user'] : 0;

// Fetch unique users the current user has chatted with
$stmt_users = $pdo->prepare("
    SELECT DISTINCT u.user_id, u.name 
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
        $stmt = $pdo->prepare("SELECT user_id, name FROM users WHERE user_id = ?");
        $stmt->execute([$active_chat]);
        $new_user = $stmt->fetch();
        if ($new_user) array_unshift($chatted_users, $new_user);
    }
}

// Fetch active chat user info
$active_user_name = '';
if ($active_chat > 0) {
    $stmt = $pdo->prepare("SELECT name FROM users WHERE user_id = ?");
    $stmt->execute([$active_chat]);
    $active_user_name = $stmt->fetchColumn();
}

require_once 'includes/header.php';
?>

<div class="flex h-[calc(100vh-8rem)] bg-white/80 backdrop-blur-md rounded-3xl shadow-xl border border-white/50 overflow-hidden">
    <!-- Contacts Sidebar -->
    <div class="w-1/3 md:w-1/4 border-r border-gray-100 bg-gray-50/50 flex flex-col">
        <div class="p-5 bg-gradient-to-r from-uitmPurple to-indigo-800 text-white font-bold text-lg border-b border-purple-900 shadow-sm flex items-center justify-between">
            <span>Chats</span>
            <svg class="w-5 h-5 text-purple-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
        </div>
        <div class="overflow-y-auto flex-1 p-2 space-y-1">
            <?php if(count($chatted_users) > 0): ?>
                <?php foreach($chatted_users as $cu): ?>
                    <a href="chat.php?user=<?php echo $cu['user_id']; ?>" class="flex items-center gap-3 p-3 rounded-xl transition-all duration-300 <?php echo ($active_chat == $cu['user_id']) ? 'bg-purple-100/80 shadow-sm border border-purple-200' : 'hover:bg-white border border-transparent'; ?>">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-br from-uitmPurple to-indigo-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-inner">
                            <?php echo strtoupper(substr($cu['name'], 0, 1)); ?>
                        </div>
                        <div class="overflow-hidden">
                            <h4 class="font-bold text-gray-900 truncate text-sm"><?php echo escape($cu['name']); ?></h4>
                            <p class="text-xs text-gray-400 truncate mt-0.5">Tap to chat</p>
                        </div>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-6 text-gray-400 text-sm text-center flex flex-col items-center justify-center h-full">
                    <svg class="w-12 h-12 mb-3 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    No active chats.
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Chat Window -->
    <div class="w-2/3 md:w-3/4 flex flex-col relative bg-white bg-opacity-90">
        <?php if($active_chat > 0 && $active_user_name): ?>
            <!-- Chat Header -->
            <div class="px-6 py-4 bg-white border-b border-gray-100 flex justify-between items-center z-10 shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-uitmPurple to-indigo-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0 shadow-sm">
                        <?php echo strtoupper(substr($active_user_name, 0, 1)); ?>
                    </div>
                    <div>
                        <h3 class="font-bold text-gray-900"><?php echo escape($active_user_name); ?></h3>
                        <span id="chat-status-badge" class="flex items-center gap-1.5 text-xs font-medium text-gray-500 mt-0.5">
                            <span id="chat-status-dot" class="inline-block w-2 h-2 rounded-full shrink-0 bg-yellow-400 animate-pulse"></span>
                            <span id="chat-status-text">Connecting…</span>
                        </span>
                    </div>
                </div>
                
                <!-- Quick-Action Panel -->
                <div class="flex gap-2">
                    <a href="user_dashboard.php?mode=buying" class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-indigo-50 text-indigo-700 hover:bg-indigo-100 text-xs font-bold rounded-lg transition-colors border border-indigo-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        Upload Proof
                    </a>
                    <a href="user_dashboard.php?mode=selling" class="hidden sm:flex items-center gap-1.5 px-3 py-1.5 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 text-xs font-bold rounded-lg transition-colors border border-emerald-200">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                        Deliver Order
                    </a>
                </div>
            </div>
            
            <!-- Chat Messages -->
            <div id="chat-messages" class="flex-1 p-6 overflow-y-auto chat-container bg-slate-50/50" data-receiver="<?php echo $active_chat; ?>">
                <!-- Messages injected via JS -->
            </div>

            <!-- Scroll-to-bottom nudge -->
            <button id="scroll-btn" onclick="document.getElementById('chat-messages').scrollTo({top:9999999,behavior:'smooth'})"
                class="absolute bottom-24 right-6 bg-white border border-gray-200 text-uitmPurple w-10 h-10 rounded-full shadow-lg flex items-center justify-center hover:bg-gray-50 hover:scale-105 transition-all z-20"
                title="Scroll to latest">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"></path></svg>
            </button>
            
            <!-- Input Area -->
            <div class="p-4 bg-white border-t border-gray-100 flex gap-3 items-end">
                <div class="flex-1 bg-gray-50 border border-gray-200 rounded-2xl flex items-center overflow-hidden focus-within:ring-2 focus-within:ring-purple-100 focus-within:border-uitmPurple transition-all">
                    <input type="text" id="chat-input" placeholder="Message..." class="w-full bg-transparent px-4 py-3 outline-none text-sm text-gray-700" autocomplete="off" onkeypress="if(event.key === 'Enter') sendMessage()">
                </div>
                <button onclick="sendMessage()" class="bg-gradient-to-r from-uitmPurple to-indigo-700 text-white w-12 h-12 rounded-2xl flex items-center justify-center flex-shrink-0 hover:shadow-lg hover:scale-105 transition-all">
                    <svg class="w-5 h-5 transform rotate-90" fill="currentColor" viewBox="0 0 20 20"><path d="M10.894 2.553a1 1 0 00-1.788 0l-7 14a1 1 0 001.169 1.409l5-1.429A1 1 0 009 15.571V11a1 1 0 112 0v4.571a1 1 0 00.725.962l5 1.428a1 1 0 001.17-1.408l-7-14z"></path></svg>
                </button>
            </div>
        <?php else: ?>
            <div class="flex-1 flex flex-col justify-center items-center text-gray-400 bg-slate-50/30">
                <div class="w-24 h-24 bg-white rounded-full shadow-sm border border-gray-100 flex items-center justify-center mb-6">
                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-600 mb-2 font-serif">Your Messages</h3>
                <p class="text-sm">Select a conversation to start chatting securely.</p>
            </div>
        <?php endif; ?>
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
