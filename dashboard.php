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

<!-- Header Section -->
<div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 animate-fade-in-up opacity-0 transition-colors duration-300" style="animation-fill-mode: forwards;">
    <div>
        <h1 class="text-4xl sm:text-5xl font-extrabold <?php echo $mode === 'buying' ? 'text-indigo-700 dark:text-indigo-400' : 'text-emerald-600 dark:text-emerald-400'; ?> font-serif pb-2">
            <?php echo $mode === 'buying' ? 'Buying Dashboard' : 'Selling Dashboard'; ?>
        </h1>
        <p class="text-gray-550 dark:text-slate-400 mt-1 transition-colors duration-300">
            <?php echo $mode === 'buying' ? 'Track your orders and purchases.' : 'Manage your gigs and incoming orders.'; ?>
        </p>
    </div>
    
    <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
        <?php if ($mode === 'selling'): ?>
            <a href="<?php echo ROOT_URL; ?>gigs/create" class="bg-emerald-500 text-white font-bold py-2.5 px-5 rounded-lg hover:bg-emerald-600 transition-all duration-300 shadow-xl flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Create New Gig
            </a>
        <?php endif; ?>
        <?php $toggle_mode = ($mode === 'buying' ? 'selling' : 'buying'); ?>
        <a href="?mode=<?php echo $toggle_mode; ?>" class="bg-gray-100 dark:bg-slate-800 p-1 rounded-xl flex items-center shadow-inner hover:opacity-80 transition-opacity">
            <span class="px-4 py-2 rounded-lg text-sm font-bold transition-all duration-300 <?php echo $mode === 'buying' ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-400 shadow' : 'text-gray-500 dark:text-slate-400'; ?>">
                Buying
            </span>
            <span class="px-4 py-2 rounded-lg text-sm font-bold transition-all duration-300 <?php echo $mode === 'selling' ? 'bg-white dark:bg-slate-700 text-emerald-600 dark:text-emerald-400 shadow' : 'text-gray-500 dark:text-slate-400'; ?>">
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
        SELECT o.*, g.title, u.name as seller_name 
        FROM orders o
        JOIN gigs g ON o.gig_id = g.gig_id
        JOIN users u ON g.seller_id = u.user_id
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
        <div class="bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-2xl p-6 shadow-md flex items-center gap-4 transition-colors duration-300">
            <div class="p-3.5 bg-indigo-50 dark:bg-indigo-950/40 text-indigo-600 dark:text-indigo-400 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
            </div>
            <div>
                <span class="block text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Total Spent</span>
                <span class="text-2xl font-extrabold text-gray-900 dark:text-white font-serif">RM <?php echo number_format($buy_stats['total_spent'] ?: 0, 2); ?></span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-2xl p-6 shadow-md flex items-center gap-4 transition-colors duration-300">
            <div class="p-3.5 bg-blue-50 dark:bg-blue-950/40 text-blue-600 dark:text-blue-400 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <span class="block text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Active Purchases</span>
                <span class="text-2xl font-extrabold text-gray-900 dark:text-white font-serif"><?php echo (int)$buy_stats['active_purchases']; ?></span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-2xl p-6 shadow-md flex items-center gap-4 transition-colors duration-300">
            <div class="p-3.5 bg-green-50 dark:bg-green-950/40 text-green-600 dark:text-green-400 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
            </div>
            <div>
                <span class="block text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Completed Purchases</span>
                <span class="text-2xl font-extrabold text-gray-900 dark:text-white font-serif"><?php echo (int)$buy_stats['completed_purchases']; ?></span>
            </div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-gray-100 dark:border-slate-800/80 p-5 mb-8 flex flex-col md:flex-row gap-4 items-center justify-between transition-colors duration-300 animate-fade-in-up opacity-0" style="animation-fill-mode: forwards; animation-delay: 150ms;">
        <form method="GET" class="flex flex-col md:flex-row w-full gap-4">
            <div class="flex-grow relative w-full">
                <input type="text" name="search" value="<?php echo escape($search); ?>" placeholder="Search purchases by gig title or seller..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800/50 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-uitmPurple/30 focus:border-uitmPurple transition-all placeholder-gray-400 dark:placeholder-slate-550 text-sm">
                <svg class="absolute left-3.5 top-3 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <div class="w-full md:w-48">
                <select name="status" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/50 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-uitmPurple/30 focus:border-uitmPurple transition-all text-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php if ($status_filter === 'pending') echo 'selected'; ?>>Pending</option>
                    <option value="paid" <?php if ($status_filter === 'paid') echo 'selected'; ?>>Paid</option>
                    <option value="delivered" <?php if ($status_filter === 'delivered') echo 'selected'; ?>>Delivered</option>
                    <option value="complete" <?php if ($status_filter === 'complete') echo 'selected'; ?>>Complete</option>
                    <option value="cancelled" <?php if ($status_filter === 'cancelled') echo 'selected'; ?>>Cancelled</option>
                </select>
            </div>
            <div class="flex w-full md:w-auto gap-2">
                <button type="submit" class="flex-grow md:flex-grow-0 bg-uitmPurple hover:bg-purple-900 text-white font-bold py-2.5 px-6 rounded-xl transition-all duration-300 text-sm cursor-pointer border-0">
                    Filter
                </button>
                <?php if (!empty($search) || !empty($status_filter)): ?>
                    <a href="dashboard" class="flex-grow md:flex-grow-0 text-center bg-gray-100 dark:bg-slate-800 hover:opacity-85 text-gray-700 dark:text-white border border-gray-200 dark:border-slate-700 font-bold py-2.5 px-4 rounded-xl transition-all text-sm inline-block">
                        Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-gray-100 dark:border-slate-800 overflow-hidden animate-fade-in-up opacity-0 transition-colors duration-300" style="animation-fill-mode: forwards; animation-delay: 225ms;">
        <div class="h-1 bg-indigo-500"></div>
        <div class="px-6 py-5 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between transition-colors duration-300">
            <h2 class="text-xl font-bold text-gray-900 dark:text-white">My Purchases</h2>
            <span class="bg-indigo-50 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 text-xs font-bold px-3 py-1 rounded-md border border-indigo-100 dark:border-indigo-800/50"><?php echo count($orders); ?> orders</span>
        </div>
        <?php if (count($orders) > 0): ?>
            <div class="p-6 space-y-6">
                <?php foreach($orders as $o): ?>
                    <div class="bg-gray-50 dark:bg-slate-800/50 rounded-2xl border border-gray-100 dark:border-slate-700 p-6 flex flex-col hover:shadow-2xl transition-all duration-300 animate-fade-in-up">
                        <div class="flex justify-between items-start mb-6">
                            <div>
                                <h3 class="text-lg font-bold text-gray-900 dark:text-white font-serif mb-1"><?php echo escape($o['title']); ?></h3>
                                <p class="text-sm text-gray-500 dark:text-slate-400 font-medium">Seller: <span class="text-gray-700 dark:text-slate-300"><?php echo escape($o['seller_name']); ?></span></p>
                            </div>
                            <div class="flex-shrink-0">
                                <?php if ($o['status'] === 'delivered'): ?>
                                    <form action="api/order_action" method="POST" class="inline">
                                        <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                                        <input type="hidden" name="action" value="complete">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold px-4 py-2 rounded-xl transition-all shadow-xl text-sm flex items-center gap-2 border-0 cursor-pointer"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>Mark Complete</button>
                                    </form>
                                <?php elseif ($o['status'] === 'pending'): ?>
                                    <form action="api/order_action" method="POST" class="inline" onsubmit="return confirm('Cancel this order?');">
                                        <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                                        <input type="hidden" name="action" value="cancel">
                                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                        <button type="submit" class="bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 font-bold px-4 py-2 rounded-xl transition-all text-sm border-0 cursor-pointer">Cancel Order</button>
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
                                <div class="absolute left-0 top-1/2 transform -translate-y-1/2 w-full h-1 bg-gray-200 dark:bg-slate-700 rounded-full z-0 transition-colors duration-300"></div>
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
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs transition-colors duration-300 <?php echo $is_completed ? 'bg-indigo-600 text-white shadow-2xl ring-4 ring-white dark:ring-slate-900' : 'bg-white dark:bg-slate-800 text-gray-400 dark:text-slate-555 border-2 border-gray-200 dark:border-slate-600 ring-4 ring-gray-50 dark:ring-slate-900'; ?>">
                                            <?php if ($is_completed && !$is_active): ?>
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                                            <?php else: ?>
                                                <?php echo $num; ?>
                                            <?php endif; ?>
                                        </div>
                                        <span class="mt-2 text-xs font-bold <?php echo $is_active ? 'text-indigo-700 dark:text-indigo-400' : ($is_completed ? 'text-gray-700 dark:text-slate-300' : 'text-gray-400 dark:text-slate-500'); ?> transition-colors duration-300"><?php echo $label; ?></span>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-xl p-4 flex items-center gap-3 text-red-700 dark:text-red-300 font-bold transition-colors duration-300">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                This order has been cancelled.
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="px-6 py-16 text-center">
                <svg class="w-12 h-12 text-gray-200 dark:text-slate-600 mx-auto mb-4 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                <p class="text-gray-500 dark:text-slate-450 font-medium transition-colors duration-300">No purchases found matching your query.</p>
                <a href="marketplace" class="mt-4 inline-block bg-uitmPurple text-white font-bold px-6 py-2.5 rounded-xl hover:bg-purple-900 transition-all duration-300 text-sm">Browse Marketplace</a>
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

    <!-- Stats Section -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 animate-fade-in-up opacity-0" style="animation-fill-mode: forwards; animation-delay: 75ms;">
        <div class="bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-2xl p-6 shadow-md flex items-center gap-4 transition-colors duration-300">
            <div class="p-3.5 bg-emerald-50 dark:bg-emerald-950/40 text-emerald-600 dark:text-emerald-400 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            </div>
            <div>
                <span class="block text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Total Earnings</span>
                <span class="text-2xl font-extrabold text-gray-900 dark:text-white font-serif">RM <?php echo number_format($sell_stats['total_earned'] ?: 0, 2); ?></span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-2xl p-6 shadow-md flex items-center gap-4 transition-colors duration-300">
            <div class="p-3.5 bg-teal-50 dark:bg-teal-950/40 text-teal-600 dark:text-teal-400 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h5a2 2 0 012 2v10a2 2 0 01-2 2H5z"></path></svg>
            </div>
            <div>
                <span class="block text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Active Gigs</span>
                <span class="text-2xl font-extrabold text-gray-900 dark:text-white font-serif"><?php echo (int)$active_gigs_count; ?></span>
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 border border-gray-100 dark:border-slate-800 rounded-2xl p-6 shadow-md flex items-center gap-4 transition-colors duration-300">
            <div class="p-3.5 bg-orange-50 dark:bg-orange-950/40 text-orange-600 dark:text-orange-400 rounded-xl">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
            </div>
            <div>
                <span class="block text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">Active Sales</span>
                <span class="text-2xl font-extrabold text-gray-900 dark:text-white font-serif"><?php echo (int)$sell_stats['active_sales']; ?></span>
            </div>
        </div>
    </div>

    <!-- Search & Filter Controls -->
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-lg border border-gray-100 dark:border-slate-800/80 p-5 mb-8 flex flex-col md:flex-row gap-4 items-center justify-between transition-colors duration-300 animate-fade-in-up opacity-0" style="animation-fill-mode: forwards; animation-delay: 150ms;">
        <form method="GET" class="flex flex-col md:flex-row w-full gap-4">
            <div class="flex-grow relative w-full">
                <input type="text" name="search" value="<?php echo escape($search); ?>" placeholder="Search incoming orders by gig title or buyer..." class="w-full pl-10 pr-4 py-2.5 bg-slate-50 dark:bg-slate-800/50 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition-all placeholder-gray-400 dark:placeholder-slate-550 text-sm">
                <svg class="absolute left-3.5 top-3 w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </div>
            <div class="w-full md:w-48">
                <select name="status" class="w-full px-4 py-2.5 bg-slate-50 dark:bg-slate-800/50 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500/30 focus:border-emerald-500 transition-all text-sm">
                    <option value="">All Statuses</option>
                    <option value="pending" <?php if ($status_filter === 'pending') echo 'selected'; ?>>Pending</option>
                    <option value="paid" <?php if ($status_filter === 'paid') echo 'selected'; ?>>Paid</option>
                    <option value="delivered" <?php if ($status_filter === 'delivered') echo 'selected'; ?>>Delivered</option>
                    <option value="complete" <?php if ($status_filter === 'complete') echo 'selected'; ?>>Complete</option>
                    <option value="cancelled" <?php if ($status_filter === 'cancelled') echo 'selected'; ?>>Cancelled</option>
                </select>
            </div>
            <div class="flex w-full md:w-auto gap-2">
                <button type="submit" class="flex-grow md:flex-grow-0 bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-2.5 px-6 rounded-xl transition-all duration-300 text-sm cursor-pointer border-0">
                    Filter
                </button>
                <?php if (!empty($search) || !empty($status_filter)): ?>
                    <a href="dashboard" class="flex-grow md:flex-grow-0 text-center bg-gray-100 dark:bg-slate-800 hover:opacity-85 text-gray-700 dark:text-white border border-gray-200 dark:border-slate-700 font-bold py-2.5 px-4 rounded-xl transition-all text-sm inline-block">
                        Clear
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
        <!-- My Gigs -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-gray-100 dark:border-slate-800 overflow-hidden animate-fade-in-up opacity-0 transition-colors duration-300" style="animation-fill-mode: forwards; animation-delay: 225ms;">
            <div class="h-1 bg-emerald-500"></div>
            <div class="px-6 py-5 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between transition-colors duration-300">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">My Active Gigs</h2>
                <span class="bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 font-bold text-xs px-3 py-1 rounded-md border border-emerald-100 dark:border-emerald-800/50"><?php echo count($my_gigs); ?></span>
            </div>
            <?php if(count($my_gigs) > 0): ?>
                <ul class="divide-y divide-gray-50 dark:divide-slate-800">
                    <?php foreach($my_gigs as $g): ?>
                        <li class="px-6 py-4 flex justify-between items-center hover:bg-gray-50/80 dark:hover:bg-slate-800/80 transition-colors duration-200 group">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900 dark:text-white group-hover:text-emerald-600 dark:group-hover:text-emerald-400 transition-colors"><?php echo escape($g['title']); ?></h3>
                                <p class="text-xs text-gray-400 dark:text-slate-400 mt-1 font-medium">RM <?php echo number_format($g['price'], 2); ?> &bull; <?php echo escape($g['category']); ?></p>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="px-3 py-1 text-xs font-bold rounded-md bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 flex-shrink-0">
                                    <?php echo ucfirst(escape($g['status'])); ?>
                                </span>
                                
                                <a href="<?php echo ROOT_URL; ?>gigs/edit?id=<?php echo $g['gig_id']; ?>" class="text-gray-400 hover:text-indigo-500 transition-colors p-1" title="Edit Gig">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                                    </svg>
                                </a>

                                <form action="api/gig_action" method="POST" onsubmit="return confirm('Are you sure you want to delete this gig? This action cannot be undone.');" class="inline">
                                    <input type="hidden" name="gig_id" value="<?php echo $g['gig_id']; ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <button type="submit" class="text-gray-400 hover:text-red-500 transition-colors p-1 border-0 cursor-pointer bg-transparent" title="Delete Gig">
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
                    <svg class="w-12 h-12 text-gray-200 dark:text-slate-600 mx-auto mb-4 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 002-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path></svg>
                    <p class="text-gray-500 dark:text-slate-450 font-medium transition-colors duration-300">You don't have any active gigs yet.</p>
                    <a href="<?php echo ROOT_URL; ?>gigs/create" class="mt-4 inline-block bg-emerald-500 text-white font-bold px-6 py-2.5 rounded-xl hover:bg-emerald-600 transition-all duration-300 text-sm">Create your first gig</a>
                </div>
            <?php endif; ?>
        </div>
        
        <!-- Incoming Orders -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-gray-100 dark:border-slate-800 overflow-hidden animate-fade-in-up opacity-0 transition-colors duration-300" style="animation-fill-mode: forwards; animation-delay: 300ms;">
            <div class="h-1 bg-emerald-500"></div>
            <div class="px-6 py-5 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between transition-colors duration-300">
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Incoming Orders</h2>
                <span class="bg-emerald-50 dark:bg-emerald-900/30 text-emerald-700 dark:text-emerald-300 font-bold text-xs px-3 py-1 rounded-md border border-emerald-100 dark:border-emerald-800/50"><?php echo count($incoming_orders); ?></span>
            </div>
            <?php if(count($incoming_orders) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="bg-gray-50/70 dark:bg-slate-800/70 transition-colors duration-300">
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Order</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Buyer</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Proof</th>
                                <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50 dark:divide-slate-800">
                            <?php foreach($incoming_orders as $io): ?>
                                <tr class="hover:bg-gray-50/80 dark:hover:bg-slate-800/80 transition-colors duration-200">
                                    <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white max-w-[140px] truncate"><?php echo escape($io['title']); ?></td>
                                    <td class="px-6 py-4 text-sm text-gray-500 dark:text-slate-400">
                                        <div class="flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-emerald-500 flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                                                <?php echo strtoupper(substr($io['buyer_name'], 0, 1)); ?>
                                            </div>
                                            <?php echo escape($io['buyer_name']); ?>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm">
                                        <?php if($io['payment_proof_path']): ?>
                                            <a href="<?php echo asset_url($io['payment_proof_path']); ?>" target="_blank" class="text-emerald-600 hover:text-emerald-800 font-bold underline underline-offset-2 transition-colors">View</a>
                                        <?php else: ?>
                                            <span class="text-gray-300">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 text-sm font-medium">
                                        <?php if ($io['status'] === 'paid'): ?>
                                            <form action="api/order_action" method="POST" class="inline">
                                                <input type="hidden" name="order_id" value="<?php echo $io['order_id']; ?>">
                                                <input type="hidden" name="action" value="deliver">
                                                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                                <button type="submit" class="bg-emerald-600 hover:bg-emerald-700 text-white font-bold px-4 py-1.5 rounded-xl transition-all duration-300 hover:shadow-2xl text-xs border-0 cursor-pointer">Mark Delivered</button>
                                            </form>
                                        <?php else: ?>
                                            <?php
                                            $s = $io['status'];
                                            $b = match($s) {
                                                'pending'   => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300',
                                                'delivered' => 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300',
                                                'complete'  => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300',
                                                'cancelled' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300',
                                                default     => 'bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-300',
                                            };
                                            ?>
                                            <span class="px-3 py-1 inline-flex text-xs font-bold rounded-md <?php echo $b; ?> transition-colors duration-300">
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
                    <svg class="w-12 h-12 text-gray-200 dark:text-slate-600 mx-auto mb-4 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path></svg>
                    <p class="text-gray-500 dark:text-slate-450 font-medium transition-colors duration-300">No incoming orders found matching your query.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
