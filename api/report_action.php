<?php
// api/report_action.php — Submit a report against another user
// =====================================================================
// Any logged-in student can report any other user.
// Admins handle the report via admin/index.php → admin/report_action.php
// =====================================================================
require_once '../includes/auth_check.php';
require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('home');
}

// CSRF guard
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    set_toast('error', 'Invalid security token.');
    redirect('home');
}

$reporter_id  = (int)$_SESSION['user_id'];
$reported_id  = (int)($_POST['reported_id'] ?? 0);
$reason       = $_POST['reason'] ?? '';
$details      = trim($_POST['details'] ?? '');
$redirect_to  = $_POST['redirect_to'] ?? 'marketplace';

$allowed_reasons = [
    'scam',
    'fake_payment_proof',
    'non_delivery',
    'harassment',
    'inappropriate_content',
    'other',
];

// Validate inputs
if ($reported_id <= 0) {
    set_toast('error', 'Invalid user to report.');
    redirect($redirect_to);
}

if ($reporter_id === $reported_id) {
    set_toast('error', 'You cannot report yourself.');
    redirect($redirect_to);
}

if (!in_array($reason, $allowed_reasons, true)) {
    set_toast('error', 'Please select a valid reason.');
    redirect($redirect_to);
}

// Verify reported user actually exists
$stmt = $pdo->prepare("SELECT user_id FROM users WHERE user_id = ?");
$stmt->execute([$reported_id]);
if (!$stmt->fetchColumn()) {
    set_toast('error', 'User not found.');
    redirect($redirect_to);
}

// Prevent duplicate pending reports from the same reporter against the same user
$stmt = $pdo->prepare("
    SELECT report_id FROM reports
    WHERE reporter_id = ? AND reported_id = ? AND status = 'pending'
    LIMIT 1
");
$stmt->execute([$reporter_id, $reported_id]);
if ($stmt->fetchColumn()) {
    set_toast('info', 'You already have a pending report against this user. Our team is reviewing it.');
    redirect($redirect_to);
}

// Cap details at 1000 chars
$details = mb_substr($details, 0, 1000);

try {
    $stmt = $pdo->prepare("
        INSERT INTO reports (reporter_id, reported_id, reason, details)
        VALUES (?, ?, ?, ?)
    ");
    $stmt->execute([$reporter_id, $reported_id, $reason, $details ?: null]);

    set_toast('success', 'Report submitted. Our admin team will review it shortly. Thank you for keeping UiTM STEP safe.');
} catch (\Exception $e) {
    error_log('[report_action.php] ' . $e->getMessage());
    set_toast('error', 'Failed to submit report. Please try again.');
}

redirect($redirect_to);
