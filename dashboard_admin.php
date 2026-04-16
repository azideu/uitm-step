<?php
// dashboard_admin.php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

require_admin(); // Ensure only admins

// Fetch all orders
$stmt = $pdo->query("
    SELECT o.*, g.title, gb.name as buyer_name, gs.name as seller_name 
    FROM orders o
    JOIN gigs g ON o.gig_id = g.gig_id
    JOIN users gb ON o.buyer_id = gb.user_id
    JOIN users gs ON g.seller_id = gs.user_id
    ORDER BY o.created_at DESC
");
$orders = $stmt->fetchAll();

$statuses = ['pending', 'paid', 'delivered', 'complete', 'cancelled'];

require_once 'includes/header.php';
?>

<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-uitmPurple">Admin Dashboard - All Orders</h1>
</div>

<div class="bg-white rounded-lg shadow overflow-hidden">
    <?php if (count($orders) > 0): ?>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order ID</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gig Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Buyer</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proof</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status Override</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    <?php foreach($orders as $o): ?>
                        <tr>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">#<?php echo $o['order_id']; ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-ellipsis overflow-hidden max-w-[150px]"><?php echo escape($o['title']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo escape($o['buyer_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo escape($o['seller_name']); ?></td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <?php if($o['payment_proof_path']): ?>
                                    <a href="<?php echo escape($o['payment_proof_path']); ?>" target="_blank" class="text-uitmPurple hover:underline">View File</a>
                                <?php else: ?>
                                    <span class="text-gray-400">N/A</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <form action="order_action.php" method="POST" class="flex gap-2">
                                    <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                                    <input type="hidden" name="action" value="admin_update">
                                    <select name="status" class="border rounded px-2 py-1 text-sm bg-white focus:outline-none focus:ring focus:border-uitmPurple">
                                        <?php foreach($statuses as $st): ?>
                                            <option value="<?php echo $st; ?>" <?php if($o['status'] === $st) echo 'selected'; ?>><?php echo ucfirst($st); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <button type="submit" class="bg-uitmPurple text-white px-2 py-1 rounded text-xs font-bold hover:bg-purple-900 transition">Update</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php else: ?>
        <div class="px-6 py-8 text-center text-gray-500">No orders found on the platform.</div>
    <?php endif; ?>
</div>

<?php require_once 'includes/footer.php'; ?>
