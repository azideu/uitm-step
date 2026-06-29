<?php
// dashboard.php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

if ($_SESSION['role'] !== 'student') {
    redirect('home');
}

// Toggle Mode
if (isset($_GET['mode']) && in_array($_GET['mode'], ['buying', 'selling'])) {
    $_SESSION['mode'] = $_GET['mode'];
    // redirect to clear query param
    redirect('dashboard');
}

$mode = $_SESSION['mode'] ?? 'buying';

// Fetch Search and Filter query params
$search = trim($_GET['search'] ?? '');
$status_filter = trim($_GET['status'] ?? '');

require_once 'includes/header.php';
?>

<!-- Include Chart.js via CDN for Selling analytics -->
<?php if ($mode === 'selling'): ?>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<?php endif; ?>

<!-- Header Section -->
<div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 animate-fade-in-up opacity-0 transition-colors duration-300" style="animation-fill-mode: forwards;">
    <div>
        <h1 class="text-4xl sm:text-5xl font-extrabold <?php echo $mode === 'buying' ? 'text-indigo-700 dark:text-indigo-400' : 'text-emerald-600 dark:text-emerald-400'; ?> font-serif pb-2">
            <?php echo $mode === 'buying' ? 'Buying Dashboard' : 'Selling Dashboard'; ?>
        </h1>
        <p class="text-gray-500 dark:text-slate-400 mt-1 transition-colors duration-300">
            <?php echo $mode === 'buying' ? 'Track your orders and purchases.' : 'Manage your gigs, earnings, and incoming orders.'; ?>
        </p>
    </div>
    
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
        <?php if ($mode === 'selling'): ?>
            <a href="<?php echo ROOT_URL; ?>profile?preview=true" class="bg-slate-100 hover:bg-gray-200 dark:bg-slate-800 dark:hover:bg-slate-700 text-gray-700 dark:text-white border border-gray-200 dark:border-slate-700 font-bold py-2.5 px-5 rounded-lg transition-all duration-300 shadow-md flex items-center gap-2 transform hover:-translate-y-0.5 text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                Live Preview Profile
            </a>
            <a href="<?php echo ROOT_URL; ?>gigs/create" class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-2.5 px-5 rounded-lg transition-all duration-300 shadow-lg hover:shadow-emerald-500/20 flex items-center gap-2 transform hover:-translate-y-0.5 text-sm">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create New Gig
            </a>
        <?php endif; ?>
        <?php $toggle_mode = ($mode === 'buying' ? 'selling' : 'buying'); ?>
        <a href="?mode=<?php echo $toggle_mode; ?>" class="bg-gray-100 dark:bg-slate-800 p-1.5 rounded-xl flex items-center shadow-inner border border-gray-200 dark:border-slate-700/60 hover:opacity-95 transition-all">
            <span class="px-5 py-2 rounded-lg text-sm font-bold transition-all duration-300 <?php echo $mode === 'buying' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow-md' : 'text-gray-400 hover:text-gray-600 dark:text-slate-400 dark:hover:text-slate-300'; ?>">
                Buying
            </span>
            <span class="px-5 py-2 rounded-lg text-sm font-bold transition-all duration-300 <?php echo $mode === 'selling' ? 'bg-white dark:bg-slate-700 text-emerald-600 dark:text-emerald-400 shadow-md' : 'text-gray-400 hover:text-gray-600 dark:text-slate-400 dark:hover:text-slate-300'; ?>">
                Selling
            </span>
        </a>
    </div>
</div>

