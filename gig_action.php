<?php
// gig_action.php
require_once 'includes/auth_check.php';
require_once 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        set_toast('error', 'Invalid security token.');
        redirect('user_dashboard.php?mode=selling');
    }
    $gig_id = (int)($_POST['gig_id'] ?? 0);
    $action = $_POST['action'] ?? '';

    if ($gig_id <= 0) {
        set_toast('error', 'Invalid gig ID.');
        redirect('user_dashboard.php?mode=selling');
    }

    // Fetch gig details to check ownership
    $stmt = $pdo->prepare("SELECT * FROM gigs WHERE gig_id = ?");
    $stmt->execute([$gig_id]);
    $gig = $stmt->fetch();

    if (!$gig) {
        set_toast('error', 'Gig not found.');
        redirect('user_dashboard.php?mode=selling');
    }

    // Check ownership or admin role
    if ((int)$gig['seller_id'] !== (int)$_SESSION['user_id'] && $_SESSION['role'] !== 'admin') {
        set_toast('error', 'Unauthorized action.');
        redirect('user_dashboard.php?mode=selling');
    }

    try {
        if ($action === 'delete') {
            // Check if there are any active orders for this gig
            $stmt_orders = $pdo->prepare("SELECT COUNT(*) FROM orders WHERE gig_id = ? AND status NOT IN ('complete', 'cancelled')");
            $stmt_orders->execute([$gig_id]);
            $active_orders = $stmt_orders->fetchColumn();

            if ($active_orders > 0) {
                // If there are active orders, we should probably only set status to 'inactive' 
                // but for "delete", let's just mark it as 'deleted' so it's hidden everywhere.
                // Or inform the user they can't delete yet.
                // Actually, marking as 'deleted' is safe as long as orders still reference it.
                $stmt = $pdo->prepare("UPDATE gigs SET status = 'inactive' WHERE gig_id = ?");
                $stmt->execute([$gig_id]);
                set_toast('success', 'Gig marked as inactive (hidden from marketplace due to active orders).');
            } else {
                // No active orders, we can mark as inactive.
                $stmt = $pdo->prepare("UPDATE gigs SET status = 'inactive' WHERE gig_id = ?");
                $stmt->execute([$gig_id]);
                set_toast('success', 'Gig deleted successfully.');
            }
        } else {
            set_toast('error', 'Unknown action.');
        }
    } catch (\Exception $e) {
        set_toast('error', 'Database error occurred.');
        error_log($e->getMessage());
    }

    redirect('user_dashboard.php?mode=selling');
} else {
    redirect('index.php');
}
