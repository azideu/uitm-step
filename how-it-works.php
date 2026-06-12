<?php
// how-it-works.php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-gray-100 dark:border-slate-800 p-8 md:p-12 transition-colors duration-300">
        <h1 class="text-3xl md:text-4xl font-bold mb-8 text-uitmPurple dark:text-purple-300 font-serif border-b border-gray-200 dark:border-slate-800 pb-4">How It Works</h1>
        
        <div class="prose prose-slate dark:prose-invert max-w-none">
            <p class="text-lg text-gray-600 dark:text-slate-400 mb-6 font-medium">
                UiTM STEP is a freelance marketplace built exclusively for UiTM students. It is designed to connect talented student freelancers with clients, student associations, and campus departments looking for services.
            </p>

            <p class="mb-8 text-gray-700 dark:text-slate-300">
                Whether you want to earn extra income by offering your services, or you're looking to hire high-quality talent for your next project, UiTM STEP makes the process simple, secure, and direct.
            </p>

            <!-- Quick Path Selection -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 my-8 pb-8 border-b border-gray-200 dark:border-slate-800">
                <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-100 dark:border-slate-800/80">
                    <h3 class="text-xl font-bold text-uitmPurple dark:text-purple-300 mb-2 flex items-center gap-2">
                        <svg class="w-6 h-6 text-uitmPurple dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14zm-4 6v-7.5l4-2.222"></path></svg>
                        For Student Freelancers
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-slate-400 mb-4 leading-relaxed">
                        Turn your skills into real-world experience. Build your portfolio, work on campus gigs, and earn income while studying.
                    </p>
                    <a href="register" class="text-sm font-bold text-uitmPurple dark:text-purple-400 hover:underline inline-flex items-center gap-1">
                        Start Freelancing
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
                
                <div class="p-6 bg-slate-50 dark:bg-slate-800/40 rounded-xl border border-slate-100 dark:border-slate-800/80">
                    <h3 class="text-xl font-bold text-uitmPurple dark:text-purple-300 mb-2 flex items-center gap-2">
                        <svg class="w-6 h-6 text-uitmPurple dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        For Clients & Gigs
                    </h3>
                    <p class="text-sm text-gray-600 dark:text-slate-400 mb-4 leading-relaxed">
                        Access top-tier student designers, developers, writers, and tutors. Support students directly with fair pricing.
                    </p>
                    <a href="marketplace" class="text-sm font-bold text-uitmPurple dark:text-purple-400 hover:underline inline-flex items-center gap-1">
                        Find Talent
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                    </a>
                </div>
            </div>

            <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">1. The Freelancer Guide: How to Sell</h2>
            <p class="mb-4 text-gray-700 dark:text-slate-300">
                Are you a student looking to monetize your programming, writing, design, tutoring, or editing skills? Follow these simple steps:
            </p>
            <ul class="list-disc pl-6 mb-8 text-gray-700 dark:text-slate-300 space-y-3">
                <li><strong>Step 1: Sign Up as a Student:</strong> Register using your official UiTM student email. This ensures only verified UiTM students can offer services, maintaining trust.</li>
                <li><strong>Step 2: Build Your Profile:</strong> Complete your profile page. Add your profile photo, biography, lists of skills, and upload past coursework or projects to your portfolio. A rich profile helps you stand out.</li>
                <li><strong>Step 3: Post Your Services:</strong> Create detail-rich services with specific pricing, description, delivery timeframe, and tags (such as Design, Programming, or Tutoring).</li>
                <li><strong>Step 4: Chat with Clients:</strong> When a client is interested in your service, they will contact you. Use our real-time <strong>Chat system</strong> to align on instructions, ask questions, and share project drafts.</li>
                <li><strong>Step 5: Complete & Deliver:</strong> Once the work is done, deliver the assets directly to the client. They will review it, mark the gig as completed, and leave a review on your profile.</li>
            </ul>

            <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">2. The Client Guide: How to Hire</h2>
            <p class="mb-4 text-gray-700 dark:text-slate-300">
                Are you a student club president, department head, or local business owner needing quick, high-quality work? Here's how you can find the perfect freelancer:
            </p>
            <ul class="list-disc pl-6 mb-8 text-gray-700 dark:text-slate-300 space-y-3">
                <li><strong>Step 1: Browse the Marketplace:</strong> Use our search bar or categories list on the <strong>Marketplace page</strong>. Filter results by specific tags like 'Programming' or 'Design' to find what you need.</li>
                <li><strong>Step 2: Check Portfolios & Reviews:</strong> Click on profiles to inspect student portfolio samples, verify their skills list, and read testimonials from other buyers.</li>
                <li><strong>Step 3: Initiate a Chat:</strong> Click the "Chat" button on a student's profile to open a direct message. Discuss the project scope, custom requirements, deadline, and negotiate the final pricing.</li>
                <li><strong>Step 4: Collaborate & Finalize:</strong> Work with the student as they progress. Since STEP is campus-centric, you can even coordinate physical meetings if necessary (e.g. for photo shoots or physical tutoring).</li>
            </ul>

            <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">3. Key Platform Features</h2>
            <p class="mb-4 text-gray-700 dark:text-slate-300">
                UiTM STEP offers tools tailored specifically for the university environment:
            </p>
            <ul class="list-disc pl-6 mb-8 text-gray-700 dark:text-slate-300 space-y-2">
                <li><strong>Verified Campus Network:</strong> Every freelancer's campus (Shah Alam, Puncak Alam, Jasin, etc.) is visible. You can search for freelancers inside your local campus for quick coordination.</li>
                <li><strong>Built-in Chat:</strong> Message any user in real-time. Share specifications, negotiate details, and get instant updates directly on the platform.</li>
                <li><strong>Rating & Review System:</strong> Help maintain quality. Leave transparent reviews for freelancers to highlight stellar work and guide other clients.</li>
                <li><strong>Responsive Interface:</strong> The site adapts perfectly to mobile, tablet, and desktop screens, allowing you to track tasks on the go.</li>
            </ul>

            <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">4. Frequently Asked Questions</h2>
            
            <h3 class="text-lg font-bold mt-6 mb-2 text-gray-800 dark:text-slate-200">Is UiTM STEP free to use?</h3>
            <p class="mb-4 text-gray-700 dark:text-slate-300">
                Yes! There are no platform registration fees or hidden commission percentages. We aim to support student talent and direct peer-to-peer collaboration without transaction cuts.
            </p>

            <h3 class="text-lg font-bold mt-6 mb-2 text-gray-800 dark:text-slate-200">How is payment handled?</h3>
            <p class="mb-4 text-gray-700 dark:text-slate-300">
                Payments are arranged directly between the client and the student freelancer. Common methods include bank transfers, DuitNow QR, or cash. Always clarify the payment terms (deposit, final milestones) in the chat before starting a project.
            </p>

            <h3 class="text-lg font-bold mt-6 mb-2 text-gray-800 dark:text-slate-200">Who can sign up as a freelancer?</h3>
            <p class="mb-8 text-gray-700 dark:text-slate-300">
                Only active UiTM students can register as freelancers. A valid student email address is required during the sign-up process.
            </p>

            <!-- Call To Action Card -->
            <div class="mt-10 p-8 bg-uitmPurple/5 dark:bg-slate-800/60 rounded-2xl border border-uitmPurple/10 dark:border-slate-800 text-center transition-colors duration-300">
                <h3 class="text-2xl font-bold text-uitmPurple dark:text-purple-300 mb-2 font-serif">Ready to get started?</h3>
                <p class="text-slate-600 dark:text-slate-400 mb-6 text-base max-w-lg mx-auto leading-relaxed">Join thousands of UiTM students and begin showcasing your talent or finding local services today.</p>
                <div class="flex flex-col sm:flex-row justify-center gap-4">
                    <a href="register" class="bg-uitmPurple text-white px-8 py-3 rounded-lg font-bold hover:bg-opacity-90 transition-colors shadow-md text-base">Register Now</a>
                    <a href="marketplace" class="bg-white dark:bg-slate-900 text-uitmPurple dark:text-purple-300 border border-slate-200 dark:border-slate-800 px-8 py-3 rounded-lg font-bold hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-colors shadow-sm text-base">Browse Gigs</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
