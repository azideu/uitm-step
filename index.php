<?php
// index.php
require_once 'includes/config.php';
require_once 'includes/functions.php';

$no_container = true;
require_once 'includes/header.php';
?>

<!-- Hero Section -->
<div class="relative bg-slate-950 text-white min-h-[700px] lg:h-[750px] flex items-center pt-16 mt-[-4rem] overflow-hidden">
    <!-- Ambient Moving Glow Blobs -->
    <div class="absolute top-[10%] left-[5%] bg-glow-blob blob-purple"></div>
    <div class="absolute bottom-[10%] right-[10%] bg-glow-blob blob-gold"></div>
    <div class="absolute top-[30%] right-[30%] bg-glow-blob blob-blue"></div>

    <!-- Background Noise & Image Overlay -->
    <div class="absolute inset-0 w-full h-full pointer-events-none">
        <div class="absolute inset-0 bg-noise opacity-30 mix-blend-overlay"></div>
        <img src="assets/img/hero_bg.png" alt="Student Freelancer" class="absolute inset-0 w-full h-full object-cover mix-blend-luminosity opacity-15">
        <!-- Bottom fade to mask transition -->
        <div class="absolute bottom-0 left-0 right-0 h-32 bg-gradient-to-t from-slate-950 to-transparent"></div>
    </div>
    
    <!-- Hero Content -->
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full flex flex-col justify-center h-full pt-12 lg:pt-0">
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
            
            <!-- Left Column: Title, Subtitle, Search -->
            <div class="lg:col-span-7 flex flex-col justify-center text-left animate-fade-in-up opacity-0" style="animation-fill-mode: forwards;">
                <!-- Official Badge -->
                <div class="inline-flex items-center space-x-2 bg-uitmPurple/30 border border-uitmGold/20 text-uitmGold px-4 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider mb-6 w-fit">
                    <svg class="w-4 h-4 text-uitmGold animate-pulse" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                    <span>The Official UiTM Freelance Hub</span>
                </div>

                <h1 class="text-4xl sm:text-5xl md:text-6xl font-extrabold leading-tight mb-6 text-white tracking-tight font-serif">
                    Student talent,<br>tailored for <span class="text-uitmGold italic relative">you.</span>
                </h1>
                
                <p class="text-lg md:text-xl text-slate-300 mb-8 font-medium max-w-xl">
                    Discover top UiTM student talent for your project, campus assignment, or business needs. Fast, reliable, and peer-to-peer.
                </p>
                
                <!-- Search Bar Container with focus glow styling -->
                <form action="marketplace" method="GET" class="search-container-glow relative group w-full p-2.5 rounded-2xl flex items-center shadow-2xl max-w-2xl">
                    <div class="flex items-center pl-3 pr-2 pointer-events-none">
                        <svg class="w-6 h-6 text-slate-400 group-focus-within:text-uitmGold transition-colors duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </div>
                    <input type="text" name="search" placeholder="What service do you need today?" required
                           class="w-full py-3.5 bg-transparent text-white placeholder-slate-400 border-none outline-none focus:ring-0 text-base sm:text-lg">
                    <button type="submit" class="bg-uitmGold text-uitmPurple hover:bg-yellow-400 px-6 sm:px-8 py-3.5 rounded-xl transition-all duration-300 font-bold text-base sm:text-lg inline-flex items-center hover:scale-[1.03] active:scale-95 shadow-lg shadow-uitmGold/10">
                        Search
                    </button>
                </form>

                <!-- Popular Links -->
                <div class="mt-8 flex flex-wrap items-center gap-3 text-sm text-slate-400 font-medium">
                    <span>Popular searches:</span>
                    <a href="marketplace?tag=Programming" class="glass-tag rounded-full px-4 py-1.5 text-xs font-semibold text-white">Programming</a>
                    <a href="marketplace?tag=Design" class="glass-tag rounded-full px-4 py-1.5 text-xs font-semibold text-white">Design</a>
                    <a href="marketplace?tag=Writing" class="glass-tag rounded-full px-4 py-1.5 text-xs font-semibold text-white">Writing</a>
                    <a href="marketplace?tag=Video" class="glass-tag rounded-full px-4 py-1.5 text-xs font-semibold text-white">Video Editing</a>
                </div>
            </div>

            <!-- Right Column: Asymmetric Floating Cards Showcase -->
            <div class="lg:col-span-5 hidden md:flex relative h-[480px] w-full items-center justify-center animate-fade-in-up opacity-0" style="animation-fill-mode: forwards; animation-delay: 0.2s;">
                
                <!-- Card 1: Putera Mikhail (Web Dev) -->
                <div class="glass-hero-card absolute top-2 left-0 z-20 p-5 rounded-2xl w-[280px] sm:w-[310px] animate-float-card-1">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-purple-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                            PM
                        </div>
                        <div class="flex-grow">
                            <div class="flex items-center justify-between">
                                <h4 class="text-white font-bold text-sm">Putera Mikhail</h4>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-500/20 text-green-400">
                                    <span class="w-1.5 h-1.5 mr-1 rounded-full bg-green-400 animate-pulse"></span>
                                    Online
                                </span>
                            </div>
                            <p class="text-slate-400 text-xs font-medium">Web Developer (Shah Alam)</p>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-white/5 flex items-center justify-between">
                        <span class="text-uitmGold font-bold text-xs uppercase tracking-wider">HTML, React, PHP</span>
                        <span class="text-white font-extrabold text-sm">RM 150<span class="text-slate-400 text-xs font-normal">/project</span></span>
                    </div>
                    <div class="mt-3 flex items-center space-x-1.5">
                        <div class="flex text-amber-400">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        </div>
                        <span class="text-white text-xs font-bold">5.0</span>
                        <span class="text-slate-400 text-xs">(42 reviews)</span>
                    </div>
                </div>

                <!-- Card 2: Ainun Nadiah (Logo Design) -->
                <div class="glass-hero-card absolute top-28 right-0 z-10 p-5 rounded-2xl w-[260px] sm:w-[280px] animate-float-card-2">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-pink-500 to-rose-600 flex items-center justify-center text-white font-bold text-sm shadow-md">
                            AN
                        </div>
                        <div class="flex-grow">
                            <div class="flex items-center justify-between">
                                <h4 class="text-white font-bold text-sm">Ainun Nadiah</h4>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-500/20 text-green-400">
                                    <span class="w-1.5 h-1.5 mr-1 rounded-full bg-green-400 animate-pulse"></span>
                                    Active
                                </span>
                            </div>
                            <p class="text-slate-400 text-xs font-medium">Brand Designer (Raub)</p>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-white/5 flex items-center justify-between">
                        <span class="text-pink-400 font-bold text-xs uppercase tracking-wider">Logo, UX, Canva</span>
                        <span class="text-white font-extrabold text-sm">RM 80<span class="text-slate-400 text-xs font-normal">/gig</span></span>
                    </div>
                    <div class="mt-3 flex items-center space-x-1.5">
                        <div class="flex text-amber-400">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-3.5 h-3.5 fill-current opacity-30" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        </div>
                        <span class="text-white text-xs font-bold">4.9</span>
                        <span class="text-slate-400 text-xs">(28 reviews)</span>
                    </div>
                </div>

                <!-- Card 3: Ammar Danish (Video Editing) -->
                <div class="glass-hero-card absolute bottom-2 left-12 z-30 p-5 rounded-2xl w-[270px] sm:w-[290px] animate-float-card-3">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-blue-500 to-teal-500 flex items-center justify-center text-white font-bold text-sm shadow-md">
                            AD
                        </div>
                        <div class="flex-grow">
                            <div class="flex items-center justify-between">
                                <h4 class="text-white font-bold text-sm">Ammar Danish</h4>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-500/20 text-green-400">
                                    <span class="w-1.5 h-1.5 mr-1 rounded-full bg-green-400 animate-pulse"></span>
                                    Online
                                </span>
                            </div>
                            <p class="text-slate-400 text-xs font-medium">Video Editor (Terengganu)</p>
                        </div>
                    </div>
                    <div class="mt-4 pt-3 border-t border-white/5 flex items-center justify-between">
                        <span class="text-blue-400 font-bold text-xs uppercase tracking-wider">Premiere, CapCut, Reels</span>
                        <span class="text-white font-extrabold text-sm">RM 100<span class="text-slate-400 text-xs font-normal">/video</span></span>
                    </div>
                    <div class="mt-3 flex items-center space-x-1.5">
                        <div class="flex text-amber-400">
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                            <svg class="w-3.5 h-3.5 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"></path></svg>
                        </div>
                        <span class="text-white text-xs font-bold">5.0</span>
                        <span class="text-slate-400 text-xs">(15 reviews)</span>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<!-- Trusted By Section -->
