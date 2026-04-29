<?php
// index.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$no_container = true;
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<div class="relative bg-black text-white h-[650px] flex items-center justify-center pt-16 mt-[-4rem] overflow-hidden">
    <!-- Animated Gradient Background & Image -->
    <div class="absolute inset-0 w-full h-full">
        <div class="absolute inset-0 bg-gradient-to-br from-uitmPurple via-[#1a0033] to-blue-900 bg-moving-gradient"></div>
        <div class="absolute inset-0 bg-noise opacity-30 mix-blend-overlay"></div>
        <img src="assets/img/hero_bg.png" alt="Student Freelancer" class="absolute inset-0 w-full h-full object-cover mix-blend-overlay opacity-40 animate-pulse-slow">
    </div>
    
    <!-- Hero Content -->
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full flex flex-col justify-center h-full animate-fade-in-up opacity-0" style="animation-fill-mode: forwards;">
        <div class="max-w-3xl">
            <h1 class="text-5xl sm:text-6xl md:text-7xl font-extrabold leading-tight mb-6 mt-16 text-white tracking-tight font-serif">
                Student talent,<br> tailored for <span class="text-uitmGold italic">you.</span>
            </h1>
            <p class="text-xl md:text-2xl text-gray-200 mb-10 font-medium max-w-2xl">Our freelancers will take it from here. Discover top UiTM talent for your gigs.</p>
            
            <!-- Search Bar -->
            <form action="marketplace.php" method="GET" class="relative group mt-4 transform transition-all duration-300">
                <input type="text" name="search" placeholder="Search for any service..." required
                       class="w-full py-5 pl-6 pr-40 rounded-lg glass-panel text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-uitmGold shadow-md text-lg">
                <button type="submit" class="absolute right-2 top-2 bottom-2 bg-uitmGold text-uitmPurple px-8 py-2 rounded-md shadow-sm hover:bg-yellow-400 transition-all duration-300 font-bold text-lg inline-flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    Search
                </button>
            </form>

            <!-- Popular Links -->
            <div class="mt-8 flex flex-wrap items-center gap-3 text-sm text-gray-300 font-medium">
                <span>Popular:</span>
                <a href="marketplace.php?tag=Programming" class="border border-white/50 rounded-full px-4 py-1 hover:bg-white hover:text-black transition-colors">Programming</a>
                <a href="marketplace.php?tag=Design" class="border border-white/50 rounded-full px-4 py-1 hover:bg-white hover:text-black transition-colors">Design</a>
                <a href="marketplace.php?tag=Writing" class="border border-white/50 rounded-full px-4 py-1 hover:bg-white hover:text-black transition-colors">Writing</a>
                <a href="marketplace.php?tag=Video" class="border border-white/50 rounded-full px-4 py-1 hover:bg-white hover:text-black transition-colors">Video Editing</a>
            </div>
        </div>
    </div>
</div>

<!-- Trusted By Section -->
<div class="bg-gray-50 dark:bg-slate-900 border-b border-gray-200 dark:border-slate-800 py-6 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-wrap items-center justify-center gap-8 md:gap-16 text-gray-400 dark:text-slate-500 font-bold text-lg">
        <span class="text-sm text-gray-400 dark:text-slate-500 uppercase tracking-widest font-semibold mr-4">Trusted By Students At:</span>
        <span class="hover:text-gray-600 dark:hover:text-slate-300 transition-colors cursor-default">UiTM Shah Alam</span>
        <span class="hover:text-gray-600 dark:hover:text-slate-300 transition-colors cursor-default">UiTM Kuala Terengganu</span>
        <span class="hover:text-gray-600 dark:hover:text-slate-300 transition-colors cursor-default">UiTM Puncak Alam</span>
        <span class="hover:text-gray-600 dark:hover:text-slate-300 transition-colors cursor-default">UiTM Seri Iskandar</span>
    </div>
</div>

