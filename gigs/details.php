<?php
// details.php
require_once '../includes/config.php';
require_once '../includes/functions.php';
require_once '../includes/db.php';

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
    redirect('marketplace');
}

// Ensure the gig is active OR the current user has a relationship to it
$is_seller = isset($_SESSION['user_id']) && ($gig['seller_id'] == $_SESSION['user_id']);
$is_admin = isset($_SESSION['role']) && ($_SESSION['role'] === 'admin');

// Check if user is a buyer with an active order for this gig
$is_buyer = false;
if (isset($_SESSION['user_id'])) {
    $stmt_check = $pdo->prepare("SELECT 1 FROM orders WHERE gig_id = ? AND buyer_id = ? LIMIT 1");
    $stmt_check->execute([$gig_id, $_SESSION['user_id']]);
    $is_buyer = (bool)$stmt_check->fetchColumn();
}

if ($gig['status'] !== 'active' && !$is_seller && !$is_admin && !$is_buyer) {
    set_toast('error', 'This gig is no longer active.');
    redirect('marketplace');
}

// Fetch all reviews for this gig (with error handling for missing table)
$reviews = [];
$completed_order_for_review = null;

// Check if reviews table exists
try {
    $checkTableStmt = $pdo->query("SELECT 1 FROM INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'reviews' LIMIT 1");
    $tableExists = $checkTableStmt->fetchColumn() !== false;
    
    if ($tableExists) {
        $stmt_reviews = $pdo->prepare("
            SELECT r.*, u.name as reviewer_name, u.profile_picture
            FROM reviews r
            JOIN users u ON r.buyer_id = u.user_id
            WHERE r.gig_id = ?
            ORDER BY r.created_at DESC
        ");
        $stmt_reviews->execute([$gig_id]);
        $reviews = $stmt_reviews->fetchAll();

        // Check if current user (buyer) has a paid, delivered or completed order that can be reviewed
        if (isset($_SESSION['role']) && $_SESSION['role'] === 'student' && !$is_seller) {
            $stmt_my_order = $pdo->prepare("
                SELECT o.* FROM orders o
                WHERE o.gig_id = ?
                  AND o.buyer_id = ?
                  AND o.payment_proof_path IS NOT NULL
                  AND o.payment_proof_path != ''
                  AND TRIM(o.payment_proof_path) != ''
                  AND o.status IN ('paid','delivered','complete')
                  AND NOT EXISTS (SELECT 1 FROM reviews WHERE order_id = o.order_id)
                ORDER BY o.created_at DESC
                LIMIT 1
            ");
            $stmt_my_order->execute([$gig_id, $_SESSION['user_id']]);
            $completed_order_for_review = $stmt_my_order->fetch();
        }
    }
} catch (\Exception $e) {
    // Reviews table might not exist yet - this is fine
    error_log("Reviews fetch error (safe to ignore): " . $e->getMessage());
    $reviews = [];
    $completed_order_for_review = null;
}

// Handle Order placement (Purchase)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'buy') {
    if (!isset($_SESSION['user_id'])) {
        set_toast('error', 'Please log in to purchase.');
        redirect('login');
    }

    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        set_toast('error', 'Invalid security token.');
        redirect("gigs/details?id=$gig_id");
    }

    if ($_SESSION['role'] !== 'student') {
        set_toast('error', 'Only students can buy gigs.');
        redirect("gigs/details?id=$gig_id");
    } elseif ($gig['seller_id'] == $_SESSION['user_id']) {
        set_toast('error', 'You cannot buy your own gig.');
        redirect("gigs/details?id=$gig_id");
    } else {
        $existing_stmt = $pdo->prepare("SELECT order_id, status FROM orders WHERE gig_id = ? AND buyer_id = ? AND status IN ('pending', 'paid', 'delivered', 'complete') LIMIT 1");
        $existing_stmt->execute([$gig_id, $_SESSION['user_id']]);
        $existing_order = $existing_stmt->fetch();

        if ($existing_order) {
            if ($existing_order['status'] === 'pending') {
                redirect("payment-gateway?order_id=" . $existing_order['order_id']);
            }
            set_toast('info', 'You already have an active order for this gig.');
            redirect('dashboard?mode=buying');
        }

        try {
            $create_stmt = $pdo->prepare("INSERT INTO orders (buyer_id, gig_id, status) VALUES (?, ?, 'pending')");
            $create_stmt->execute([$_SESSION['user_id'], $gig_id]);
            $new_order_id = $pdo->lastInsertId();
            redirect("payment-gateway?order_id=" . $new_order_id);
        } catch (\Exception $e) {
            error_log("Order creation error: " . $e->getMessage());
            set_toast('error', 'Unable to create your order. Please try again.');
            redirect("gigs/details?id=$gig_id");
        }
    }
}

