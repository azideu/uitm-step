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

<div class="max-w-4xl mx-auto bg-white rounded shadow-lg overflow-hidden flex flex-col md:flex-row">
    <div class="p-8 md:w-2/3 border-b md:border-b-0 md:border-r border-gray-200">
        <span class="inline-block bg-purple-100 text-uitmPurple px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wide mb-4">
            <?php echo escape($gig['category']); ?>
        </span>
        <h1 class="text-3xl font-bold text-gray-900 mb-4"><?php echo escape($gig['title']); ?></h1>
        
        <div class="flex items-center text-gray-600 mb-6">
            <span class="font-medium mr-4">Seller: <?php echo escape($gig['seller_name']); ?></span>
            <span class="font-medium">Campus: <?php echo escape($gig['campus']); ?></span>
        </div>
        
        <h2 class="text-xl font-bold text-gray-800 mb-2">Description</h2>
        <p class="text-gray-700 whitespace-pre-line leading-relaxed mb-6">
            <?php echo escape($gig['description']); ?>
        </p>
    </div>
    
    <div class="p-8 md:w-1/3 bg-gray-50 flex flex-col justify-center">
        <div class="text-center mb-6">
            <div class="text-sm text-gray-500 uppercase tracking-wide mb-1">Price</div>
            <div class="text-4xl font-bold text-uitmPurple">RM <?php echo number_format($gig['price'], 2); ?></div>
        </div>
        
        <?php if($_SESSION['role'] === 'student' && $gig['seller_id'] != $_SESSION['user_id']): ?>
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-lg font-bold text-gray-800 mb-4">Purchase this Gig</h3>
                <form action="gig_details.php?id=<?php echo $gig_id; ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="action" value="buy">
                    <div class="mb-4">
                        <label class="block text-sm text-gray-700 font-bold mb-2">Upload Payment Proof</label>
                        <input type="file" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" required class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-purple-50 file:text-uitmPurple hover:file:bg-purple-100">
                        <p class="text-xs text-gray-500 mt-1">Allowed: JPG, PNG, PDF. Max: 2MB.</p>
                    </div>
                    <button type="submit" class="w-full bg-uitmPurple text-white font-bold py-3 px-4 rounded hover:bg-purple-900 transition shadow">Submit Order</button>
                </form>
            </div>
            
            <div class="mt-4 text-center">
                <a href="chat.php?user=<?php echo $gig['seller_id']; ?>" class="inline-block text-uitmPurple hover:underline font-medium">Contact Seller</a>
            </div>
        <?php elseif($gig['seller_id'] == $_SESSION['user_id']): ?>
            <div class="bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded text-center font-medium">
                This is your own Gig.
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
