<?php
// verify_email.php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (!isset($_SESSION['verify_email'])) {
    redirect('login');
}

$email = $_SESSION['verify_email'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $otp = trim($_POST['otp'] ?? '');

    if (!empty($otp)) {
        $stmt = $pdo->prepare("SELECT user_id, otp_code FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && $user['otp_code'] === $otp) {
            $update = $pdo->prepare("UPDATE users SET is_verified = 1, otp_code = NULL WHERE user_id = ?");
            if ($update->execute([$user['user_id']])) {
                unset($_SESSION['verify_email']);
                set_toast('success', 'Email verified successfully! You can now login.');
                redirect('login');
            } else {
                set_toast('error', 'An error occurred during verification.');
            }
        } else {
            set_toast('error', 'Invalid verification code.');
        }
    } else {
        set_toast('error', 'Please enter the verification code.');
    }
}

require_once 'includes/header.php';
?>

<div class="max-w-md mx-auto bg-white dark:bg-slate-900 p-8 rounded-2xl shadow-xl border border-gray-100 dark:border-slate-800 transition-colors duration-300 mt-10">
    <h2 class="text-2xl font-bold mb-6 text-center text-uitmPurple dark:text-purple-300 font-serif">Verify Your Email</h2>
    <p class="text-center text-gray-600 dark:text-slate-400 mb-6">
        We've sent a 6-digit verification code to <strong><?php echo escape($email); ?></strong>.<br>
        Please enter it below.
    </p>

    <form action="verify_email" method="POST">
        <div class="mb-6">
            <label class="block text-gray-700 dark:text-slate-300 font-bold mb-2" for="otp">Verification Code</label>
            <input type="text" name="otp" id="otp" required maxlength="6" pattern="\d{6}" title="6-digit code" placeholder="123456" class="w-full text-center tracking-widest text-xl px-4 py-3 bg-white dark:bg-slate-800 text-gray-900 dark:text-white border border-gray-200 dark:border-slate-700 rounded-lg focus:outline-none focus:ring-2 focus:ring-uitmPurple dark:focus:ring-purple-900/50 focus:border-uitmPurple transition-all">
        </div>
        <button type="submit" class="w-full bg-uitmPurple text-white font-bold py-3 px-4 rounded-md shadow-sm hover:bg-purple-900 transition-all">Verify Now</button>
    </form>

    <p class="mt-6 text-center text-sm text-gray-500 dark:text-slate-400">
        Didn't receive the code? Check your spam folder or <a href="login" class="text-uitmPurple dark:text-purple-400 hover:underline">return to login</a> and try again.
    </p>
</div>

<?php require_once 'includes/footer.php'; ?>
