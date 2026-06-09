<?php
// privacy.php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-gray-100 dark:border-slate-800 p-8 md:p-12 transition-colors duration-300">
        <h1 class="text-3xl md:text-4xl font-bold mb-8 text-uitmPurple dark:text-purple-300 font-serif border-b border-gray-200 dark:border-slate-800 pb-4">Privacy Policy</h1>
        
        <div class="prose prose-slate dark:prose-invert max-w-none">
            <p class="text-lg text-gray-600 dark:text-slate-400 mb-6">
                Last updated: <?php echo date('F j, Y'); ?>
            </p>

            <p class="mb-6 text-gray-700 dark:text-slate-300">
                UiTM STEP ("we," "our," or "us") is committed to protecting your privacy. This Privacy Policy explains how we collect, use, disclose, and safeguard your information when you visit our platform. We have designed our practices to align with international data protection standards, including the General Data Protection Regulation (GDPR).
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">1. Information We Collect</h2>
            <p class="mb-4 text-gray-700 dark:text-slate-300">
                We may collect personal identification information from you in various ways, including:
            </p>
            <ul class="list-disc pl-6 mb-4 text-gray-700 dark:text-slate-300 space-y-2">
                <li><strong>Identity Data:</strong> First name, last name, and student ID.</li>
                <li><strong>Contact Data:</strong> UiTM student email address and contact numbers.</li>
                <li><strong>Profile Data:</strong> Skills, portfolio items, biography, and profile images you choose to upload.</li>
                <li><strong>Technical Data:</strong> IP address, browser type and version, time zone setting, operating system, and platform details.</li>
                <li><strong>Usage Data:</strong> Information about how you use our platform, products, and services.</li>
            </ul>

            <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">2. Legal Basis for Processing (GDPR)</h2>
            <p class="mb-4 text-gray-700 dark:text-slate-300">
                Under the GDPR, we must have a lawful basis for processing your personal data. We rely on the following bases:
            </p>
            <ul class="list-disc pl-6 mb-4 text-gray-700 dark:text-slate-300 space-y-2">
                <li><strong>Consent:</strong> You have given clear consent for us to process your personal data for specific purposes.</li>
                <li><strong>Contract:</strong> Processing is necessary for a contract we have with you (e.g., providing our marketplace services).</li>
                <li><strong>Legitimate Interests:</strong> Processing is necessary for our legitimate interests, such as improving our platform and preventing fraud, provided those interests are not overridden by your rights.</li>
            </ul>

            <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">3. How We Use Your Information</h2>
            <p class="mb-4 text-gray-700 dark:text-slate-300">
                We use the collected information for various purposes, including to:
            </p>
            <ul class="list-disc pl-6 mb-4 text-gray-700 dark:text-slate-300 space-y-2">
                <li>Provide, operate, and maintain our platform.</li>
                <li>Verify your student status and manage your account.</li>
                <li>Facilitate connections between students and clients.</li>
                <li>Send administrative information, such as updates, security alerts, and support messages.</li>
                <li>Comply with legal obligations and resolve disputes.</li>
            </ul>

            <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">4. Data Retention</h2>
            <p class="mb-4 text-gray-700 dark:text-slate-300">
                We will retain your personal information only for as long as is necessary for the purposes set out in this Privacy Policy. We will retain and use your information to the extent necessary to comply with our legal obligations, resolve disputes, and enforce our policies. When your data is no longer needed, we will securely delete or anonymize it.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">5. Information Sharing and International Transfers</h2>
            <p class="mb-4 text-gray-700 dark:text-slate-300">
                We do not sell your personal data. We may share your public profile information with other users to facilitate freelance opportunities. If we transfer your personal information across international borders, we ensure appropriate safeguards are in place (such as Standard Contractual Clauses) to protect your data under international laws.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">6. Your Data Protection Rights</h2>
            <p class="mb-4 text-gray-700 dark:text-slate-300">
                Depending on your location, you may have the following rights regarding your personal data:
            </p>
            <ul class="list-disc pl-6 mb-4 text-gray-700 dark:text-slate-300 space-y-2">
                <li><strong>The right to access:</strong> You can request copies of your personal data.</li>
                <li><strong>The right to rectification:</strong> You can request that we correct inaccurate or incomplete data.</li>
                <li><strong>The right to erasure (Right to be forgotten):</strong> You can request that we erase your personal data under certain conditions.</li>
                <li><strong>The right to restrict processing:</strong> You can request that we restrict the processing of your data.</li>
                <li><strong>The right to data portability:</strong> You can request that we transfer the data that we have collected to another organization, or directly to you.</li>
                <li><strong>The right to object:</strong> You can object to our processing of your personal data.</li>
            </ul>
            <p class="mb-4 text-gray-700 dark:text-slate-300">
                To exercise any of these rights, please contact us using the information provided below. We have one month to respond to your request.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">7. Cookies and Tracking Technologies</h2>
            <p class="mb-4 text-gray-700 dark:text-slate-300">
                We use cookies and similar tracking technologies to track activity on our platform and hold certain information. You can instruct your browser to refuse all cookies or to indicate when a cookie is being sent. However, if you do not accept cookies, you may not be able to use some portions of our platform.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">8. Data Security</h2>
            <p class="mb-4 text-gray-700 dark:text-slate-300">
                We implement robust technical and organizational security measures designed to protect your personal data from unauthorized access, disclosure, alteration, or destruction. However, no internet transmission is completely secure, and we cannot guarantee absolute security.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">9. Changes to This Privacy Policy</h2>
            <p class="mb-4 text-gray-700 dark:text-slate-300">
                We may update our Privacy Policy from time to time to reflect changes in our practices or relevant laws. We will notify you of any significant changes by posting the new policy on this page and updating the "Last updated" date.
            </p>

            <h2 class="text-2xl font-bold mt-8 mb-4 text-gray-900 dark:text-white">10. Contact Us</h2>
            <p class="text-gray-700 dark:text-slate-300">
                If you have any questions about this Privacy Policy, your rights, or our data practices, please contact our Data Protection Officer at <strong>privacy@step.uitm.edu.my</strong> or through official UiTM administrative channels.
            </p>
        </div>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>