<!-- Popular Services (Visual Cards) -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 border-b border-gray-100 dark:border-slate-800 relative transition-colors duration-300">
    <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 dark:text-white mb-12 font-serif tracking-tight">Popular services</h2>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <!-- Service Card 1 -->
        <a href="marketplace.php?tag=Design" class="group relative block h-80 rounded-2xl overflow-hidden shadow-lg hover:shadow-[0_20px_50px_rgba(8,_112,_184,_0.7)] transition-all duration-500 transform hover:-translate-y-3 hover:rotate-1">
            <img src="assets/img/cat_design.jpg" alt="Graphics & Design" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-in-out">
            <div class="absolute inset-0 bg-uitmPurple/60 opacity-80 group-hover:opacity-90 transition-opacity duration-500"></div>
            <div class="absolute inset-0 border-2 border-transparent group-hover:border-white/20 rounded-2xl transition-colors duration-500"></div>
            <div class="absolute bottom-10 left-6 right-6 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
               <span class="text-uitmGold text-sm font-bold uppercase tracking-wider drop-shadow-lg">Build your brand</span>
               <h3 class="text-white text-3xl font-bold mt-2 drop-shadow-2xl leading-tight font-serif">Logo Design</h3>
            </div>
        </a>
        
        <!-- Service Card 2 -->
        <a href="marketplace.php?tag=Programming" class="group relative block h-80 rounded-2xl overflow-hidden shadow-lg hover:shadow-[0_20px_50px_rgba(8,_112,_184,_0.7)] transition-all duration-500 transform hover:-translate-y-3 hover:-rotate-1 delay-100">
            <img src="assets/img/cat_programming.jpg" alt="Programming" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-in-out">
            <div class="absolute inset-0 bg-uitmPurple/60 opacity-80 group-hover:opacity-90 transition-opacity duration-500"></div>
             <div class="absolute inset-0 border-2 border-transparent group-hover:border-white/20 rounded-2xl transition-colors duration-500"></div>
            <div class="absolute bottom-10 left-6 right-6 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
               <span class="text-blue-400 text-sm font-bold uppercase tracking-wider drop-shadow-lg">Custom web apps</span>
               <h3 class="text-white text-3xl font-bold mt-2 drop-shadow-2xl leading-tight font-serif">Website <br>Dev</h3>
            </div>
        </a>

        <!-- Service Card 3 -->
        <a href="marketplace.php?tag=Writing" class="group relative block h-80 rounded-2xl overflow-hidden shadow-lg hover:shadow-[0_20px_50px_rgba(8,_112,_184,_0.7)] transition-all duration-500 transform hover:-translate-y-3 hover:rotate-1 delay-200">
            <img src="assets/img/cat_writing.jpg" alt="Writing" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-in-out">
            <div class="absolute inset-0 bg-uitmPurple/60 opacity-80 group-hover:opacity-90 transition-opacity duration-500"></div>
            <div class="absolute inset-0 border-2 border-transparent group-hover:border-white/20 rounded-2xl transition-colors duration-500"></div>
            <div class="absolute bottom-10 left-6 right-6 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
               <span class="text-emerald-400 text-sm font-bold uppercase tracking-wider drop-shadow-lg">Ace your assignments</span>
               <h3 class="text-white text-3xl font-bold mt-2 drop-shadow-2xl leading-tight font-serif">Proofreading</h3>
            </div>
        </a>

        <!-- Service Card 4 -->
        <a href="marketplace.php?tag=Video" class="group relative block h-80 rounded-2xl overflow-hidden shadow-lg hover:shadow-[0_20px_50px_rgba(8,_112,_184,_0.7)] transition-all duration-500 transform hover:-translate-y-3 hover:-rotate-1 delay-300">
            <img src="assets/img/cat_video.jpg" alt="Video Editing" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-in-out">
            <div class="absolute inset-0 bg-uitmPurple/60 opacity-80 group-hover:opacity-90 transition-opacity duration-500"></div>
            <div class="absolute inset-0 border-2 border-transparent group-hover:border-white/20 rounded-2xl transition-colors duration-500"></div>
            <div class="absolute bottom-10 left-6 right-6 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
               <span class="text-rose-400 text-sm font-bold uppercase tracking-wider drop-shadow-lg">Share your story</span>
               <h3 class="text-white text-3xl font-bold mt-2 drop-shadow-2xl leading-tight font-serif">Video <br>Editing</h3>
            </div>
        </a>
    </div>
</div>

