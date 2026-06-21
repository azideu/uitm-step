<?php
// api/review_action.php — Review Controller
// =====================================================================
// Handles review submission by buyer for completed orders
// Only buyers can leave reviews on gigs they purchased and completed
// =====================================================================
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    set_toast('error', 'Invalid request method.');
    redirect('home');
}

// Verify CSRF Token
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    set_toast('error', 'Invalid security token.');
    redirect('home');
}

// Only students (buyers) can leave reviews
if ($_SESSION['role'] !== 'student') {
    set_toast('error', 'Only buyers can leave reviews.');
    redirect('home');
}

$order_id = (int)($_POST['order_id'] ?? 0);
$gig_id = (int)($_POST['gig_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 0);
$review_text = trim($_POST['review_text'] ?? '');

// Validation
if ($order_id <= 0 || $gig_id <= 0) {
    set_toast('error', 'Invalid order or gig ID.');
    redirect('home');
}

if ($rating < 1 || $rating > 5) {
    set_toast('error', 'Rating must be between 1 and 5.');
    redirect('home');
}

if (empty($review_text) || strlen($review_text) < 10) {
    set_toast('error', 'Review must be at least 10 characters long.');
    redirect('home');
}

if (strlen($review_text) > 1000) {
    set_toast('error', 'Review cannot exceed 1000 characters.');
    redirect('home');
}

try {
    // Fetch the order to verify buyer ownership and status with comprehensive validation
    $stmt = $pdo->prepare("
        SELECT o.*, g.seller_id, g.gig_id as gig_gig_id
        FROM orders o
        JOIN gigs g ON o.gig_id = g.gig_id
        WHERE o.order_id = ? AND o.gig_id = ?
    ");
    $stmt->execute([$order_id, $gig_id]);
    $order = $stmt->fetch();

    if (!$order) {
        set_toast('error', 'Order not found.');
        redirect('home');
    }

    // Verify buyer ownership
    if ($order['buyer_id'] != $_SESSION['user_id']) {
        set_toast('error', 'You can only review your own orders.');
        redirect('home');
    }

    // Comprehensive cross-check: Verify order is paid, delivered or complete
    if (!in_array($order['status'], ['paid', 'delivered', 'complete'], true)) {
        set_toast('error', 'You can only review orders that have been paid for.');
        redirect('home');
    }

    $proof_path_raw = $order['payment_proof_path'] ?? '';
    if (trim($proof_path_raw) !== '') {
        // If a payment proof path is provided, ensure the file actually exists on disk (legacy manual payment flow)
        $proof_path = '../uploads/' . $order['payment_proof_path'];
        if (!file_exists($proof_path)) {
            set_toast('error', 'Payment proof file not found. Please contact support.');
            redirect('home');
        }
    }

    // Cross-check: Verify the gig_id from the order matches the requested gig_id
    if ($order['gig_id'] != $gig_id) {
        set_toast('error', 'Order does not match the specified gig.');
        redirect('home');
    }

    // Check if review already exists for this order (prevent duplicate reviews)
    $check_stmt = $pdo->prepare("SELECT review_id FROM reviews WHERE order_id = ?");
    $check_stmt->execute([$order_id]);
    if ($check_stmt->fetch()) {
        set_toast('error', 'You have already reviewed this order.');
        redirect('gigs/details?id=' . $gig_id);
    }

    // Insert review
    $insert_stmt = $pdo->prepare("
        INSERT INTO reviews (order_id, gig_id, buyer_id, seller_id, rating, review_text)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $insert_stmt->execute([
        $order_id,
        $gig_id,
        $_SESSION['user_id'],
        $order['seller_id'],
        $rating,
        $review_text
    ]);

    set_toast('success', 'Review submitted successfully!');
    redirect('gigs/details?id=' . $gig_id);

} catch (\Exception $e) {
    set_toast('error', 'Error submitting review: ' . $e->getMessage());
    error_log($e->getMessage());
    redirect('gigs/details?id=' . $gig_id);
}
?>