<div class="relative bg-slate-950 border-y border-white/5 py-8 transition-colors duration-300">
    <div class="absolute inset-0 bg-noise opacity-20 pointer-events-none"></div>
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row items-center justify-between gap-6">
        <span class="text-xs text-slate-450 uppercase tracking-widest font-semibold text-center md:text-left">Trusted By Campus Students At:</span>
        <div class="flex flex-wrap items-center justify-center gap-8 md:gap-12 text-slate-300 text-sm font-bold">
            <span class="hover:text-uitmGold hover:scale-105 transition-all duration-300 cursor-default flex items-center"><span class="w-1.5 h-1.5 mr-2 rounded-full bg-purple-500"></span>UiTM Shah Alam</span>
            <span class="hover:text-uitmGold hover:scale-105 transition-all duration-300 cursor-default flex items-center"><span class="w-1.5 h-1.5 mr-2 rounded-full bg-blue-500"></span>UiTM Kuala Terengganu</span>
            <span class="hover:text-uitmGold hover:scale-105 transition-all duration-300 cursor-default flex items-center"><span class="w-1.5 h-1.5 mr-2 rounded-full bg-pink-500"></span>UiTM Puncak Alam</span>
            <span class="hover:text-uitmGold hover:scale-105 transition-all duration-300 cursor-default flex items-center"><span class="w-1.5 h-1.5 mr-2 rounded-full bg-amber-500"></span>UiTM Seri Iskandar</span>
        </div>
    </div>