<!-- Value Proposition: Make it happen -->
<div class="bg-blue-50/50 dark:bg-slate-800/50 py-24 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-12">Why choose UiTM STEP?</h2>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-12 gap-y-10">
            <!-- Feature 1 -->
            <div class="flex items-start">
                <div class="flex-shrink-0 mr-4">
                    <svg class="w-10 h-10 text-gray-700 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">The best for every budget</h3>
                    <p class="text-gray-600 dark:text-slate-400 text-lg leading-relaxed">Find high-quality talent at student-friendly rates. Prices are transparent, fixed, and agreed upon upfront.</p>
                </div>
            </div>
            
            <!-- Feature 2 -->
            <div class="flex items-start">
                <div class="flex-shrink-0 mr-4">
                    <svg class="w-10 h-10 text-gray-700 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Quality work done quickly</h3>
                    <p class="text-gray-600 dark:text-slate-400 text-lg leading-relaxed">Need it in 24 hours? Connect with freelancers within your own campus who understand your tight assignment deadlines.</p>
                </div>
            </div>

            <!-- Feature 3 -->
            <div class="flex items-start">
                <div class="flex-shrink-0 mr-4">
                    <svg class="w-10 h-10 text-gray-700 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Protected and secure</h3>
                    <p class="text-gray-600 dark:text-slate-400 text-lg leading-relaxed">Built exclusively for verified UiTM students. Trust in a community where accountability is verified with official student IDs.</p>
                </div>
            </div>

            <!-- Feature 4 -->
            <div class="flex items-start">
                <div class="flex-shrink-0 mr-4">
                    <svg class="w-10 h-10 text-gray-700 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Support your peers</h3>
                    <p class="text-gray-600 dark:text-slate-400 text-lg leading-relaxed">When you hire on STEP, you are directly funding a fellow student's education and building their professional portfolio.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Category Icons Library (Explore) -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 border-t border-gray-100 dark:border-slate-800 transition-colors duration-300">
    <h2 class="text-4xl font-extrabold text-gray-900 dark:text-white mb-12 font-serif">Explore the marketplace</h2>
    <div class="flex flex-wrap gap-4 justify-center md:justify-start">
        <a href="marketplace.php?tag=Programming" class="flex flex-col items-center justify-center p-6 border border-gray-200 dark:border-slate-700 rounded-2xl hover:shadow-xl hover:border-uitmPurple/30 transition-all duration-300 w-40 h-40 bg-white dark:bg-slate-800 group hover:-translate-y-2">
            <div class="bg-gray-50 dark:bg-slate-700 rounded-full p-4 mb-3 group-hover:bg-purple-50 dark:group-hover:bg-uitmPurple/20 transition-colors">
                <svg class="w-8 h-8 text-gray-500 dark:text-slate-400 group-hover:text-uitmPurple dark:group-hover:text-purple-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
            </div>
            <span class="font-bold text-sm text-center text-gray-700 dark:text-slate-200 group-hover:text-uitmPurple dark:group-hover:text-purple-300">Programming & Tech</span>
        </a>
        <a href="marketplace.php?tag=Design" class="flex flex-col items-center justify-center p-6 border border-gray-200 dark:border-slate-700 rounded-2xl hover:shadow-xl hover:border-uitmPurple/30 transition-all duration-300 w-40 h-40 bg-white dark:bg-slate-800 group hover:-translate-y-2">
            <div class="bg-gray-50 dark:bg-slate-700 rounded-full p-4 mb-3 group-hover:bg-purple-50 dark:group-hover:bg-uitmPurple/20 transition-colors">
                <svg class="w-8 h-8 text-gray-500 dark:text-slate-400 group-hover:text-uitmPurple dark:group-hover:text-purple-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <span class="font-bold text-sm text-center text-gray-700 dark:text-slate-200 group-hover:text-uitmPurple dark:group-hover:text-purple-300">Graphics & Design</span>
        </a>
        <a href="marketplace.php?tag=Writing" class="flex flex-col items-center justify-center p-6 border border-gray-200 dark:border-slate-700 rounded-2xl hover:shadow-xl hover:border-uitmPurple/30 transition-all duration-300 w-40 h-40 bg-white dark:bg-slate-800 group hover:-translate-y-2">
            <div class="bg-gray-50 dark:bg-slate-700 rounded-full p-4 mb-3 group-hover:bg-purple-50 dark:group-hover:bg-uitmPurple/20 transition-colors">
                <svg class="w-8 h-8 text-gray-500 dark:text-slate-400 group-hover:text-uitmPurple dark:group-hover:text-purple-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477-4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </div>
            <span class="font-bold text-sm text-center text-gray-700 dark:text-slate-200 group-hover:text-uitmPurple dark:group-hover:text-purple-300">Writing & Translation</span>
        </a>
        <a href="marketplace.php?tag=Video" class="flex flex-col items-center justify-center p-6 border border-gray-200 dark:border-slate-700 rounded-2xl hover:shadow-xl hover:border-uitmPurple/30 transition-all duration-300 w-40 h-40 bg-white dark:bg-slate-800 group hover:-translate-y-2">
            <div class="bg-gray-50 dark:bg-slate-700 rounded-full p-4 mb-3 group-hover:bg-purple-50 dark:group-hover:bg-uitmPurple/20 transition-colors">
                <svg class="w-8 h-8 text-gray-500 dark:text-slate-400 group-hover:text-uitmPurple dark:group-hover:text-purple-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
            </div>
            <span class="font-bold text-sm text-center text-gray-700 dark:text-slate-200 group-hover:text-uitmPurple dark:group-hover:text-purple-300">Video & Animation</span>
        </a>
        <a href="marketplace.php?tag=Tutor" class="flex flex-col items-center justify-center p-6 border border-gray-200 dark:border-slate-700 rounded-2xl hover:shadow-xl hover:border-uitmPurple/30 transition-all duration-300 w-40 h-40 bg-white dark:bg-slate-800 group hover:-translate-y-2">
            <div class="bg-gray-50 dark:bg-slate-700 rounded-full p-4 mb-3 group-hover:bg-purple-50 dark:group-hover:bg-uitmPurple/20 transition-colors">
                <svg class="w-8 h-8 text-gray-500 dark:text-slate-400 group-hover:text-uitmPurple dark:group-hover:text-purple-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>
            </div>
            <span class="font-bold text-sm text-center text-gray-700 dark:text-slate-200 group-hover:text-uitmPurple dark:group-hover:text-purple-300">Education & Tutoring</span>
        </a>
    </div>
