<?php
// guides/portfolio.php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

$current_guide = 'portfolio';
?>

<div class="max-w-7xl mx-auto px-4 py-12 sm:px-6 lg:px-8 animate-fade-in-up">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Left Column: Sidebar (Topics & Popular) -->
        <div class="lg:w-64 flex-shrink-0 space-y-6">
            <!-- Topics Panel -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-gray-100 dark:border-slate-800 transition-colors duration-300">
                <h3 class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest mb-4">Choose Your Topic</h3>
                <ul class="space-y-3.5 text-sm font-bold text-slate-700 dark:text-slate-350">
                    <li><a href="<?php echo ROOT_URL; ?>guides/?topic=career" class="hover:text-uitmPurple dark:hover:text-purple-400 transition-colors flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700"></span> Career Development</a></li>
                    <li><a href="<?php echo ROOT_URL; ?>guides/?topic=finance" class="hover:text-uitmPurple dark:hover:text-purple-400 transition-colors flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700"></span> Financial Tips</a></li>
                    <li><a href="<?php echo ROOT_URL; ?>guides/?topic=student-life" class="hover:text-uitmPurple dark:hover:text-purple-400 transition-colors flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700"></span> Student Life</a></li>
                    <li class="border-t border-slate-100 dark:border-slate-800 pt-3"></li>
                    <li><a href="<?php echo ROOT_URL; ?>guides/?topic=programming" class="hover:text-uitmPurple dark:hover:text-purple-400 transition-colors flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700"></span> Programming & Tech</a></li>
                    <li><a href="<?php echo ROOT_URL; ?>guides/?topic=design" class="hover:text-uitmPurple dark:hover:text-purple-400 transition-colors flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700"></span> Graphics & Design</a></li>
                    <li><a href="<?php echo ROOT_URL; ?>guides/?topic=writing" class="hover:text-uitmPurple dark:hover:text-purple-400 transition-colors flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700"></span> Writing & Translation</a></li>
                    <li><a href="<?php echo ROOT_URL; ?>guides/?topic=video" class="hover:text-uitmPurple dark:hover:text-purple-400 transition-colors flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700"></span> Video & Animation</a></li>
                    <li><a href="<?php echo ROOT_URL; ?>guides/?topic=tutor" class="hover:text-uitmPurple dark:hover:text-purple-400 transition-colors flex items-center gap-2"><span class="w-1.5 h-1.5 rounded-full bg-slate-300 dark:bg-slate-700"></span> Education & Tutoring</a></li>
                </ul>
            </div>

            <!-- Popular Panel -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 border border-gray-100 dark:border-slate-800 transition-colors duration-300">
                <h3 class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest mb-4">Most Popular Guides</h3>
                <ul class="space-y-4 text-xs font-bold text-slate-700 dark:text-slate-350">
                    <li>
                        <a href="<?php echo ROOT_URL; ?>guides/portfolio" class="transition-colors flex flex-col gap-1 <?php echo $current_guide === 'portfolio' ? 'text-uitmPurple dark:text-purple-300' : 'hover:text-uitmPurple dark:hover:text-purple-400'; ?>">
                            <span>How to build a freelance portfolio while studying</span>
                            <span class="text-[10px] text-gray-400 font-medium font-sans">5 min read</span>
                        </a>
                    </li>
                    <li class="border-t border-slate-100 dark:border-slate-800 pt-3">
                        <a href="<?php echo ROOT_URL; ?>guides/pricing" class="transition-colors flex flex-col gap-1 <?php echo $current_guide === 'pricing' ? 'text-uitmPurple dark:text-purple-300' : 'hover:text-uitmPurple dark:hover:text-purple-400'; ?>">
                            <span>Pricing your first gig: A student's guide</span>
                            <span class="text-[10px] text-gray-400 font-medium font-sans">4 min read</span>
                        </a>
                    </li>
                    <li class="border-t border-slate-100 dark:border-slate-800 pt-3">
                        <a href="<?php echo ROOT_URL; ?>guides/time" class="transition-colors flex flex-col gap-1 <?php echo $current_guide === 'time' ? 'text-uitmPurple dark:text-purple-300' : 'hover:text-uitmPurple dark:hover:text-purple-400'; ?>">
                            <span>Time Management: Freelancing and Finals</span>
                            <span class="text-[10px] text-gray-400 font-medium font-sans">6 min read</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Right Column: Main Content -->
        <div class="flex-grow bg-white dark:bg-slate-900 rounded-3xl shadow-xl border border-gray-100 dark:border-slate-800 overflow-hidden transition-colors duration-300">
            <div class="h-2 bg-uitmPurple"></div>
            
            <div class="p-8 md:p-12">
                <!-- Breadcrumbs -->
                <nav class="mb-4 text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-wider">
                    <a href="<?php echo ROOT_URL; ?>home" class="hover:text-uitmPurple dark:hover:text-purple-400 transition-colors">STEP Guides</a>
                    <span class="mx-1.5">&rsaquo;</span>
                    <span class="text-slate-600 dark:text-slate-400">Career Development</span>
                </nav>

                <!-- Title -->
                <h1 class="text-3xl md:text-5xl font-extrabold text-slate-900 dark:text-white mb-4 leading-tight font-serif transition-colors duration-300">
                    How to Build a Freelance Portfolio While Studying
                </h1>

                <!-- Subtitle / Excerpt -->
                <p class="text-lg md:text-xl text-slate-500 dark:text-slate-400 font-medium mb-6 leading-relaxed">
                    Learn how to turn coursework, student association projects, and personal work into a professional portfolio that lands you paying clients on campus.
                </p>

                <!-- Metadata Row -->
                <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-slate-500 mb-8 border-b border-slate-100 dark:border-slate-800 pb-5 font-medium">
                    <div class="flex items-center gap-1.5">
                        <span class="font-bold text-slate-700 dark:text-slate-400">By: STEP Editorial</span>
                    </div>
                    <span>•</span>
                    <span>June 12, 2026</span>
                    <span>•</span>
                    <span>5 min read</span>
                </div>

                <!-- Hero Image -->
                <div class="w-full aspect-video bg-slate-50 dark:bg-slate-850 rounded-2xl overflow-hidden mb-10 shadow-md border border-gray-100 dark:border-slate-800">
                    <img src="../assets/img/guide_portfolio.jpg" alt="Student Freelancer" class="w-full h-full object-cover">
                </div>

                <!-- Article Body (Prose) -->
                <div class="prose prose-slate dark:prose-invert max-w-none">
                    <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">1. Turn Class Assignments into Case Studies</h2>
                    <p class="mb-4 text-slate-700 dark:text-slate-350 leading-relaxed text-base">
                        Don't let your assignments gather dust in your downloads folder. That programming lab project, UI redesign, or academic essay can be converted into a case study. 
                    </p>
                    <ul class="list-disc pl-6 mb-6 text-slate-700 dark:text-slate-350 space-y-2">
                        <li>Show the before-and-after of your code or design changes.</li>
                        <li>Write a brief explanation of the problem the assignment solved.</li>
                        <li>Upload the results to GitHub or Behance and link them on your STEP profile.</li>
                    </ul>

                    <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">2. Design or Develop for Student Clubs</h2>
                    <p class="mb-4 text-slate-700 dark:text-slate-350 leading-relaxed text-base">
                        UiTM has hundreds of student associations and clubs that constantly need flyers, social media graphics, event websites, and copywriting. 
                    </p>
                    <p class="mb-6 text-slate-700 dark:text-slate-350 leading-relaxed text-base">
                        Reach out to your club committee and offer to design their next event poster or develop a simple landing page. While this might be low-paying or volunteer work, it provides you with invaluable, real-world pieces for your portfolio showing you can work under client instructions.
                    </p>

                    <div class="bg-purple-50 dark:bg-slate-800/60 border-l-4 border-uitmPurple p-5 rounded-r-xl my-6">
                        <p class="text-sm font-semibold text-uitmPurple dark:text-purple-300 italic">
                            "Working for my faculty's student club allowed me to design 3 event flyers. That small portfolio landed me my first two paying design gigs on UiTM STEP!" 
                        </p>
                        <span class="block text-xs font-bold text-gray-505 dark:text-slate-400 mt-2">— Ainun Nadiah, UiTM Raub student</span>
                    </div>

                    <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">3. Build Personal "Side Projects"</h2>
                    <p class="mb-4 text-slate-700 dark:text-slate-350 leading-relaxed text-base">
                        If you don't have clients, create them. Invent a hypothetical brand and design their identity, or build a utility web app that solves a problem you face on campus (e.g. a GPA calculator or class schedule organizer).
                    </p>
                    <p class="mb-6 text-slate-700 dark:text-slate-350 leading-relaxed text-base">
                        Personal projects show initiative, creativity, and self-motivation—traits that clients highly value in student freelancers.
                    </p>

                    <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">4. Keep it Organized and Relevant</h2>
                    <p class="mb-4 text-slate-700 dark:text-slate-350 leading-relaxed text-base">
                        Quality always beats quantity. Select your top 3 to 5 strongest pieces that represent the services you want to sell. Keep description copy short and highlight the skills you used (e.g. Photoshop, PHP, Video Editing).
                    </p>

                    <div class="mt-10 p-8 bg-slate-50 dark:bg-slate-800/40 rounded-2xl border border-gray-100 dark:border-slate-800 text-center">
                        <h3 class="text-xl font-bold text-uitmPurple dark:text-purple-300 mb-2">Ready to list your portfolio?</h3>
                        <p class="text-sm text-gray-600 dark:text-slate-400 mb-6">Register your UiTM student account and showcase your talent to clients on campus.</p>
                        <a href="../register" class="bg-uitmPurple text-white px-8 py-3 rounded-lg font-bold hover:bg-opacity-90 transition-colors shadow-md text-base inline-block">Create Seller Profile</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
