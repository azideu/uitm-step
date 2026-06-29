<?php
// admin/report_action.php — Admin handles a submitted report (review/dismiss/ban)
// =====================================================================
require_once '../includes/auth_check.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

require_admin();

$tab = trim($_POST['tab'] ?? '');
$tab_query = ($tab !== '') ? '?tab=' . urlencode($tab) : '';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('admin/' . $tab_query);
}

// CSRF guard
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    set_toast('error', 'Invalid security token.');
    redirect('admin/' . $tab_query);
}

$report_id  = (int)($_POST['report_id'] ?? 0);
$action     = $_POST['action'] ?? '';
$admin_note = mb_substr(trim($_POST['admin_note'] ?? ''), 0, 500);

$allowed_actions = ['reviewed', 'dismissed', 'banned'];

if ($report_id <= 0 || !in_array($action, $allowed_actions, true)) {
    set_toast('error', 'Invalid request.');
    redirect('admin/' . $tab_query);
}

// Fetch the report so we know who the reported user is
$stmt = $pdo->prepare("SELECT * FROM reports WHERE report_id = ?");
$stmt->execute([$report_id]);
$report = $stmt->fetch();

if (!$report) {
    set_toast('error', 'Report not found.');
    redirect('admin/' . $tab_query);
}

try {
    // Update report status
    $pdo->prepare("UPDATE reports SET status = ?, admin_note = ? WHERE report_id = ?")
        ->execute([$action, $admin_note ?: null, $report_id]);

    // If action is 'banned', also deactivate the reported user's account
    // We repurpose the `role` column to mark them as 'banned' for a lightweight ban.
    if ($action === 'banned') {
        $pdo->prepare("UPDATE users SET role = 'banned' WHERE user_id = ?")
            ->execute([$report['reported_id']]);

        // Also soft-delete / deactivate all their gigs
        $pdo->prepare("UPDATE gigs SET status = 'inactive' WHERE seller_id = ?")
            ->execute([$report['reported_id']]);

        set_toast('success', "User #{$report['reported_id']} has been banned and their gigs deactivated.");
    } elseif ($action === 'dismissed') {
        set_toast('success', "Report #$report_id dismissed.");
    } else {
        set_toast('success', "Report #$report_id marked as reviewed.");
    }

} catch (\Exception $e) {
    error_log('[admin/report_action.php] ' . $e->getMessage());
    set_toast('error', 'Database error. Please try again.');
}

redirect('admin/' . $tab_query);
?>
