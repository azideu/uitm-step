<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/functions.php';

// Lightweight role sync for logged-in users (to handle unbanning/role changes)
if (isset($_SESSION['user_id'])) {
    $stmt = $pdo->prepare("SELECT role, campus FROM users WHERE user_id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $u_sync = $stmt->fetch();
    if ($u_sync) {
        $_SESSION['role'] = $u_sync['role'];
        $_SESSION['campus'] = $u_sync['campus']; // Also sync campus for the check below

        // Global ban enforcement (for public pages like index.php that don't use auth_check.php)
        if ($_SESSION['role'] === 'banned') {
            $current_page = basename($_SERVER['PHP_SELF']);
            if ($current_page !== 'banned.php' && $current_page !== 'logout.php') {
                redirect('banned');
            }
        }
    } else {
        session_destroy();
        redirect('login');
    }
}

// Enforce campus selection if missing (exclude banned users)
if (isset($_SESSION['user_id']) && empty($_SESSION['campus']) && ($_SESSION['role'] ?? '') !== 'banned') {
    $current_page = basename($_SERVER['PHP_SELF']);
    if ($current_page !== 'complete-registration.php' && $current_page !== 'logout.php') {
        redirect('complete-registration');
    }
}
?>
<!DOCTYPE html>
<html lang="en" class="scroll-smooth md:overscroll-y-none">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>UiTM STEP</title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <meta name="theme-color" content="#330066">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        // Check local storage for dark mode preference before Tailwind loads
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            document.documentElement.classList.add('dark');
        } else {
            document.documentElement.classList.remove('dark');
        }

        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Manrope', 'sans-serif'],
                        serif: ['Outfit', 'sans-serif'],
                    },
                    colors: {
                        uitmPurple: '#330066',
                        uitmGold: '#FFD700',
                        glass: 'rgba(255, 255, 255, 0.1)',
                        glassDark: 'rgba(51, 0, 102, 0.85)',
                    },
                    animation: {
                        'float': 'float 3s ease-in-out infinite',
                        'pulse-slow': 'pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite',
                    },
                    keyframes: {
                        float: {
                            '0%, 100%': { transform: 'translateY(0)' },
                            '50%': { transform: 'translateY(-10px)' },
                        }
                    }
                }
            }
        }
    </script>
    <?php
    $logo_path = __DIR__ . '/../assets/img/STEP.svg';
    $logo_version = is_file($logo_path) ? filemtime($logo_path) : '1';
    $style_path = __DIR__ . '/../assets/css/style.css';
    $style_version = is_file($style_path) ? filemtime($style_path) : '1';
    ?>
    <link rel="icon" type="image/svg+xml" href="<?php echo ROOT_URL; ?>assets/img/STEP.svg?v=<?php echo $logo_version; ?>">
    <link rel="stylesheet" href="<?php echo ROOT_URL; ?>assets/css/style.css?v=<?php echo $style_version; ?>">
