<?php
// guides/index.php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';
require_once '../includes/header.php';

// Define available topics and their guides
$topics = [
    'career' => [
        'title' => 'Career Development Guides',
        'subtitle' => 'Accelerating your career growth and building professional networks.',
        'desc' => 'Learn how to land gigs, design portfolios, network with alumni, prepare for interviews, and transition to post-graduation jobs.',
        'guides' => [
            [
                'title' => 'How to Build a Freelance Portfolio While Studying',
                'url' => 'guides/portfolio',
                'img' => '../assets/img/guide_portfolio.jpg',
                'date' => 'June 12, 2026',
                'read_time' => '5 min read'
            ]
        ]
    ],
    'finance' => [
        'title' => 'Financial Tips & Pricing Guides',
        'subtitle' => 'Managing your income, budgeting, and pricing freelance projects.',
        'desc' => 'Guides on estimating hourly rates, setting fixed gig prices, structuring payment milestones, and managing savings as a student.',
        'guides' => [
            [
                'title' => 'Pricing Your First Gig: A Student\'s Guide',
                'url' => 'guides/pricing',
                'img' => null, // Will render styled fallback
                'date' => 'June 12, 2026',
                'read_time' => '4 min read',
                'placeholder_text' => 'Pricing 101'
            ]
        ]
    ],
    'student-life' => [
        'title' => 'Student Life & Balance Guides',
        'subtitle' => 'Navigating university schedules, side-hustles, and exam stress.',
        'desc' => 'Learn how to balance high academic achievement with freelancing, final exams, peer relationships, and health.',
        'guides' => [
            [
                'title' => 'Time Management: Freelancing and Finals',
                'url' => 'guides/time',
                'img' => '../assets/img/guide_time.jpg',
                'date' => 'June 12, 2026',
                'read_time' => '6 min read'
            ]
        ]
    ],
    'programming' => [
        'title' => 'Programming & Tech Guides',
        'subtitle' => 'Everything you need to know about coding, databases, and software deployment.',
        'desc' => 'Highly practical guides covering web development, databases, mobile apps, code optimization, version control, and more!',
        'guides' => []
    ],
    'design' => [
        'title' => 'Graphics & Design Guides',
        'subtitle' => 'Unlocking your creative potential and building stunning visual brands.',
        'desc' => 'Master layout design, brand identity, vector illustration, dark/light mode UI components, and software tooling.',
        'guides' => []
    ],
    'writing' => [
        'title' => 'Writing & Translation Guides',
        'subtitle' => 'Crafting compelling copy, translation workflows, and academic proofreading.',
        'desc' => 'Learn how to outline essays, build brand voices, proofread thesis documents, and write engaging newsletters.',
        'guides' => []
    ],
    'video' => [
        'title' => 'Video & Animation Guides',
        'subtitle' => 'Editing high-quality videos, adding motion graphics, and telling stories.',
        'desc' => 'Guides on timeline pacing, audio adjustments, color grading, social media reels formatting, and export presets.',
        'guides' => []
    ],
    'tutor' => [
        'title' => 'Education & Tutoring Guides',
        'subtitle' => 'Tips and strategies for tutoring students and academic success.',
        'desc' => 'Learn how to structure lesson plans, explain complex concepts, run test prep sessions, and manage virtual classes.',
        'guides' => []
    ]
];

// Get current topic parameter
$active_topic_key = isset($_GET['topic']) && array_key_exists($_GET['topic'], $topics) ? $_GET['topic'] : 'career';
$active_topic = $topics[$active_topic_key];
?>

