<?php
// admin/user_action.php — Admin handles a user change (verify, update role)
require_once '../includes/auth_check.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

require_admin(); // Ensure only admins can access this page

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/');
}

// CSRF check
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    set_toast('error', 'Invalid security token.');
    redirect('admin/');
}

$target_user_id = (int)($_POST['target_user_id'] ?? 0);
$action         = $_POST['action'] ?? '';

if ($target_user_id <= 0) {
    set_toast('error', 'Invalid user selected.');
    redirect('admin/');
}

// Prevent admins from modifying their own roles/banning themselves
if ($target_user_id === (int)$_SESSION['user_id']) {
    set_toast('error', 'You cannot change your own account status.');
    redirect('admin/');
}

try {
    if ($action === 'verify') {
        $pdo->prepare("UPDATE users SET is_verified = 1 WHERE user_id = ?")->execute([$target_user_id]);
        set_toast('success', "User #$target_user_id has been verified.");
    } elseif ($action === 'unverify') {
        $pdo->prepare("UPDATE users SET is_verified = 0 WHERE user_id = ?")->execute([$target_user_id]);
        set_toast('success', "User #$target_user_id verification has been revoked.");
    } elseif ($action === 'change_role') {
        $new_role = $_POST['role'] ?? '';
        
        // Fetch current user details
        $stmt_current = $pdo->prepare("SELECT role FROM users WHERE user_id = ?");
        $stmt_current->execute([$target_user_id]);
        $target_user = $stmt_current->fetch();
        
        if (!$target_user) {
            set_toast('error', 'User not found.');
            redirect('admin/');
        }

        // Protect other admins from being modified
        if ($target_user['role'] === 'admin') {
            set_toast('error', 'You cannot modify another administrator\'s account.');
            redirect('admin/');
        }

        if ($new_role === 'banned') {
            // Can only ban student users
            if ($target_user['role'] === 'student') {
                $pdo->prepare("UPDATE users SET role = 'banned' WHERE user_id = ?")->execute([$target_user_id]);
                $pdo->prepare("UPDATE gigs SET status = 'inactive' WHERE seller_id = ?")->execute([$target_user_id]);
                set_toast('success', "User #$target_user_id has been banned and their active gigs hidden.");
            } else {
                set_toast('error', 'Only student users can be banned.');
            }
        } elseif ($new_role === 'student') {
            // Can only restore/unban banned users
            if ($target_user['role'] === 'banned') {
                $pdo->prepare("UPDATE users SET role = 'student' WHERE user_id = ?")->execute([$target_user_id]);
                set_toast('success', "User #$target_user_id has been unbanned.");
            } else {
                set_toast('error', 'Only banned users can be reinstated to Student status.');
            }
        } else {
            set_toast('error', 'Admins are not permitted to change user roles to Admin or Student directly.');
        }
    } else {
        set_toast('error', 'Unknown action.');
    }
} catch (\Exception $e) {
    error_log('[admin/user_action.php] ' . $e->getMessage());
    set_toast('error', 'Database error. Please try again.');
}

redirect('admin/');
