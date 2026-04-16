<?php
// gig_details.php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

$gig_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch gig and seller info
$stmt = $pdo->prepare("SELECT g.*, u.name as seller_name, u.campus FROM gigs g JOIN users u ON g.seller_id = u.user_id WHERE g.gig_id = ?");
$stmt->execute([$gig_id]);
$gig = $stmt->fetch();

if (!$gig) {
    set_toast('error', 'Gig not found.');
    redirect('marketplace.php');
}

// Handle Order placement (Purchase)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'buy') {
    if ($_SESSION['role'] !== 'student') {
        set_toast('error', 'Only students can buy gigs.');
    } elseif ($gig['seller_id'] == $_SESSION['user_id']) {
        set_toast('error', 'You cannot buy your own gig.');
    } else {
        // Handle File Upload
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === UPLOAD_ERR_OK) {
            $tmp_path = $_FILES['payment_proof']['tmp_name'];
            $file_size = $_FILES['payment_proof']['size'];
            
            // 2MB Limit
            $max_size = 2 * 1024 * 1024;
            if ($file_size > $max_size) {
                set_toast('error', 'File size exceeds 2MB limit.');
                redirect("gig_details.php?id=$gig_id");
            }
            
            // MIME Type Check
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $tmp_path);
            finfo_close($finfo);
            
            $allowed_mimes = ['image/jpeg', 'image/png', 'application/pdf'];
            if (!in_array($mime_type, $allowed_mimes)) {
                set_toast('error', 'Invalid file type. Only JPG, PNG, and PDF allowed.');
                redirect("gig_details.php?id=$gig_id");
            }
            
            // File extension
            $ext = 'jpg';
            if ($mime_type === 'image/png') $ext = 'png';
            if ($mime_type === 'application/pdf') $ext = 'pdf';
            
            // Safe rename
            $new_filename = uniqid('receipt_', true) . '.' . $ext;
            $upload_path = __DIR__ . '/uploads/' . basename($new_filename);
            
            if (move_uploaded_file($tmp_path, $upload_path)) {
                // Insert Order
                $db_upload_path = 'uploads/' . basename($new_filename);
                $stmt = $pdo->prepare("INSERT INTO orders (buyer_id, gig_id, status, payment_proof_path) VALUES (?, ?, 'paid', ?)");
                try {
                    $stmt->execute([$_SESSION['user_id'], $gig_id, $db_upload_path]);
                    set_toast('success', 'Order placed successfully!');
                    redirect('user_dashboard.php?mode=buying');
                } catch (\Exception $e) {
                    set_toast('error', 'Error creating order.');
                    error_log($e->getMessage());
                }
            } else {
                set_toast('error', 'Failed to save uploaded file.');
            }
        } else {
            set_toast('error', 'Please upload a valid payment proof.');
        }
    }
}

require_once 'includes/header.php';
?>

