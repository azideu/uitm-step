<?php
// dashboard_admin.php — Admin Oversight & Dispute Resolution Panel
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

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

// Fetch all reports with reporter/reported user info
$stmt_reports = $pdo->query("
    SELECT r.*,
           u1.name  AS reporter_name,
           u1.email AS reporter_email,
           u2.name  AS reported_name,
           u2.email AS reported_email,
           u2.role  AS reported_role
    FROM reports r
    JOIN users u1 ON r.reporter_id = u1.user_id
    JOIN users u2 ON r.reported_id = u2.user_id
    ORDER BY r.created_at DESC
");
$reports = $stmt_reports->fetchAll();

$pending_reports = array_filter($reports, fn($r) => $r['status'] === 'pending');
$pending_count   = count($pending_reports);

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

function report_status_badge(string $status): string {
    return match($status) {
        'pending'   => 'bg-red-100 dark:bg-red-900/30 text-red-800 dark:text-red-300 border border-red-200 dark:border-red-800/50',
        'reviewed'  => 'bg-blue-100 dark:bg-blue-900/30 text-blue-800 dark:text-blue-300 border border-blue-200 dark:border-blue-800/50',
        'dismissed' => 'bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-400',
        'banned'    => 'bg-rose-900 dark:bg-rose-900/60 text-rose-100 border border-rose-700',
        default     => 'bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-400',
    };
}

function reason_label(string $reason): string {
    return match($reason) {
        'scam'                  => '🚨 Scam / Fraud',
        'fake_payment_proof'    => '📄 Fake Payment Proof',
        'non_delivery'          => '📦 Did Not Deliver Work',
        'harassment'            => '⚠️ Harassment / Threats',
        'inappropriate_content' => '🚫 Inappropriate Content',
        'other'                 => '💬 Other',
        default                 => ucfirst(str_replace('_', ' ', $reason)),
    };
}

require_once '../includes/header.php';
?>

<div class="mb-8 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
    <div>
        <h1 class="text-4xl font-extrabold text-uitmPurple dark:text-purple-300 font-serif pb-1">Admin Dashboard</h1>
        <p class="text-gray-500 dark:text-slate-400 mt-1 transition-colors duration-300">Full order oversight &amp; dispute resolution panel.</p>
    </div>
    <div class="flex gap-3 flex-wrap">
        <!-- Order Summary Badges -->
        <?php
        $counts = array_fill_keys($statuses, 0);
        foreach ($orders as $o) { $counts[$o['status']] = ($counts[$o['status']] ?? 0) + 1; }
        ?>
        <?php foreach ($counts as $st => $count): ?>
            <span class="px-3 py-1.5 text-xs font-bold rounded-md <?php echo status_badge($st); ?>">
                <?php echo ucfirst($st); ?>: <?php echo $count; ?>
            </span>
        <?php endforeach; ?>

        <?php if ($pending_count > 0): ?>
            <span class="px-3 py-1.5 text-xs font-bold rounded-md bg-red-600 text-white border border-red-700 animate-pulse-slow">
                🚨 <?php echo $pending_count; ?> Pending Report<?php echo $pending_count !== 1 ? 's' : ''; ?>
            </span>
        <?php endif; ?>
    </div>
</div>

<!-- =====================================================================
     SECTION 1: ORDERS
     ===================================================================== -->
<div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-gray-100 dark:border-slate-800 overflow-hidden transition-colors duration-300 mb-10">
    <div class="h-1 bg-uitmPurple"></div>
    <div class="px-6 py-5 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-uitmPurple dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
            Order Management
        </h2>
        <span class="bg-purple-50 dark:bg-purple-900/30 text-uitmPurple dark:text-purple-300 font-bold text-xs px-3 py-1 rounded-md border border-purple-100 dark:border-purple-800/50"><?php echo count($orders); ?> orders</span>
    </div>

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

                            <!-- Admin Status Override -->
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <form action="../api/order_action" method="POST" class="flex gap-2 items-center">
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
                                            class="bg-uitmPurple text-white px-3 py-1.5 rounded-lg text-xs font-bold hover:bg-purple-900 transition-all duration-300 hover:shadow-2xl">
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

<!-- =====================================================================
     SECTION 2: USER REPORTS
     ===================================================================== -->
<div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-gray-100 dark:border-slate-800 overflow-hidden transition-colors duration-300">
    <div class="h-1 bg-gradient-to-r from-red-500 to-rose-600"></div>
    <div class="px-6 py-5 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
            </svg>
            User Reports
        </h2>
        <div class="flex items-center gap-3">
            <?php if ($pending_count > 0): ?>
                <span class="bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 font-bold text-xs px-3 py-1 rounded-md border border-red-200 dark:border-red-800/50">
                    <?php echo $pending_count; ?> pending
                </span>
            <?php endif; ?>
            <span class="bg-gray-100 dark:bg-slate-800 text-gray-500 dark:text-slate-400 font-bold text-xs px-3 py-1 rounded-md"><?php echo count($reports); ?> total</span>
        </div>
    </div>

    <?php if (count($reports) > 0): ?>
        <div class="divide-y divide-gray-50 dark:divide-slate-800">
            <?php foreach ($reports as $rep): ?>
                <div class="p-6 hover:bg-gray-50/70 dark:hover:bg-slate-800/50 transition-colors duration-200 <?php echo $rep['status'] === 'pending' ? 'border-l-4 border-red-400' : ''; ?>">
                    <div class="flex flex-col lg:flex-row lg:items-start gap-5">

                        <!-- Report Meta -->
                        <div class="flex-grow min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-3">
                                <span class="text-xs font-bold text-gray-400 dark:text-slate-500">#<?php echo $rep['report_id']; ?></span>
                                <span class="px-2.5 py-0.5 rounded-md text-xs font-bold <?php echo report_status_badge($rep['status']); ?>">
                                    <?php echo ucfirst(escape($rep['status'])); ?>
                                </span>
                                <span class="px-2.5 py-0.5 rounded-md text-xs font-semibold bg-gray-100 dark:bg-slate-800 text-gray-600 dark:text-slate-400">
                                    <?php echo reason_label($rep['reason']); ?>
                                </span>
                                <span class="text-xs text-gray-400 dark:text-slate-500 ml-auto">
                                    <?php echo date('d M Y, H:i', strtotime($rep['created_at'])); ?>
                                </span>
                            </div>

                            <!-- People involved -->
                            <div class="flex flex-wrap gap-4 mb-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-300 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                        <?php echo strtoupper(substr($rep['reporter_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400 dark:text-slate-500 font-medium">Reporter</p>
                                        <p class="text-sm font-bold text-gray-800 dark:text-white"><?php echo escape($rep['reporter_name']); ?></p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1 text-gray-300 dark:text-slate-600 self-center">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </div>
                                <div class="flex items-center gap-2">
                                    <div class="w-7 h-7 rounded-full bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300 flex items-center justify-center font-bold text-xs flex-shrink-0">
                                        <?php echo strtoupper(substr($rep['reported_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <p class="text-xs text-gray-400 dark:text-slate-500 font-medium">Reported User
                                            <?php if ($rep['reported_role'] === 'banned'): ?>
                                                <span class="ml-1 text-red-600 dark:text-red-400 font-bold">[BANNED]</span>
                                            <?php endif; ?>
                                        </p>
                                        <p class="text-sm font-bold text-gray-800 dark:text-white"><?php echo escape($rep['reported_name']); ?></p>
                                    </div>
                                </div>
                            </div>

                            <!-- Details -->
                            <?php if ($rep['details']): ?>
                                <div class="bg-gray-50 dark:bg-slate-800/60 rounded-xl p-3 mb-3 border border-gray-100 dark:border-slate-700">
                                    <p class="text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-1">Report details</p>
                                    <p class="text-sm text-gray-700 dark:text-slate-300 leading-relaxed"><?php echo escape($rep['details']); ?></p>
                                </div>
                            <?php endif; ?>

                            <!-- Admin note (if previously set) -->
                            <?php if ($rep['admin_note']): ?>
                                <div class="bg-blue-50 dark:bg-blue-900/20 rounded-xl p-3 border border-blue-100 dark:border-blue-800/50">
                                    <p class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase tracking-wider mb-1">Admin note</p>
                                    <p class="text-sm text-blue-800 dark:text-blue-300"><?php echo escape($rep['admin_note']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Admin Actions (only for non-final statuses) -->
                        <?php if (!in_array($rep['status'], ['banned'])): ?>
                            <div class="lg:w-64 flex-shrink-0">
                                <form action="report_action" method="POST" class="bg-gray-50 dark:bg-slate-800/60 rounded-2xl p-4 border border-gray-100 dark:border-slate-700 space-y-3"
                                      onsubmit="return confirm('Are you sure you want to apply this action?');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="report_id" value="<?php echo $rep['report_id']; ?>">

                                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Admin Note (optional)</label>
                                    <textarea name="admin_note" rows="2" maxlength="500"
                                              placeholder="Internal note about your decision..."
                                              class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-uitmPurple/30 transition resize-none"><?php echo escape($rep['admin_note'] ?? ''); ?></textarea>

                                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider">Action</label>
                                    <div class="grid grid-cols-1 gap-2">
                                        <button type="submit" name="action" value="reviewed"
                                                class="w-full py-2 rounded-xl bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 border border-blue-200 dark:border-blue-800/50 text-xs font-bold hover:bg-blue-100 dark:hover:bg-blue-900/40 transition-all duration-200 flex items-center justify-center gap-2">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                            Mark Reviewed
                                        </button>
                                        <button type="submit" name="action" value="dismissed"
                                                class="w-full py-2 rounded-xl bg-gray-100 dark:bg-slate-700 text-gray-600 dark:text-slate-300 border border-gray-200 dark:border-slate-600 text-xs font-bold hover:bg-gray-200 dark:hover:bg-slate-600 transition-all duration-200 flex items-center justify-center gap-2">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                            Dismiss Report
                                        </button>
                                        <?php if ($rep['reported_role'] !== 'banned'): ?>
                                            <button type="submit" name="action" value="banned"
                                                    class="w-full py-2 rounded-xl bg-red-600 hover:bg-red-700 text-white text-xs font-bold shadow transition-all duration-200 flex items-center justify-center gap-2">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                                                Ban User
                                            </button>
                                        <?php else: ?>
                                            <div class="w-full py-2 rounded-xl bg-rose-900/20 text-rose-400 text-xs font-bold text-center border border-rose-800/50 flex items-center justify-center gap-2">
                                                <svg class="w-3.5 h-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="M12 8v4"></path><path d="M12 16h.01"></path></svg>
                                                User Already Banned
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </form>
                            </div>
                        <?php else: ?>
                            <div class="lg:w-64 flex-shrink-0 flex items-center justify-center">
                                <div class="bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800/50 rounded-2xl p-4 text-center w-full">
                                    <div class="w-10 h-10 bg-rose-100 dark:bg-rose-900/40 rounded-full flex items-center justify-center mx-auto mb-2 text-rose-600 dark:text-rose-400">
                                        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"></line></svg>
                                    </div>
                                    <p class="text-xs font-bold text-rose-700 dark:text-rose-300">User Banned</p>
                                    <p class="text-[10px] text-rose-500 dark:text-rose-400 mt-1 uppercase tracking-wider font-semibold">Security Action Applied</p>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="px-6 py-16 text-center">
            <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4 text-emerald-600 dark:text-emerald-400 shadow-lg shadow-emerald-500/10">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
            </div>
            <p class="text-gray-500 dark:text-slate-500 font-medium transition-colors duration-300">No user reports yet. The platform is clean!</p>
        </div>
    <?php endif; ?>
</div>

<!-- =====================================================================
     SECTION 3: ACCOUNT APPEALS
     ===================================================================== -->
<div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-gray-100 dark:border-slate-800 overflow-hidden transition-colors duration-300 mt-10">
    <div class="h-1 bg-gradient-to-r from-indigo-500 to-purple-600"></div>
    <div class="px-6 py-5 border-b border-gray-100 dark:border-slate-800 flex items-center justify-between">
        <h2 class="text-xl font-bold text-gray-900 dark:text-white flex items-center gap-2">
            <svg class="w-5 h-5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
            </svg>
            Account Appeals
        </h2>
        <?php
        $stmt_appeals = $pdo->query("
            SELECT a.*, u.name, u.email 
            FROM ban_appeals a 
            JOIN users u ON a.user_id = u.user_id 
            ORDER BY a.created_at DESC
        ");
        $appeals = $stmt_appeals->fetchAll();
        $pending_appeals = array_filter($appeals, fn($a) => $a['status'] === 'pending');
        ?>
        <div class="flex items-center gap-3">
            <?php if (count($pending_appeals) > 0): ?>
                <span class="bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 font-bold text-xs px-3 py-1 rounded-md border border-indigo-200 dark:border-indigo-800/50">
                    <?php echo count($pending_appeals); ?> pending
                </span>
            <?php endif; ?>
            <span class="bg-gray-100 dark:bg-slate-800 text-gray-500 dark:text-slate-400 font-bold text-xs px-3 py-1 rounded-md"><?php echo count($appeals); ?> total</span>
        </div>
    </div>

    <?php if (count($appeals) > 0): ?>
        <div class="divide-y divide-gray-50 dark:divide-slate-800">
            <?php foreach ($appeals as $app): ?>
                <div class="p-6 hover:bg-gray-50/70 dark:hover:bg-slate-800/50 transition-colors duration-200">
                    <div class="flex flex-col lg:flex-row lg:items-start gap-6">
                        
                        <div class="flex-grow min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-4">
                                <span class="text-xs font-bold text-gray-400 dark:text-slate-500">#APPEAL-<?php echo $app['appeal_id']; ?></span>
                                <?php
                                $as = $app['status'];
                                $ab = match($as) {
                                    'pending'  => 'bg-yellow-100 dark:bg-yellow-900/30 text-yellow-700 dark:text-yellow-300',
                                    'approved' => 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300',
                                    'rejected' => 'bg-red-100 dark:bg-red-900/30 text-red-700 dark:text-red-300',
                                    default    => 'bg-gray-100 text-gray-500',
                                };
                                ?>
                                <span class="px-2.5 py-0.5 rounded-md text-xs font-bold <?php echo $ab; ?>">
                                    <?php echo ucfirst($as); ?>
                                </span>
                                <span class="text-xs text-gray-400 dark:text-slate-500 ml-auto">
                                    Submitted <?php echo date('d M Y, H:i', strtotime($app['created_at'])); ?>
                                </span>
                            </div>

                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-slate-800 flex items-center justify-center font-bold text-slate-500">
                                    <?php echo strtoupper(substr($app['name'], 0, 1)); ?>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-gray-800 dark:text-white"><?php echo escape($app['name']); ?></p>
                                    <p class="text-xs text-gray-500 dark:text-slate-500"><?php echo escape($app['email']); ?></p>
                                </div>
                            </div>

                            <div class="bg-slate-50 dark:bg-slate-800/40 rounded-2xl p-4 border border-slate-100 dark:border-slate-700 mb-4">
                                <p class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-2">Student's Statement</p>
                                <p class="text-sm text-slate-700 dark:text-slate-300 leading-relaxed whitespace-pre-line"><?php echo escape($app['content']); ?></p>
                            </div>

                            <?php if ($app['admin_note']): ?>
                                <div class="bg-indigo-50 dark:bg-indigo-900/20 rounded-2xl p-4 border border-indigo-100 dark:border-indigo-800/50">
                                    <p class="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-2">Admin Response</p>
                                    <p class="text-sm text-indigo-800 dark:text-indigo-300 italic">"<?php echo escape($app['admin_note']); ?>"</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <?php if ($app['status'] === 'pending'): ?>
                            <div class="lg:w-64 flex-shrink-0">
                                <form action="appeal_action" method="POST" class="bg-gray-50 dark:bg-slate-800/60 rounded-2xl p-4 border border-gray-100 dark:border-slate-700 space-y-4"
                                      onsubmit="return confirm('Are you sure? Approving will instantly restore this user\'s access.');">
                                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                                    <input type="hidden" name="appeal_id" value="<?php echo $app['appeal_id']; ?>">

                                    <div>
                                        <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-wider mb-2">Response Note</label>
                                        <textarea name="admin_note" rows="3" maxlength="500" 
                                                  placeholder="Explain the decision to the student..."
                                                  class="w-full px-3 py-2 text-xs rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-900 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-indigo-500/30 transition resize-none"></textarea>
                                    </div>

                                    <div class="grid grid-cols-1 gap-2">
                                        <button type="submit" name="action" value="approve"
                                                class="w-full py-2.5 rounded-xl bg-green-600 hover:bg-green-700 text-white text-xs font-bold shadow-lg transition-all">
                                            ✅ Approve & Unban
                                        </button>
                                        <button type="submit" name="action" value="reject"
                                                class="w-full py-2.5 rounded-xl bg-white dark:bg-slate-700 text-red-600 dark:text-red-400 border border-red-200 dark:border-red-800/50 text-xs font-bold hover:bg-red-50 transition-all">
                                            ❌ Reject Appeal
                                        </button>
                                    </div>
                                </form>
                            </div>
                        <?php endif; ?>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div class="px-6 py-16 text-center text-gray-400">
            <svg class="w-12 h-12 mx-auto mb-4 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/></svg>
            <p class="font-medium">No account appeals to display.</p>
        </div>
    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>