<div class="max-w-7xl mx-auto px-4 py-12 sm:px-6 lg:px-8 animate-fade-in-up">
    <div class="flex flex-col lg:flex-row gap-8">
        <!-- Left Column: Sidebar (Topics & Popular) -->
        <div class="lg:w-64 flex-shrink-0 space-y-6">
            <!-- Topics Panel -->
            <div class="bg-white dark:bg-slate-900 rounded-lg p-6 border border-gray-100 dark:border-slate-800 transition-colors duration-300">
                <h3 class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest mb-4">Choose Your Topic</h3>
                <ul class="space-y-3.5 text-sm font-bold text-slate-700 dark:text-slate-300">
                    <li><a href="<?php echo ROOT_URL; ?>guides/?topic=career" class="transition-colors flex items-center gap-2 <?php echo $active_topic_key === 'career' ? 'text-uitmPurple dark:text-purple-300' : 'hover:text-uitmPurple dark:hover:text-purple-400'; ?>"><span class="w-1.5 h-1.5 rounded-full <?php echo $active_topic_key === 'career' ? 'bg-uitmPurple dark:bg-purple-300' : 'bg-slate-300 dark:bg-slate-700'; ?>"></span> Career Development</a></li>
                    <li><a href="<?php echo ROOT_URL; ?>guides/?topic=finance" class="transition-colors flex items-center gap-2 <?php echo $active_topic_key === 'finance' ? 'text-uitmPurple dark:text-purple-300' : 'hover:text-uitmPurple dark:hover:text-purple-400'; ?>"><span class="w-1.5 h-1.5 rounded-full <?php echo $active_topic_key === 'finance' ? 'bg-uitmPurple dark:bg-purple-300' : 'bg-slate-300 dark:bg-slate-700'; ?>"></span> Financial Tips</a></li>
                    <li><a href="<?php echo ROOT_URL; ?>guides/?topic=student-life" class="transition-colors flex items-center gap-2 <?php echo $active_topic_key === 'student-life' ? 'text-uitmPurple dark:text-purple-300' : 'hover:text-uitmPurple dark:hover:text-purple-400'; ?>"><span class="w-1.5 h-1.5 rounded-full <?php echo $active_topic_key === 'student-life' ? 'bg-uitmPurple dark:bg-purple-300' : 'bg-slate-300 dark:bg-slate-700'; ?>"></span> Student Life</a></li>
                    <li class="border-t border-slate-100 dark:border-slate-800 pt-3"></li>
                    <li><a href="<?php echo ROOT_URL; ?>guides/?topic=programming" class="transition-colors flex items-center gap-2 <?php echo $active_topic_key === 'programming' ? 'text-uitmPurple dark:text-purple-300' : 'hover:text-uitmPurple dark:hover:text-purple-400'; ?>"><span class="w-1.5 h-1.5 rounded-full <?php echo $active_topic_key === 'programming' ? 'bg-uitmPurple dark:bg-purple-300' : 'bg-slate-300 dark:bg-slate-700'; ?>"></span> Programming & Tech</a></li>
                    <li><a href="<?php echo ROOT_URL; ?>guides/?topic=design" class="transition-colors flex items-center gap-2 <?php echo $active_topic_key === 'design' ? 'text-uitmPurple dark:text-purple-300' : 'hover:text-uitmPurple dark:hover:text-purple-400'; ?>"><span class="w-1.5 h-1.5 rounded-full <?php echo $active_topic_key === 'design' ? 'bg-uitmPurple dark:bg-purple-300' : 'bg-slate-300 dark:bg-slate-700'; ?>"></span> Graphics & Design</a></li>
                    <li><a href="<?php echo ROOT_URL; ?>guides/?topic=writing" class="transition-colors flex items-center gap-2 <?php echo $active_topic_key === 'writing' ? 'text-uitmPurple dark:text-purple-300' : 'hover:text-uitmPurple dark:hover:text-purple-400'; ?>"><span class="w-1.5 h-1.5 rounded-full <?php echo $active_topic_key === 'writing' ? 'bg-uitmPurple dark:bg-purple-300' : 'bg-slate-300 dark:bg-slate-700'; ?>"></span> Writing & Translation</a></li>
                    <li><a href="<?php echo ROOT_URL; ?>guides/?topic=video" class="transition-colors flex items-center gap-2 <?php echo $active_topic_key === 'video' ? 'text-uitmPurple dark:text-purple-300' : 'hover:text-uitmPurple dark:hover:text-purple-400'; ?>"><span class="w-1.5 h-1.5 rounded-full <?php echo $active_topic_key === 'video' ? 'bg-uitmPurple dark:bg-purple-300' : 'bg-slate-300 dark:bg-slate-700'; ?>"></span> Video & Animation</a></li>
                    <li><a href="<?php echo ROOT_URL; ?>guides/?topic=tutor" class="transition-colors flex items-center gap-2 <?php echo $active_topic_key === 'tutor' ? 'text-uitmPurple dark:text-purple-300' : 'hover:text-uitmPurple dark:hover:text-purple-400'; ?>"><span class="w-1.5 h-1.5 rounded-full <?php echo $active_topic_key === 'tutor' ? 'bg-uitmPurple dark:bg-purple-300' : 'bg-slate-300 dark:bg-slate-700'; ?>"></span> Education & Tutoring</a></li>
                </ul>
            </div>

            <!-- Popular Panel -->
            <div class="bg-white dark:bg-slate-900 rounded-lg p-6 border border-gray-100 dark:border-slate-800 transition-colors duration-300">
                <h3 class="text-xs font-bold text-gray-400 dark:text-slate-500 uppercase tracking-widest mb-4">Most Popular Guides</h3>
                <ul class="space-y-4 text-xs font-bold text-slate-700 dark:text-slate-300">
                    <li>
                        <a href="<?php echo ROOT_URL; ?>guides/portfolio" class="transition-colors flex flex-col gap-1 hover:text-uitmPurple dark:hover:text-purple-400">
                            <span>How to build a freelance portfolio while studying</span>
                            <span class="text-[10px] text-gray-400 font-medium font-sans">5 min read</span>
                        </a>
                    </li>
                    <li class="border-t border-slate-100 dark:border-slate-800 pt-3">
                        <a href="<?php echo ROOT_URL; ?>guides/pricing" class="transition-colors flex flex-col gap-1 hover:text-uitmPurple dark:hover:text-purple-400">
                            <span>Pricing your first gig: A student's guide</span>
                            <span class="text-[10px] text-gray-400 font-medium font-sans">4 min read</span>
                        </a>
                    </li>
                    <li class="border-t border-slate-100 dark:border-slate-800 pt-3">
                        <a href="<?php echo ROOT_URL; ?>guides/time" class="transition-colors flex flex-col gap-1 hover:text-uitmPurple dark:hover:text-purple-400">
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
                    <a href="<?php echo ROOT_URL; ?>guides" class="hover:text-uitmPurple dark:hover:text-purple-400 transition-colors">STEP Guides</a>
                    <span class="mx-1.5">&rsaquo;</span>
                    <span class="text-slate-600 dark:text-slate-400"><?php echo escape($active_topic['title']); ?></span>
                </nav>

                <!-- Topic Header -->
                <h1 class="text-3xl md:text-5xl font-extrabold text-slate-900 dark:text-white mb-3 font-serif transition-colors duration-300">
                    <?php echo escape($active_topic['title']); ?>
                </h1>
                
                <h3 class="text-lg md:text-xl font-bold text-slate-700 dark:text-slate-300 mb-2 leading-relaxed transition-colors duration-300">
                    <?php echo escape($active_topic['subtitle']); ?>
                </h3>
                
                <p class="text-slate-500 dark:text-slate-400 text-sm leading-relaxed mb-10 pb-6 border-b border-slate-100 dark:border-slate-800 transition-colors duration-300">
                    <?php echo escape($active_topic['desc']); ?>
                </p>

                <!-- Guides Grid -->
                <?php if (count($active_topic['guides']) > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                        <?php foreach ($active_topic['guides'] as $guide): ?>
                            <a href="<?php echo ROOT_URL . $guide['url']; ?>" class="group block bg-slate-50 dark:bg-slate-800 rounded-lg overflow-hidden border border-slate-100 dark:border-slate-800 shadow-md hover:shadow-xl hover:-translate-y-1.5 transition-all duration-300">
                                <!-- Guide Thumbnail -->
                                <div class="w-full aspect-video bg-slate-200 dark:bg-slate-800 relative overflow-hidden flex items-center justify-center">
                                    <?php if (!empty($guide['img'])): ?>
                                        <img src="<?php echo $guide['img']; ?>" alt="<?php echo escape($guide['title']); ?>" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                    <?php else: ?>
                                        <div class="absolute inset-0 bg-gradient-to-br from-uitmPurple via-[#26004d] to-indigo-950 flex items-center justify-center p-4">
                                            <div class="border border-white/20 rounded-lg py-3 px-6 bg-white/5 backdrop-blur-sm text-center">
                                                <span class="text-lg font-extrabold text-uitmGold tracking-wider font-serif"><?php echo escape($guide['placeholder_text'] ?? 'STEP GUIDE'); ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                <!-- Guide Details -->
                                <div class="p-6">
                                    <h4 class="font-extrabold text-slate-800 dark:text-white text-base group-hover:text-uitmPurple dark:group-hover:text-purple-300 transition-colors line-clamp-2 leading-snug mb-3 font-serif">
                                        <?php echo escape($guide['title']); ?>
                                    </h4>
                                    <div class="flex items-center gap-2 text-[10px] text-gray-500 dark:text-slate-500 font-bold uppercase tracking-wider font-sans">
                                        <span><?php echo $guide['date']; ?></span>
                                        <span>•</span>
                                        <span><?php echo $guide['read_time']; ?></span>
                                    </div>
                                </div>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <!-- Empty State for empty categories -->
                    <div class="text-center py-16 px-4 bg-slate-50/50 dark:bg-slate-800/30 rounded-lg border border-dashed border-slate-200 dark:border-slate-800 transition-all duration-300">
                        <svg class="w-16 h-16 text-gray-300 dark:text-slate-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477-4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                        </svg>
                        <h3 class="text-2xl font-bold text-slate-700 dark:text-slate-400 mb-2 font-serif">No guides for this topic yet!</h3>
                        <p class="text-gray-500 dark:text-slate-500 max-w-sm mx-auto text-sm leading-relaxed">We are currently drafting tutorials and guides for this category. Check back soon or browse other topics in the sidebar!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>