require_once '../includes/header.php';
?>

<div class="max-w-5xl mx-auto animate-fade-in-up">
    <!-- Breadcrumb -->
    <nav class="mb-6 text-sm text-gray-400 dark:text-slate-500 font-medium transition-colors duration-300">
        <a href="<?php echo ROOT_URL; ?>marketplace" class="hover:text-uitmPurple dark:hover:text-purple-400 transition-colors">Marketplace</a>
        <span class="mx-2">›</span>
        <span class="text-gray-700 dark:text-slate-300"><?php echo escape($gig['category']); ?></span>
    </nav>

    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Left: Gig Info -->
        <div class="flex-grow bg-white dark:bg-slate-900 rounded-3xl shadow-xl border border-gray-100 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            <!-- Header gradient band -->
            <div class="h-2 bg-uitmPurple"></div>
            
            <div class="p-8 md:p-10">
                <!-- Category Label -->
                <div class="inline-flex items-center gap-2 border-l-2 border-uitmGold/70 pl-3 text-slate-500 dark:text-slate-400 text-xs font-bold uppercase tracking-widest mb-6">
                    Category: <?php echo escape($gig['category']); ?>
                </div>
                
                <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 dark:text-white mb-5 leading-tight font-serif transition-colors duration-300"><?php echo escape($gig['title']); ?></h1>
                
                <!-- Seller Info Card -->
                <div class="flex items-center gap-4 mb-8 p-4 bg-gray-50 dark:bg-slate-800/50 rounded-2xl border border-gray-100 dark:border-slate-700 transition-colors duration-300">
                    <?php
                        $seller_avatar = !empty($gig['profile_picture']) 
                            ? asset_url($gig['profile_picture']) 
                            : get_avatar_url($gig['seller_name']);
                    ?>
                    <div class="w-12 h-12 rounded-full bg-uitmPurple flex items-center justify-center text-white font-extrabold text-lg flex-shrink-0 overflow-hidden border-2 border-white shadow-xl">
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
                    $media_items[] = ['type' => 'image', 'content' => asset_url($gig['image_url'])];
                }
                ?>

                <?php if (!empty($media_items)): ?>
                    <div class="mb-8 relative group">
                        <div id="gig-media-slider" class="relative w-full aspect-video bg-black rounded-2xl overflow-hidden shadow-2xl border border-gray-100 dark:border-slate-800 transition-colors duration-300">
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
            <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-xl border border-gray-100 dark:border-slate-800 overflow-hidden sticky top-24 transition-colors duration-300">
                <!-- Price header -->
                <div class="bg-uitmPurple p-8 text-center text-white">
                    <div class="text-sm uppercase tracking-widest text-white/70 font-bold mb-2">Starting Price</div>
                    <div class="text-5xl font-extrabold tracking-tight font-serif">RM <?php echo number_format($gig['price'], 2); ?></div>
                    <div class="text-white/60 text-sm mt-2">One-time fee</div>
                </div>
                
                <div class="p-6">
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <div class="space-y-4">
                            <div class="bg-indigo-50/50 dark:bg-slate-800/80 border border-indigo-100/50 dark:border-slate-700/80 text-slate-700 dark:text-slate-300 p-5 rounded-2xl text-center text-sm leading-relaxed mb-4 transition-colors duration-300">
                                Interested in hiring <span class="font-bold text-uitmPurple dark:text-purple-300"><?php echo escape($gig['seller_name']); ?></span>? Sign in to place an order or contact this seller.
                            </div>
                            <a href="<?php echo ROOT_URL; ?>login?redirect=gigs/details?id=<?php echo $gig_id; ?>" class="w-full bg-uitmPurple text-white font-bold py-4 px-6 rounded-2xl shadow-xl hover:bg-purple-900 transition-all duration-300 text-base flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                                Login to Hire Seller
                            </a>
                            <a href="<?php echo ROOT_URL; ?>login?redirect=gigs/details?id=<?php echo $gig_id; ?>" class="w-full border-2 border-uitmPurple/30 dark:border-purple-500/30 text-uitmPurple dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20 font-bold py-3 px-6 rounded-2xl transition-all duration-300 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                Contact Seller
                            </a>
                        </div>
                    <?php elseif(isset($_SESSION['role']) && $_SESSION['role'] === 'student' && $gig['seller_id'] != $_SESSION['user_id']): ?>
                        <h3 class="text-lg font-bold text-gray-800 dark:text-slate-200 mb-5 transition-colors duration-300">Proceed to payment</h3>
                        <form action="details?id=<?php echo $gig_id; ?>" method="POST" class="space-y-4">
                            <input type="hidden" name="action" value="buy">
                            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                            <button type="submit" class="w-full bg-uitmPurple text-white font-bold py-4 px-6 rounded-2xl shadow-xl hover:bg-purple-900 transition-all duration-300 text-base flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                Continue to Payment
                            </button>
                        </form>
                        
                        <div class="mt-4 border-t border-gray-100 dark:border-slate-800 pt-4 transition-colors duration-300">
                            <a href="<?php echo ROOT_URL; ?>chat?user=<?php echo $gig['seller_id']; ?>" class="w-full border-2 border-uitmPurple/30 dark:border-purple-500/30 text-uitmPurple dark:text-purple-400 hover:bg-purple-50 dark:hover:bg-purple-900/20 font-bold py-3 px-6 rounded-2xl transition-all duration-300 flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"></path></svg>
                                Contact Seller
                            </a>
                        </div>
                    <?php elseif(isset($_SESSION['user_id']) && $gig['seller_id'] == $_SESSION['user_id']): ?>
                        <div class="bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 text-blue-700 dark:text-blue-300 px-4 py-4 rounded-2xl text-center font-medium flex flex-col items-center gap-2 transition-colors duration-300">
                            <svg class="w-8 h-8 text-blue-400 dark:text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            This is your own gig.
                        </div>
                        <div class="mt-4">
                            <a href="<?php echo ROOT_URL; ?>gigs/edit?id=<?php echo $gig_id; ?>" class="w-full bg-white dark:bg-slate-800 border-2 border-uitmPurple text-uitmPurple dark:text-purple-400 hover:bg-uitmPurple hover:text-white dark:hover:bg-purple-900 font-bold py-3 px-6 rounded-2xl transition-all duration-300 flex items-center justify-center gap-2">
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

                    <?php if($_SESSION['role'] === 'student' && $gig['seller_id'] != $_SESSION['user_id']): ?>
                    <!-- Report Seller -->
                    <div class="mt-6 pt-5 border-t border-gray-100 dark:border-slate-800 transition-colors duration-300">
                        <button
                            id="open-report-modal-btn"
                            onclick="openReportModal()"
                            class="w-full flex items-center justify-center gap-2 text-xs text-gray-400 dark:text-slate-500 hover:text-red-500 dark:hover:text-red-400 font-medium py-2 rounded-xl hover:bg-red-50 dark:hover:bg-red-900/20 border border-transparent hover:border-red-200 dark:hover:border-red-800/50 transition-all duration-300 group"
                        >
                            <svg class="w-3.5 h-3.5 group-hover:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                            </svg>
                            Report this seller
                        </button>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- =====================================================================
     REVIEWS SECTION
     ===================================================================== -->