<?php if ($mode === 'buying'): ?>
    <?php
    // Fetch stats for Buying
    $stmt_stats = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN o.status IN ('pending', 'paid', 'delivered') THEN 1 END) as active_purchases,
            COUNT(CASE WHEN o.status = 'complete' THEN 1 END) as completed_purchases,
            SUM(CASE WHEN o.status IN ('paid', 'delivered', 'complete') THEN g.price ELSE 0 END) as total_spent
        FROM orders o
        JOIN gigs g ON o.gig_id = g.gig_id
        WHERE o.buyer_id = ?
    ");
    $stmt_stats->execute([$_SESSION['user_id']]);
    $buy_stats = $stmt_stats->fetch() ?: ['active_purchases' => 0, 'completed_purchases' => 0, 'total_spent' => 0];

    // Fetch user's orders with search & status filters applied
    $sql_orders = "
        SELECT o.*, g.title, g.seller_id, u.name as seller_name,
               CASE WHEN r.review_id IS NOT NULL THEN 1 ELSE 0 END as has_review
        FROM orders o
        JOIN gigs g ON o.gig_id = g.gig_id
        JOIN users u ON g.seller_id = u.user_id
        LEFT JOIN reviews r ON o.order_id = r.order_id
        WHERE o.buyer_id = ?
    ";
    $params_orders = [$_SESSION['user_id']];
    if (!empty($search)) {
        $sql_orders .= " AND (g.title LIKE ? OR u.name LIKE ?)";
        $params_orders[] = "%$search%";
        $params_orders[] = "%$search%";
    }
    if (!empty($status_filter)) {
        $sql_orders .= " AND o.status = ?";
        $params_orders[] = $status_filter;
    }
    $sql_orders .= " ORDER BY o.created_at DESC";
    $stmt = $pdo->prepare($sql_orders);
    $stmt->execute($params_orders);
    $orders = $stmt->fetchAll();
    ?>

    <!-- Stats Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 animate-fade-in-up opacity-0" style="animation-fill-mode: forwards; animation-delay: 75ms;">
        <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-2xl p-6 shadow-md hover:shadow-lg transition-all duration-300 flex items-center gap-5 group">
            <div class="p-4 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-xl group-hover:scale-110 transition-transform duration-350">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            </div>
            <div>
                <span class="block text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Total Spent</span>
                <span class="text-3xl font-extrabold text-gray-900 dark:text-white font-serif tracking-tight mt-0.5 block">RM <?php echo number_format($buy_stats['total_spent'] ?: 0, 2); ?></span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-2xl p-6 shadow-md hover:shadow-lg transition-all duration-300 flex items-center gap-5 group">
            <div class="p-4 bg-amber-50 dark:bg-amber-950/30 text-amber-500 dark:text-amber-400 rounded-xl group-hover:scale-110 transition-transform duration-350">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <span class="block text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Active Purchases</span>
                <span class="text-3xl font-extrabold text-gray-900 dark:text-white font-serif tracking-tight mt-0.5 block"><?php echo (int)$buy_stats['active_purchases']; ?></span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-2xl p-6 shadow-md hover:shadow-lg transition-all duration-300 flex items-center gap-5 group">
            <div class="p-4 bg-emerald-50 dark:bg-emerald-950/30 text-emerald-600 dark:text-emerald-400 rounded-xl group-hover:scale-110 transition-transform duration-350">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            </div>
            <div>
                <span class="block text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Completed Purchases</span>
                <span class="text-3xl font-extrabold text-gray-900 dark:text-white font-serif tracking-tight mt-0.5 block"><?php echo (int)$buy_stats['completed_purchases']; ?></span>
            </div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-gray-200 dark:border-slate-800/80 p-5 mb-8 flex flex-col md:flex-row gap-4 items-center justify-between transition-colors duration-300 animate-fade-in-up opacity-0" style="animation-fill-mode: forwards; animation-delay: 150ms;">
        <form method="GET" class="flex flex-col md:flex-row w-full gap-4">
            <div class="flex-grow relative w-full">
                <input type="text" name="search" value="<?php echo escape($search); ?>" placeholder="Search purchases by gig title or seller..." class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-800/50 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition-all placeholder-gray-400 dark:placeholder-slate-500 text-sm">
                <svg class="absolute left-4 top-3.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <div class="w-full md:w-52">
                <select name="status" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition-all text-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php if ($status_filter === 'pending') echo 'selected'; ?>>Pending</option>
                    <option value="paid" <?php if ($status_filter === 'paid') echo 'selected'; ?>>Paid</option>
                    <option value="delivered" <?php if ($status_filter === 'delivered') echo 'selected'; ?>>Delivered</option>
                    <option value="complete" <?php if ($status_filter === 'complete') echo 'selected'; ?>>Complete</option>
                    <option value="cancelled" <?php if ($status_filter === 'cancelled') echo 'selected'; ?>>Cancelled</option>
                </select>
            </div>
            <div class="flex w-full md:w-auto gap-2">
                <button type="submit" class="flex-grow md:flex-grow-0 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-xl transition-all duration-300 text-sm cursor-pointer border-0 shadow-md shadow-indigo-600/10">
                    Filter
                </button>
                <?php if (!empty($search) || !empty($status_filter)): ?>
                    <a href="dashboard" class="flex-grow md:flex-grow-0 text-center bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 dark:hover:bg-slate-850 text-gray-700 dark:text-white border border-gray-200 dark:border-slate-700 font-bold py-3 px-5 rounded-xl transition-all text-sm inline-block">
                        Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <!-- Purchases Container -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-gray-200 dark:border-slate-800/80 overflow-hidden animate-fade-in-up opacity-0 transition-colors duration-300" style="animation-fill-mode: forwards; animation-delay: 225ms;">
        <div class="h-1.5 bg-gradient-to-r from-indigo-500 to-purple-500"></div>
        <div class="px-6 py-5 border-b border-gray-100 dark:border-slate-800/80 flex items-center justify-between transition-colors duration-300">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">My Purchases</h2>
            <span class="bg-indigo-50 dark:bg-indigo-950/50 text-indigo-700 dark:text-indigo-300 text-xs font-bold px-3 py-1.5 rounded-lg border border-indigo-100 dark:border-indigo-900/30"><?php echo count($orders); ?> orders</span>
        </div>
        <?php if (count($orders) > 0): ?>
            <div class="p-6 space-y-6">
                <?php foreach($orders as $o): ?>
                    <div class="bg-slate-555/5 dark:bg-slate-800/30 rounded-2xl border border-gray-200 dark:border-slate-800/70 p-6 flex flex-col hover:border-indigo-400 dark:hover:border-indigo-900/50 hover:shadow-xl transition-all duration-350 animate-fade-in-up">
                        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-start gap-4 mb-6">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white font-serif mb-1.5"><?php echo escape($o['title']); ?></h3>
                                <p class="text-xs text-gray-400 dark:text-slate-500 font-bold uppercase tracking-wider">Seller: <span class="text-gray-700 dark:text-slate-300 font-sans normal-case ml-1"><?php echo escape($o['seller_name']); ?></span></p>
                            </div>
                            <div class="flex items-center gap-2.5 flex-wrap w-full sm:w-auto">
                                <!-- Chat with Seller Button -->
                                <a href="chat?user=<?php echo $o['seller_id']; ?>" class="bg-indigo-50 hover:bg-indigo-100 dark:bg-indigo-950/40 dark:hover:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400 border border-indigo-200/50 dark:border-indigo-900/40 font-bold px-5 py-2.5 rounded-xl transition-all text-sm flex items-center gap-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                                    Chat with Seller
                                </a>

                                <?php if ($o['status'] === 'complete'): ?>
                                    <?php if ($o['has_review']): ?>
                                        <!-- Reviewed Status Indicator -->
                                        <span class="bg-slate-100 dark:bg-slate-800 text-gray-500 dark:text-slate-400 border border-gray-200 dark:border-slate-700/60 font-bold px-5 py-2.5 rounded-xl text-sm flex items-center gap-2">
                                            <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                            Reviewed
                                        </span>
                                    <?php else: ?>
                                        <!-- Review Gig Button -->
                                        <a href="gigs/details?id=<?php echo $o['gig_id']; ?>#review-form" class="bg-amber-50 hover:bg-amber-100 dark:bg-amber-950/40 dark:hover:bg-amber-900/40 text-amber-600 dark:text-amber-400 border border-amber-200/50 dark:border-amber-900/40 font-bold px-5 py-2.5 rounded-xl transition-all text-sm flex items-center gap-2 cursor-pointer transform hover:-translate-y-0.5 shadow-md shadow-amber-500/5">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                                            Review Gig
                                        </a>
                                    <?php endif; ?>
                                <?php endif; ?>

                                <?php if ($o['status'] === 'delivered'): ?>
                                    <form action="api/order_action" method="POST" class="inline">
                                        <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                                        <input type="hidden" name="action" value="complete">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-5 py-2.5 rounded-xl transition-all shadow-md shadow-indigo-600/15 text-sm flex items-center gap-2 border-0 cursor-pointer transform hover:-translate-y-0.5"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Mark Complete</button>
                                    </form>
                                <?php elseif ($o['status'] === 'pending'): ?>
                                    <!-- Continue Payment Button -->
                                    <a href="payment-gateway?order_id=<?php echo $o['order_id']; ?>" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-5 py-2.5 rounded-xl transition-all shadow-md shadow-emerald-700/10 text-sm flex items-center gap-2 cursor-pointer transform hover:-translate-y-0.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                                        Continue Payment
                                    </a>

                                    <form action="api/order_action" method="POST" class="inline" onsubmit="return confirm('Cancel this order?');">
                                        <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <button type="submit" class="bg-red-50 hover:bg-red-100 dark:bg-red-950/20 dark:hover:bg-red-950/40 text-red-600 dark:text-red-400 border border-red-200/50 dark:border-red-900/30 font-bold px-5 py-2.5 rounded-xl transition-all text-sm cursor-pointer">Cancel Order</button>
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
                            <div class="relative flex items-center justify-between w-full mt-4 px-4 sm:px-12">
                                <div class="absolute left-4 sm:left-12 right-4 sm:right-12 top-1/2 transform -translate-y-1/2 h-1 z-0">
                                    <div class="w-full h-full bg-gray-200 dark:bg-slate-800 rounded-full transition-colors duration-300"></div>
                                    <div class="absolute left-0 top-0 h-full bg-indigo-500 rounded-full transition-all duration-500" style="width: <?php echo (($current_step - 1) / 3) * 100; ?>%;"></div>
                                </div>
                                
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
                                        <div class="w-9 h-9 rounded-full flex items-center justify-center font-bold text-xs transition-all duration-300 <?php echo $is_completed ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-600/30 ring-4 ring-white dark:ring-slate-900' : 'bg-white dark:bg-slate-800 text-gray-400 dark:text-slate-500 border-2 border-gray-200 dark:border-slate-700 ring-4 ring-slate-50 dark:ring-slate-900'; ?>">
                                            <?php if ($is_completed && !$is_active): ?>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            <?php else: ?>
                                                <?php echo $num; ?>
                                            <?php endif; ?>
                                        </div>
                                        <span class="mt-2.5 text-[11px] sm:text-xs font-bold <?php echo $is_active ? 'text-indigo-700 dark:text-indigo-400' : ($is_completed ? 'text-gray-700 dark:text-slate-300' : 'text-gray-405 dark:text-slate-500'); ?> transition-colors duration-300"><?php echo $label; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="bg-red-50 dark:bg-red-950/20 border border-red-200/50 dark:border-red-900/30 rounded-xl p-4 flex items-center gap-3 text-red-700 dark:text-red-400 font-bold transition-colors duration-300 mt-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                This order has been cancelled.
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="px-6 py-20 text-center">
                <svg class="w-16 h-16 text-gray-200 dark:text-slate-700 mx-auto mb-4 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">No purchases found</h3>
                <p class="text-gray-500 dark:text-slate-400 font-medium max-w-xs mx-auto text-sm">No items match your filters or search query.</p>
                <a href="marketplace" class="mt-5 inline-block bg-indigo-600 text-white font-bold px-6 py-3 rounded-xl hover:bg-indigo-700 transition-all duration-300 text-sm shadow-md shadow-indigo-600/10">Browse Marketplace</a>
            </div>
        <?php endif; ?>
    </div>

