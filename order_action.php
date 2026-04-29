<?php
// order_action.php — Order State Machine Controller
// =====================================================================
// LEGAL TRANSITIONS (State Machine):
//   pending   → cancelled  (buyer or admin)
//   paid      → delivered  (seller only)
//   paid      → cancelled  (admin only)
//   delivered → complete   (buyer only)
//   delivered → cancelled  (admin only)
//   *         → *          (admin status-override via dropdown)
//
// Any other transition is rejected with a toast error. This prevents
// logical errors such as a buyer "completing" an order that was never
// delivered, or a seller marking an unconfirmed order as delivered.
// =====================================================================
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('home');
}

// Verify CSRF Token
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    set_toast('error', 'Invalid security token.');
    redirect('home');
}

$order_id = (int)($_POST['order_id'] ?? 0);
$action   = $_POST['action'] ?? '';

if ($order_id <= 0) {
    set_toast('error', 'Invalid order ID.');
    redirect('home');
}

// Fetch order + seller_id in one query so we can verify actor ownership
$stmt = $pdo->prepare("
    SELECT o.*, g.seller_id
    FROM orders o
    JOIN gigs g ON o.gig_id = g.gig_id
    WHERE o.order_id = ?
");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if (!$order) {
    set_toast('error', 'Order not found.');
    redirect('home');
}

$current_status = $order['status'];
$buyer_id       = (int)$order['buyer_id'];
$seller_id      = (int)$order['seller_id'];
$user_id        = (int)$_SESSION['user_id'];
$role           = $_SESSION['role'];

try {

    // ------------------------------------------------------------------
    // SELLER ACTION: paid → delivered
    // ------------------------------------------------------------------
    if ($action === 'deliver') {
        // Security: only the seller of this specific gig may deliver
        if ($seller_id !== $user_id || $role !== 'student') {
            set_toast('error', 'Unauthorized action.');
            redirect('user_dashboard?mode=selling');
        }

        // State Machine guard: can only deliver a PAID order
        if ($current_status !== 'paid') {
            set_toast('error', 'You can only deliver an order that has been paid.');
            redirect('user_dashboard?mode=selling');
        }

        $pdo->prepare("UPDATE orders SET status = 'delivered' WHERE order_id = ?")
            ->execute([$order_id]);
        set_toast('success', 'Order marked as delivered! The buyer will confirm completion.');
        redirect('user_dashboard?mode=selling');
    }

    // ------------------------------------------------------------------
    // BUYER ACTION: delivered → complete
    // ------------------------------------------------------------------
    if ($action === 'complete') {
        // Security: only the buyer of this specific order may complete it
        if ($buyer_id !== $user_id || $role !== 'student') {
            set_toast('error', 'Unauthorized action.');
            redirect('user_dashboard?mode=buying');
        }

        // State Machine guard: order MUST be delivered before completion.
        // This prevents bypassing the delivery step entirely.
        if ($current_status !== 'delivered') {
            set_toast('error', 'You can only complete an order after the seller has marked it as delivered.');
            redirect('user_dashboard?mode=buying');
        }

        $pdo->prepare("UPDATE orders SET status = 'complete' WHERE order_id = ?")
            ->execute([$order_id]);
        set_toast('success', 'Order marked as complete! Thank you.');
        redirect('user_dashboard?mode=buying');
    }

    // ------------------------------------------------------------------
    // BUYER ACTION: pending → cancelled
    // ------------------------------------------------------------------
    if ($action === 'cancel') {
        // Security: only the buyer may self-cancel
        if ($buyer_id !== $user_id || $role !== 'student') {
            set_toast('error', 'Unauthorized action.');
            redirect('user_dashboard?mode=buying');
        }

        // State Machine guard: buyers may only cancel PENDING orders.
        // Once payment has been confirmed (paid), only an admin can intervene.
        if ($current_status !== 'pending') {
            set_toast('error', 'You can only cancel an order while it is still pending.');
            redirect('user_dashboard?mode=buying');
        }

        $pdo->prepare("UPDATE orders SET status = 'cancelled' WHERE order_id = ?")
            ->execute([$order_id]);
        set_toast('success', 'Order cancelled.');
        redirect('user_dashboard?mode=buying');
    }

    // ------------------------------------------------------------------
    // ADMIN ACTION: full status override for dispute resolution
    // ------------------------------------------------------------------
    if ($action === 'admin_update') {
        if ($role !== 'admin') {
            set_toast('error', 'Unauthorized action.');
            redirect('dashboard_admin');
        }

        $new_status      = $_POST['status'] ?? '';
        $allowed_statuses = ['pending', 'paid', 'delivered', 'complete', 'cancelled'];

        if (!in_array($new_status, $allowed_statuses, true)) {
            set_toast('error', 'Invalid status selected.');
            redirect('dashboard_admin');
        }

        // Admins bypass the state machine to resolve disputes
        $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?")
            ->execute([$new_status, $order_id]);
        set_toast('success', "Order #$order_id status updated to '$new_status' by admin.");
        redirect('dashboard_admin');
    }


    // Catch-all for any unknown action
    set_toast('error', 'Unknown action.');
    redirect('home');

} catch (\Exception $e) {
    set_toast('error', 'A database error occurred. Please try again.');
    error_log('[order_action.php] ' . $e->getMessage());
    redirect('home');
}
?>