</div>

<!-- Popular Services (Visual Cards) -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 border-b border-gray-100 dark:border-slate-800 relative transition-colors duration-300">
    <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 dark:text-white mb-12 font-serif tracking-tight">Popular services</h2>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
        <!-- Service Card 1 -->
        <a href="marketplace?tag=Design" class="group relative block h-80 rounded-2xl overflow-hidden shadow-2xl hover:shadow-[0_20px_50px_rgba(8,_112,_184,_0.7)] transition-all duration-500 transform hover:-translate-y-3 hover:rotate-1">
            <img src="assets/img/cat_design.jpg" alt="Graphics & Design" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-in-out">
            <div class="absolute inset-0 bg-uitmPurple/60 opacity-80 group-hover:opacity-90 transition-opacity duration-500"></div>
            <div class="absolute inset-0 border-2 border-transparent group-hover:border-white/20 rounded-2xl transition-colors duration-500"></div>
            <div class="absolute bottom-10 left-6 right-6 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
               <span class="text-uitmGold text-sm font-bold uppercase tracking-wider drop-shadow-2xl">Build your brand</span>
               <h3 class="text-white text-3xl font-bold mt-2 drop-shadow-2xl leading-tight font-serif">Logo Design</h3>
            </div>
        </a>
        
        <!-- Service Card 2 -->
        <a href="marketplace?tag=Programming" class="group relative block h-80 rounded-2xl overflow-hidden shadow-2xl hover:shadow-[0_20px_50px_rgba(8,_112,_184,_0.7)] transition-all duration-500 transform hover:-translate-y-3 hover:-rotate-1 delay-100">
            <img src="assets/img/cat_programming.jpg" alt="Programming" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-in-out">
            <div class="absolute inset-0 bg-uitmPurple/60 opacity-80 group-hover:opacity-90 transition-opacity duration-500"></div>
             <div class="absolute inset-0 border-2 border-transparent group-hover:border-white/20 rounded-2xl transition-colors duration-500"></div>
            <div class="absolute bottom-10 left-6 right-6 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
               <span class="text-blue-400 text-sm font-bold uppercase tracking-wider drop-shadow-2xl">Custom web apps</span>
               <h3 class="text-white text-3xl font-bold mt-2 drop-shadow-2xl leading-tight font-serif">Website <br>Dev</h3>
            </div>
        </a>

        <!-- Service Card 3 -->
        <a href="marketplace?tag=Writing" class="group relative block h-80 rounded-2xl overflow-hidden shadow-2xl hover:shadow-[0_20px_50px_rgba(8,_112,_184,_0.7)] transition-all duration-500 transform hover:-translate-y-3 hover:rotate-1 delay-200">
            <img src="assets/img/cat_writing.jpg" alt="Writing" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-in-out">
            <div class="absolute inset-0 bg-uitmPurple/60 opacity-80 group-hover:opacity-90 transition-opacity duration-500"></div>
            <div class="absolute inset-0 border-2 border-transparent group-hover:border-white/20 rounded-2xl transition-colors duration-500"></div>
            <div class="absolute bottom-10 left-6 right-6 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
               <span class="text-emerald-400 text-sm font-bold uppercase tracking-wider drop-shadow-2xl">Ace your assignments</span>
               <h3 class="text-white text-3xl font-bold mt-2 drop-shadow-2xl leading-tight font-serif">Proofreading</h3>
            </div>
        </a>

        <!-- Service Card 4 -->
        <a href="marketplace?tag=Video" class="group relative block h-80 rounded-2xl overflow-hidden shadow-2xl hover:shadow-[0_20px_50px_rgba(8,_112,_184,_0.7)] transition-all duration-500 transform hover:-translate-y-3 hover:-rotate-1 delay-300">
            <img src="assets/img/cat_video.jpg" alt="Video Editing" class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-in-out">
            <div class="absolute inset-0 bg-uitmPurple/60 opacity-80 group-hover:opacity-90 transition-opacity duration-500"></div>
            <div class="absolute inset-0 border-2 border-transparent group-hover:border-white/20 rounded-2xl transition-colors duration-500"></div>
            <div class="absolute bottom-10 left-6 right-6 transform translate-y-4 group-hover:translate-y-0 transition-transform duration-500">
               <span class="text-rose-400 text-sm font-bold uppercase tracking-wider drop-shadow-2xl">Share your story</span>
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