<?php else: // SELLING MODE ?>

    <?php
    // Fetch stats for Selling
    $stmt_gig_count = $pdo->prepare("SELECT COUNT(*) FROM gigs WHERE seller_id = ? AND status = 'active'");
    $stmt_gig_count->execute([$_SESSION['user_id']]);
    $active_gigs_count = $stmt_gig_count->fetchColumn();

    $stmt_sale_stats = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN o.status IN ('pending', 'paid', 'delivered') THEN 1 END) as active_sales,
            COUNT(CASE WHEN o.status = 'complete' THEN 1 END) as completed_sales,
            SUM(CASE WHEN o.status IN ('paid', 'delivered', 'complete') THEN g.price ELSE 0 END) as total_earned
        FROM orders o
        JOIN gigs g ON o.gig_id = g.gig_id
        WHERE g.seller_id = ?
    ");
    $stmt_sale_stats->execute([$_SESSION['user_id']]);
    $sell_stats = $stmt_sale_stats->fetch() ?: ['active_sales' => 0, 'completed_sales' => 0, 'total_earned' => 0];

    // Fiverr-Style Advanced Analytics Data Aggregations
    // 1. Average Rating Received
    $stmt_rating = $pdo->prepare("
        SELECT AVG(rating) as avg_rating, COUNT(*) as review_count
        FROM reviews
        WHERE seller_id = ?
    ");
    $stmt_rating->execute([$_SESSION['user_id']]);
    $rating_data = $stmt_rating->fetch() ?: ['avg_rating' => 0, 'review_count' => 0];
    $avg_rating = $rating_data['avg_rating'] ? round($rating_data['avg_rating'], 1) : 0.0;
    $review_count = $rating_data['review_count'] ?: 0;

    // 2. Order Completion Rate (Completed / (Completed + Cancelled))
    $stmt_completion = $pdo->prepare("
        SELECT 
            COUNT(CASE WHEN o.status = 'complete' THEN 1 END) as completed,
            COUNT(CASE WHEN o.status = 'cancelled' THEN 1 END) as cancelled
        FROM orders o
        JOIN gigs g ON o.gig_id = g.gig_id
        WHERE g.seller_id = ?
    ");
    $stmt_completion->execute([$_SESSION['user_id']]);
    $completion_data = $stmt_completion->fetch() ?: ['completed' => 0, 'cancelled' => 0];
    $completed_count = $completion_data['completed'] ?: 0;
    $cancelled_count = $completion_data['cancelled'] ?: 0;
    $total_resolved_orders = $completed_count + $cancelled_count;
    $completion_rate = $total_resolved_orders > 0 ? round(($completed_count / $total_resolved_orders) * 100) : 100;

    // 3. Average Order Value (AOV)
    $stmt_aov = $pdo->prepare("
        SELECT AVG(g.price) as avg_value
        FROM orders o
        JOIN gigs g ON o.gig_id = g.gig_id
        WHERE g.seller_id = ? AND o.status IN ('paid', 'delivered', 'complete')
    ");
    $stmt_aov->execute([$_SESSION['user_id']]);
    $avg_order_value = $stmt_aov->fetchColumn() ?: 0.0;

    // 4. Earnings Breakdown (Pending Clearance vs Cleared Earnings)
    $stmt_earnings = $pdo->prepare("
        SELECT 
            SUM(CASE WHEN o.status = 'complete' THEN g.price ELSE 0 END) as cleared_earnings,
            SUM(CASE WHEN o.status IN ('paid', 'delivered') THEN g.price ELSE 0 END) as pending_clearance
        FROM orders o
        JOIN gigs g ON o.gig_id = g.gig_id
        WHERE g.seller_id = ?
    ");
    $stmt_earnings->execute([$_SESSION['user_id']]);
    $earnings_breakdown = $stmt_earnings->fetch() ?: ['cleared_earnings' => 0, 'pending_clearance' => 0];
    $cleared_earnings = $earnings_breakdown['cleared_earnings'] ?: 0;
    $pending_clearance = $earnings_breakdown['pending_clearance'] ?: 0;

    // 5. Monthly Earnings Trend (Last 6 Months aligned dynamically)
    $stmt_monthly = $pdo->prepare("
        SELECT 
            DATE_FORMAT(o.created_at, '%Y-%m') as month_key,
            SUM(g.price) as monthly_sum
        FROM orders o
        JOIN gigs g ON o.gig_id = g.gig_id
        WHERE g.seller_id = ? AND o.status IN ('paid', 'delivered', 'complete')
        GROUP BY month_key
        ORDER BY month_key ASC
    ");
    $stmt_monthly->execute([$_SESSION['user_id']]);
    $monthly_raw = $stmt_monthly->fetchAll();

    $earnings_chart_data = [];
    for ($i = 5; $i >= 0; $i--) {
        $month_time = strtotime("-$i months");
        $key = date('Y-m', $month_time);
        $label = date('M Y', $month_time);
        $earnings_chart_data[$key] = [
            'label' => $label,
            'value' => 0.00
        ];
    }
    foreach ($monthly_raw as $row) {
        if (isset($earnings_chart_data[$row['month_key']])) {
            $earnings_chart_data[$row['month_key']]['value'] = (float)$row['monthly_sum'];
        }
    }

    // 6. Gig Performance Breakdown (Revenue by Gig)
    $stmt_gig_perf = $pdo->prepare("
        SELECT 
            g.title,
            SUM(CASE WHEN o.status IN ('paid', 'delivered', 'complete') THEN g.price ELSE 0 END) as revenue
        FROM gigs g
        LEFT JOIN orders o ON g.gig_id = o.gig_id
        WHERE g.seller_id = ? AND g.status = 'active'
        GROUP BY g.gig_id
        ORDER BY revenue DESC
        LIMIT 5
    ");
    $stmt_gig_perf->execute([$_SESSION['user_id']]);
    $gig_perf = $stmt_gig_perf->fetchAll();

    // Fetch active gigs (independent of search filter, showing all owned active gigs)
    $stmt_gigs = $pdo->prepare("SELECT * FROM gigs WHERE seller_id = ? AND status = 'active' ORDER BY created_at DESC");
    $stmt_gigs->execute([$_SESSION['user_id']]);
    $my_gigs = $stmt_gigs->fetchAll();

    // Fetch incoming orders with search & status filters applied
    $sql_incoming = "
        SELECT o.*, g.title, u.name as buyer_name 
        FROM orders o
        JOIN gigs g ON o.gig_id = g.gig_id
        JOIN users u ON o.buyer_id = u.user_id
        WHERE g.seller_id = ?
    ";
    $params_incoming = [$_SESSION['user_id']];
    if (!empty($search)) {
        $sql_incoming .= " AND (g.title LIKE ? OR u.name LIKE ?)";
        $params_incoming[] = "%$search%";
        $params_incoming[] = "%$search%";
    }
    if (!empty($status_filter)) {
        $sql_incoming .= " AND o.status = ?";
        $params_incoming[] = $status_filter;
    }
    $sql_incoming .= " ORDER BY o.created_at DESC";
    $stmt_orders = $pdo->prepare($sql_incoming);
    $stmt_orders->execute($params_incoming);
    $incoming_orders = $stmt_orders->fetchAll();
    ?>

    <!-- Advanced Metrics Scorecard Grid -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8 animate-fade-in-up opacity-0" style="animation-fill-mode: forwards; animation-delay: 75ms;">
        <!-- Total Revenue Earnings -->
        <div class="bg-gradient-to-tr from-emerald-500/10 to-teal-500/5 dark:from-emerald-950/20 dark:to-slate-900 border border-emerald-200/40 dark:border-slate-800 rounded-2xl p-6 shadow-md hover:shadow-lg transition-all duration-300 relative overflow-hidden group">
            <div class="absolute right-0 top-0 h-16 w-16 bg-emerald-500/10 rounded-full blur-2xl group-hover:scale-125 transition-transform duration-500"></div>
            <div class="flex items-center justify-between mb-4">
                <span class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Total Earnings</span>
                <span class="p-2 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-lg text-xs font-bold border border-emerald-100/50 dark:border-emerald-900/30">Cleared + Pending</span>
            </div>
            <span class="text-3xl font-extrabold text-gray-900 dark:text-white font-serif tracking-tight mt-0.5 block">RM <?php echo number_format($sell_stats['total_earned'] ?: 0, 2); ?></span>
            
            <div class="mt-4 pt-4 border-t border-gray-200/60 dark:border-slate-800/80 flex items-center justify-between gap-2 text-xs font-semibold">
                <span class="text-gray-400 dark:text-slate-400">Cleared: <b class="text-emerald-600 dark:text-emerald-400 font-serif">RM <?php echo number_format($cleared_earnings, 2); ?></b></span>
                <span class="text-gray-400 dark:text-slate-400">Pending: <b class="text-teal-600 dark:text-teal-400 font-serif">RM <?php echo number_format($pending_clearance, 2); ?></b></span>
            </div>
        </div>

        <!-- Order Completion Rate Card -->
        <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-2xl p-6 shadow-md hover:shadow-lg transition-all duration-300 flex flex-col justify-between group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Order Completion</span>
                <div class="p-2.5 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
            </div>
            <div>
                <span class="text-3xl font-extrabold text-gray-900 dark:text-white font-serif tracking-tight mt-0.5 block"><?php echo $completion_rate; ?>%</span>
                <!-- Mini Progress Bar -->
                <div class="w-full bg-gray-100 dark:bg-slate-800 h-1.5 rounded-full mt-3 overflow-hidden">
                    <div class="bg-indigo-600 dark:bg-indigo-500 h-full rounded-full transition-all duration-500" style="width: <?php echo $completion_rate; ?>%;"></div>
                </div>
            </div>
        </div>

        <!-- Avg Order Value Card -->
        <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-2xl p-6 shadow-md hover:shadow-lg transition-all duration-300 flex flex-col justify-between group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Avg Order Value (AOV)</span>
                <div class="p-2.5 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
            </div>
            <div>
                <span class="text-3xl font-extrabold text-gray-900 dark:text-white font-serif tracking-tight mt-0.5 block">RM <?php echo number_format($avg_order_value, 2); ?></span>
                <p class="text-[10px] font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider mt-2.5">Active & Complete sales mean</p>
            </div>
        </div>

        <!-- Average Rating & Feedback Card -->
        <div class="bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-2xl p-6 shadow-md hover:shadow-lg transition-all duration-300 flex flex-col justify-between group">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs font-bold text-gray-400 dark:text-slate-550 uppercase tracking-widest">Seller Rating</span>
                <div class="p-2.5 bg-amber-50 dark:bg-amber-950/30 text-amber-500 dark:text-amber-400 rounded-lg">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                </div>
            </div>
            <div>
                <div class="flex items-baseline gap-2 mt-0.5">
                    <span class="text-3xl font-extrabold text-gray-900 dark:text-white font-serif tracking-tight"><?php echo $avg_rating; ?></span>
                    <span class="text-gray-400 dark:text-slate-400 text-sm font-semibold">/ 5.0</span>
                </div>
                <p class="text-[10px] font-bold text-gray-400 dark:text-slate-400 uppercase tracking-wider mt-2.5"><?php echo $review_count; ?> customer reviews</p>
            </div>
        </div>
    </div>

    <!-- Charts & Analytics Grid Section -->
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-8 animate-fade-in-up opacity-0" style="animation-fill-mode: forwards; animation-delay: 150ms;">
        <!-- Line Chart: Monthly revenue -->
        <div class="lg:col-span-3 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-2xl p-6 shadow-md relative transition-colors duration-300">
            <h3 class="text-md font-bold text-gray-900 dark:text-white mb-5 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span>
                Monthly Revenue Trend (Past 6 Months)
            </h3>
            <div class="h-68 relative">
                <canvas id="earningsChart"></canvas>
            </div>
        </div>
        
        <!-- Bar Chart: Gig revenue ranking -->
        <div class="lg:col-span-2 bg-white dark:bg-slate-900 border border-gray-200 dark:border-slate-800 rounded-2xl p-6 shadow-md relative transition-colors duration-300">
            <h3 class="text-md font-bold text-gray-900 dark:text-white mb-5 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full bg-indigo-500"></span>
                Top Gigs Performance (RM)
            </h3>
            <div class="h-68 relative">
                <?php if (count($gig_perf) > 0): ?>
                    <canvas id="gigsChart"></canvas>
                <?php else: ?>
                    <div class="absolute inset-0 flex flex-col items-center justify-center text-center p-4">
                        <svg class="w-12 h-12 text-gray-200 dark:text-slate-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M11 3.055A9.003 9.003 0 1020.945 13H11V3.055z"></path></svg>
                        <p class="text-gray-400 dark:text-slate-400 font-semibold text-xs uppercase tracking-wider">No active gig revenue to display</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-gray-200 dark:border-slate-800/80 p-5 mb-8 flex flex-col md:flex-row gap-4 items-center justify-between transition-colors duration-300 animate-fade-in-up opacity-0" style="animation-fill-mode: forwards; animation-delay: 200ms;">
        <form method="GET" class="flex flex-col md:flex-row w-full gap-4">
            <div class="flex-grow relative w-full">
                <input type="text" name="search" value="<?php echo escape($search); ?>" placeholder="Search incoming orders by gig title or buyer..." class="w-full pl-11 pr-4 py-3 bg-slate-50 dark:bg-slate-800/50 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition-all placeholder-gray-400 dark:placeholder-slate-500 text-sm">
                <svg class="absolute left-4 top-3.5 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <div class="w-full md:w-52">
                <select name="status" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition-all text-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php if ($status_filter === 'pending') echo 'selected'; ?>>Pending</option>
                    <option value="paid" <?php if ($status_filter === 'paid') echo 'selected'; ?>>Paid</option>
                    <option value="delivered" <?php if ($status_filter === 'delivered') echo 'selected'; ?>>Delivered</option>
                    <option value="complete" <?php if ($status_filter === 'complete') echo 'selected'; ?>>Complete</option>
                    <option value="cancelled" <?php if ($status_filter === 'cancelled') echo 'selected'; ?>>Cancelled</option>
                </select>
            </div>
            <div class="flex w-full md:w-auto gap-2">
                <button type="submit" class="flex-grow md:flex-grow-0 bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3 px-8 rounded-xl transition-all duration-300 text-sm cursor-pointer border-0 shadow-md shadow-emerald-500/10">
                    Filter
                </button>
                <?php if (!empty($search) || !empty($status_filter)): ?>
                    <a href="dashboard" class="flex-grow md:flex-grow-0 text-center bg-gray-100 dark:bg-slate-800 hover:bg-gray-200 dark:hover:bg-slate-800 text-gray-750 dark:text-white border border-gray-200 dark:border-slate-700 font-bold py-3 px-5 rounded-xl transition-all text-sm inline-block">
                        Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Active Gigs & Incoming Orders Columns -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 animate-fade-in-up opacity-0" style="animation-fill-mode: forwards; animation-delay: 250ms;">
        <!-- My Gigs Column -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-gray-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            <div class="h-1.5 bg-emerald-500"></div>
            <div class="px-6 py-5 border-b border-gray-100 dark:border-slate-800/80 flex items-center justify-between transition-colors duration-300">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">My Active Gigs</h2>
                <span class="bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 font-bold text-xs px-3 py-1.5 rounded-lg border border-emerald-100/50 dark:border-emerald-900/30"><?php echo count($my_gigs); ?></span>
            </div>
            <?php if(count($my_gigs) > 0): ?>
                <ul class="divide-y divide-gray-100 dark:divide-slate-800/60">
                    <?php foreach($my_gigs as $g): ?>
                        <li class="px-6 py-5 flex justify-between items-center hover:bg-slate-50/60 dark:hover:bg-slate-800/40 transition-colors duration-200 group">
                            <div class="min-w-0 mr-4">
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors truncate"><?php echo escape($g['title']); ?></h3>
                                <p class="text-xs text-gray-400 dark:text-slate-400 mt-1 font-semibold flex items-center gap-1.5">
                                    <span class="text-emerald-600 dark:text-emerald-400 font-serif">RM <?php echo number_format($g['price'], 2); ?></span>
                                    <span class="text-gray-300 dark:text-slate-700">&bull;</span>
                                    <span><?php echo escape($g['category']); ?></span>
                                </p>
                            </div>
                            <div class="flex items-center gap-3.5 shrink-0">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 border border-emerald-100/40 dark:border-emerald-900/20">
                                    Active
                                </span>
                                
                                <a href="<?php echo ROOT_URL; ?>gigs/edit?id=<?php echo $g['gig_id']; ?>" class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800" title="Edit Gig">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>

                                <form action="api/gig_action" method="POST" onsubmit="return confirm('Are you sure you want to delete this gig? This action cannot be undone.');" class="inline">
                                    <input type="hidden" name="gig_id" value="<?php echo $g['gig_id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-slate-800 border-0 cursor-pointer bg-transparent" title="Delete Gig">
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
                <div class="px-6 py-20 text-center">
                    <svg class="w-16 h-16 text-gray-200 dark:text-slate-700 mx-auto mb-4 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">No Active Gigs</h3>
                    <p class="text-gray-500 dark:text-slate-400 font-medium max-w-xs mx-auto text-sm">You haven't listed any services for others to buy yet.</p>
                    <a href="<?php echo ROOT_URL; ?>gigs/create" class="mt-5 inline-block bg-emerald-500 text-white font-bold px-6 py-3 rounded-xl hover:bg-emerald-600 transition-all duration-300 text-sm shadow-md shadow-emerald-500/10">Create your first gig</a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Incoming Orders Column -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-gray-200 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            <div class="h-1.5 bg-emerald-500"></div>
            <div class="px-6 py-5 border-b border-gray-100 dark:border-slate-800/80 flex items-center justify-between transition-colors duration-300">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Incoming Orders</h2>
                <span class="bg-emerald-50 dark:bg-emerald-950/50 text-emerald-700 dark:text-emerald-300 font-bold text-xs px-3 py-1.5 rounded-lg border border-emerald-100/50 dark:border-emerald-900/30"><?php echo count($incoming_orders); ?></span>
            </div>
            <?php if(count($incoming_orders) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-slate-50 dark:bg-slate-800 border-b border-gray-200 dark:border-slate-800/60 transition-colors duration-300">
                                <th class="px-6 py-3.5 text-left text-[11px] font-extrabold text-gray-400 dark:text-slate-400 uppercase tracking-widest">Order Details</th>
                                <th class="px-6 py-3.5 text-left text-[11px] font-extrabold text-gray-400 dark:text-slate-400 uppercase tracking-widest">Buyer</th>
                                <th class="px-6 py-3.5 text-left text-[11px] font-extrabold text-gray-400 dark:text-slate-400 uppercase tracking-widest">Quick Actions</th>
                                <th class="px-6 py-3.5 text-left text-[11px] font-extrabold text-gray-400 dark:text-slate-400 uppercase tracking-widest">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-slate-800/60">
                            <?php foreach($incoming_orders as $io): ?>
                                <tr class="hover:bg-slate-500/5 dark:hover:bg-slate-800/20 transition-colors duration-200">
                                    <!-- Order Title -->
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white max-w-[150px] truncate" title="<?php echo escape($io['title']); ?>">
                                        <?php echo escape($io['title']); ?>
                                    </td>
                                    <!-- Buyer Profile -->
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-slate-400">
                                        <span class="font-medium text-gray-700 dark:text-slate-300"><?php echo escape($io['buyer_name']); ?></span>
                                    </td>
                                    <!-- Action Hooks -->
                                    <td class="px-6 py-4 text-sm">
                                        <div class="flex items-center gap-2">
                                            <!-- Chat Trigger -->
                                            <a href="<?php echo ROOT_URL; ?>chat?user=<?php echo $io['buyer_id']; ?>" class="inline-flex items-center gap-1.5 bg-indigo-50 dark:bg-indigo-950/50 hover:bg-indigo-100 dark:hover:bg-indigo-900/40 text-indigo-650 dark:text-indigo-400 border border-indigo-200/30 dark:border-indigo-900/20 transition-all duration-300 text-xs">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                                Chat
                                            </a>
                                            <!-- Payment Proof -->
                                            <?php if($io['payment_proof_path']): ?>
                                                <a href="<?php echo asset_url($io['payment_proof_path']); ?>" target="_blank" class="inline-flex items-center gap-1 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 font-bold py-1.5 px-3 rounded-lg hover:bg-emerald-100 dark:hover:bg-emerald-900/40 border border-emerald-200/50 dark:border-emerald-900/20 transition-all duration-300 text-xs">
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                                    Proof
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <!-- Order status state handler -->
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <?php if ($io['status'] === 'paid'): ?>
                                            <form action="api/order_action" method="POST" class="inline">
                                                <input type="hidden" name="order_id" value="<?php echo $io['order_id']; ?>">
                                                <input type="hidden" name="action" value="deliver">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <button type="submit" class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold px-4 py-2 rounded-lg transition-all duration-300 hover:shadow-lg hover:shadow-emerald-500/15 text-xs border-0 cursor-pointer">Deliver Work</button>
                                            </form>
                                        <?php else: ?>
                                            <?php
                                            $s = $io['status'];
                                            $b = match($s) {
                                                'pending'   => 'bg-amber-50 dark:bg-amber-950/20 text-amber-600 dark:text-amber-400 border-amber-200/50 dark:border-amber-900/10',
                                                'delivered' => 'bg-indigo-50 dark:bg-indigo-950/20 text-indigo-650 dark:text-indigo-400 border-indigo-200/50 dark:border-indigo-900/10',
                                                'complete'  => 'bg-emerald-50 dark:bg-emerald-950/20 text-emerald-600 dark:text-emerald-400 border-emerald-200/50 dark:border-emerald-900/10',
                                                'cancelled' => 'bg-rose-50 dark:bg-rose-950/20 text-rose-600 dark:text-rose-400 border-rose-200/50 dark:border-rose-900/10',
                                                default     => 'bg-gray-50 dark:bg-slate-800 text-gray-500 dark:text-slate-400 border-gray-200 dark:border-slate-700',
                                            };
                                            ?>
                                            <span class="px-2.5 py-1 text-xs font-bold rounded-lg border <?php echo $b; ?> transition-colors duration-300">
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
                <div class="px-6 py-20 text-center">
                    <svg class="w-16 h-16 text-gray-200 dark:text-slate-700 mx-auto mb-4 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-1">No Orders Yet</h3>
                    <p class="text-gray-500 dark:text-slate-400 font-medium max-w-xs mx-auto text-sm">You haven't received any orders matching your filters.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<!-- Chart rendering execution script for Selling analytics -->
<?php if ($mode === 'selling'): ?>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            // Sourced PHP chart arrays
            const monthlyLabels = [<?php echo implode(',', array_map(fn($item) => '"' . $item['label'] . '"', $earnings_chart_data)); ?>];
            const monthlyValues = [<?php echo implode(',', array_map(fn($item) => $item['value'], $earnings_chart_data)); ?>];

            const gigLabels = [<?php echo implode(',', array_map(fn($item) => '"' . escape(substr($item['title'], 0, 18)) . (strlen($item['title']) > 18 ? '...' : '') . '"', $gig_perf)); ?>];
            const gigValues = [<?php echo implode(',', array_map(fn($item) => (float)$item['revenue'], $gig_perf)); ?>];

            // Chart theming dynamic controller
            function getChartColors() {
                const isDark = document.documentElement.classList.contains('dark');
                return {
                    text: isDark ? '#94a3b8' : '#475569',
                    grid: isDark ? 'rgba(51, 65, 85, 0.2)' : 'rgba(226, 232, 240, 0.6)',
                    earningsGradientStart: isDark ? 'rgba(16, 185, 129, 0.2)' : 'rgba(16, 185, 129, 0.15)',
                    earningsGradientStop: 'rgba(16, 185, 129, 0)',
                    lineBorderColor: isDark ? '#34d399' : '#10b981',
                    barColor: isDark ? '#6366f1' : '#4f46e5'
                };
            }

            let colors = getChartColors();
            let earningsChart, gigsChart;

            // Render Revenue Line Chart
            const ctxEarnings = document.getElementById('earningsChart').getContext('2d');
            const earningsGrad = ctxEarnings.createLinearGradient(0, 0, 0, 240);
            earningsGrad.addColorStop(0, colors.earningsGradientStart);
            earningsGrad.addColorStop(1, colors.earningsGradientStop);

            earningsChart = new Chart(ctxEarnings, {
                type: 'line',
                data: {
                    labels: monthlyLabels,
                    datasets: [{
                        label: 'Earnings',
                        data: monthlyValues,
                        borderColor: colors.lineBorderColor,
                        borderWidth: 3,
                        pointBackgroundColor: colors.lineBorderColor,
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        backgroundColor: earningsGrad,
                        fill: true,
                        tension: 0.35
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            backgroundColor: 'rgba(15, 23, 42, 0.9)',
                            titleFont: { size: 12, weight: 'bold' },
                            bodyFont: { size: 12 },
                            padding: 10,
                            displayColors: false,
                            callbacks: {
                                label: function(context) {
                                    return 'RM ' + context.parsed.y.toFixed(2);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { display: false },
                            ticks: {
                                color: colors.text,
                                font: { size: 11, weight: '600' }
                            }
                        },
                        y: {
                            grid: { color: colors.grid },
                            border: { dash: [4, 4] },
                            ticks: {
                                color: colors.text,
                                font: { size: 11, weight: '600' },
                                callback: function(value) {
                                    return 'RM ' + value;
                                }
                            }
                        }
                    }
                }
            });

            // Render Gig Revenue Performance Bar Chart
            const ctxGigs = document.getElementById('gigsChart');
            if (ctxGigs) {
                const ctxGigsContext = ctxGigs.getContext('2d');
                gigsChart = new Chart(ctxGigsContext, {
                    type: 'bar',
                    data: {
                        labels: gigLabels,
                        datasets: [{
                            data: gigValues,
                            backgroundColor: colors.barColor,
                            borderRadius: 6,
                            barThickness: 16
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                backgroundColor: 'rgba(15, 23, 42, 0.9)',
                                padding: 10,
                                displayColors: false,
                                callbacks: {
                                    label: function(context) {
                                        return 'RM ' + context.parsed.x.toFixed(2);
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { color: colors.grid },
                                border: { dash: [4, 4] },
                                ticks: {
                                    color: colors.text,
                                    font: { size: 11, weight: '600' },
                                    callback: function(value) {
                                        return 'RM ' + value;
                                    }
                                }
                            },
                            y: {
                                grid: { display: false },
                                ticks: {
                                    color: colors.text,
                                    font: { size: 11, weight: '600' }
                                }
                            }
                        }
                    }
                });
            }

            // Theme MutationObserver callback to adapt Chart themes on-the-fly
            const themeObserver = new MutationObserver(() => {
                const newColors = getChartColors();
                
                // Update Line Chart Colors
                if (earningsChart) {
                    earningsChart.options.scales.x.ticks.color = newColors.text;
                    earningsChart.options.scales.y.ticks.color = newColors.text;
                    earningsChart.options.scales.y.grid.color = newColors.grid;
                    
                    const newGrad = ctxEarnings.createLinearGradient(0, 0, 0, 240);
                    newGrad.addColorStop(0, newColors.earningsGradientStart);
                    newGrad.addColorStop(1, newColors.earningsGradientStop);
                    
                    earningsChart.data.datasets[0].borderColor = newColors.lineBorderColor;
                    earningsChart.data.datasets[0].pointBackgroundColor = newColors.lineBorderColor;
                    earningsChart.data.datasets[0].backgroundColor = newGrad;
                    earningsChart.update();
                }

                // Update Bar Chart Colors
                if (gigsChart) {
                    gigsChart.options.scales.x.ticks.color = newColors.text;
                    gigsChart.options.scales.x.grid.color = newColors.grid;
                    gigsChart.options.scales.y.ticks.color = newColors.text;
                    gigsChart.data.datasets[0].backgroundColor = newColors.barColor;
                    gigsChart.update();
                }
            });

            themeObserver.observe(document.documentElement, { attributes: true, attributeFilter: ['class'] });
        });
    </script>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
