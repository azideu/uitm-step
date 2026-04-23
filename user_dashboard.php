<?php
// user_dashboard.php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

if ($_SESSION['role'] !== 'student') {
    redirect('index.php');
}

// Toggle Mode
if (isset($_GET['mode']) && in_array($_GET['mode'], ['buying', 'selling'])) {
    $_SESSION['mode'] = $_GET['mode'];
    // redirect to clear query param
    redirect('user_dashboard.php');
}

$mode = $_SESSION['mode'] ?? 'buying';

require_once 'includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 animate-fade-in-up">
    <div>
        <h1 class="text-4xl sm:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-uitmPurple to-indigo-600 font-serif pb-2">
            <?php echo $mode === 'buying' ? 'Buying Dashboard' : 'Selling Dashboard'; ?>
        </h1>
        <p class="text-gray-500 mt-1">
            <?php echo $mode === 'buying' ? 'Track your orders and purchases.' : 'Manage your gigs and incoming orders.'; ?>
        </p>
    </div>
    
    <?php if ($mode === 'selling'): ?>
        <a href="create_gig.php" class="bg-gradient-to-r from-uitmGold to-yellow-400 text-uitmPurple font-bold py-3 px-6 rounded-2xl hover:shadow-xl hover:scale-105 transition-all duration-300 shadow-md flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            Create New Gig
        </a>
    <?php endif; ?>
</div>

