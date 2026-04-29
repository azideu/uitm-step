<?php
// gig_details.php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

$gig_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch gig and seller info
$stmt = $pdo->prepare("
    SELECT g.*, u.name as seller_name, u.campus, u.profile_picture 
    FROM gigs g 
    JOIN users u ON g.seller_id = u.user_id 
    WHERE g.gig_id = ?
");
$stmt->execute([$gig_id]);
$gig = $stmt->fetch();

if (!$gig) {
    set_toast('error', 'Gig not found.');
    redirect('marketplace.php');
}

// Ensure the gig is active OR the current user has a relationship to it
$is_seller = ($gig['seller_id'] == $_SESSION['user_id']);
$is_admin = ($_SESSION['role'] === 'admin');

// Check if user is a buyer with an active order for this gig
$stmt_check = $pdo->prepare("SELECT 1 FROM orders WHERE gig_id = ? AND buyer_id = ? LIMIT 1");
$stmt_check->execute([$gig_id, $_SESSION['user_id']]);
$is_buyer = (bool)$stmt_check->fetchColumn();

if ($gig['status'] !== 'active' && !$is_seller && !$is_admin && !$is_buyer) {
    set_toast('error', 'This gig is no longer active.');
    redirect('marketplace.php');
}

// Handle Order placement (Purchase)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'buy') {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        set_toast('error', 'Invalid security token.');
        redirect("gig_details.php?id=$gig_id");
    }
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
            require_once 'includes/storage.php';
            $uploaded_path = Storage::upload($tmp_path, 'receipts/' . $new_filename, $mime_type);
            
            if ($uploaded_path) {
                // Insert Order
                $stmt = $pdo->prepare("INSERT INTO orders (buyer_id, gig_id, status, payment_proof_path) VALUES (?, ?, 'paid', ?)");
                try {
                    $stmt->execute([$_SESSION['user_id'], $gig_id, $uploaded_path]);
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
    <nav class="mb-6 text-sm text-gray-400 dark:text-slate-500 font-medium transition-colors duration-300">
        <a href="marketplace.php" class="hover:text-uitmPurple dark:hover:text-purple-400 transition-colors">Marketplace</a>
        <span class="mx-2">›</span>
        <span class="text-gray-700 dark:text-slate-300"><?php echo escape($gig['category']); ?></span>
    </nav>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Left: Gig Info -->
        <div class="flex-grow bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            <!-- Header gradient band -->
            <div class="h-2 bg-uitmPurple"></div>
            
            <div class="p-8 md:p-10">
                <span class="inline-block bg-purple-100 dark:bg-purple-900/30 text-uitmPurple dark:text-purple-300 px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-widest mb-6 transition-colors duration-300">
                    <?php echo escape($gig['category']); ?>
                </span>
                
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white mb-5 leading-tight font-serif transition-colors duration-300"><?php echo escape($gig['title']); ?></h1>
                
                <!-- Seller Info Card -->
                <div class="flex items-center gap-4 mb-8 p-4 bg-gray-50 dark:bg-slate-800/50 rounded-2xl border border-gray-100 dark:border-slate-700 transition-colors duration-300">
                    <?php
                        $seller_avatar = !empty($gig['profile_picture']) 
                            ? escape($gig['profile_picture']) 
                            : 'https://ui-avatars.com/api/?name=' . urlencode($gig['seller_name']) . '&background=330066&color=FFD700';
                    ?>
                    <div class="w-12 h-12 rounded-full bg-uitmPurple flex items-center justify-center text-white font-extrabold text-lg flex-shrink-0 overflow-hidden border-2 border-white shadow-sm">
                        <img src="<?php echo $seller_avatar; ?>" alt="<?php echo escape($gig['seller_name']); ?>" class="w-full h-full object-cover">
                    </div>
                    <div>
                        <p class="font-bold text-gray-900 dark:text-white transition-colors duration-300"><?php echo escape($gig['seller_name']); ?></p>
                        <p class="text-sm text-gray-500 dark:text-slate-400 transition-colors duration-300"><?php echo escape(str_replace(['UiTM Kampus ', 'UiTM '], '', $gig['campus'])); ?></p>
                    </div>
                    <div class="ml-auto">
                        <span class="bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-300 text-xs font-bold px-3 py-1 rounded-full transition-colors duration-300">Available</span>
                    </div>
                </div>
                
                <?php 
                $media_items = [];
                if (!empty($gig['youtube_url'])) {
                    if (preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/i', $gig['youtube_url'], $matches)) {
                        $media_items[] = ['type' => 'youtube', 'content' => 'https://www.youtube.com/embed/' . $matches[1]];
                    }
                }
                if (!empty($gig['image_url'])) {
                    $media_items[] = ['type' => 'image', 'content' => escape($gig['image_url'])];
                }
                ?>

                <?php if (!empty($media_items)): ?>
                    <div class="mb-8 relative group">
                        <div id="gig-media-slider" class="relative w-full aspect-video bg-black rounded-2xl overflow-hidden shadow-lg border border-gray-100 dark:border-slate-800 transition-colors duration-300">
                            <?php foreach ($media_items as $index => $item): ?>
                                <div class="media-slide absolute inset-0 transition-opacity duration-500 <?php echo $index === 0 ? 'opacity-100 z-10' : 'opacity-0 z-0'; ?>" data-index="<?php echo $index; ?>">
                                    <?php if ($item['type'] === 'youtube'): ?>
                                        <iframe class="w-full h-full" src="<?php echo $item['content']; ?>?enablejsapi=1" title="YouTube video player" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                    <?php else: ?>
                                        <img src="<?php echo $item['content']; ?>" alt="Gig Image" class="w-full h-full object-cover">
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>

                            <?php if (count($media_items) > 1): ?>
                                <!-- Navigation Arrows -->
                                <button onclick="changeSlide(-1)" class="absolute left-4 top-1/2 -translate-y-1/2 z-20 bg-black/50 hover:bg-black/70 text-white p-3 rounded-full backdrop-blur-sm transition-all opacity-0 group-hover:opacity-100">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                                </button>
                                <button onclick="changeSlide(1)" class="absolute right-4 top-1/2 -translate-y-1/2 z-20 bg-black/50 hover:bg-black/70 text-white p-3 rounded-full backdrop-blur-sm transition-all opacity-0 group-hover:opacity-100">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </button>

                                <!-- Navigation Dots -->
                                <div class="absolute bottom-6 left-1/2 -translate-x-1/2 z-20 flex gap-2">
                                    <?php foreach ($media_items as $index => $item): ?>
                                        <button onclick="goToSlide(<?php echo $index; ?>)" class="slider-dot w-2.5 h-2.5 rounded-full transition-all <?php echo $index === 0 ? 'bg-white w-6' : 'bg-white/50'; ?>" data-index="<?php echo $index; ?>"></button>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <script>
                            let currentSlide = 0;
                            const slides = document.querySelectorAll('.media-slide');
                            const dots = document.querySelectorAll('.slider-dot');
                            const totalSlides = slides.length;

                            function updateSlider() {
                                slides.forEach((slide, i) => {
                                    if (i === currentSlide) {
                                        slide.classList.remove('opacity-0', 'z-0');
                                        slide.classList.add('opacity-100', 'z-10');
                                    } else {
                                        slide.classList.add('opacity-0', 'z-0');
                                        slide.classList.remove('opacity-100', 'z-10');
                                        // Pause YouTube videos when sliding away
                                        const iframe = slide.querySelector('iframe');
                                        if (iframe) {
                                            iframe.contentWindow.postMessage('{"event":"command","func":"pauseVideo","args":""}', '*');
                                        }
                                    }
                                });
                                dots.forEach((dot, i) => {
                                    if (i === currentSlide) {
                                        dot.classList.add('bg-white', 'w-6');
                                        dot.classList.remove('bg-white/50');
                                    } else {
                                        dot.classList.remove('bg-white', 'w-6');
                                        dot.classList.add('bg-white/50');
                                    }
                                });
                            }

                            function changeSlide(direction) {
                                currentSlide = (currentSlide + direction + totalSlides) % totalSlides;
                                updateSlider();
                            }

                            function goToSlide(index) {
                                currentSlide = index;
                                updateSlider();
                            }
                        </script>
                    </div>
                <?php endif; ?>

                <h2 class="text-lg font-bold text-gray-800 dark:text-slate-200 mb-3 flex items-center gap-2 transition-colors duration-300">
                    <svg class="w-5 h-5 text-uitmPurple dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    About this Gig
                </h2>
                <p class="text-gray-600 dark:text-slate-400 whitespace-pre-line leading-relaxed text-base transition-colors duration-300">
                    <?php echo escape($gig['description']); ?>
                </p>
            </div>
        </div>
        
        <!-- Right: Order Panel -->
        <div class="lg:w-80 xl:w-96 flex-shrink-0">
            <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-sm border border-gray-100 dark:border-slate-800 overflow-hidden sticky top-24 transition-colors duration-300">
                <!-- Price header -->
                <div class="bg-uitmPurple p-8 text-center text-white">
                    <div class="text-sm uppercase tracking-widest text-white/70 font-bold mb-2">Starting Price</div>
                    <div class="text-5xl font-extrabold tracking-tight font-serif">RM <?php echo number_format($gig['price'], 2); ?></div>
                    <div class="text-white/60 text-sm mt-2">One-time fee</div>
                </div>
                
                <div class="p-6">
                    <?php if($_SESSION['role'] === 'student' && $gig['seller_id'] != $_SESSION['user_id']): ?>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-slate-200 mb-5 transition-colors duration-300">Place your order</h3>
                        <form action="gig_details.php?id=<?php echo $gig_id; ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
                            <input type="hidden" name="action" value="buy">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <div>
                                <label class="block text-sm text-gray-700 dark:text-slate-300 font-bold mb-2 flex items-center gap-1.5 transition-colors duration-300">
                                    <svg class="w-4 h-4 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                                    Secure Upload Payment Proof
                                </label>
                                <div class="border-2 border-dashed border-gray-200 dark:border-slate-700 rounded-2xl p-5 text-center bg-gray-50 dark:bg-slate-800/50 hover:bg-white dark:hover:bg-slate-800 hover:border-uitmPurple dark:hover:border-purple-500 transition-all cursor-pointer group relative">
                                    <input type="file" id="payment_proof_input" name="payment_proof" accept=".jpg,.jpeg,.png,.pdf" required class="block w-full text-sm text-gray-500 dark:text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-bold file:bg-purple-100 dark:file:bg-purple-900/30 file:text-uitmPurple dark:file:text-purple-300 hover:file:bg-purple-200 dark:hover:file:bg-purple-900/50 transition-all cursor-pointer relative z-10">
                                    <div class="mt-3 text-xs text-gray-400 dark:text-slate-500 flex items-center justify-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                        Bank transfer receipt (JPG, PNG, PDF · Max 2MB)
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="w-full bg-uitmPurple text-white font-bold py-4 px-6 rounded-md shadow-sm hover:bg-purple-900 transition-all duration-300 text-base flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Verify & Submit Order
                            </button>
                        </form>
                        
                        <div class="mt-4 border-t border-gray-100 dark:border-slate-800 pt-4 transition-colors duration-300">
                            <a href="chat.php?user=<?php echo $gig['seller_id']; ?>" class="w-full border-2 border-uitmPurple/30 dark:border-purple-500/30 text-uitmPurple dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20 font-bold py-3 px-6 rounded-2xl transition-all duration-300 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                Contact Seller
                            </a>
                        </div>
                    <?php elseif($gig['seller_id'] == $_SESSION['user_id']): ?>
                        <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 px-4 py-4 rounded-2xl text-center font-medium flex flex-col items-center gap-2 transition-colors duration-300">
                            <svg class="w-8 h-8 text-blue-400 dark:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            This is your own gig.
                        </div>
                        <div class="mt-4">
                            <a href="edit_gig.php?id=<?php echo $gig_id; ?>" class="w-full bg-white dark:bg-slate-800 border-2 border-uitmPurple text-uitmPurple dark:text-purple-400 hover:bg-uitmPurple hover:text-white dark:hover:bg-purple-900 font-bold py-3 px-6 rounded-2xl transition-all duration-300 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                Edit Gig Details
                            </a>
                        </div>
                    <?php endif; ?>
                    
                    <!-- Trust badges -->
                    <div class="mt-6 space-y-3">
                        <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-slate-400 transition-colors duration-300">
                            <svg class="w-4 h-4 text-green-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path></svg>
                            Verified UiTM student seller
                        </div>
                        <div class="flex items-center gap-3 text-sm text-gray-500 dark:text-slate-400 transition-colors duration-300">
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