<!-- Testimonials Section -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24 relative overflow-hidden">
    <!-- Subtle Section Background Glow -->
    <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-purple-500/5 dark:bg-purple-500/10 rounded-full blur-3xl pointer-events-none"></div>

    <div class="text-center mb-16 relative z-10">
        <h2 class="text-4xl md:text-5xl font-extrabold text-gray-900 dark:text-white mb-4 font-serif tracking-tight">
            What <span class="text-uitmPurple dark:text-purple-400">Students Love</span> About STEP
        </h2>
        <p class="text-gray-500 dark:text-slate-400 text-lg max-w-2xl mx-auto font-medium">
            Feedback from fellow students on using our platform, its interface, and the quality of gigs.
        </p>
    </div>

    <?php
    $site_testimonials = [
        [
            'rating' => 5,
            'review_text' => 'The interface of this website is incredibly clean and modern! Navigating between gigs is fast and seamless. Easily the best student portal I have ever used.',
            'name' => 'Ammar Danish',
            'role' => 'Student Freelancer (Kuala Terengganu)',
            'glow_from' => 'from-purple-500',
            'glow_to' => 'to-indigo-500',
            'text_hover' => 'group-hover:text-purple-600 dark:group-hover:text-purple-400'
        ],
        [
            'rating' => 5,
            'review_text' => 'I love the dark mode option! The design looks very premium, especially with the subtle micro-animations and smooth transitions. Incredible user experience!',
            'name' => 'Ainun Nadiah',
            'role' => 'Client / Student (Raub)',
            'glow_from' => 'from-pink-500',
            'glow_to' => 'to-rose-500',
            'text_hover' => 'group-hover:text-pink-600 dark:group-hover:text-pink-400'
        ],
        [
            'rating' => 5,
            'review_text' => 'Creating my freelance profile and showcasing my programming services was super easy. The step-by-step onboarding and styling options are top-tier.',
            'name' => 'Putera Mikhail Fallon',
            'role' => 'Web Developer Student (Shah Alam)',
            'glow_from' => 'from-blue-500',
            'glow_to' => 'to-teal-500',
            'text_hover' => 'group-hover:text-blue-600 dark:group-hover:text-blue-400'
        ],
        [
            'rating' => 5,
            'review_text' => 'As a student seller, this platform has changed my life. I was able to secure 5 local programming projects within my campus during finals week alone!',
            'name' => 'Adam Khairi',
            'role' => 'Freelancer Student (Jasin)',
            'glow_from' => 'from-amber-500',
            'glow_to' => 'to-orange-500',
            'text_hover' => 'group-hover:text-amber-600 dark:group-hover:text-amber-400'
        ],
        [
            'rating' => 5,
            'review_text' => 'Ordering a logo for my student association was incredibly fast. The seller was friendly, local, and knew exactly what our faculty guidelines required. Highly recommended!',
            'name' => 'Siti Aliya',
            'role' => 'Client / Student (Puncak Alam)',
            'glow_from' => 'from-emerald-500',
            'glow_to' => 'to-teal-500',
            'text_hover' => 'group-hover:text-emerald-600 dark:group-hover:text-emerald-400'
        ],
        [
            'rating' => 5,
            'review_text' => 'STEP makes peer-to-peer transactions within campus super secure. No more worrying about payment issues or scams. Every transaction is fast and transparent.',
            'name' => 'Firas Faiq',
            'role' => 'Video Editor (Seri Iskandar)',
            'glow_from' => 'from-rose-500',
            'glow_to' => 'to-purple-600',
            'text_hover' => 'group-hover:text-rose-600 dark:group-hover:text-rose-400'
        ]
    ];

    if (!function_exists('render_testimonial_card')) {
        function render_testimonial_card(array $t) {
            ?>
            <!-- Card Wrapper with 1px Gradient Border on Hover -->
            <div class="group relative p-[1.5px] rounded-3xl bg-gradient-to-br from-gray-200/60 to-gray-100/60 dark:from-slate-850/60 dark:to-slate-900/40 hover:<?php echo $t['glow_from']; ?> hover:<?php echo $t['glow_to']; ?> transition-all duration-500 hover:shadow-[0_20px_50px_-12px_rgba(0,0,0,0.15)] dark:hover:shadow-[0_20px_50px_-12px_rgba(0,0,0,0.5)] hover:-translate-y-2 h-full flex flex-col w-[350px] md:w-[400px] flex-shrink-0">
                <!-- Main Card Body -->
                <div class="relative bg-white dark:bg-slate-900 rounded-[23px] p-8 h-full flex flex-col justify-between overflow-hidden flex-grow border border-transparent dark:border-slate-800/30">
                    <!-- Subtle Glow Spot on Hover -->
                    <div class="absolute -right-12 -top-12 w-40 h-40 rounded-full bg-gradient-to-br <?php echo $t['glow_from']; ?> <?php echo $t['glow_to']; ?> opacity-0 group-hover:opacity-10 dark:group-hover:opacity-20 blur-3xl transition-all duration-700 pointer-events-none"></div>
                    
                    <!-- Quote Icon Decoration -->
                    <div class="absolute top-8 right-8 text-uitmPurple/5 dark:text-purple-400/5 group-hover:text-uitmPurple/10 dark:group-hover:text-purple-400/10 transition-colors duration-500 pointer-events-none">
                        <svg class="w-14 h-14 fill-current" viewBox="0 0 24 24">
                            <path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/>
                        </svg>
                    </div>

                    <div>
                        <!-- Stars & Verified Badge Row -->
                        <div class="flex items-center justify-between mb-6 relative z-10">
                            <div class="flex gap-0.5">
                                <?php for ($i = 1; $i <= $t['rating']; $i++): ?>
                                    <svg class="w-4.5 h-4.5 text-uitmGold fill-current transform group-hover:scale-110 transition-transform duration-300" style="transition-delay: <?php echo ($i * 75); ?>ms" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                <?php endfor; ?>
                            </div>
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold tracking-wider text-emerald-600 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-950/30 px-2.5 py-0.5 rounded-full border border-emerald-100/50 dark:border-emerald-900/30 uppercase">
                                <svg class="w-3 h-3 fill-current" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.857-9.809a.75.75 0 00-1.214-.882l-3.483 4.79-1.88-1.88a.75.75 0 10-1.06 1.061l2.5 2.5a.75.75 0 001.137-.089l4-5.5z" clip-rule="evenodd" />
                                </svg>
                                Verified Student
                            </span>
                        </div>
                        
                        <!-- Review Text -->
                        <blockquote class="text-slate-600 dark:text-slate-300 text-[15px] leading-relaxed mb-8 relative z-10 italic font-medium whitespace-normal">
                            "<?php echo escape($t['review_text']); ?>"
                        </blockquote>
                    </div>
                    
                    <!-- Reviewer Info -->
                    <div class="flex items-center gap-4 relative z-10 pt-5 border-t border-slate-100 dark:border-slate-800/80 mt-auto">
                        <!-- Avatar with Glowing Border -->
                        <div class="relative flex-shrink-0">
                            <!-- Glow Behind Avatar -->
                            <div class="absolute -inset-0.5 bg-gradient-to-tr <?php echo $t['glow_from']; ?> <?php echo $t['glow_to']; ?> rounded-full blur opacity-30 group-hover:opacity-75 transition duration-500"></div>
                            <!-- Avatar Circle -->
                            <div class="relative w-11 h-11 rounded-full bg-gradient-to-br from-slate-900 to-slate-800 dark:from-slate-800 dark:to-slate-700 flex items-center justify-center text-white font-extrabold text-base border border-white/10 shadow-md">
                                <?php echo strtoupper(substr($t['name'], 0, 1)); ?>
                            </div>
                        </div>
                        <div class="min-w-0">
                            <h4 class="font-bold text-gray-900 dark:text-white text-sm tracking-tight truncate <?php echo $t['text_hover']; ?> transition-colors duration-300">
                                <?php echo escape($t['name']); ?>
                            </h4>
                            <p class="text-xs text-gray-400 dark:text-slate-500 font-semibold truncate mt-0.5">
                                <?php echo escape($t['role']); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <?php
        }
    }
    ?>

    <style>
    @keyframes marquee {
        0% { transform: translateX(0); }
        100% { transform: translateX(calc(-50% - 12px)); }
    }
    .animate-marquee {
        animation: marquee 35s linear infinite;
    }
    .animate-marquee:hover {
        animation-play-state: paused;
    }
    </style>

    <div class="relative w-full overflow-hidden mt-8">
        <!-- Gradient Overlays for smooth fading edges -->
        <div class="absolute left-0 top-0 bottom-0 w-12 md:w-32 bg-gradient-to-r from-slate-50 via-slate-50/80 to-transparent dark:from-slate-900 dark:via-slate-900/80 to-transparent z-20 pointer-events-none"></div>
        <div class="absolute right-0 top-0 bottom-0 w-12 md:w-32 bg-gradient-to-l from-slate-50 via-slate-50/80 to-transparent dark:from-slate-900 dark:via-slate-900/80 to-transparent z-20 pointer-events-none"></div>

        <!-- Scrolling Flex Container -->
        <div class="flex gap-6 py-8 w-max animate-marquee relative z-10">
            <!-- First loop of testimonials -->
            <div class="flex gap-6 flex-shrink-0">
                <?php foreach ($site_testimonials as $t) {
                    render_testimonial_card($t);
                } ?>
            </div>

            <!-- Second loop of testimonials (identical duplicate for infinite seamless loop) -->
            <div class="flex gap-6 flex-shrink-0" aria-hidden="true">
                <?php foreach ($site_testimonials as $t) {
                    render_testimonial_card($t);
                } ?>
            </div>
        </div>
    </div>