<?php if ($mode === 'buying'): ?>
    <?php
    // Fetch user's orders
    $stmt = $pdo->prepare("
        SELECT o.*, g.title, u.name as seller_name 
        FROM orders o
        JOIN gigs g ON o.gig_id = g.gig_id
        JOIN users u ON g.seller_id = u.user_id
        WHERE o.buyer_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $orders = $stmt->fetchAll();
    ?>
    
    <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden animate-fade-in-up">
        <div class="h-1 bg-gradient-to-r from-uitmPurple to-indigo-500"></div>
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">My Orders</h2>
            <span class="bg-purple-100 text-uitmPurple text-xs font-bold px-3 py-1 rounded-full"><?php echo count($orders); ?> orders</span>
        </div>
        <?php if (count($orders) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full">
                    <thead>
                        <tr class="bg-gray-50/70">
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Gig Title</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Seller</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Status</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <?php foreach($orders as $o): ?>
                            <tr class="hover:bg-gray-50/80 transition-colors duration-200">
                                <td class="px-6 py-4 text-sm font-semibold text-gray-900"><?php echo escape($o['title']); ?></td>
                                <td class="px-6 py-4 text-sm text-gray-500"><?php echo escape($o['seller_name']); ?></td>
                                <td class="px-6 py-4">
                                    <?php
                                    $status = $o['status'];
                                    $badge = match($status) {
                                        'pending'   => 'bg-yellow-100 text-yellow-700',
                                        'paid'      => 'bg-blue-100   text-blue-700',
                                        'delivered' => 'bg-indigo-100 text-indigo-700',
                                        'complete'  => 'bg-green-100  text-green-700',
                                        'cancelled' => 'bg-red-100    text-red-700',
                                        default     => 'bg-gray-100   text-gray-600',
                                    };
                                    ?>
                                    <span class="px-3 py-1 inline-flex text-xs leading-5 font-bold rounded-full <?php echo $badge; ?>">
                                        <?php echo ucfirst(escape($status)); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-medium">
                                    <?php if ($o['status'] === 'delivered'): ?>
                                        <!-- State Machine: buyer can only complete a DELIVERED order -->
                                        <form action="order_action.php" method="POST" class="inline">
                                            <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                                            <input type="hidden" name="action" value="complete">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <button type="submit" class="bg-green-500 hover:bg-green-600 text-white font-bold px-4 py-1.5 rounded-xl transition-all duration-300 hover:shadow-md text-xs">Mark Complete</button>
                                        </form>
                                    <?php elseif ($o['status'] === 'paid'): ?>
                                        <!-- Informational badge: order paid, waiting for seller to deliver -->
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1 text-xs font-bold rounded-full bg-blue-50 text-blue-600 border border-blue-200">
                                            <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg>
                                            Waiting for Delivery
                                        </span>
                                    <?php elseif ($o['status'] === 'pending'): ?>
                                        <!-- State Machine: buyer can cancel only PENDING orders -->
                                        <form action="order_action.php" method="POST" class="inline"
                                              onsubmit="return confirm('Cancel this order?');">
                                            <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                                            <input type="hidden" name="action" value="cancel">
                                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                            <button type="submit" class="bg-red-100 hover:bg-red-200 text-red-700 font-bold px-4 py-1.5 rounded-xl transition-all duration-300 text-xs">Cancel Order</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-gray-400 text-xs">—</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="px-6 py-16 text-center">
                <svg class="w-12 h-12 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                <p class="text-gray-500 font-medium">You haven't bought anything yet.</p>
                <a href="marketplace.php" class="mt-4 inline-block bg-uitmPurple text-white font-bold px-6 py-2.5 rounded-xl hover:bg-purple-900 transition-all duration-300 text-sm">Browse Marketplace</a>
            </div>
        <?php endif; ?>
    </div>

<?php else: // SELLING MODE ?>

    <?php
    // Fetch active gigs
    $stmt_gigs = $pdo->prepare("SELECT * FROM gigs WHERE seller_id = ? AND status = 'active' ORDER BY created_at DESC");
    $stmt_gigs->execute([$_SESSION['user_id']]);
    $my_gigs = $stmt_gigs->fetchAll();

    // Fetch incoming orders
    $stmt_orders = $pdo->prepare("
        SELECT o.*, g.title, u.name as buyer_name 
        FROM orders o
        JOIN gigs g ON o.gig_id = g.gig_id
        JOIN users u ON o.buyer_id = u.user_id
        WHERE g.seller_id = ?
        ORDER BY o.created_at DESC
    ");
    $stmt_orders->execute([$_SESSION['user_id']]);
    $incoming_orders = $stmt_orders->fetchAll();
    ?>
    
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- My Gigs -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden animate-fade-in-up">
            <div class="h-1 bg-gradient-to-r from-uitmGold to-yellow-400"></div>
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900">My Active Gigs</h2>
                <span class="bg-purple-100 text-uitmPurple font-bold text-xs px-3 py-1 rounded-full"><?php echo count($my_gigs); ?></span>
            </div>
            <?php if(count($my_gigs) > 0): ?>
                <ul class="divide-y divide-gray-50">
                    <?php foreach($my_gigs as $g): ?>
                        <li class="px-6 py-4 flex justify-between items-center hover:bg-gray-50/80 transition-colors duration-200 group">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 group-hover:text-uitmPurple transition-colors"><?php echo escape($g['title']); ?></h3>
                                <p class="text-xs text-gray-400 mt-1 font-medium">RM <?php echo number_format($g['price'], 2); ?> &bull; <?php echo escape($g['category']); ?></p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 text-xs font-bold rounded-full bg-green-100 text-green-700 flex-shrink-0">
                                    <?php echo ucfirst(escape($g['status'])); ?>
                                </span>
                                
                                <form action="gig_action.php" method="POST" onsubmit="return confirm('Are you sure you want to delete this gig? This action cannot be undone.');" class="inline">
                                    <input type="hidden" name="gig_id" value="<?php echo $g['gig_id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors p-1" title="Delete Gig">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="px-6 py-16 text-center">
                    <svg class="w-12 h-12 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <p class="text-gray-500 font-medium">You don't have any gigs yet.</p>
                    <a href="create_gig.php" class="mt-4 inline-block bg-uitmGold text-uitmPurple font-bold px-6 py-2.5 rounded-xl hover:bg-yellow-400 transition-all duration-300 text-sm">Create your first gig</a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Incoming Orders -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden animate-fade-in-up" style="animation-delay:100ms;">
            <div class="h-1 bg-gradient-to-r from-uitmPurple to-indigo-500"></div>
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900">Incoming Orders</h2>
                <span class="bg-purple-100 text-uitmPurple font-bold text-xs px-3 py-1 rounded-full"><?php echo count($incoming_orders); ?></span>
            </div>
            <?php if(count($incoming_orders) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50/70">
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Order</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Buyer</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Proof</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase tracking-widest">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <?php foreach($incoming_orders as $io): ?>
                                <tr class="hover:bg-gray-50/80 transition-colors duration-200">
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900 max-w-[140px] truncate"><?php echo escape($io['title']); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-uitmPurple to-indigo-600 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                                <?php echo strtoupper(substr($io['buyer_name'], 0, 1)); ?>
                                            </div>
                                            <?php echo escape($io['buyer_name']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <?php if($io['payment_proof_path']): ?>
                                            <a href="<?php echo escape($io['payment_proof_path']); ?>" target="_blank" class="text-uitmPurple hover:text-indigo-700 font-bold underline underline-offset-2 transition-colors">View</a>
                                        <?php else: ?>
                                            <span class="text-gray-300">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <?php if ($io['status'] === 'paid'): ?>
                                            <form action="order_action.php" method="POST" class="inline">
                                                <input type="hidden" name="order_id" value="<?php echo $io['order_id']; ?>">
                                                <input type="hidden" name="action" value="deliver">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <button type="submit" class="bg-uitmPurple hover:bg-purple-900 text-white font-bold px-4 py-1.5 rounded-xl transition-all duration-300 hover:shadow-md text-xs">Mark Delivered</button>
                                            </form>
                                        <?php else: ?>
                                            <?php
                                            $s = $io['status'];
                                            $b = match($s) {
                                                'pending'   => 'bg-yellow-100 text-yellow-700',
                                                'delivered' => 'bg-indigo-100 text-indigo-700',
                                                'complete'  => 'bg-green-100  text-green-700',
                                                'cancelled' => 'bg-red-100    text-red-700',
                                                default     => 'bg-gray-100   text-gray-600',
                                            };
                                            ?>
                                            <span class="px-3 py-1 inline-flex text-xs font-bold rounded-full <?php echo $b; ?>">
                                                <?php echo ucfirst(escape($s)); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="px-6 py-16 text-center">
                    <svg class="w-12 h-12 text-gray-200 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    <p class="text-gray-500 font-medium">No incoming orders right now.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