<div class="max-w-5xl mx-auto mt-12 mb-12 animate-fade-in-up">
    <div class="bg-white dark:bg-slate-900 rounded-3xl shadow-xl border border-gray-100 dark:border-slate-800 overflow-hidden transition-colors duration-300">
        <!-- Header -->
        <div class="h-2 bg-blue-500"></div>
        <div class="p-8 md:p-10">
            <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white mb-8 flex items-center gap-3 transition-colors duration-300">
                <svg class="w-7 h-7 text-blue-500 dark:text-blue-400" fill="currentColor" viewBox="0 0 24 24">
                    <path fill-rule="evenodd" clip-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z"></path>
                </svg>
                Reviews & Ratings
            </h2>

            <!-- Review Form (always show, but conditionally enabled) -->
            <div class="mb-10 p-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800/50 rounded-2xl">
                <h3 class="text-lg font-bold text-gray-800 dark:text-slate-200 mb-5 flex items-center gap-2 transition-colors duration-300">
                    Share Your Experience
                </h3>

                <?php if (!$completed_order_for_review): ?>
                <div class="mb-5 p-4 bg-gray-100 dark:bg-gray-800/50 border border-gray-200 dark:border-gray-700 rounded-xl">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            You can leave a review after submitting payment proof for your order.
                        </p>
                    </div>
                </div>
                <?php endif; ?>

                <form action="<?php echo ROOT_URL; ?>api/review_action" method="POST" id="review-form" class="space-y-5 <?php echo !$completed_order_for_review ? 'opacity-50 pointer-events-none' : ''; ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                    <?php if ($completed_order_for_review): ?>
                    <input type="hidden" name="order_id" value="<?php echo $completed_order_for_review['order_id']; ?>">
                    <input type="hidden" name="gig_id" value="<?php echo $gig_id; ?>">
                    <?php endif; ?>

                    <!-- Rating Stars -->
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-slate-300 mb-3">
                            Your Rating <span class="text-red-500">*</span>
                        </label>
                        <div class="flex gap-3">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                            <label class="cursor-pointer group">
                                <input type="radio" name="rating" value="<?php echo $i; ?>" required class="sr-only rating-input" data-rating="<?php echo $i; ?>" <?php echo !$completed_order_for_review ? 'disabled' : ''; ?>>
                                <svg class="w-10 h-10 text-gray-300 dark:text-slate-600 group-hover:text-amber-400 transition-colors duration-200 rating-star" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" fill-rule="evenodd" clip-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z"></path>
                                </svg>
                            </label>
                            <?php endfor; ?>
                        </div>
                        <div id="rating-text" class="mt-2 text-xs text-gray-500 dark:text-slate-400 font-medium">Select a rating</div>
                    </div>

                    <!-- Review Text -->
                    <div>
                        <label for="review_text" class="block text-sm font-bold text-gray-700 dark:text-slate-300 mb-2">
                            Your Review <span class="text-red-500">*</span>
                            <span class="text-gray-400 font-normal ml-2 text-xs">(10-1000 characters)</span>
                        </label>
                        <textarea
                            id="review_text"
                            name="review_text"
                            rows="4"
                            minlength="10"
                            maxlength="1000"
                            placeholder="Share your experience with this gig. Was the seller professional? Did they deliver quality work? How was the communication?"
                            class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-blue-400/40 focus:border-blue-400 dark:focus:ring-blue-800/40 dark:focus:border-blue-700 transition-all resize-none"
                            required
                            <?php echo !$completed_order_for_review ? 'disabled' : ''; ?>
                        ></textarea>
                        <div class="mt-2 flex justify-between items-center">
                            <p class="text-xs text-gray-400 dark:text-slate-500">
                                <span id="review-char-count">0</span>/1000 characters
                            </p>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <?php if ($completed_order_for_review): ?>
                    <div class="flex gap-3 pt-4">
                        <button type="submit" class="flex-1 bg-blue-500 hover:bg-blue-600 text-white font-bold py-3 px-6 rounded-xl transition-all duration-300 shadow-lg hover:shadow-blue-200 dark:hover:shadow-blue-900/30 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            Submit Review
                        </button>
                    </div>
                    <?php endif; ?>
                </form>

                <script>
                    const ratingInputs = document.querySelectorAll('.rating-input');
                    const ratingText = document.getElementById('rating-text');
                    const ratingStars = document.querySelectorAll('.rating-star');
                    const reviewTextarea = document.getElementById('review_text');
                    const reviewCharCount = document.getElementById('review-char-count');

                    const ratingLabels = {
                        1: '😞 Poor - Not satisfied',
                        2: '😐 Fair - Could be better',
                        3: '😊 Good - Satisfied',
                        4: '😄 Very Good - Highly satisfied',
                        5: '😍 Excellent - Outstanding'
                    };

                    ratingInputs.forEach((input, index) => {
                        input.addEventListener('change', function() {
                            const rating = this.value;
                            ratingText.textContent = ratingLabels[rating];

                            // Update star fill
                            ratingStars.forEach((star, starIndex) => {
                                if (starIndex < rating) {
                                    star.setAttribute('fill', 'currentColor');
                                    star.classList.remove('text-gray-300', 'dark:text-slate-600');
                                    star.classList.add('text-amber-400');
                                } else {
                                    star.setAttribute('fill', 'none');
                                    star.classList.add('text-gray-300', 'dark:text-slate-600');
                                    star.classList.remove('text-amber-400');
                                }
                            });
                        });

                        // Hover effect
                        input.parentElement.addEventListener('mouseenter', function() {
                            ratingStars.forEach((star, starIndex) => {
                                if (starIndex < index + 1) {
                                    star.classList.add('text-amber-400');
                                    star.classList.remove('text-gray-300', 'dark:text-slate-600');
                                } else {
                                    star.classList.remove('text-amber-400');
                                    star.classList.add('text-gray-300', 'dark:text-slate-600');
                                }
                            });
                        });
                    });

                    document.getElementById('rating-text').parentElement.addEventListener('mouseleave', function() {
                        const checked = document.querySelector('.rating-input:checked');
                        if (checked) {
                            const rating = checked.value;
                            ratingStars.forEach((star, starIndex) => {
                                if (starIndex < rating) {
                                    star.setAttribute('fill', 'currentColor');
                                    star.classList.remove('text-gray-300', 'dark:text-slate-600');
                                    star.classList.add('text-amber-400');
                                } else {
                                    star.setAttribute('fill', 'none');
                                    star.classList.add('text-gray-300', 'dark:text-slate-600');
                                    star.classList.remove('text-amber-400');
                                }
                            });
                        }
                    });

                    // Character counter
                    reviewTextarea.addEventListener('input', function() {
                        reviewCharCount.textContent = this.value.length;
                    });
                </script>
            </div>

            <!-- Reviews Display -->
            <?php if (!empty($reviews)): ?>
                <div class="space-y-5">
                    <?php 
                    $total_rating = 0;
                    foreach ($reviews as $review) {
                        $total_rating += $review['rating'];
                    }
                    $average_rating = number_format($total_rating / count($reviews), 1);
                    ?>
                    
                    <!-- Average Rating Summary -->
                    <div class="mb-8 p-6 bg-gradient-to-r from-amber-50 to-orange-50 dark:from-amber-900/20 dark:to-orange-900/20 border border-amber-200 dark:border-amber-800/50 rounded-2xl">
                        <div class="flex items-center gap-8">
                            <div class="flex flex-col items-center">
                                <div class="text-4xl font-extrabold text-amber-600 dark:text-amber-400"><?php echo $average_rating; ?></div>
                                <div class="flex gap-1 mt-2">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <svg class="w-4 h-4 <?php echo $i <= round($average_rating) ? 'fill-amber-400 text-amber-400' : 'fill-gray-200 dark:fill-slate-700 text-gray-200 dark:text-slate-700'; ?>" viewBox="0 0 24 24">
                                        <path d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z"></path>
                                    </svg>
                                    <?php endfor; ?>
                                </div>
                                <div class="text-xs text-gray-600 dark:text-slate-400 mt-2 font-medium"><?php echo count($reviews); ?> <?php echo count($reviews) === 1 ? 'review' : 'reviews'; ?></div>
                            </div>
                            
                            <!-- Rating Distribution -->
                            <div class="flex-1">
                                <?php 
                                $rating_distribution = array_count_values(array_column($reviews, 'rating'));
                                for ($i = 5; $i >= 1; $i--):
                                    $count = $rating_distribution[$i] ?? 0;
                                    $percentage = (count($reviews) > 0) ? ($count / count($reviews)) * 100 : 0;
                                ?>
                                <div class="flex items-center gap-3 mb-2">
                                    <span class="text-xs font-bold text-gray-600 dark:text-slate-400 w-6"><?php echo $i; ?></span>
                                    <div class="flex-1 bg-gray-200 dark:bg-slate-700 rounded-full h-2 overflow-hidden">
                                        <div class="bg-amber-400 h-full rounded-full" style="width: <?php echo $percentage; ?>%"></div>
                                    </div>
                                    <span class="text-xs font-medium text-gray-600 dark:text-slate-400 w-10 text-right"><?php echo $count; ?></span>
                                </div>
                                <?php endfor; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Individual Reviews -->
                    <div class="space-y-5">
                        <?php foreach ($reviews as $review): ?>
                        <div class="p-6 border border-gray-100 dark:border-slate-800 rounded-2xl hover:shadow-md dark:hover:shadow-slate-900 transition-all duration-300">
                            <!-- Review Header -->
                            <div class="flex items-start justify-between mb-4">
                                <div class="flex items-center gap-3 flex-1">
                                    <?php
                                        $reviewer_avatar = !empty($review['profile_picture']) 
                                            ? asset_url($review['profile_picture']) 
                                            : get_avatar_url($review['reviewer_name']);
                                    ?>
                                    <div class="w-10 h-10 rounded-full overflow-hidden border-2 border-white dark:border-slate-800 shadow-md flex-shrink-0">
                                        <img src="<?php echo $reviewer_avatar; ?>" alt="<?php echo escape($review['reviewer_name']); ?>" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <p class="font-bold text-gray-900 dark:text-white text-sm"><?php echo escape($review['reviewer_name']); ?></p>
                                        <p class="text-xs text-gray-500 dark:text-slate-400"><?php echo date('M d, Y', strtotime($review['created_at'])); ?></p>
                                    </div>
                                </div>
                                
                                <!-- Rating Stars -->
                                <div class="flex gap-1">
                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <svg class="w-4 h-4 <?php echo $i <= $review['rating'] ? 'fill-amber-400 text-amber-400' : 'fill-gray-200 dark:fill-slate-700 text-gray-200 dark:text-slate-700'; ?>" viewBox="0 0 24 24">
                                        <path d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z"></path>
                                    </svg>
                                    <?php endfor; ?>
                                </div>
                            </div>

                            <!-- Review Text -->
                            <p class="text-gray-700 dark:text-slate-300 text-sm leading-relaxed"><?php echo escape($review['review_text']); ?></p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <!-- No Reviews State -->
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-300 dark:text-slate-700 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" fill-rule="evenodd" clip-rule="evenodd" d="M10.788 3.21c.448-1.077 1.976-1.077 2.424 0l2.082 5.006 5.404.434c1.164.093 1.636 1.545.749 2.305l-4.117 3.527 1.257 5.273c.271 1.136-.964 2.033-1.96 1.425L12 18.354 7.373 21.18c-.996.608-2.231-.29-1.96-1.425l1.257-5.273-4.117-3.527c-.887-.76-.415-2.212.749-2.305l5.404-.434 2.082-5.005Z"></path>
                    </svg>
                    <p class="text-gray-500 dark:text-slate-400 font-medium">No reviews yet</p>
                    <p class="text-sm text-gray-400 dark:text-slate-500 mt-1">Be the first to review this gig!</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>


