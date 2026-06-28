<?php
// terms.php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-slate-900 rounded-lg shadow-xl border border-gray-100 dark:border-slate-800 p-8 md:p-12 transition-colors duration-300">
        <h1 class="text-3xl md:text-4xl font-bold mb-8 text-uitmPurple dark:text-purple-300 font-serif border-b border-gray-200 dark:border-slate-800 pb-4">Terms of Service</h1>
        
        <div class="prose prose-slate dark:prose-invert max-w-none">
            <p class="text-lg text-gray-600 dark:text-slate-400 mb-6">
                Last updated: <?php echo date('F j, Y'); ?>
            </p>

            <p class="mb-6 text-gray-700 dark:text-slate-300">
                Welcome to UiTM STEP (Student Talent Empowerment Platform). These Terms of Service ("Terms") govern your access to and use of our website and services. By accessing or using UiTM STEP, you agree to be bound by these Terms.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">1. Acceptance of Terms</h2>
            <p class="mb-4 text-gray-700 dark:text-slate-300">
                By creating an account, accessing, or using the platform, you acknowledge that you have read, understood, and agree to be bound by these Terms. If you do not agree, you must not use the platform.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">2. User Accounts</h2>
            <ul class="list-disc pl-6 mb-4 text-gray-700 dark:text-slate-300 space-y-2">
                <li><strong>Eligibility:</strong> You must be an active student of UiTM or an authorized client to use certain features.</li>
                <li><strong>Account Security:</strong> You are responsible for maintaining the confidentiality of your account credentials and for all activities that occur under your account.</li>
                <li><strong>Accuracy of Information:</strong> You agree to provide accurate, current, and complete information during registration and to update such information to keep it accurate.</li>
            </ul>

            <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">3. Platform Rules and Conduct</h2>
            <p class="mb-4 text-gray-700 dark:text-slate-300">
                You agree not to use the platform for any unlawful purpose or in any way that interrupts, damages, or impairs the service. Prohibited behaviors include:
            </p>
            <ul class="list-disc pl-6 mb-4 text-gray-700 dark:text-slate-300 space-y-2">
                <li>Submitting false, misleading, or inappropriate content.</li>
                <li>Attempting to bypass the platform's communication or payment systems.</li>
                <li>Harassing, threatening, or defrauding other users.</li>
                <li>Violating any applicable local, national, or international law.</li>
            </ul>

            <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">4. Services and Payments</h2>
            <p class="mb-4 text-gray-700 dark:text-slate-300">
                UiTM STEP facilitates connections between student freelancers and clients. The terms of any specific project, including deliverables, timelines, and payments, are agreed upon directly between the student and the client. UiTM STEP is not a party to these individual contracts and assumes no liability for unpaid services or unfulfilled work.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">5. Intellectual Property</h2>
            <p class="mb-4 text-gray-700 dark:text-slate-300">
                Users retain all ownership rights to the original content they upload. However, by posting content on UiTM STEP, you grant us a non-exclusive, worldwide, royalty-free license to use, display, and distribute that content in connection with operating and promoting the platform.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">6. Termination</h2>
            <p class="mb-4 text-gray-700 dark:text-slate-300">
                We reserve the right to suspend or terminate your account and access to the platform at our sole discretion, without notice, for conduct that we believe violates these Terms or is harmful to other users of the platform, us, or third parties, or for any other reason.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">7. Limitation of Liability</h2>
            <p class="mb-4 text-gray-700 dark:text-slate-300">
                To the maximum extent permitted by law, UiTM STEP shall not be liable for any indirect, incidental, special, consequential, or punitive damages, or any loss of profits or revenues, whether incurred directly or indirectly, or any loss of data, use, goodwill, or other intangible losses resulting from your use of the platform.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">8. Changes to Terms</h2>
            <p class="mb-4 text-gray-700 dark:text-slate-300">
                We may modify these Terms at any time. We will provide notice of significant changes by posting the updated Terms on the platform. Your continued use of the platform after any such changes constitutes your acceptance of the new Terms.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">9. Contact Information</h2>
            <p class="text-gray-700 dark:text-slate-300">
                If you have any questions or concerns regarding these Terms, please contact us at <strong>support@step.uitm.edu.my</strong>.
            </p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
