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

<!-- HI -->

<div class="flex h-[calc(100vh-8rem)] bg-white rounded shadow-lg overflow-hidden border">
    <!-- Contacts Sidebar -->
    <div class="w-1/3 border-r bg-gray-50 flex flex-col">
        <div class="p-4 bg-uitmPurple text-white font-bold border-b">Conversations</div>
        <div class="overflow-y-auto flex-1">
            <?php if(count($chatted_users) > 0): ?>
                <?php foreach($chatted_users as $cu): ?>
                    <a href="chat.php?user=<?php echo $cu['user_id']; ?>" class="block p-4 border-b hover:bg-gray-100 transition whitespace-nowrap text-ellipsis overflow-hidden <?php echo ($active_chat == $cu['user_id']) ? 'bg-purple-100 font-bold' : ''; ?>">
                        <?php echo escape($cu['name']); ?>
                    </a>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="p-4 text-gray-500 text-sm text-center">No active chats. Contact a seller to start!</div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Chat Window -->
    <div class="w-2/3 flex flex-col bg-white relative">
        <?php if($active_chat > 0 && $active_user_name): ?>
            <!-- Chat Header -->
            <div class="p-4 bg-gray-50 border-b font-bold text-gray-800 flex justify-between items-center z-10 shadow-sm">
                <span><?php echo escape($active_user_name); ?></span>
                <span id="chat-status-badge" class="flex items-center gap-1.5 bg-gray-100 border border-gray-200 text-gray-600 text-xs font-medium px-2.5 py-1 rounded-full">
                    <span id="chat-status-dot" class="inline-block w-2 h-2 rounded-full shrink-0 bg-yellow-400 animate-pulse"></span>
                    <span id="chat-status-text">Connecting…</span>
                </span>
            </div>
            
            <!-- Chat Messages -->
            <div id="chat-messages" class="flex-1 p-4 overflow-y-auto chat-container bg-white" data-receiver="<?php echo $active_chat; ?>">
                <!-- Messages injected via JS -->
            </div>

            <!-- Scroll-to-bottom nudge (shown by JS when user scrolls up) -->
            <button id="scroll-btn" onclick="document.getElementById('chat-messages').scrollTo({top:9999999,behavior:'smooth'})"
                class="absolute bottom-20 right-6 bg-uitmPurple text-white w-9 h-9 rounded-full shadow-lg flex items-center justify-center hover:bg-purple-900 transition z-20"
                title="Scroll to latest">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            
            <!-- Input Area -->
            <div class="p-4 bg-gray-50 border-t flex gap-2">
                <input type="text" id="chat-input" placeholder="Type a message..." class="flex-1 border rounded px-3 py-2 outline-none focus:ring focus:border-uitmPurple bg-white" autocomplete="off" onkeypress="if(event.key === 'Enter') sendMessage()">
                <button onclick="sendMessage()" class="bg-uitmPurple text-white px-4 py-2 rounded focus:outline-none hover:bg-purple-900 transition">Send</button>
            </div>
        <?php else: ?>
            <div class="flex-1 flex flex-col justify-center items-center text-gray-400">
                <svg class="w-24 h-24 mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                <p>Select a conversation to start chatting.</p>
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