<?php if($_SESSION['role'] === 'student' && $gig['seller_id'] != $_SESSION['user_id']): ?>
<!-- =====================================================================
     REPORT SELLER MODAL
     ===================================================================== -->
<div
    id="report-modal-overlay"
    onclick="if(event.target===this) closeReportModal()"
    class="fixed inset-0 z-[200] flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm opacity-0 pointer-events-none transition-opacity duration-300"
    aria-modal="true"
    role="dialog"
    aria-labelledby="report-modal-title"
>
    <div
        id="report-modal-panel"
        class="relative w-full max-w-lg bg-white dark:bg-slate-900 rounded-2xl shadow-2xl border-x border-b border-gray-100 dark:border-slate-800 overflow-hidden transform scale-95 transition-all duration-300"
    >
        <div class="h-1.5 bg-gradient-to-r from-red-500 to-rose-600 rounded-t-2xl border-x border-gray-100 dark:border-slate-800"></div>

        <div class="p-8">
            <!-- Header -->
            <div class="flex items-start justify-between mb-6">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-2xl bg-red-100 dark:bg-red-900/30 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                        </svg>
                    </div>
                    <div>
                        <h2 id="report-modal-title" class="text-lg font-extrabold text-gray-900 dark:text-white">Report Seller</h2>
                        <p class="text-sm text-gray-500 dark:text-slate-400">Reporting: <span class="font-bold text-gray-700 dark:text-slate-300"><?php echo escape($gig['seller_name']); ?></span></p>
                    </div>
                </div>
                <button onclick="closeReportModal()" class="p-2 rounded-xl hover:bg-gray-100 dark:hover:bg-slate-800 text-gray-400 dark:text-slate-500 hover:text-gray-700 dark:hover:text-slate-300 transition-colors" aria-label="Close">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <!-- Info banner -->
            <div class="mb-5 flex gap-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 rounded-2xl p-4">
                <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-xs text-amber-700 dark:text-amber-300 leading-relaxed font-medium">
                    Reports are reviewed by UiTM STEP admins within 24–48 hours. False or malicious reports may result in action against your account.
                </p>
            </div>

            <!-- Form -->
            <form action="<?php echo ROOT_URL; ?>api/report_action" method="POST" id="report-form">
                <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
                <input type="hidden" name="reported_id" value="<?php echo $gig['seller_id']; ?>">
                <input type="hidden" name="redirect_to" value="<?php echo ROOT_URL; ?>gigs/details?id=<?php echo $gig_id; ?>">

                <!-- Reason -->
                <div class="mb-5">
                    <label class="block text-xs font-bold text-gray-500 dark:text-slate-400 uppercase tracking-widest mb-3">
                        Reason for report <span class="text-red-500">*</span>
                    </label>
                    <div class="grid grid-cols-1 gap-2" id="reason-options">
                        <?php
                        $reasons = [
                            'scam'                  => ['label' => 'Scam / Fraud',                  'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'],
                            'fake_payment_proof'    => ['label' => 'Fake Payment Proof',            'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>'],
                            'non_delivery'          => ['label' => 'Did Not Deliver Work',          'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>'],
                            'harassment'            => ['label' => 'Harassment / Threats',          'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>'],
                            'inappropriate_content' => ['label' => 'Inappropriate Gig Content',     'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636"></path></svg>'],
                            'other'                 => ['label' => 'Other',                         'icon' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>'],
                        ];
                        foreach ($reasons as $value => $meta):
                        ?>
                        <label class="reason-card flex items-center gap-3 p-3 rounded-xl border border-gray-100 dark:border-slate-800 cursor-pointer hover:border-red-300 dark:hover:border-red-700 hover:bg-red-50/50 dark:hover:bg-red-900/10 transition-all duration-200 has-[:checked]:border-red-500 has-[:checked]:bg-red-50 dark:has-[:checked]:bg-red-900/20 dark:has-[:checked]:border-red-600 group">
                            <input type="radio" name="reason" value="<?php echo $value; ?>" class="sr-only" required>
                            <span class="w-8 h-8 rounded-lg bg-gray-50 dark:bg-slate-800 flex items-center justify-center text-gray-400 group-hover:text-red-500 transition-colors group-has-[:checked]:bg-red-100 dark:group-has-[:checked]:bg-red-900/40 group-has-[:checked]:text-red-600">
                                <?php echo $meta['icon']; ?>
                            </span>
                            <span class="text-sm font-bold text-gray-700 dark:text-slate-300"><?php echo $meta['label']; ?></span>
                        </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Details -->
                <div class="mb-6">
                    <label for="report-details" class="block text-sm font-bold text-gray-700 dark:text-slate-300 mb-2">
                        Additional details <span class="text-gray-400 font-normal">(optional)</span>
                    </label>
                    <textarea
                        id="report-details"
                        name="details"
                        rows="3"
                        maxlength="1000"
                        placeholder="Describe what happened in as much detail as possible..."
                        class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-gray-900 dark:text-white placeholder-gray-400 dark:placeholder-slate-500 text-sm focus:outline-none focus:ring-2 focus:ring-red-400/40 focus:border-red-400 dark:focus:ring-red-800/40 dark:focus:border-red-700 transition-all resize-none"
                    ></textarea>
                    <p class="text-xs text-gray-400 dark:text-slate-500 mt-1 text-right"><span id="report-char-count">0</span>/1000</p>
                </div>

                <!-- Actions -->
                <div class="flex gap-3">
                    <button type="button" onclick="closeReportModal()" class="flex-1 py-3 rounded-xl border-2 border-gray-200 dark:border-slate-700 text-gray-600 dark:text-slate-400 font-bold text-sm hover:bg-gray-50 dark:hover:bg-slate-800 transition-all duration-300">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 py-3 rounded-xl bg-red-500 hover:bg-red-600 text-white font-bold text-sm shadow-lg hover:shadow-red-200 dark:hover:shadow-red-900/30 transition-all duration-300 flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                        </svg>
                        Submit Report
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    const reportOverlay = document.getElementById('report-modal-overlay');
    const reportPanel   = document.getElementById('report-modal-panel');
    const detailsTextarea = document.getElementById('report-details');
    const reportCharCount = document.getElementById('report-char-count');

    function openReportModal() {
        reportOverlay.classList.remove('opacity-0', 'pointer-events-none');
        reportPanel.classList.remove('scale-95');
        reportPanel.classList.add('scale-100');
        document.body.style.overflow = 'hidden';
    }

    function closeReportModal() {
        reportOverlay.classList.add('opacity-0', 'pointer-events-none');
        reportPanel.classList.remove('scale-100');
        reportPanel.classList.add('scale-95');
        document.body.style.overflow = '';
    }

    // Keyboard close
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeReportModal();
    });

    // Character counter
    detailsTextarea.addEventListener('input', function() {
        reportCharCount.textContent = this.value.length;
    });

    // Radio card visual selection
    document.querySelectorAll('.reason-card input[type="radio"]').forEach(function(radio) {
        radio.addEventListener('change', function() {
            document.querySelectorAll('.reason-card').forEach(function(card) {
                card.classList.remove('border-red-500', 'bg-red-50', 'dark:bg-red-900/20', 'dark:border-red-600');
            });
            if (this.checked) {
                this.closest('.reason-card').classList.add('border-red-500', 'bg-red-50');
            }
        });
    });
</script>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>

