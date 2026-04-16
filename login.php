<?php
// login.php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

if (isset($_SESSION['user_id'])) {
    redirect('index.php');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!empty($email) && !empty($password)) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Mitigate Session Fixation
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['campus'] = $user['campus'];
            $_SESSION['student_id'] = $user['student_id'];
            
            // Set default mode for student
            if ($user['role'] === 'student') {
                $_SESSION['mode'] = 'buying';
            }

            set_toast('success', "Welcome back, " . escape($user['name']) . "!");
            redirect('index.php');
        } else {
            set_toast('error', 'Invalid email or password.');
        }
    } else {
        set_toast('error', 'Please complete all fields.');
    }
}

require_once 'includes/header.php';
?>

<div class="max-w-md mx-auto bg-white p-8 rounded shadow-lg mt-10">
    <h2 class="text-2xl font-bold mb-6 text-center text-uitmPurple">Login to UiTM STEP</h2>
    <form action="login.php" method="POST">
        <div class="mb-4">
            <label class="block text-gray-700 font-bold mb-2" for="email">Student Email</label>
            <input type="email" name="email" id="email" required pattern=".*@student\.uitm\.edu\.my" title="Must be a @student.uitm.edu.my email" class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-uitmPurple">
        </div>
        <div class="mb-6">
            <label class="block text-gray-700 font-bold mb-2" for="password">Password</label>
            <input type="password" name="password" id="password" required class="w-full px-3 py-2 border rounded focus:outline-none focus:ring focus:border-uitmPurple">
        </div>
        <button type="submit" class="w-full bg-uitmPurple text-white font-bold py-2 px-4 rounded hover:bg-purple-900 transition-colors">Login</button>
    </form>
    <p class="mt-4 text-center">Don't have an account? <a href="register.php" class="text-uitmPurple hover:underline">Register here</a></p>
</div>

<?php require_once 'includes/footer.php'; ?>
