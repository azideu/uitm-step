<?php
// guides/time.php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

$current_guide = 'time';
?>

<div class="max-w-7xl mx-auto px-4 py-12 sm:px-6 lg:px-8 animate-fade-in-up">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Left Column: Sidebar (Topics & Popular) -->
        <div class="lg:w-64 flex-shrink-0 space-y-6">
            <!-- Topics Panel -->
            <div class="bg-white dark:bg-slate-900 rounded-lg p-6 border border-gray-100 dark:border-slate-800 transition-colors duration-300">
                <h3 class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest mb-4">Choose Your Topic</h3>
                <ul class="space-y-3.5 text-sm font-bold text-slate-700 dark:text-slate-300">
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
            <div class="bg-white dark:bg-slate-900 rounded-lg p-6 border border-gray-100 dark:border-slate-800 transition-colors duration-300">
                <h3 class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest mb-4">Most Popular Guides</h3>
                <ul class="space-y-4 text-xs font-bold text-slate-700 dark:text-slate-300">
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
                    <span class="text-slate-600 dark:text-slate-400">Student Life</span>
                </nav>

                <!-- Title -->
                <h1 class="text-3xl md:text-5xl font-extrabold text-slate-900 dark:text-white mb-4 leading-tight font-serif transition-colors duration-300">
                    Time Management: Freelancing and Finals
                </h1>

                <!-- Subtitle / Excerpt -->
                <p class="text-lg md:text-xl text-slate-500 dark:text-slate-400 font-medium mb-6 leading-relaxed">
                    Maintaining high academic grades while running a side hustle requires systemized planning. Read on for key focus strategies.
                </p>

                <!-- Metadata Row -->
                <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-slate-500 mb-8 border-b border-slate-100 dark:border-slate-800 pb-5 font-medium">
                    <div class="flex items-center gap-1.5">
                        <span class="font-bold text-slate-700 dark:text-slate-400">By: STEP Editorial</span>
                    </div>
                    <span>•</span>
                    <span>June 12, 2026</span>
                    <span>•</span>
                    <span>6 min read</span>
                </div>

                <!-- Hero Image -->
                <div class="w-full aspect-video bg-slate-50 dark:bg-slate-800 rounded-lg overflow-hidden mb-10 shadow-md border border-gray-100 dark:border-slate-800">
                    <img src="../assets/img/guide_time.jpg" alt="Student Studying" class="w-full h-full object-cover">
                </div>

                <!-- Article Body (Prose) -->
                <div class="prose prose-slate dark:prose-invert max-w-none">
                    <p class="text-lg text-slate-700 dark:text-slate-300 mb-6 leading-relaxed">
                        Succeeding academically while maintaining a steady stream of freelance income is a balancing act. When finals week hits, the pressure increases. Here is how you can manage your schedule to keep your grades high and your clients happy.
                    </p>

                    <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">1. Set Realistic Delivery Deadlines</h2>
                    <p class="mb-4 text-slate-700 dark:text-slate-300 leading-relaxed text-base">
                        During regular semester weeks, you might comfortably offer a 24-hour turnaround. During mid-terms or finals, however, this is a recipe for disaster.
                    </p>
                    <p class="mb-6 text-slate-700 dark:text-slate-300 leading-relaxed text-base">
                        Update your active gigs to increase delivery times (e.g. from 2 days to 5 or 7 days). Most clients on STEP are fellow students or campus staff who understand the academic cycle and will gladly wait a few extra days for quality work.
                    </p>

                    <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">2. Block Out Study vs. Work Hours</h2>
                    <p class="mb-4 text-slate-700 dark:text-slate-300 leading-relaxed text-base">
                        Multi-tasking is highly inefficient. Avoid answering client chats during lectures or coding a gig in the library while studying for a final exam.
                    </p>
                    <p class="mb-6 text-slate-700 dark:text-slate-300 leading-relaxed text-base">
                        Use time-blocking: dedicate weekday evenings solely to exam prep, and assign specific blocks (e.g., Saturday morning) to work on active freelance deliverables. Keeping these boundaries clean reduces stress and prevents mistakes.
                    </p>

                    <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">3. Communicate Early and Proactively</h2>
                    <p class="mb-6 text-slate-700 dark:text-slate-300 leading-relaxed text-base">
                        If you have an active order and notice you are falling behind due to class workloads, open the **Chat** and tell your client immediately. Explain that you are in the middle of exams and request a brief extension. Clear communication build trust and prevents negative reviews.
                    </p>

                    <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">4. Pause Gigs When Needed</h2>
                    <p class="mb-6 text-slate-700 dark:text-slate-300 leading-relaxed text-base">
                        Remember: your education comes first. If your exam load is overwhelming, don't hesitate to temporarily disable your listings. On UiTM STEP, you can toggle your gigs to 'inactive' to pause new orders, and simply toggle them back to 'active' when exams are finished!
                    </p>

                    <div class="mt-10 p-8 bg-slate-50 dark:bg-slate-800/40 rounded-lg border border-gray-100 dark:border-slate-800 text-center">
                        <h3 class="text-xl font-bold text-uitmPurple dark:text-purple-300 mb-2">Back from exams?</h3>
                        <p class="text-sm text-gray-600 dark:text-slate-400 mb-4">Re-enable your gigs or check out the latest student listings on the marketplace.</p>
                        <a href="../marketplace" class="bg-uitmPurple text-white px-8 py-3 rounded-lg font-bold hover:bg-opacity-90 transition-colors shadow-md text-base inline-block">Browse Marketplace</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
