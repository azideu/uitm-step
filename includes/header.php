<?php
require_once 'config.php';
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UiTM STEP</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        uitmPurple: '#330066',
                        uitmGold: '#FFD700',
                    }
                }
            }
        }
    </script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-50 flex flex-col min-h-screen text-gray-800">
    <!-- Navbar -->
    <nav class="bg-uitmPurple text-white sticky top-0 z-50 shadow-md">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="text-2xl font-bold tracking-wider text-uitmGold">UiTM STEP</a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if($_SESSION['role'] === 'student'): ?>
                            <!-- Toggle switch handled by session or link depending on context -->
                            <?php $current_mode = $_SESSION['mode'] ?? 'buying'; ?>
                            <?php if($current_mode === 'buying'): ?>
                                <a href="user_dashboard.php?mode=selling" class="bg-uitmGold text-uitmPurple px-3 py-1 rounded font-semibold text-sm">Switch to Selling</a>
                            <?php else: ?>
                                <a href="user_dashboard.php?mode=buying" class="bg-uitmGold text-uitmPurple px-3 py-1 rounded font-semibold text-sm">Switch to Buying</a>
                            <?php endif; ?>
                            
                            <a href="marketplace.php" class="hover:text-uitmGold px-3 py-2 rounded-md font-medium">Marketplace</a>
                            <a href="chat.php" class="hover:text-uitmGold px-3 py-2 rounded-md font-medium">Chat</a>
                        <?php endif; ?>
                        
                        <?php if($_SESSION['role'] === 'admin'): ?>
                            <a href="dashboard_admin.php" class="hover:text-uitmGold px-3 py-2 rounded-md font-medium">Admin Dashboard</a>
                        <?php endif; ?>
                        
                        <a href="logout.php" class="hover:bg-red-600 px-3 py-2 rounded-md font-medium">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="hover:text-uitmGold px-3 py-2 rounded-md font-medium">Login</a>
                        <a href="register.php" class="bg-uitmGold text-uitmPurple px-3 py-2 rounded-md font-bold hover:bg-yellow-400">Register</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>
    <?php $no_container = $no_container ?? false; ?>
    <?php if (!$no_container): ?>
    <main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php else: ?>
    <main class="flex-grow w-full">
    <?php endif; ?>
        <?php display_toast(); ?>
