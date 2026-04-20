<?php
require_once 'config.php';
require_once 'functions.php';
?>
<!DOCTYPE html>
<html lang="en" class="bg-uitmPurple">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UiTM STEP</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta name="theme-color" content="#330066">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'sans-serif'],
                        serif: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        uitmPurple: '#330066',
                        uitmGold: '#FFD700',
                        glass: 'rgba(255, 255, 255, 0.1)',
                        glassDark: 'rgba(51, 0, 102, 0.85)',
                    },
                    animation: {
                        'fade-in-up': 'fadeInUp 0.6s ease-out',
                        'float': 'float 3s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        fadeInUp: {
                            '0%': { opacity: '0', transform: 'translateY(20px)' },
                            '100%': { opacity: '1', transform: 'translateY(0)' },
                        },
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        }
                    }
                }
            }
        }
    </script>
    <link rel="icon" type="image/png" href="assets/img/uitm_logo.png">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-uitmPurple flex flex-col min-h-screen text-slate-800 font-sans selection:bg-uitmPurple selection:text-white">
    <div class="flex flex-col min-h-screen bg-slate-50">
    <!-- Navbar -->
    <nav class="bg-uitmPurple border-b border-uitmPurple/30 text-white sticky top-0 z-50 shadow-lg transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="index.php" class="flex items-center space-x-2 text-2xl font-bold tracking-wider text-uitmGold hover:scale-105 transition-transform duration-300 font-serif">
                        <img src="assets/img/uitm_logo.png" alt="UiTM Logo" class="h-10 w-10 object-contain rounded-md shadow-sm">
                        <span class="drop-shadow-sm">UiTM STEP</span>
                    </a>
                </div>
                <div class="flex items-center space-x-4">
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if($_SESSION['role'] === 'student'): ?>
                            <!-- Toggle switch handled by session or link depending on context -->
                            <?php $current_mode = $_SESSION['mode'] ?? 'buying'; ?>
                            <div class="group relative inline-block">
                            <?php if($current_mode === 'buying'): ?>
                                <a href="user_dashboard.php?mode=selling" class="bg-uitmGold text-uitmPurple px-4 py-1.5 rounded-full font-bold text-sm shadow-md hover:bg-yellow-400 hover:shadow-lg transition-all duration-300 transform group-hover:-translate-y-0.5">Switch to Selling</a>
                            <?php else: ?>
                                <a href="user_dashboard.php?mode=buying" class="bg-uitmGold text-uitmPurple px-4 py-1.5 rounded-full font-bold text-sm shadow-md hover:bg-yellow-400 hover:shadow-lg transition-all duration-300 transform group-hover:-translate-y-0.5">Switch to Buying</a>
                            <?php endif; ?>
                            </div>
                            
                            <a href="marketplace.php" class="hover:text-uitmGold hover:bg-white/10 px-3 py-2 rounded-md font-medium transition-colors">Marketplace</a>
                            <a href="chat.php" class="hover:text-uitmGold hover:bg-white/10 px-3 py-2 rounded-md font-medium transition-colors">Chat</a>
                        <?php endif; ?>
                        
                        <?php if($_SESSION['role'] === 'admin'): ?>
                            <a href="dashboard_admin.php" class="hover:text-uitmGold hover:bg-white/10 px-3 py-2 rounded-md font-medium transition-colors">Admin Dashboard</a>
                        <?php endif; ?>
                        
                        <a href="logout.php" class="hover:bg-red-500 hover:text-white px-3 py-2 rounded-md font-medium transition-all duration-300 border border-transparent hover:border-red-400">Logout</a>
                    <?php else: ?>
                        <a href="login.php" class="hover:text-uitmGold px-3 py-2 rounded-md font-medium transition-colors">Login</a>
                        <a href="register.php" class="bg-gradient-to-r from-uitmGold to-yellow-400 text-uitmPurple px-5 py-2 rounded-full font-bold shadow-md hover:shadow-lg hover:scale-105 transition-all duration-300">Register</a>
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
