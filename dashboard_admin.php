<?php
// dashboard_admin.php — Admin Oversight & Dispute Resolution Panel
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

require_admin(); // Ensure only admins can access this page

// Fetch all orders with buyer name, seller name, and payment proof
$stmt = $pdo->query("
    SELECT o.*, g.title,
           gb.name  AS buyer_name,
           gs.name  AS seller_name,
           o.payment_proof_path
    FROM orders o
    JOIN gigs  g  ON o.gig_id     = g.gig_id
    JOIN users gb ON o.buyer_id   = gb.user_id
    JOIN users gs ON g.seller_id  = gs.user_id
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();

$statuses = ['pending', 'paid', 'delivered', 'complete', 'cancelled'];

// Helper: return Tailwind badge classes for each status
function status_badge(string $status): string {
    return match($status) {
        'pending'   => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-300 border border-yellow-200 dark:border-yellow-800/50',
        'paid'      => 'bg-blue-100 dark:bg-blue-900/30   text-blue-800 dark:text-blue-300   border border-blue-200 dark:border-blue-800/50',
        'delivered' => 'bg-indigo-100 dark:bg-indigo-900/30 text-indigo-800 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800/50',
        'complete'  => 'bg-green-100 dark:bg-green-900/30  text-green-800 dark:text-green-300  border border-green-200 dark:border-green-800/50',
        'cancelled' => 'bg-red-100 dark:bg-red-900/30    text-red-800 dark:text-red-300    border border-red-200 dark:border-red-800/50',
        default     => 'bg-gray-100 dark:bg-slate-800   text-gray-600 dark:text-slate-400',
    };
}

require_once 'includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h1 class="text-4xl font-extrabold text-uitmPurple dark:text-purple-300 font-serif pb-1">Admin Dashboard</h1>
        <p class="text-gray-500 dark:text-slate-400 mt-1 transition-colors duration-300">Full order oversight &amp; dispute resolution panel.</p>
    </div>
    <div class="flex gap-3">
        <!-- Summary Badges -->
        <?php
        $counts = array_fill_keys($statuses, 0);
        foreach ($orders as $o) { $counts[$o['status']] = ($counts[$o['status']] ?? 0) + 1; }
        ?>
        <?php foreach ($counts as $st => $count): ?>
            <span class="px-3 py-1.5 text-xs font-bold rounded-md <?php echo status_badge($st); ?>">
                <?php echo ucfirst($st); ?>: <?php echo $count; ?>
            </span>
        <?php endforeach; ?>
    </div>
</div>

<div class="bg-white dark:bg-slate-900 rounded-2xl shadow-sm border border-gray-100 dark:border-slate-800 overflow-hidden transition-colors duration-300">
    <div class="h-1 bg-uitmPurple"></div>

    <?php if (count($orders) > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="bg-gray-50/70 dark:bg-slate-800/50 transition-colors duration-300">
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">#</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Gig Title</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Buyer</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Seller</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Payment Proof</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Current Status</th>
                        <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest">Status Override</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-50 dark:divide-slate-800">
                    <?php foreach ($orders as $o): ?>
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-slate-800/50 transition-colors duration-200">

                            <!-- Order ID -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-400 dark:text-slate-500">#<?php echo $o['order_id']; ?></td>

                            <!-- Gig Title -->
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900 dark:text-white max-w-[160px] truncate transition-colors">
                                <?php echo escape($o['title']); ?>
                            </td>

                            <!-- Buyer -->
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-slate-400 transition-colors">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                        <?php echo strtoupper(substr($o['buyer_name'], 0, 1)); ?>
                                    </div>
                                    <?php echo escape($o['buyer_name']); ?>
                                </div>
                            </td>

                            <!-- Seller -->
                            <td class="px-6 py-4 text-sm text-gray-600 dark:text-slate-400 transition-colors">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-purple-100 dark:bg-purple-900/30 text-uitmPurple dark:text-purple-300 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                        <?php echo strtoupper(substr($o['seller_name'], 0, 1)); ?>
                                    </div>
                                    <?php echo escape($o['seller_name']); ?>
                                </div>
                            </td>

                            <!-- Payment Proof -->
                            <td class="px-6 py-4 text-sm">
                                <?php if ($o['payment_proof_path']): ?>
                                    <a href="<?php echo escape($o['payment_proof_path']); ?>" target="_blank"
                                       class="inline-flex items-center gap-1 text-uitmPurple dark:text-purple-400 hover:text-indigo-700 dark:hover:text-purple-300 font-bold underline underline-offset-2 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                        View
                                    </a>
                                <?php else: ?>
                                    <span class="text-gray-300 dark:text-slate-600 text-xs">—</span>
                                <?php endif; ?>
                            </td>

                            <!-- Current Status Badge -->
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-3 py-1 inline-flex text-xs font-bold rounded-md <?php echo status_badge($o['status']); ?>">
                                    <?php echo ucfirst(escape($o['status'])); ?>
                                </span>
                            </td>

                            <!-- Admin Status Override (State Machine bypass for disputes) -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <form action="order_action" method="POST" class="flex gap-2 items-center">
                                    <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                                    <input type="hidden" name="action"   value="admin_update">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <select name="status"
                                            class="border border-gray-200 dark:border-slate-700 rounded-lg px-2 py-1.5 text-sm bg-white dark:bg-slate-800 text-gray-900 dark:text-white focus:outline-none focus:ring-2 focus:ring-uitmPurple/40 dark:focus:ring-purple-900/40 focus:border-uitmPurple transition">
                                        <?php foreach ($statuses as $st): ?>
                                            <option value="<?php echo $st; ?>" <?php if ($o['status'] === $st) echo 'selected'; ?>>
                                                <?php echo ucfirst($st); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit"
                                            class="bg-uitmPurple text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-purple-900 transition-all duration-300 hover:shadow-md">
                                        Update
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="px-6 py-16 text-center">
            <svg class="w-12 h-12 text-gray-200 dark:text-slate-700 mx-auto mb-4 transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            <p class="text-gray-500 dark:text-slate-500 font-medium transition-colors duration-300">No orders on the platform yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
