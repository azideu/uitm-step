<?php
// api/appeal_action.php — Submit a ban appeal
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('home');
}

// CSRF guard
if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
    set_toast('error', 'Invalid security token.');
    redirect('banned');
}

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'banned') {
    set_toast('error', 'You must be logged in as a suspended user to submit an appeal.');
    redirect('banned');
}

$user_id = (int)$_SESSION['user_id'];
$content = trim($_POST['content'] ?? '');

if (empty($content)) {
    set_toast('error', 'Please provide a reason for your appeal.');
    redirect('banned');
}

// Cap content at 2000 chars
$content = mb_substr($content, 0, 2000);

try {
    // Check if they already have a pending appeal
    $stmt = $pdo->prepare("SELECT appeal_id FROM ban_appeals WHERE user_id = ? AND status = 'pending' LIMIT 1");
    $stmt->execute([$user_id]);
    if ($stmt->fetch()) {
        set_toast('info', 'You already have a pending appeal under review.');
        redirect('banned');
    }

    $stmt = $pdo->prepare("INSERT INTO ban_appeals (user_id, content) VALUES (?, ?)");
    $stmt->execute([$user_id, $content]);

    set_toast('success', 'Your appeal has been submitted for review.');
} catch (\Exception $e) {
    error_log('[appeal_action.php] ' . $e->getMessage());
    set_toast('error', 'Failed to submit appeal. Please try again.');
}

redirect('banned');