</div>

<!-- Category Icons Library (Explore) -->
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 border-t border-gray-100 dark:border-slate-800 transition-colors duration-300">
    <h2 class="text-4xl font-extrabold text-gray-900 dark:text-white mb-12 font-serif">Explore the marketplace</h2>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-4 lg:gap-6">
        <a href="marketplace?tag=Programming" class="flex flex-col items-center justify-center p-6 border border-gray-200 dark:border-slate-700 rounded-2xl hover:shadow-xl hover:border-uitmPurple/30 transition-all duration-300 w-full h-40 bg-white dark:bg-slate-800 group hover:-translate-y-2">
            <div class="bg-gray-50 dark:bg-slate-700 rounded-full p-4 mb-3 group-hover:bg-purple-50 dark:group-hover:bg-uitmPurple/20 transition-colors">
                <svg class="w-8 h-8 text-gray-500 dark:text-slate-400 group-hover:text-uitmPurple dark:group-hover:text-purple-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
            </div>
            <span class="font-bold text-sm text-center text-gray-700 dark:text-slate-200 group-hover:text-uitmPurple dark:group-hover:text-purple-300">Programming & Tech</span>
        </a>
        <a href="marketplace?tag=Design" class="flex flex-col items-center justify-center p-6 border border-gray-200 dark:border-slate-700 rounded-2xl hover:shadow-xl hover:border-uitmPurple/30 transition-all duration-300 w-full h-40 bg-white dark:bg-slate-800 group hover:-translate-y-2">
            <div class="bg-gray-50 dark:bg-slate-700 rounded-full p-4 mb-3 group-hover:bg-purple-50 dark:group-hover:bg-uitmPurple/20 transition-colors">
                <svg class="w-8 h-8 text-gray-500 dark:text-slate-400 group-hover:text-uitmPurple dark:group-hover:text-purple-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
            </div>
            <span class="font-bold text-sm text-center text-gray-700 dark:text-slate-200 group-hover:text-uitmPurple dark:group-hover:text-purple-300">Graphics & Design</span>
        </a>
        <a href="marketplace?tag=Writing" class="flex flex-col items-center justify-center p-6 border border-gray-200 dark:border-slate-700 rounded-2xl hover:shadow-xl hover:border-uitmPurple/30 transition-all duration-300 w-full h-40 bg-white dark:bg-slate-800 group hover:-translate-y-2">
            <div class="bg-gray-50 dark:bg-slate-700 rounded-full p-4 mb-3 group-hover:bg-purple-50 dark:group-hover:bg-uitmPurple/20 transition-colors">
                <svg class="w-8 h-8 text-gray-500 dark:text-slate-400 group-hover:text-uitmPurple dark:group-hover:text-purple-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477-4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </div>
            <span class="font-bold text-sm text-center text-gray-700 dark:text-slate-200 group-hover:text-uitmPurple dark:group-hover:text-purple-300">Writing & Translation</span>
        </a>
        <a href="marketplace?tag=Video" class="flex flex-col items-center justify-center p-6 border border-gray-200 dark:border-slate-700 rounded-2xl hover:shadow-xl hover:border-uitmPurple/30 transition-all duration-300 w-full h-40 bg-white dark:bg-slate-800 group hover:-translate-y-2">
            <div class="bg-gray-50 dark:bg-slate-700 rounded-full p-4 mb-3 group-hover:bg-purple-50 dark:group-hover:bg-uitmPurple/20 transition-colors">
                <svg class="w-8 h-8 text-gray-500 dark:text-slate-400 group-hover:text-uitmPurple dark:group-hover:text-purple-300 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"></path></svg>
            </div>
            <span class="font-bold text-sm text-center text-gray-700 dark:text-slate-200 group-hover:text-uitmPurple dark:hover:text-purple-300">Video & Animation</span>
        </a>
        <a href="marketplace?tag=Tutor" class="flex flex-col items-center justify-center p-6 border border-gray-200 dark:border-slate-700 rounded-2xl hover:shadow-xl hover:border-uitmPurple/30 transition-all duration-300 w-full h-40 bg-white dark:bg-slate-800 group hover:-translate-y-2">
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
            <a href="guides/portfolio" class="bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition block border border-gray-100 dark:border-slate-700">
                <div class="h-48 overflow-hidden">
                    <img src="assets/img/guide_portfolio.jpg" class="w-full h-full object-cover transform hover:scale-105 transition duration-500">
                </div>
                <div class="p-6">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white mb-2">How to build a freelance portfolio while studying</h3>
                    <p class="text-gray-500 dark:text-slate-400 text-sm">Discover how to leverage your college coursework to build a powerful portfolio that fellow students love.</p>
                </div>
            </a>

            <!-- Article 2 -->
            <a href="guides/pricing" class="bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition block border border-gray-100 dark:border-slate-700">
                <div class="h-48 overflow-hidden bg-gray-100 dark:bg-slate-700 flex items-center justify-center p-6">
                    <div class="w-full h-full rounded shadow-xl bg-uitmPurple relative">
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
            <a href="guides/time" class="bg-white dark:bg-slate-800 rounded-xl overflow-hidden shadow-xl hover:shadow-2xl transition block border border-gray-100 dark:border-slate-700">
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
                $hero_cta_class = 'bg-white text-uitmPurple hover:bg-gray-100 font-bold px-10 py-4 rounded-md text-xl transition-all duration-300 inline-block shadow-2xl hover:shadow-2xl transform hover:-translate-y-0.5';
                $is_logged_in = isset($_SESSION['user_id']);
                $hero_cta_href = $is_logged_in ? 'marketplace' : 'register';
                $hero_cta_text = $is_logged_in ? 'Explore Marketplace' : 'Join UiTM STEP';
            ?>
            <a href="<?php echo htmlspecialchars($hero_cta_href, ENT_QUOTES, 'UTF-8'); ?>" class="<?php echo $hero_cta_class; ?>"><?php echo htmlspecialchars($hero_cta_text, ENT_QUOTES, 'UTF-8'); ?></a>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