<div class="max-w-5xl mx-auto animate-fade-in-up">
    <!-- Breadcrumb -->
    <nav class="mb-6 text-sm text-gray-400 font-medium">
        <a href="marketplace.php" class="hover:text-uitmPurple transition-colors">Marketplace</a>
        <span class="mx-2">›</span>
        <span class="text-gray-700"><?php echo escape($gig['category']); ?></span>
    </nav>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Left: Gig Info -->
        <div class="flex-grow bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden">
            <!-- Header gradient band -->
            <div class="h-2 bg-gradient-to-r from-uitmPurple via-indigo-600 to-blue-700 bg-moving-gradient"></div>
            
            <div class="p-8 md:p-10">
                <span class="inline-block bg-purple-100 text-uitmPurple px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest mb-6">
                    <?php echo escape($gig['category']); ?>
                </span>
                
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 mb-5 leading-tight font-serif"><?php echo escape($gig['title']); ?></h1>
                
                <!-- Seller Info Card -->
                <div class="flex items-center gap-4 mb-8 p-4 bg-gray-50 rounded-2xl border border-gray-100">
                    <div class="w-12 h-12 rounded-full bg-gradient-to-br from-uitmPurple to-indigo-600 flex items-center justify-center text-white font-extrabold text-lg flex-shrink-0">
                        <?php echo strtoupper(substr($gig['seller_name'], 0, 1)); ?>
                    </div>
                    <div>
                        <p class="font-bold text-gray-900"><?php echo escape($gig['seller_name']); ?></p>
                        <p class="text-sm text-gray-500"><?php echo escape($gig['campus']); ?></p>
                    </div>
                    <div class="ml-auto">
                        <span class="bg-green-100 text-green-700 text-xs font-bold px-3 py-1 rounded-full">Available</span>
                    </div>
                </div>
                
                <h2 class="text-lg font-bold text-gray-800 mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-uitmPurple" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    About this Gig
                </h2>
                <p class="text-gray-600 whitespace-pre-line leading-relaxed text-base">
                    <?php echo escape($gig['description']); ?>
                </p>
            </div>
        </div>
        
        <!-- Right: Order Panel -->
        <div class="lg:w-80 xl:w-96 flex-shrink-0">
            <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden sticky top-24">
                <!-- Price header -->
                <div class="bg-gradient-to-br from-[#1a0033] to-uitmPurple p-8 text-center text-white">
                    <div class="text-sm uppercase tracking-widest text-white/70 font-bold mb-2">Starting Price</div>
                    <div class="text-5xl font-extrabold tracking-tight font-serif">RM <?php echo number_format($gig['price'], 2); ?></div>
                    <div class="text-white/60 text-sm mt-2">One-time fee</div>
                </div>
                
                <div class="p-6">
                    <?php if($_SESSION['role'] === 'student' && $gig['seller_id'] != $_SESSION['user_id']): ?>
                        <h3 class="text-lg font-bold text-gray-800 mb-5">Place your order</h3>
                        <form action="gig_details.php?id=<?php echo $gig_id; ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                            <input type="hidden" name="action" value="buy">
                            <div>
                                <label class="block text-sm text-gray-700 font-bold mb-2">Upload Payment Proof</label>
                                <div class="border-2 border-dashed border-gray-200 rounded-2xl p-4 text-center hover:border-uitmPurple transition-colors cursor-pointer">
                                    <input type="file" id="payment_proof_input" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" required class="block w-full text-sm text-gray-500 file:mr-3 file:py-1.5 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-purple-100 file:text-uitmPurple hover:file:bg-purple-200 transition-all">
                                    <p class="text-xs text-gray-400 mt-2">JPG, PNG, or PDF · Max 2MB</p>
                                </div>
                            </div>
                            <button type="submit" class="w-full bg-gradient-to-r from-uitmPurple to-indigo-800 text-white font-bold py-4 px-6 rounded-2xl hover:shadow-xl hover:scale-[1.02] transition-all duration-300 shadow-md text-base">
                                Submit Order
                            </button>
                        </form>
                        
                        <div class="mt-4 border-t border-gray-100 pt-4">
                            <a href="chat.php?user=<?php echo $gig['seller_id']; ?>" class="w-full border-2 border-uitmPurple/30 text-uitmPurple hover:bg-purple-50 font-bold py-3 px-6 rounded-2xl transition-all duration-300 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                Contact Seller
                            </a>
                        </div>
                    <?php elseif($gig['seller_id'] == $_SESSION['user_id']): ?>
                        <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-4 rounded-2xl text-center font-medium flex flex-col items-center gap-2">
                            <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            This is your own gig.
                        </div>
                    <?php endif; ?>
                    
                    <!-- Trust badges -->
                    <div class="mt-6 space-y-3">
                        <div class="flex items-center gap-3 text-sm text-gray-500">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            Verified UiTM student seller
                        </div>
                        <div class="flex items-center gap-3 text-sm text-gray-500">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            Direct campus peer support
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
