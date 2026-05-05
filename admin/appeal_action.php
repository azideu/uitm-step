<?php
// admin/appeal_action.php — Process ban appeals
require_once '../includes/auth_check.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/index');
}

// CSRF check
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    set_toast('error', 'Invalid security token.');
    redirect('admin/index');
}

$appeal_id = (int)($_POST['appeal_id'] ?? 0);
$action    = $_POST['action']    ?? ''; // approve | reject
$admin_note = trim($_POST['admin_note'] ?? '');

if (!$appeal_id || !in_array($action, ['approve', 'reject'])) {
    set_toast('error', 'Invalid request parameters.');
    redirect('admin/index');
}

try {
    $pdo->beginTransaction();

    // Fetch the appeal to get user_id
    $stmt = $pdo->prepare("SELECT user_id FROM ban_appeals WHERE appeal_id = ?");
    $stmt->execute([$appeal_id]);
    $appeal = $stmt->fetch();

    if (!$appeal) {
        throw new Exception("Appeal not found.");
    }

    $user_id = $appeal['user_id'];

    if ($action === 'approve') {
        // 1. Update appeal status
        $update_app = $pdo->prepare("UPDATE ban_appeals SET status = 'approved', admin_note = ? WHERE appeal_id = ?");
        $update_app->execute([$admin_note, $appeal_id]);

        // 2. Restore user role to student
        $update_user = $pdo->prepare("UPDATE users SET role = 'student' WHERE user_id = ?");
        $update_user->execute([$user_id]);

        set_toast('success', 'Appeal approved! The user has been reinstated.');
    } else {
        // Reject
        $update_app = $pdo->prepare("UPDATE ban_appeals SET status = 'rejected', admin_note = ? WHERE appeal_id = ?");
        $update_app->execute([$admin_note, $appeal_id]);

        set_toast('info', 'Appeal rejected. The user remains banned.');
    }

    $pdo->commit();
} catch (\Exception $e) {
    if ($pdo->inTransaction()) $pdo->rollBack();
    error_log('[admin/appeal_action.php] ' . $e->getMessage());
    set_toast('error', 'Failed to process appeal: ' . $e->getMessage());
}

redirect('admin/index');