</div>

<!-- Guides/Blog Section -->
<div class="bg-gray-50 dark:bg-slate-900 py-20 border-t border-gray-100 dark:border-slate-800 transition-colors duration-300">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-end mb-10">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Guides to help you grow</h2>
            <a href="#" class="text-blue-600 font-medium hover:underline hidden sm:block">See more guides &rarr;</a>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
            <!-- Article 1 -->
            <a href="#" class="bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow hover:shadow-lg transition block border border-gray-100 dark:border-slate-700">
                <div class="h-48 overflow-hidden">
                    <img src="assets/img/guide_portfolio.jpg" class="w-full h-full object-cover transform hover:scale-105 transition duration-500">
                </div>
                <div class="p-6">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-2">How to build a freelance portfolio while studying</h3>
                    <p class="text-gray-500 dark:text-slate-400 text-sm">Discover how to leverage your college coursework to build a powerful portfolio that fellow students love.</p>
                </div>
            </a>

            <!-- Article 2 -->
            <a href="#" class="bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow hover:shadow-lg transition block border border-gray-100 dark:border-slate-700">
                <div class="h-48 overflow-hidden bg-gray-100 dark:bg-slate-700 flex items-center justify-center p-6">
                    <div class="w-full h-full rounded shadow-sm bg-uitmPurple relative">
                        <div class="absolute inset-2 bg-white dark:bg-slate-800 rounded flex items-center justify-center">
                            <span class="text-2xl font-bold text-uitmPurple dark:text-purple-300">Pricing 101</span>
                        </div>
                    </div>
                </div>
                <div class="p-6">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-2">Pricing your first gig: A student's guide</h3>
                    <p class="text-gray-500 dark:text-slate-400 text-sm">Not sure how much to charge? Here are the industry standards and strategies to get your first order.</p>
                </div>
            </a>

            <!-- Article 3 -->
            <a href="#" class="bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow hover:shadow-lg transition block border border-gray-100 dark:border-slate-700">
                <div class="h-48 overflow-hidden">
                    <img src="assets/img/guide_time.jpg" class="w-full h-full object-cover transform hover:scale-105 transition duration-500">
                </div>
                <div class="p-6">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-2">Time Management: Freelancing and Finals</h3>
                    <p class="text-gray-500 dark:text-slate-400 text-sm">Our top-rated sellers share their secrets on balancing academic success with a thriving side hustle.</p>
                </div>
            </a>
        </div>
    </div>
</div>

<!-- Fat CTA -->
<div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-16 -mt-12 relative z-10 hidden sm:block delay-100">
    <div class="bg-uitmPurple rounded-2xl shadow-xl overflow-hidden border border-white/10">
        <div class="px-8 py-14 md:p-20 text-center text-white relative">
            <h2 class="text-4xl md:text-6xl font-extrabold mb-8 font-serif leading-tight">
                Freelance services at your <br><span class="text-uitmGold italic">fingertips</span>
            </h2>
            <?php
                $hero_cta_class = 'bg-white text-uitmPurple hover:bg-gray-100 font-bold px-10 py-4 rounded-md text-xl transition-all duration-300 inline-block shadow-md hover:shadow-lg transform hover:-translate-y-0.5';
                $is_logged_in = isset($_SESSION['user_id']);
                $hero_cta_href = $is_logged_in ? 'marketplace.php' : 'register.php';
                $hero_cta_text = $is_logged_in ? 'Explore Marketplace' : 'Join UiTM STEP';
            ?>
            <a href="<?php echo htmlspecialchars($hero_cta_href, ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $hero_cta_class; ?>"><?php echo htmlspecialchars($hero_cta_text, ENT_QUOTES, 'UTF-8'); ?></a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
