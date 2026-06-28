<?php
// guides/pricing.php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

$current_guide = 'pricing';
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
                    <span class="text-slate-600 dark:text-slate-400">Financial Tips</span>
                </nav>

                <!-- Title -->
                <h1 class="text-3xl md:text-5xl font-extrabold text-slate-900 dark:text-white mb-4 leading-tight font-serif transition-colors duration-300">
                    Pricing Your First Gig: A Student's Guide
                </h1>

                <!-- Subtitle / Excerpt -->
                <p class="text-lg md:text-xl text-slate-500 dark:text-slate-400 font-medium mb-6 leading-relaxed">
                    Setting your rates for the first time can be intimidating. Learn the strategies to price competitively while earning what your skills are worth.
                </p>

                <!-- Metadata Row -->
                <div class="flex items-center gap-4 text-xs text-gray-500 dark:text-slate-500 mb-8 border-b border-slate-100 dark:border-slate-800 pb-5 font-medium">
                    <div class="flex items-center gap-1.5">
                        <span class="font-bold text-slate-700 dark:text-slate-400">By: STEP Editorial</span>
                    </div>
                    <span>•</span>
                    <span>June 12, 2026</span>
                    <span>•</span>
                    <span>4 min read</span>
                </div>

                <!-- Hero Visual Block -->
                <div class="w-full h-48 bg-gradient-to-br from-uitmPurple via-[#26004d] to-indigo-955 rounded-lg flex items-center justify-center mb-10 shadow-md relative overflow-hidden">
                    <div class="absolute inset-0 bg-noise opacity-10 mix-blend-overlay"></div>
                    <div class="border-2 border-white/20 rounded-lg p-8 bg-white/5 backdrop-blur-sm text-center">
                        <span class="text-3xl font-extrabold text-uitmGold tracking-wider font-serif">PRICING 101</span>
                    </div>
                </div>

                <!-- Article Body (Prose) -->
                <div class="prose prose-slate dark:prose-invert max-w-none">
                    <p class="text-lg text-slate-700 dark:text-slate-300 mb-6 leading-relaxed">
                        Setting your rates for the first time can be intimidating. If you price too high, you risk alienating clients; if you price too low, you undervalue your skills and effort. Finding the sweet spot is key to starting strong.
                    </p>

                    <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">1. Research the Market on STEP</h2>
                    <p class="mb-4 text-slate-700 dark:text-slate-300 leading-relaxed text-base">
                        Before naming a price, check the marketplace. Look at active gigs in your category:
                    </p>
                    <ul class="list-disc pl-6 mb-6 text-slate-700 dark:text-slate-300 space-y-2">
                        <li>What are other freelancers charging for similar deliverables?</li>
                        <li>What do their packages include (e.g. revisions, formats, source files)?</li>
                        <li>Set your price close to the average to remain competitive.</li>
                    </ul>

                    <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">2. Calculate Your Cost of Time</h2>
                    <p class="mb-4 text-slate-700 dark:text-slate-300 leading-relaxed text-base">
                        While freelancing provides flexible hours, your time still has cost. Estimate how many hours a project will take you.
                    </p>
                    <p class="mb-6 text-slate-700 dark:text-slate-300 leading-relaxed text-base">
                        If you desire to make roughly RM20/hour, and a website coding task will take you 10 hours of active programming, price your gig around RM200. Always build in buffer hours for revisions and unforeseen roadblocks.
                    </p>

                    <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">3. Use Tiered Packages</h2>
                    <p class="mb-4 text-slate-700 dark:text-slate-300 leading-relaxed text-base">
                        Offering different levels of service helps you capture buyers with varying budgets. On STEP, clearly outline what different price points buy:
                    </p>
                    <ul class="list-disc pl-6 mb-6 text-slate-700 dark:text-slate-300 space-y-2">
                        <li><strong>Basic:</strong> Standard deliverables with limited revisions (e.g. Logo Design, RM50).</li>
                        <li><strong>Standard:</strong> Higher quality files, quicker delivery, or minor source code additions (e.g. RM120).</li>
                        <li><strong>Premium:</strong> Full source files, commercial use licensing, and premium formats (e.g. RM250).</li>
                    </ul>

                    <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">4. Build Trust First, Scale Later</h2>
                    <p class="mb-6 text-slate-700 dark:text-slate-300 leading-relaxed text-base">
                        When you're new with 0 reviews, consider pricing your first 3 gigs slightly lower than standard rates. This introductory pricing lowers the risk for clients, helping you quickly build positive ratings and project reviews on your profile. Once you have a 5-star profile reputation, you can confidently raise your prices!
                    </p>

                    <div class="mt-10 p-8 bg-slate-50 dark:bg-slate-800/40 rounded-lg border border-gray-100 dark:border-slate-800 text-center">
                        <h3 class="text-xl font-bold text-uitmPurple dark:text-purple-300 mb-2">Ready to list your gig?</h3>
                        <p class="text-sm text-gray-600 dark:text-slate-400 mb-6">Set your rates and open your freelance catalog to student buyers.</p>
                        <a href="../register" class="bg-uitmPurple text-white px-8 py-3 rounded-lg font-bold hover:bg-opacity-90 transition-colors shadow-md text-base inline-block">Post Your Gig</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
