<?php
// payment-gateway.php — Integrated Single-File Checkout & Payment Portal
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

// NOTE: The `transactions` table must be created via your DB migration/setup script.
// Run the following SQL once in your database before using this page:
//
// CREATE TABLE IF NOT EXISTS transactions (
//     transaction_id INT AUTO_INCREMENT PRIMARY KEY,
//     order_id INT NOT NULL,
//     reference_number VARCHAR(50) UNIQUE NOT NULL,
//     bank_name VARCHAR(100) NOT NULL,
//     amount DECIMAL(10,2) NOT NULL,
//     status ENUM('success', 'failed', 'pending') DEFAULT 'pending',
//     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
// );

$order_id = isset($_GET['order_id']) ? (int)$_GET['order_id'] : 0;
$is_demo = ($order_id <= 0);

// Fetch current user email from database for checkout form
$user_stmt = $pdo->prepare("SELECT email FROM users WHERE user_id = ?");
$user_stmt->execute([$_SESSION['user_id']]);
$user_data = $user_stmt->fetch();
$user_email = $user_data ? $user_data['email'] : 'student@student.uitm.edu.my';

$order = null;

if (!$is_demo) {
    // Fetch active order details
    $stmt = $pdo->prepare("
        SELECT o.*, g.title, g.price, u.name AS seller_name
        FROM orders o
        JOIN gigs g ON o.gig_id = g.gig_id
        JOIN users u ON g.seller_id = u.user_id
        WHERE o.order_id = ? AND o.buyer_id = ?
    ");
    $stmt->execute([$order_id, $_SESSION['user_id']]);
    $order = $stmt->fetch();

    if (!$order) {
        set_toast('error', 'Order not found or access denied.');
        redirect('dashboard?mode=buying');
    }

    if ($order['status'] !== 'pending') {
        set_toast('info', 'This order is already ' . htmlspecialchars($order['status']) . '.');
        redirect('dashboard?mode=buying');
    }
} else {
    // Demonstration sandbox details
    $order = [
        'order_id' => 0,
        'status' => 'pending',
        'price' => 150.00,
        'title' => 'UiTM Custom Web Design & Development Services',
        'seller_name' => 'Ahmad Khairul (Mock Seller)',
    ];
}

// -------------------------------------------------------------------------
// POST HANDLER: Processes transaction via AJAX to avoid page refreshes
// -------------------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'complete_payment') {
    header('Content-Type: application/json');

    // CSRF verification — check both the session token AND the posted token exist before comparing
    if (!isset($_SESSION['csrf_token'], $_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Invalid security token.']);
        exit;
    }

    $payment_method = $_POST['payment_method'] ?? 'card';
    $bank_name = 'Credit/Debit Card';
    
    if ($payment_method === 'fpx') {
        $bank_name = 'FPX: ' . ($_POST['fpx_bank'] ?? 'STEP Bank');
    } elseif ($payment_method === 'wallet') {
        $bank_name = 'E-Wallet: ' . ($_POST['e_wallet'] ?? 'Touch \'n Go');
    }

    $reference_number = 'STEP-TXN-' . strtoupper(bin2hex(random_bytes(4))) . rand(100, 999);

    if (!$is_demo) {
        try {
            $pdo->beginTransaction();

            // 1. Update order status to paid
            $update_stmt = $pdo->prepare("UPDATE orders SET status = 'paid' WHERE order_id = ?");
            $update_stmt->execute([$order_id]);

            // 2. Log transaction details
            $log_stmt = $pdo->prepare("
                INSERT INTO transactions (order_id, reference_number, bank_name, amount, status) 
                VALUES (?, ?, ?, ?, 'success')
            ");
            $log_stmt->execute([$order_id, $reference_number, $bank_name, $order['price']]);

            $pdo->commit();
            
            // Set toast success for when they redirect back to dashboard
            set_toast('success', 'Payment verified! Your order is active.');


            echo json_encode([
                'success' => true,
                'reference' => $reference_number,
                'payment_method' => $bank_name,
                'redirect_url' => 'dashboard?mode=buying'
            ]);
            exit;
        } catch (\Exception $e) {
            $pdo->rollBack();
            error_log('[payment-gateway.php error] ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Database error processing transaction. Please try again.']);
            exit;
        }
    } else {
        // Mock success response for sandbox
        echo json_encode([
            'success' => true,
            'reference' => $reference_number,
            'payment_method' => $bank_name,
            'redirect_url' => 'marketplace'
        ]);
        exit;
    }
}

$title = "Checkout - STEP-Pay";
require_once 'includes/header.php';
?>

<!-- Outer Checkout Container -->
<div class="max-w-6xl mx-auto my-8 px-4 animate-fade-in-up">
    
    <!-- State 1: Active Checkout Form & Details -->
    <div id="checkout-view" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Page Title & Subtitle (matching design language of other pages) -->
        <div class="lg:col-span-3 mb-2">
            <h1 class="text-4xl sm:text-5xl font-extrabold text-uitmPurple dark:text-white font-serif mb-2 transition-colors duration-300">Checkout</h1>
            <p class="text-gray-500 dark:text-slate-400 transition-colors duration-300">Complete your campus order securely.</p>
        </div>

        <!-- Left 2 Columns: Checkout Steps -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Step 1: Contact Information -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-xl transition-colors">
                <div class="flex items-center gap-3 mb-6">
                    <span class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/40 text-uitmPurple dark:text-purple-300 flex items-center justify-center font-bold text-sm">1</span>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Contact Information</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Student Email</label>
                        <input type="email" disabled value="<?= htmlspecialchars($user_email) ?>" class="w-full px-4 py-3 bg-slate-50 dark:bg-slate-800/50 text-slate-500 border border-slate-200 dark:border-slate-700 rounded-lg focus:outline-none cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Contact Number <span class="text-red-500">*</span></label>
                        <input type="tel" id="contact_phone" placeholder="012-345 6789" required class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-slate-800 dark:text-white border border-slate-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple focus:border-uitmPurple transition-all">
                    </div>
                </div>
            </div>

            <!-- Step 2: Gig Requirements -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-xl transition-colors">
                <div class="flex items-center gap-3 mb-6">
                    <span class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/40 text-uitmPurple dark:text-purple-300 flex items-center justify-center font-bold text-sm">2</span>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Requirements for Seller</h2>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Instructions / Project Details <span class="text-red-500">*</span></label>
                    <textarea id="order_requirements" rows="4" placeholder="Provide any details, links, documents, or guidelines the seller needs to complete your work." required class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-slate-800 dark:text-white border border-slate-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple focus:border-uitmPurple transition-all resize-none"></textarea>
                </div>
            </div>

            <!-- Step 3: Payment Method Selection -->
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-xl transition-colors">
                <div class="flex items-center gap-3 mb-6">
                    <span class="w-8 h-8 rounded-full bg-purple-100 dark:bg-purple-900/40 text-uitmPurple dark:text-purple-300 flex items-center justify-center font-bold text-sm">3</span>
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white">Payment Method</h2>
                </div>

                <!-- Tabs header -->
                <div class="flex border-b border-slate-100 dark:border-slate-800 mb-6">
                    <button type="button" onclick="setTab('card')" id="tab-card" class="flex-1 pb-4 text-sm font-bold text-uitmPurple border-b-2 border-uitmPurple dark:text-purple-400 dark:border-purple-400 transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        Card Payment
                    </button>
                    <button type="button" onclick="setTab('fpx')" id="tab-fpx" class="flex-1 pb-4 text-sm font-bold text-slate-400 border-b-2 border-transparent hover:text-slate-600 dark:hover:text-slate-200 transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        FPX Online Banking
                    </button>
                    <button type="button" onclick="setTab('wallet')" id="tab-wallet" class="flex-1 pb-4 text-sm font-bold text-slate-400 border-b-2 border-transparent hover:text-slate-600 dark:hover:text-slate-200 transition-all flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                        E-Wallet
                    </button>
                </div>

                <!-- Tab: Card Form -->
                <div id="panel-card" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Cardholder Name</label>
                        <input type="text" id="card_name" placeholder="John Doe" class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-slate-800 dark:text-white border border-slate-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple focus:border-uitmPurple transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Card Number</label>
                        <div class="relative">
                            <input type="text" id="card_number" maxlength="19" placeholder="4111 1111 1111 1111" class="w-full pl-12 pr-4 py-3 bg-white dark:bg-slate-800 text-slate-800 dark:text-white border border-slate-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple focus:border-uitmPurple transition-all tracking-widest">
                            <div class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                            </div>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Expiry Date</label>
                            <input type="text" id="card_expiry" maxlength="5" placeholder="MM/YY" class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-slate-800 dark:text-white border border-slate-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple focus:border-uitmPurple transition-all">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Security Code (CVV)</label>
                            <input type="text" id="card_cvv" maxlength="3" placeholder="123" class="w-full px-4 py-3 bg-white dark:bg-slate-800 text-slate-800 dark:text-white border border-slate-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple focus:border-uitmPurple transition-all">
                        </div>
                    </div>
                </div>

                <!-- Tab: FPX Banking Grid -->
                <div id="panel-fpx" class="hidden space-y-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Choose your preferred internet banking account:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <label class="fpx-bank-btn flex items-center gap-3 p-4 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 cursor-pointer transition-all relative">
                            <input type="radio" name="fpx_select" value="Maybank2u" checked class="peer sr-only">
                            <div class="absolute inset-0 rounded-lg border-2 border-transparent peer-checked:border-amber-500 pointer-events-none"></div>
                            <div class="w-8 h-8 rounded-lg bg-amber-500 text-slate-900 flex items-center justify-center font-bold text-xs select-none">M2U</div>
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Maybank2u</span>
                        </label>
                        <label class="fpx-bank-btn flex items-center gap-3 p-4 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 cursor-pointer transition-all relative">
                            <input type="radio" name="fpx_select" value="CIMB Clicks" class="peer sr-only">
                            <div class="absolute inset-0 rounded-lg border-2 border-transparent peer-checked:border-red-500 pointer-events-none"></div>
                            <div class="w-8 h-8 rounded-lg bg-red-600 text-white flex items-center justify-center font-bold text-xs select-none">CIMB</div>
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">CIMB Clicks</span>
                        </label>
                        <label class="fpx-bank-btn flex items-center gap-3 p-4 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 cursor-pointer transition-all relative">
                            <input type="radio" name="fpx_select" value="RHB Now" class="peer sr-only">
                            <div class="absolute inset-0 rounded-lg border-2 border-transparent peer-checked:border-blue-600 pointer-events-none"></div>
                            <div class="w-8 h-8 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xs select-none">RHB</div>
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">RHB Now</span>
                        </label>
                        <label class="fpx-bank-btn flex items-center gap-3 p-4 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 cursor-pointer transition-all relative">
                            <input type="radio" name="fpx_select" value="Hong Leong Bank" class="peer sr-only">
                            <div class="absolute inset-0 rounded-lg border-2 border-transparent peer-checked:border-sky-500 pointer-events-none"></div>
                            <div class="w-8 h-8 rounded-lg bg-sky-500 text-white flex items-center justify-center font-bold text-xs select-none">HLB</div>
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Hong Leong Bank</span>
                        </label>
                        <label class="fpx-bank-btn flex items-center gap-3 p-4 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 cursor-pointer transition-all relative">
                            <input type="radio" name="fpx_select" value="AmBank" class="peer sr-only">
                            <div class="absolute inset-0 rounded-lg border-2 border-transparent peer-checked:border-red-500 pointer-events-none"></div>
                            <div class="w-8 h-8 rounded-lg bg-red-500 text-white flex items-center justify-center font-bold text-xs select-none">AMB</div>
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">AmBank</span>
                        </label>
                        <label class="fpx-bank-btn flex items-center gap-3 p-4 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 cursor-pointer transition-all relative">
                            <input type="radio" name="fpx_select" value="Bank Islam" class="peer sr-only">
                            <div class="absolute inset-0 rounded-lg border-2 border-transparent peer-checked:border-emerald-600 pointer-events-none"></div>
                            <div class="w-8 h-8 rounded-lg bg-emerald-600 text-white flex items-center justify-center font-bold text-xs select-none">BI</div>
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-300">Bank Islam</span>
                        </label>
                    </div>
                </div>

                <!-- Tab: E-Wallet Options -->
                <div id="panel-wallet" class="hidden space-y-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">Select your e-wallet platform:</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                        <label class="wallet-select flex flex-col items-center justify-center p-5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 cursor-pointer transition-all relative text-center">
                            <input type="radio" name="wallet_select" value="Touch 'n Go" checked class="peer sr-only">
                            <div class="absolute inset-0 rounded-lg border-2 border-transparent peer-checked:border-blue-500 pointer-events-none"></div>
                            <div class="w-10 h-10 rounded-lg bg-blue-500 text-white flex items-center justify-center font-black text-xs mb-2">TNG</div>
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">Touch 'n Go</span>
                        </label>
                        <label class="wallet-select flex flex-col items-center justify-center p-5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 cursor-pointer transition-all relative text-center">
                            <input type="radio" name="wallet_select" value="MAE" class="peer sr-only">
                            <div class="absolute inset-0 rounded-lg border-2 border-transparent peer-checked:border-amber-500 pointer-events-none"></div>
                            <div class="w-10 h-10 rounded-lg bg-amber-500 text-slate-900 flex items-center justify-center font-extrabold text-xs mb-2">MAE</div>
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">MAE by Maybank</span>
                        </label>
                        <label class="wallet-select flex flex-col items-center justify-center p-5 rounded-lg border border-slate-200 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-900/50 hover:bg-slate-100/50 dark:hover:bg-slate-800/50 cursor-pointer transition-all relative text-center">
                            <input type="radio" name="wallet_select" value="RHB" class="peer sr-only">
                            <div class="absolute inset-0 rounded-lg border-2 border-transparent peer-checked:border-blue-600 pointer-events-none"></div>
                            <div class="w-10 h-10 rounded-lg bg-blue-600 text-white flex items-center justify-center font-bold text-xs mb-2">RHB</div>
                            <span class="text-xs font-bold text-slate-700 dark:text-slate-300">RHB</span>
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right 1 Column: Summary Sidebar -->
        <div class="lg:col-span-1 space-y-6">
            <div class="bg-white dark:bg-slate-900 rounded-3xl p-6 border border-slate-100 dark:border-slate-800 shadow-xl transition-colors">
                <h3 class="text-xs font-bold text-slate-400 uppercase tracking-widest mb-4">Order Summary</h3>

                <div class="space-y-4">
                    <div>
                        <span class="text-xs text-slate-400 dark:text-slate-500">Service</span>
                        <p class="font-extrabold text-slate-800 dark:text-slate-200 text-sm leading-snug"><?= htmlspecialchars($order['title']) ?></p>
                    </div>

                    <div class="flex justify-between border-t border-slate-100 dark:border-slate-800/60 pt-4">
                        <div>
                            <span class="text-xs text-slate-400 dark:text-slate-500">Seller</span>
                            <p class="font-bold text-slate-800 dark:text-slate-200 text-sm"><?= htmlspecialchars($order['seller_name']) ?></p>
                        </div>
                        <?php if ($order['order_id'] > 0): ?>
                            <div class="text-right">
                                <span class="text-xs text-slate-400 dark:text-slate-500">Order ID</span>
                                <p class="font-bold text-slate-800 dark:text-slate-200 text-sm">#<?= $order['order_id'] ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Discount code input to match checkout inspiration -->
                    <div class="border-t border-slate-100 dark:border-slate-800/60 pt-4 flex gap-2">
                        <input type="text" placeholder="Promo code (e.g. STEP5)" class="flex-grow px-3 py-2 text-xs bg-slate-50 dark:bg-slate-800 text-slate-800 dark:text-white border border-slate-200 dark:border-slate-700 rounded-lg focus:outline-none">
                        <button type="button" class="px-3 py-2 text-xs font-bold text-slate-600 dark:text-slate-300 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-800 transition-colors">Apply</button>
                    </div>

                    <div class="border-t border-slate-100 dark:border-slate-800/60 pt-4 bg-slate-50/50 dark:bg-slate-950/20 -mx-6 -mb-6 p-6 rounded-b-3xl">
                        <span class="text-xs text-slate-400 dark:text-slate-500 uppercase tracking-widest font-bold block mb-1">Total Amount</span>
                        <div class="text-3xl font-extrabold text-uitmPurple dark:text-purple-300 font-serif">
                            RM <?= number_format($order['price'], 2) ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Primary Action Button -->
            <button type="button" onclick="startVerification()" class="w-full bg-uitmPurple hover:bg-purple-900 text-white font-bold py-4 px-6 rounded-lg shadow-xl transition-all flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                Complete Payment
            </button>

            <a href="<?= $is_demo ? 'marketplace' : 'dashboard?mode=buying' ?>" class="flex items-center justify-center gap-2 text-xs text-slate-400 dark:text-slate-500 hover:text-slate-600 dark:hover:text-slate-300 font-bold transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Cancel and return to UiTM STEP
            </a>
        </div>
    </div>

    <!-- State 2: Simulated OTP Modal Popup Overlay -->
    <div id="otp-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
        <div class="bg-white dark:bg-slate-900 rounded-3xl p-8 max-w-md w-full shadow-2xl border border-slate-100 dark:border-slate-800 text-center animate-fade-in-up">
            
            <div id="loading-screen" class="space-y-4 py-8">
                <div class="relative w-16 h-16 mx-auto">
                    <div class="absolute inset-0 rounded-full border-4 border-purple-100 dark:border-purple-900/30"></div>
                    <div class="absolute inset-0 rounded-full border-4 border-uitmPurple dark:border-purple-400 border-t-transparent animate-spin"></div>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-900 dark:text-white">Connecting Securely</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">Contacting secure banking verification services...</p>
                </div>
            </div>

            <div id="otp-form-screen" class="hidden space-y-6">
                <!-- Lock Shield Icon -->
                <div class="w-16 h-16 rounded-full bg-purple-100 dark:bg-purple-900/40 text-uitmPurple dark:text-purple-300 flex items-center justify-center mx-auto text-3xl">
                    🔒
                </div>
                
                <div>
                    <h3 class="text-xl font-bold text-slate-900 dark:text-white">Secure Bank Verification</h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">
                        A secure OTP verification code has been dispatched to your simulated device. Please input it below.
                    </p>
                </div>

                <!-- Bank OTP Simulator Message Banner -->
                <div id="otp-sms-simulator" class="p-4 bg-amber-50 dark:bg-amber-950/20 border border-amber-200/50 dark:border-amber-900/30 text-amber-800 dark:text-amber-300 rounded-lg text-left text-xs space-y-1">
                    <div class="flex items-center gap-1.5 font-bold uppercase tracking-wider text-amber-700 dark:text-amber-400">
                        💬 SMS Simulator (Mock Device)
                    </div>
                    <p>Verification Code: <strong class="text-sm tracking-widest text-slate-800 dark:text-white ml-1" id="mock-otp-value">------</strong></p>
                    <p class="opacity-80">Valid for 3 minutes. Do not share this authentication pin.</p>
                </div>

                <div class="space-y-3">
                    <input type="text" id="otp_input" maxlength="6" placeholder="------" class="w-full text-center tracking-widest font-mono text-2xl font-bold px-4 py-3 bg-slate-50 dark:bg-slate-800 text-slate-900 dark:text-white border border-slate-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple focus:border-uitmPurple transition-all">
                    <p id="otp-error" class="hidden text-xs text-red-500 font-bold">Incorrect verification code. Please check the SMS helper and try again.</p>
                </div>

                <div class="flex gap-3 pt-2">
                    <button type="button" onclick="cancelPayment()" class="flex-1 py-3 text-xs font-bold text-slate-500 border border-slate-200 dark:border-slate-700 rounded-lg hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">Cancel</button>
                    <button type="button" onclick="submitOTP()" class="flex-1 py-3 text-xs font-bold text-white bg-uitmPurple hover:bg-purple-900 rounded-lg shadow-md transition-colors">Verify & Pay</button>
                </div>
            </div>
        </div>
    </div>

    <!-- State 3: Checkout Success Screen Receipt (Fades in on payment approval) -->
    <div id="success-view" class="hidden max-w-md mx-auto bg-white dark:bg-slate-900 rounded-3xl border border-slate-100 dark:border-slate-800 shadow-2xl p-8 text-center animate-fade-in-up">
        
        <!-- Big Checkmark -->
        <div class="w-20 h-20 bg-emerald-100 dark:bg-emerald-950/40 text-emerald-500 dark:text-emerald-400 rounded-full flex items-center justify-center mx-auto text-4xl mb-6 shadow-lg">
            ✓
        </div>

        <h2 class="text-2xl font-extrabold text-slate-900 dark:text-white font-serif">Payment Approved!</h2>
        <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">Your mock payment was authorized and registered successfully.</p>

        <!-- Receipt Box -->
        <div class="my-6 p-5 bg-slate-50 dark:bg-slate-950/20 border border-slate-100 dark:border-slate-800/80 rounded-lg text-left space-y-4 text-xs">
            <div class="flex justify-between">
                <span class="text-slate-400">Total Price</span>
                <span class="font-bold text-slate-800 dark:text-white">RM <?= number_format($order['price'], 2) ?></span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Payment Gateway</span>
                <span class="font-bold text-slate-800 dark:text-white" id="success-method">Card</span>
            </div>
            <div class="flex justify-between">
                <span class="text-slate-400">Verification Ref</span>
                <span class="font-mono font-bold text-slate-800 dark:text-white" id="success-ref">STEP-TXN-XXXXXX</span>
            </div>
            <div class="flex justify-between border-t border-slate-100 dark:border-slate-800 pt-3">
                <span class="text-slate-400">Status</span>
                <span class="font-bold text-emerald-600 dark:text-emerald-400">✓ Settled (Paid)</span>
            </div>
        </div>

        <button type="button" onclick="handleFinishRedirect()" class="w-full bg-uitmPurple hover:bg-purple-900 text-white font-bold py-3.5 px-6 rounded-lg shadow-xl transition-all">
            Return to Dashboard
        </button>
    </div>

</div>

<!-- CSS & JS Controller for Tab Toggles and State Handlers -->
<script>
    let activeTab = 'card';
    let mockOTP = '';
    let redirectUrl = '';

    // Switch between Credit Card, FPX, and E-wallet layouts
    function setTab(tabName) {
        activeTab = tabName;

        // Reset styling for all tabs
        const tabs = ['card', 'fpx', 'wallet'];
        tabs.forEach(t => {
            const el = document.getElementById('tab-' + t);
            el.classList.remove('text-uitmPurple', 'border-uitmPurple', 'dark:text-purple-400', 'dark:border-purple-400');
            el.classList.add('text-slate-400', 'border-transparent');
            document.getElementById('panel-' + t).classList.add('hidden');
        });

        // Highlight selected tab
        const activeEl = document.getElementById('tab-' + tabName);
        activeEl.classList.remove('text-slate-400', 'border-transparent');
        activeEl.classList.add('text-uitmPurple', 'border-b-2', 'border-uitmPurple', 'dark:text-purple-400', 'dark:border-purple-400');
        document.getElementById('panel-' + tabName).classList.remove('hidden');
    }

    // Handles initial validation and pops up the OTP/verification modal
    function startVerification() {
        const phone = document.getElementById('contact_phone').value.trim();
        const reqs = document.getElementById('order_requirements').value.trim();

        if (!phone) {
            alert('Please input a valid Contact Number to proceed.');
            document.getElementById('contact_phone').focus();
            return;
        }

        if (!reqs) {
            alert('Please input instructions/project details for the seller.');
            document.getElementById('order_requirements').focus();
            return;
        }

        // Generate a random 6-digit mock OTP
        mockOTP = String(Math.floor(100000 + Math.random() * 900000));

        // Show Modal backdrop + spin loader
        const modal = document.getElementById('otp-modal');
        modal.classList.remove('hidden');
        document.getElementById('loading-screen').classList.remove('hidden');
        document.getElementById('otp-form-screen').classList.add('hidden');
        document.getElementById('otp-error').classList.add('hidden');
        document.getElementById('otp_input').value = '';

        // Simulate secure bank gateway loading lag
        setTimeout(() => {
            document.getElementById('loading-screen').classList.add('hidden');
            document.getElementById('otp-form-screen').classList.remove('hidden');
            
            // Present mock SMS containing the verification code to copy/paste
            document.getElementById('mock-otp-value').textContent = mockOTP;
            document.getElementById('otp_input').focus();
        }, 1500);
    }

    function cancelPayment() {
        document.getElementById('otp-modal').classList.add('hidden');
    }

    // Submits verification details via AJAX to update DB
    function submitOTP() {
        const enteredVal = document.getElementById('otp_input').value.trim();
        const errAlert = document.getElementById('otp-error');

        if (enteredVal !== mockOTP) {
            errAlert.classList.remove('hidden');
            document.getElementById('otp_input').focus();
            return;
        }

        // If correct, play payment verification flow
        errAlert.classList.add('hidden');
        document.getElementById('otp-form-screen').classList.add('hidden');
        document.getElementById('loading-screen').classList.remove('hidden');

        // Capture payment method value
        let finalBankName = 'Credit/Debit Card';
        if (activeTab === 'fpx') {
            finalBankName = document.querySelector('input[name="fpx_select"]:checked').value;
        } else if (activeTab === 'wallet') {
            finalBankName = document.querySelector('input[name="wallet_select"]:checked').value;
        }

        // Perform AJAX request to update orders on the server
        const formData = new FormData();
        formData.append('action', 'complete_payment');
        formData.append('csrf_token', '<?= htmlspecialchars($_SESSION['csrf_token'] ?? '', ENT_QUOTES, 'UTF-8') ?>');
        formData.append('payment_method', activeTab);
        if (activeTab === 'fpx') {
            formData.append('fpx_bank', finalBankName);
        } else if (activeTab === 'wallet') {
            formData.append('e_wallet', finalBankName);
        }

        fetch('payment-gateway.php?order_id=<?= $order_id ?>', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            setTimeout(() => {
                if (data.success) {
                    // Populate success page values
                    document.getElementById('success-method').textContent = data.payment_method;
                    document.getElementById('success-ref').textContent = data.reference;
                    redirectUrl = data.redirect_url;

                    // Transition to Success View
                    document.getElementById('otp-modal').classList.add('hidden');
                    document.getElementById('checkout-view').classList.add('hidden');
                    document.getElementById('success-view').classList.remove('hidden');
                } else {
                    document.getElementById('otp-modal').classList.add('hidden');
                    alert(data.message || 'Payment processing failed. Please try again.');
                }
            }, 1000);
        })
        .catch(err => {
            document.getElementById('otp-modal').classList.add('hidden');
            alert('A network error occurred while verifying the transaction.');
            console.error(err);
        });
    }

    function handleFinishRedirect() {
        window.location.href = redirectUrl || 'dashboard';
    }

    // Format phone number dynamically based on prefix (011 -> 11 digits, others -> 10 digits)
    const contactPhoneInput = document.getElementById('contact_phone');
    if (contactPhoneInput) {
        contactPhoneInput.addEventListener('input', function() {
            let digits = this.value.replace(/\D/g, '');
            const is011 = digits.startsWith('011');
            const maxLen = is011 ? 11 : 10;
            
            if (digits.length > maxLen) {
                digits = digits.substring(0, maxLen);
            }
            
            let formatted = '';
            if (digits.length > 0) {
                if (is011) {
                    if (digits.length <= 3) {
                        formatted = digits;
                    } else if (digits.length <= 7) {
                        formatted = digits.substring(0, 3) + '-' + digits.substring(3);
                    } else {
                        formatted = digits.substring(0, 3) + '-' + digits.substring(3, 7) + ' ' + digits.substring(7);
                    }
                } else {
                    if (digits.length <= 3) {
                        formatted = digits;
                    } else if (digits.length <= 6) {
                        formatted = digits.substring(0, 3) + '-' + digits.substring(3);
                    } else {
                        formatted = digits.substring(0, 3) + '-' + digits.substring(3, 6) + ' ' + digits.substring(6);
                    }
                }
            }
            this.value = formatted;
        });
    }

    // Restrict card number to 16 digits only
    const cardNumberInput = document.getElementById('card_number');
    if (cardNumberInput) {
        cardNumberInput.addEventListener('input', function() {
            let digits = this.value.replace(/\D/g, '');
            if (digits.length > 16) {
                digits = digits.substring(0, 16);
            }
            // Format as groups of 4 digits: 4111 1111 1111 1111
            this.value = digits.match(/.{1,4}/g)?.join(' ') ?? digits;
        });
    }

    // Format card expiry to "MM/YY" (xx/xx)
    const cardExpiryInput = document.getElementById('card_expiry');
    if (cardExpiryInput) {
        cardExpiryInput.addEventListener('input', function() {
            let digits = this.value.replace(/\D/g, '');
            if (digits.length > 4) {
                digits = digits.substring(0, 4);
            }
            let formatted = '';
            if (digits.length > 0) {
                if (digits.length <= 2) {
                    formatted = digits;
                } else {
                    formatted = digits.substring(0, 2) + '/' + digits.substring(2);
                }
            }
            this.value = formatted;
        });
    }
</script>

<?php
require_once 'includes/footer.php';
?>
