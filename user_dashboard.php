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
        <h1 class="text-4xl sm:text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r <?php echo $mode === 'buying' ? 'from-indigo-700 to-blue-500' : 'from-emerald-600 to-teal-400'; ?> font-serif pb-2">
            <?php echo $mode === 'buying' ? 'Buying Dashboard' : 'Selling Dashboard'; ?>
        </h1>
        <p class="text-gray-500 mt-1">
            <?php echo $mode === 'buying' ? 'Track your orders and purchases.' : 'Manage your gigs and incoming orders.'; ?>
        </p>
    </div>
    
    <?php if ($mode === 'selling'): ?>
        <a href="create_gig.php" class="bg-gradient-to-r from-emerald-500 to-teal-400 text-white font-bold py-3 px-6 rounded-2xl hover:shadow-xl hover:scale-105 transition-all duration-300 shadow-md flex items-center gap-2">
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
        <div class="h-1 bg-gradient-to-r from-indigo-500 to-blue-500"></div>
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
            <h2 class="text-xl font-bold text-gray-900">My Purchases</h2>
            <span class="bg-indigo-50 text-indigo-700 text-xs font-bold px-3 py-1 rounded-full border border-indigo-100"><?php echo count($orders); ?> orders</span>
        </div>
        <?php if (count($orders) > 0): ?>
            <div class="p-6 space-y-6">
                <?php foreach($orders as $o): ?>
                    <div class="bg-gray-50 rounded-2xl border border-gray-100 p-6 flex flex-col hover:shadow-md transition-shadow">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 font-serif mb-1"><?php echo escape($o['title']); ?></h3>
                                <p class="text-sm text-gray-500 font-medium">Seller: <span class="text-gray-700"><?php echo escape($o['seller_name']); ?></span></p>
                            </div>
                            <div class="flex-shrink-0">
                                <?php if ($o['status'] === 'delivered'): ?>
                                    <form action="order_action.php" method="POST" class="inline">
                                        <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                                        <input type="hidden" name="action" value="complete">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-xl transition-all shadow-sm text-sm flex items-center gap-2"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Mark Complete</button>
                                    </form>
                                <?php elseif ($o['status'] === 'pending'): ?>
                                    <form action="order_action.php" method="POST" class="inline" onsubmit="return confirm('Cancel this order?');">
                                        <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 font-bold px-4 py-2 rounded-xl transition-all text-sm">Cancel Order</button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>

                        <?php if ($o['status'] !== 'cancelled'): ?>
                            <!-- Step Progress UI -->
                            <?php 
                                $steps = ['pending' => 1, 'paid' => 2, 'delivered' => 3, 'complete' => 4];
                                $current_step = $steps[$o['status']] ?? 1;
                            ?>
                            <div class="relative flex items-center justify-between w-full mt-2">
                                <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-full h-1 bg-gray-200 rounded-full z-0"></div>
                                <div class="absolute left-0 top-1/2 transform -translate-y-1/2 h-1 bg-indigo-500 rounded-full z-0 transition-all duration-500" style="width: <?php echo (($current_step - 1) / 3) * 100; ?>%;"></div>
                                
                                <?php 
                                $step_labels = [
                                    1 => 'Pending',
                                    2 => 'Paid',
                                    3 => 'Delivered',
                                    4 => 'Complete'
                                ];
                                foreach($step_labels as $num => $label): 
                                    $is_completed = $num <= $current_step;
                                    $is_active = $num === $current_step;
                                ?>
                                    <div class="relative z-10 flex flex-col items-center">
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs transition-colors duration-300 <?php echo $is_completed ? 'bg-indigo-600 text-white shadow-md ring-4 ring-white' : 'bg-white text-gray-400 border-2 border-gray-200 ring-4 ring-gray-50'; ?>">
                                            <?php if ($is_completed && !$is_active): ?>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            <?php else: ?>
                                                <?php echo $num; ?>
                                            <?php endif; ?>
                                        </div>
                                        <span class="mt-2 text-xs font-bold <?php echo $is_active ? 'text-indigo-700' : ($is_completed ? 'text-gray-700' : 'text-gray-400'); ?>"><?php echo $label; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="bg-red-50 border border-red-200 rounded-xl p-4 flex items-center gap-3 text-red-700 font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                This order has been cancelled.
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
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
            <div class="h-1 bg-gradient-to-r from-emerald-500 to-teal-400"></div>
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900">My Active Gigs</h2>
                <span class="bg-emerald-50 text-emerald-700 font-bold text-xs px-3 py-1 rounded-full border border-emerald-100"><?php echo count($my_gigs); ?></span>
            </div>
            <?php if(count($my_gigs) > 0): ?>
                <ul class="divide-y divide-gray-50">
                    <?php foreach($my_gigs as $g): ?>
                        <li class="px-6 py-4 flex justify-between items-center hover:bg-gray-50/80 transition-colors duration-200 group">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 group-hover:text-emerald-600 transition-colors"><?php echo escape($g['title']); ?></h3>
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
                    <a href="create_gig.php" class="mt-4 inline-block bg-emerald-500 text-white font-bold px-6 py-2.5 rounded-xl hover:bg-emerald-600 transition-all duration-300 text-sm">Create your first gig</a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Incoming Orders -->
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden animate-fade-in-up" style="animation-delay:100ms;">
            <div class="h-1 bg-gradient-to-r from-teal-400 to-emerald-500"></div>
            <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between">
                <h2 class="text-xl font-bold text-gray-900">Incoming Orders</h2>
                <span class="bg-emerald-50 text-emerald-700 font-bold text-xs px-3 py-1 rounded-full border border-emerald-100"><?php echo count($incoming_orders); ?></span>
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
                                            <div class="w-6 h-6 rounded-full bg-gradient-to-br from-emerald-500 to-teal-400 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                                <?php echo strtoupper(substr($io['buyer_name'], 0, 1)); ?>
                                            </div>
                                            <?php echo escape($io['buyer_name']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <?php if($io['payment_proof_path']): ?>
                                            <a href="<?php echo escape($io['payment_proof_path']); ?>" target="_blank" class="text-emerald-600 hover:text-emerald-800 font-bold underline underline-offset-2 transition-colors">View</a>
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
                                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-1.5 rounded-xl transition-all duration-300 hover:shadow-md text-xs">Mark Delivered</button>
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
