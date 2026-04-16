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

<div class="mb-6 flex justify-between items-center">
    <h1 class="text-3xl font-bold text-uitmPurple">
        <?php echo $mode === 'buying' ? 'Buying Dashboard' : 'Selling Dashboard'; ?>
    </h1>
    
    <?php if ($mode === 'selling'): ?>
        <a href="create_gig.php" class="bg-uitmGold text-uitmPurple font-bold py-2 px-4 rounded hover:bg-yellow-400 transition shadow">Create New Gig</a>
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
    
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <h2 class="text-xl font-bold bg-gray-50 px-6 py-4 border-b">My Orders</h2>
        <?php if (count($orders) > 0): ?>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Gig Title</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Seller</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach($orders as $o): ?>
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900"><?php echo escape($o['title']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo escape($o['seller_name']); ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?php echo ucfirst(escape($o['status'])); ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <?php if ($o['status'] === 'delivered'): ?>
                                        <form action="order_action.php" method="POST" class="inline">
                                            <input type="hidden" name="order_id" value="<?php echo $o['order_id']; ?>">
                                            <input type="hidden" name="action" value="complete">
                                            <button type="submit" class="text-green-600 hover:text-green-900 border border-green-600 rounded px-2 py-1 transition hover:bg-green-50">Mark Complete</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-gray-400">Wait for delivery</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="px-6 py-8 text-center text-gray-500">You haven't bought anything yet.</div>
        <?php endif; ?>
    </div>

<?php else: // SELLING MODE ?>

    <?php
    // Fetch active gigs
    $stmt_gigs = $pdo->prepare("SELECT * FROM gigs WHERE seller_id = ? ORDER BY created_at DESC");
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
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <h2 class="text-xl font-bold bg-gray-50 px-6 py-4 border-b flex justify-between items-center">
                My Active Gigs
                <span class="bg-uitmPurple text-white text-xs px-2 py-1 rounded-full"><?php echo count($my_gigs); ?></span>
            </h2>
            <?php if(count($my_gigs) > 0): ?>
                <ul class="divide-y divide-gray-200">
                    <?php foreach($my_gigs as $g): ?>
                        <li class="px-6 py-4 flex justify-between items-center hover:bg-gray-50">
                            <div>
                                <h3 class="text-sm font-bold text-gray-900"><?php echo escape($g['title']); ?></h3>
                                <p class="text-xs text-gray-500 mt-1">RM <?php echo number_format($g['price'], 2); ?> • <?php echo escape($g['category']); ?></p>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">
                                <?php echo ucfirst(escape($g['status'])); ?>
                            </span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <div class="px-6 py-8 text-center text-gray-500">You don't have any gigs yet.</div>
            <?php endif; ?>
        </div>
        
        <!-- Incoming Orders -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <h2 class="text-xl font-bold bg-gray-50 px-6 py-4 border-b flex justify-between items-center">
                Incoming Orders
                <span class="bg-uitmPurple text-white text-xs px-2 py-1 rounded-full"><?php echo count($incoming_orders); ?></span>
            </h2>
            <?php if(count($incoming_orders) > 0): ?>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Order</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Buyer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Proof</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Action</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <?php foreach($incoming_orders as $io): ?>
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900 text-ellipsis overflow-hidden max-w-[150px]"><?php echo escape($io['title']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?php echo escape($io['buyer_name']); ?></td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <?php if($io['payment_proof_path']): ?>
                                            <a href="<?php echo escape($io['payment_proof_path']); ?>" target="_blank" class="text-uitmPurple hover:underline">View</a>
                                        <?php else: ?>
                                            N/A
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <?php if ($io['status'] === 'paid'): ?>
                                            <form action="order_action.php" method="POST" class="inline">
                                                <input type="hidden" name="order_id" value="<?php echo $io['order_id']; ?>">
                                                <input type="hidden" name="action" value="deliver">
                                                <button type="submit" class="text-uitmPurple hover:text-purple-900 border border-uitmPurple rounded px-2 py-1 transition hover:bg-purple-50">Mark Delivered</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                                <?php echo ucfirst(escape($io['status'])); ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="px-6 py-8 text-center text-gray-500">No incoming orders right now.</div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
