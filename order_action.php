<?php
// order_action.php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)($_POST['order_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($order_id <= 0) {
        set_toast('error', 'Invalid order ID.');
        redirect('index.php');
    }

    // Fetch order details
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
        redirect('index.php');
    }

    try {
        if ($action === 'deliver' && $_SESSION['role'] === 'student' && $order['seller_id'] == $_SESSION['user_id']) {
            if ($order['status'] === 'paid') {
                $stmt = $pdo->prepare("UPDATE orders SET status = 'delivered' WHERE order_id = ?");
                $stmt->execute([$order_id]);
                set_toast('success', 'Order marked as delivered!');
            } else {
                set_toast('error', 'Can only deliver paid orders.');
            }
            redirect('user_dashboard.php?mode=selling');
            
        } elseif ($action === 'complete' && $_SESSION['role'] === 'student' && $order['buyer_id'] == $_SESSION['user_id']) {
            if ($order['status'] === 'delivered') {
                $stmt = $pdo->prepare("UPDATE orders SET status = 'complete' WHERE order_id = ?");
                $stmt->execute([$order_id]);
                set_toast('success', 'Order marked as complete!');
            } else {
                set_toast('error', 'Order must be delivered before you can complete it.');
            }
            redirect('user_dashboard.php?mode=buying');
            
        } elseif ($_SESSION['role'] === 'admin') {
            // Admin can override status
            $new_status = $_POST['status'] ?? '';
            $allowed_statuses = ['pending', 'paid', 'delivered', 'complete', 'cancelled'];
            if (in_array($new_status, $allowed_statuses)) {
                $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE order_id = ?");
                $stmt->execute([$new_status, $order_id]);
                set_toast('success', 'Order status updated by admin.');
            } else {
                set_toast('error', 'Invalid status.');
            }
            redirect('dashboard_admin.php');
            
        } else {
            set_toast('error', 'Unauthorized action.');
            redirect('index.php');
        }
    } catch (\Exception $e) {
        set_toast('error', 'Database error occurred.');
        error_log($e->getMessage());
        redirect('index.php');
    }
} else {
    redirect('index.php');
}
?>
