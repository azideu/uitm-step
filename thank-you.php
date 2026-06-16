<?php
// thank-you.php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
?>

<div class="max-w-lg mx-auto bg-white dark:bg-slate-900 p-8 md:p-10 rounded-3xl shadow-xl border border-gray-100 dark:border-slate-800 transition-colors duration-300 mt-12 mb-12 text-center animate-fade-in-up">
    <!-- Success Icon -->
    <div class="w-20 h-20 bg-purple-50 dark:bg-purple-950/40 text-uitmPurple dark:text-purple-400 rounded-full flex items-center justify-center mx-auto mb-6 shadow-xl transition-colors duration-300">
        <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
    </div>

    <!-- Title -->
    <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white mb-4 font-serif transition-colors duration-300">
        We hear you
    </h1>

    <!-- Message -->
    <div class="text-gray-600 dark:text-slate-400 text-base leading-relaxed space-y-4 mb-6 transition-colors duration-300">
        <p>
            Thank you for your feedback!
        </p>
        <p>
            We truly appreciate you taking the time to share your thoughts with us. 
            Your feedback has been successfully submitted and will be reviewed by our team.
        </p>
        <p>
            It plays an important role in helping us improve and serve you better.
        </p>
    </div>

    <!-- Response Time Badge -->
    <div class="inline-block bg-purple-50 dark:bg-purple-950/40 border border-purple-100 dark:border-purple-900/50 text-uitmPurple dark:text-purple-300 text-xs font-bold px-4 py-2 rounded-full mb-8 transition-colors duration-300">
        Response time: within 1–3 working days
    </div>

    <!-- Button -->
    <div class="pt-6 border-t border-gray-100 dark:border-slate-800 transition-colors duration-300">
        <a href="feedback.php" class="w-full bg-uitmPurple hover:bg-purple-900 text-white font-bold py-3.5 px-6 rounded-2xl shadow-xl transition-all duration-300 flex items-center justify-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
            </svg>
            Back to Form
        </a>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>