</head>
<body class="bg-slate-50 dark:bg-slate-900 flex flex-col min-h-screen text-slate-800 dark:text-slate-200 font-sans selection:bg-uitmPurple selection:text-white transition-colors duration-300 relative md:overscroll-y-none">
    <div class="pointer-events-none fixed inset-0 z-[100] h-full w-full bg-noise opacity-[0.03] mix-blend-overlay"></div>
    <div class="flex flex-col min-h-screen bg-slate-50 dark:bg-slate-900 transition-colors duration-300 relative z-10">
    <!-- Navbar -->
    <nav id="main-navbar" class="bg-uitmPurple border-b border-uitmPurple/30 text-white sticky top-0 z-50 shadow-2xl transition-all duration-300 transform">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <div class="flex items-center">
                    <a href="<?php echo ROOT_URL; ?>home" class="flex items-center space-x-2 text-2xl font-bold tracking-wider text-uitmGold hover:scale-105 transition-transform duration-300 font-serif">
                        <img src="<?php echo ROOT_URL; ?>assets/img/STEP.svg?v=<?php echo $logo_version; ?>" alt="UiTM Logo" class="h-14 w-14 object-contain">
                    </a>
                </div>
                <!-- Desktop Navigation Links -->
                <div class="hidden md:flex items-center space-x-4">
                    <!-- Dark Mode Toggle -->
                    <button onclick="toggleDarkMode()" class="p-2 rounded-full hover:bg-white/10 transition-colors focus:outline-none" aria-label="Toggle Dark Mode">
                        <!-- Sun Icon (shows in dark mode) -->
                        <svg class="w-5 h-5 hidden dark:block text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <!-- Moon Icon (shows in light mode) -->
                        <svg class="w-5 h-5 block dark:hidden text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    </button>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if($_SESSION['role'] === 'student'): ?>
                            <a href="<?php echo ROOT_URL; ?>dashboard" class="hover:text-uitmGold hover:bg-white/10 px-3 py-2 rounded-md font-medium transition-colors flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                                </svg>
                                <span>Dashboard</span>
                            </a>
                            
                            <a href="<?php echo ROOT_URL; ?>marketplace" class="hover:text-uitmGold hover:bg-white/10 px-3 py-2 rounded-md font-medium transition-colors flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
                                </svg>
                                <span>Marketplace</span>
                            </a>
                            <a href="<?php echo ROOT_URL; ?>chat" class="hover:text-uitmGold hover:bg-white/10 px-3 py-2 rounded-md font-medium transition-colors flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10c0 3.866-3.582 7-8 7a8.841 8.841 0 01-4.083-.98L2 17l1.338-3.123C2.493 12.767 2 11.434 2 10c0-3.866 3.582-7 8-7s8 3.134 8 7zM7 9H5v2h2V9zm8 0h-2v2h2V9zM9 9h2v2H9V9z" clip-rule="evenodd" />
                                </svg>
                                <span>Chat</span>
                            </a>
                            <a href="<?php echo ROOT_URL; ?>profile" class="hover:text-uitmGold hover:bg-white/10 px-3 py-2 rounded-md font-medium transition-colors flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                                <span>Profile</span>
                            </a>
                        <?php endif; ?>
                        
                        <?php if($_SESSION['role'] === 'admin'): ?>
                            <a href="<?php echo ROOT_URL; ?>admin/" class="hover:text-uitmGold hover:bg-white/10 px-3 py-2 rounded-md font-medium transition-colors flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z" />
                                </svg>
                                <span>Dashboard</span>
                            </a>
                            <a href="<?php echo ROOT_URL; ?>marketplace" class="hover:text-uitmGold hover:bg-white/10 px-3 py-2 rounded-md font-medium transition-colors flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 2a4 4 0 00-4 4v1H5a1 1 0 00-.994.89l-1 9A1 1 0 004 18h12a1 1 0 00.994-1.11l-1-9A1 1 0 0015 7h-1V6a4 4 0 00-4-4zm2 5V6a2 2 0 10-4 0v1h4zm-6 3a1 1 0 112 0 1 1 0 01-2 0zm7-1a1 1 0 100 2 1 1 0 000-2z" clip-rule="evenodd" />
                                </svg>
                                <span>Marketplace</span>
                            </a>
                            <a href="<?php echo ROOT_URL; ?>profile" class="hover:text-uitmGold hover:bg-white/10 px-3 py-2 rounded-md font-medium transition-colors flex items-center space-x-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd" />
                                </svg>
                                <span>Profile</span>
                            </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="<?php echo ROOT_URL; ?>login" class="hover:text-uitmGold px-3 py-2 rounded-md font-medium transition-colors">Login</a>
                        <a href="<?php echo ROOT_URL; ?>register" class="bg-uitmGold text-uitmPurple px-5 py-2 rounded-md font-bold shadow-xl hover:bg-yellow-400 hover:scale-105 transition-all duration-300">Register</a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Navigation Toggle & Dark Mode Toggle -->
                <div class="flex items-center md:hidden space-x-1">
                    <button onclick="toggleDarkMode()" class="p-2 rounded-full hover:bg-white/10 transition-colors focus:outline-none" aria-label="Toggle Dark Mode">
                        <!-- Sun Icon (shows in dark mode) -->
                        <svg class="w-5 h-5 hidden dark:block text-yellow-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                        <!-- Moon Icon (shows in light mode) -->
                        <svg class="w-5 h-5 block dark:hidden text-indigo-200" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                    </button>
                    
                    <button onclick="toggleMobileMenu()" class="p-2 rounded-lg hover:bg-white/10 transition-colors focus:outline-none" aria-label="Toggle Menu">
                        <!-- Hamburger Icon -->
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="hamburger-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
                        </svg>
                        <!-- Close Icon -->
                        <svg class="w-6 h-6 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24" id="close-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div id="mobile-menu" class="md:hidden bg-uitmPurple border-t border-transparent max-h-0 opacity-0 overflow-hidden transition-all duration-300 ease-in-out">
            <div class="px-2 pt-2 pb-4 space-y-1">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <?php if($_SESSION['role'] === 'student'): ?>
                        <a href="<?php echo ROOT_URL; ?>dashboard" class="block hover:text-uitmGold hover:bg-white/10 px-3 py-2.5 rounded-md font-medium transition-colors">Dashboard</a>
                        <a href="<?php echo ROOT_URL; ?>marketplace" class="block hover:text-uitmGold hover:bg-white/10 px-3 py-2.5 rounded-md font-medium transition-colors">Marketplace</a>
                        <a href="<?php echo ROOT_URL; ?>chat" class="block hover:text-uitmGold hover:bg-white/10 px-3 py-2.5 rounded-md font-medium transition-colors">Chat</a>
                        <a href="<?php echo ROOT_URL; ?>profile" class="block hover:text-uitmGold hover:bg-white/10 px-3 py-2.5 rounded-md font-medium transition-colors">Profile</a>
                    <?php elseif($_SESSION['role'] === 'admin'): ?>
                        <a href="<?php echo ROOT_URL; ?>admin/" class="block hover:text-uitmGold hover:bg-white/10 px-3 py-2.5 rounded-md font-medium transition-colors">Dashboard</a>
                        <a href="<?php echo ROOT_URL; ?>marketplace" class="block hover:text-uitmGold hover:bg-white/10 px-3 py-2.5 rounded-md font-medium transition-colors">Marketplace</a>
                        <a href="<?php echo ROOT_URL; ?>profile" class="block hover:text-uitmGold hover:bg-white/10 px-3 py-2.5 rounded-md font-medium transition-colors">Profile</a>
                    <?php endif; ?>
                <?php else: ?>
                    <a href="<?php echo ROOT_URL; ?>login" class="block hover:text-uitmGold hover:bg-white/10 px-3 py-2.5 rounded-md font-medium transition-colors">Login</a>
                    <a href="<?php echo ROOT_URL; ?>register" class="block bg-uitmGold text-uitmPurple hover:bg-yellow-400 px-3 py-2.5 rounded-md font-bold shadow-xl transition-all text-center">Register</a>
                <?php endif; ?>
        </div>
    </nav>
    <?php $no_container = $no_container ?? false; ?>
    <?php if (!$no_container): ?>
    <main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <?php else: ?>
    <main class="flex-grow w-full">
    <?php endif; ?>


    <script>
        function toggleDarkMode() {
            if (document.documentElement.classList.contains('dark')) {
                document.documentElement.classList.remove('dark');
                localStorage.theme = 'light';
            } else {
                document.documentElement.classList.add('dark');
                localStorage.theme = 'dark';
            }
        }

        function toggleMobileMenu() {
            const menu = document.getElementById('mobile-menu');
            const hamburgerIcon = document.getElementById('hamburger-icon');
            const closeIcon = document.getElementById('close-icon');
            const isOpen = menu.classList.contains('max-h-[400px]');
            
            if (!isOpen) {
                // Smoothly slide down & fade in
                menu.classList.remove('max-h-0', 'opacity-0', 'border-transparent');
                menu.classList.add('max-h-[400px]', 'opacity-100', 'border-uitmPurple/30');
                hamburgerIcon.classList.add('hidden');
                closeIcon.classList.remove('hidden');
            } else {
                // Smoothly slide up & fade out
                menu.classList.remove('max-h-[400px]', 'opacity-100', 'border-uitmPurple/30');
                menu.classList.add('max-h-0', 'opacity-0', 'border-transparent');
                hamburgerIcon.classList.remove('hidden');
                closeIcon.classList.add('hidden');
            }
        }

        // Semi-sticky Smart Header behavior
        document.addEventListener('DOMContentLoaded', () => {
            const navbar = document.getElementById('main-navbar');
            if (!navbar) return;

            let lastScrollY = window.scrollY;
            const threshold = 10; // minimum scroll distance to trigger hiding/showing

            window.addEventListener('scroll', () => {
                const currentScrollY = window.scrollY;

                // Prevent negative scroll values (like mobile bounce)
                if (currentScrollY < 0) return;

                // Check threshold
                if (Math.abs(currentScrollY - lastScrollY) < threshold) return;

                if (currentScrollY > lastScrollY && currentScrollY > 80) {
                    // Scrolling down - hide navbar
                    navbar.classList.add('-translate-y-full', 'shadow-none');
                } else {
                    // Scrolling up - show navbar
                    navbar.classList.remove('-translate-y-full', 'shadow-none');
                }

                lastScrollY = currentScrollY;
            });
        });
    </script>
