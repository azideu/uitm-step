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
    <?php $style_version = @filemtime(__DIR__ . '/../assets/css/style.css') ?: '1'; ?>
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo $style_version; ?>">
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
                            
                            <a href="marketplace.php" class="hover:text-uitmGold hover:bg-white/10 px-3 py-2 rounded-md font-medium transition-colors flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
                                </svg>
                                <span>Marketplace</span>
                            </a>
                            <a href="chat.php" class="hover:text-uitmGold hover:bg-white/10 px-3 py-2 rounded-md font-medium transition-colors flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                                </svg>
                                <span>Chat</span>
                            </a>
                            <a href="profile.php" class="hover:text-uitmGold hover:bg-white/10 px-3 py-2 rounded-md font-medium transition-colors flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                                <span>Profile</span>
                            </a>
                        <?php endif; ?>
                        
                        <?php if($_SESSION['role'] === 'admin'): ?>
                            <a href="dashboard_admin.php" class="hover:text-uitmGold hover:bg-white/10 px-3 py-2 rounded-md font-medium transition-colors flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                                </svg>
                                <span>Dashboard</span>
                            </a>
                            <a href="profile.php" class="hover:text-uitmGold hover:bg-white/10 px-3 py-2 rounded-md font-medium transition-colors flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                                <span>Profile</span>
                            </a>
                        <?php endif; ?>
                        
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
