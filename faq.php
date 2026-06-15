<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
?>

<div class="max-w-4xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
    <div class="bg-white dark:bg-slate-900 rounded-2xl shadow-xl border border-gray-100 dark:border-slate-800 p-8 md:p-12 transition-colors duration-300">
        
        <!-- Header -->
        <div class="text-center mb-10 animate-fade-in-up">
            <h1 class="text-3xl md:text-4xl font-extrabold text-uitmPurple dark:text-purple-300 font-serif mb-4">
                Frequently Asked Questions
            </h1>
            <p class="text-slate-500 dark:text-slate-400 max-w-2xl mx-auto text-base">
                Got questions? We’ve got answers. Explore common questions about UiTM STEP and learn how everything works in seconds.
            </p>
        </div>

        <!-- Category Tabs -->
        <div class="flex flex-wrap gap-2.5 justify-center mb-10 animate-fade-in-up">
            <button class="tab-btn px-5 py-2.5 rounded-full text-xs md:text-sm font-semibold border transition-all duration-300 cursor-pointer bg-uitmPurple text-white border-uitmPurple dark:bg-purple-600 dark:border-purple-600 shadow-md shadow-uitmPurple/10" data-target="general">1. General</button>
            <button class="tab-btn px-5 py-2.5 rounded-full text-xs md:text-sm font-semibold border transition-all duration-300 cursor-pointer bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-350 border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/50" data-target="account">2. Account</button>
            <button class="tab-btn px-5 py-2.5 rounded-full text-xs md:text-sm font-semibold border transition-all duration-300 cursor-pointer bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-350 border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/50" data-target="products">3. Products & Services</button>
            <button class="tab-btn px-5 py-2.5 rounded-full text-xs md:text-sm font-semibold border transition-all duration-300 cursor-pointer bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-350 border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/50" data-target="ordering">4. Searching & Ordering</button>
            <button class="tab-btn px-5 py-2.5 rounded-full text-xs md:text-sm font-semibold border transition-all duration-300 cursor-pointer bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-350 border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/50" data-target="chat">5. Chat System</button>
            <button class="tab-btn px-5 py-2.5 rounded-full text-xs md:text-sm font-semibold border transition-all duration-300 cursor-pointer bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-350 border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/50" data-target="payment">6. Payment</button>
            <button class="tab-btn px-5 py-2.5 rounded-full text-xs md:text-sm font-semibold border transition-all duration-300 cursor-pointer bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-350 border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/50" data-target="delivery">7. Delivery / Services</button>
            <button class="tab-btn px-5 py-2.5 rounded-full text-xs md:text-sm font-semibold border transition-all duration-300 cursor-pointer bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-350 border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/50" data-target="support">8. Feedback & Support</button>
            <button class="tab-btn px-5 py-2.5 rounded-full text-xs md:text-sm font-semibold border transition-all duration-300 cursor-pointer bg-white dark:bg-slate-800 text-slate-600 dark:text-slate-350 border-slate-200 dark:border-slate-800 hover:bg-slate-50 dark:hover:bg-slate-700/50" data-target="safety">9. Safety</button>
        </div>

        <!-- FAQ Content Groups -->
        <div class="faq-group animate-fade-in-up" id="general">
            <div class="faq-item border border-slate-100 dark:border-slate-800 rounded-xl mb-4 bg-white dark:bg-slate-900 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="faq-question w-full text-left px-6 py-4 flex justify-between items-center font-bold text-slate-800 dark:text-slate-100 text-base md:text-lg cursor-pointer transition-colors duration-200">
                    What is UiTM STEP?
                    <span class="text-xl text-uitmPurple dark:text-purple-400 transition-transform duration-300">+</span>
                </div>
                <div class="faq-answer px-6 pb-5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-slate-50 dark:border-slate-800/50 pt-4 hidden">
                    UiTM STEP is a student marketplace platform where UiTM students can buy, sell, and offer services in a safe and simple way.
                </div>
            </div>
            <div class="faq-item border border-slate-100 dark:border-slate-800 rounded-xl mb-4 bg-white dark:bg-slate-900 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="faq-question w-full text-left px-6 py-4 flex justify-between items-center font-bold text-slate-800 dark:text-slate-100 text-base md:text-lg cursor-pointer transition-colors duration-200">
                    Is UiTM STEP free to use?
                    <span class="text-xl text-uitmPurple dark:text-purple-400 transition-transform duration-300">+</span>
                </div>
                <div class="faq-answer px-6 pb-5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-slate-50 dark:border-slate-800/50 pt-4 hidden">
                    Yes, the platform is completely free for all UiTM students.
                </div>
            </div>
            <div class="faq-item border border-slate-100 dark:border-slate-800 rounded-xl mb-4 bg-white dark:bg-slate-900 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="faq-question w-full text-left px-6 py-4 flex justify-between items-center font-bold text-slate-800 dark:text-slate-100 text-base md:text-lg cursor-pointer transition-colors duration-200">
                    Do I need to register before using the platform?
                    <span class="text-xl text-uitmPurple dark:text-purple-400 transition-transform duration-300">+</span>
                </div>
                <div class="faq-answer px-6 pb-5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-slate-50 dark:border-slate-800/50 pt-4 hidden">
                    Yes, you must create an account using your UiTM student credentials.
                </div>
            </div>
        </div>

        <div class="faq-group hidden" id="account">
            <div class="faq-item border border-slate-100 dark:border-slate-800 rounded-xl mb-4 bg-white dark:bg-slate-900 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="faq-question w-full text-left px-6 py-4 flex justify-between items-center font-bold text-slate-800 dark:text-slate-100 text-base md:text-lg cursor-pointer transition-colors duration-200">
                    Can I use UiTM STEP without logging in?
                    <span class="text-xl text-uitmPurple dark:text-purple-400 transition-transform duration-300">+</span>
                </div>
                <div class="faq-answer px-6 pb-5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-slate-50 dark:border-slate-800/50 pt-4 hidden">
                    You can browse products, but you need to log in to chat, buy, or sell.
                </div>
            </div>
            <div class="faq-item border border-slate-100 dark:border-slate-800 rounded-xl mb-4 bg-white dark:bg-slate-900 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="faq-question w-full text-left px-6 py-4 flex justify-between items-center font-bold text-slate-800 dark:text-slate-100 text-base md:text-lg cursor-pointer transition-colors duration-200">
                    Can I change my profile information?
                    <span class="text-xl text-uitmPurple dark:text-purple-400 transition-transform duration-300">+</span>
                </div>
                <div class="faq-answer px-6 pb-5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-slate-50 dark:border-slate-800/50 pt-4 hidden">
                    Yes, you can update your profile such as name, phone number, and profile picture anytime.
                </div>
            </div>
        </div>

        <div class="faq-group hidden" id="products">
            <div class="faq-item border border-slate-100 dark:border-slate-800 rounded-xl mb-4 bg-white dark:bg-slate-900 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="faq-question w-full text-left px-6 py-4 flex justify-between items-center font-bold text-slate-800 dark:text-slate-100 text-base md:text-lg cursor-pointer transition-colors duration-200">
                    What kind of items can be sold?
                    <span class="text-xl text-uitmPurple dark:text-purple-400 transition-transform duration-300">+</span>
                </div>
                <div class="faq-answer px-6 pb-5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-slate-50 dark:border-slate-800/50 pt-4 hidden">
                    Students can sell physical products (books, food, items) and digital services (design, tutoring, printing, etc.).
                </div>
            </div>
            <div class="faq-item border border-slate-100 dark:border-slate-800 rounded-xl mb-4 bg-white dark:bg-slate-900 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="faq-question w-full text-left px-6 py-4 flex justify-between items-center font-bold text-slate-800 dark:text-slate-100 text-base md:text-lg cursor-pointer transition-colors duration-200">
                    Are there any restricted items?
                    <span class="text-xl text-uitmPurple dark:text-purple-400 transition-transform duration-300">+</span>
                </div>
                <div class="faq-answer px-6 pb-5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-slate-50 dark:border-slate-800/50 pt-4 hidden">
                    Yes, illegal items, inappropriate content, or non-academic related prohibited goods are not allowed.
                </div>
            </div>
        </div>

        <div class="faq-group hidden" id="ordering">
            <div class="faq-item border border-slate-100 dark:border-slate-800 rounded-xl mb-4 bg-white dark:bg-slate-900 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="faq-question w-full text-left px-6 py-4 flex justify-between items-center font-bold text-slate-800 dark:text-slate-100 text-base md:text-lg cursor-pointer transition-colors duration-200">
                    How do I search for items?
                    <span class="text-xl text-uitmPurple dark:text-purple-400 transition-transform duration-300">+</span>
                </div>
                <div class="faq-answer px-6 pb-5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-slate-50 dark:border-slate-800/50 pt-4 hidden">
                    Use the search bar or filter by category such as food, services, or academic items.
                </div>
            </div>
            <div class="faq-item border border-slate-100 dark:border-slate-800 rounded-xl mb-4 bg-white dark:bg-slate-900 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="faq-question w-full text-left px-6 py-4 flex justify-between items-center font-bold text-slate-800 dark:text-slate-100 text-base md:text-lg cursor-pointer transition-colors duration-200">
                    Can I cancel an order after placing it?
                    <span class="text-xl text-uitmPurple dark:text-purple-400 transition-transform duration-300">+</span>
                </div>
                <div class="faq-answer px-6 pb-5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-slate-50 dark:border-slate-800/50 pt-4 hidden">
                    Orders can only be cancelled if the seller has not confirmed it yet.
                </div>
            </div>
            <div class="faq-item border border-slate-100 dark:border-slate-800 rounded-xl mb-4 bg-white dark:bg-slate-900 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="faq-question w-full text-left px-6 py-4 flex justify-between items-center font-bold text-slate-800 dark:text-slate-100 text-base md:text-lg cursor-pointer transition-colors duration-200">
                    What happens after I place an order?
                    <span class="text-xl text-uitmPurple dark:text-purple-400 transition-transform duration-300">+</span>
                </div>
                <div class="faq-answer px-6 pb-5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-slate-50 dark:border-slate-800/50 pt-4 hidden">
                    The seller will receive your order and may confirm it or contact you via chat.
                </div>
            </div>
        </div>

        <div class="faq-group hidden" id="chat">
            <div class="faq-item border border-slate-100 dark:border-slate-800 rounded-xl mb-4 bg-white dark:bg-slate-900 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="faq-question w-full text-left px-6 py-4 flex justify-between items-center font-bold text-slate-800 dark:text-slate-100 text-base md:text-lg cursor-pointer transition-colors duration-200">
                    Why do I need to use chat?
                    <span class="text-xl text-uitmPurple dark:text-purple-400 transition-transform duration-300">+</span>
                </div>
                <div class="faq-answer px-6 pb-5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-slate-50 dark:border-slate-800/50 pt-4 hidden">
                    Chat allows buyers and sellers to discuss details like pricing, delivery, or service requirements.
                </div>
            </div>
        </div>

        <div class="faq-group hidden" id="payment">
            <div class="faq-item border border-slate-100 dark:border-slate-800 rounded-xl mb-4 bg-white dark:bg-slate-900 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="faq-question w-full text-left px-6 py-4 flex justify-between items-center font-bold text-slate-800 dark:text-slate-100 text-base md:text-lg cursor-pointer transition-colors duration-200">
                    When will the seller receive payment?
                    <span class="text-xl text-uitmPurple dark:text-purple-400 transition-transform duration-300">+</span>
                </div>
                <div class="faq-answer px-6 pb-5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-slate-50 dark:border-slate-800/50 pt-4 hidden">
                    Payment is released to the seller after the order is confirmed as completed.
                </div>
            </div>
            <div class="faq-item border border-slate-100 dark:border-slate-800 rounded-xl mb-4 bg-white dark:bg-slate-900 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="faq-question w-full text-left px-6 py-4 flex justify-between items-center font-bold text-slate-800 dark:text-slate-100 text-base md:text-lg cursor-pointer transition-colors duration-200">
                    What if I pay but don’t receive my item?
                    <span class="text-xl text-uitmPurple dark:text-purple-400 transition-transform duration-300">+</span>
                </div>
                <div class="faq-answer px-6 pb-5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-slate-50 dark:border-slate-800/50 pt-4 hidden">
                    You should report the issue immediately through feedback or support system.
                </div>
            </div>
        </div>

        <div class="faq-group hidden" id="delivery">
            <div class="faq-item border border-slate-100 dark:border-slate-800 rounded-xl mb-4 bg-white dark:bg-slate-900 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="faq-question w-full text-left px-6 py-4 flex justify-between items-center font-bold text-slate-800 dark:text-slate-100 text-base md:text-lg cursor-pointer transition-colors duration-200">
                    Do sellers provide delivery?
                    <span class="text-xl text-uitmPurple dark:text-purple-400 transition-transform duration-300">+</span>
                </div>
                <div class="faq-answer px-6 pb-5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-slate-50 dark:border-slate-800/50 pt-4 hidden">
                    Yes, depending on the seller. Some offer delivery, while others require meet-up.
                </div>
            </div>
            <div class="faq-item border border-slate-100 dark:border-slate-800 rounded-xl mb-4 bg-white dark:bg-slate-900 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="faq-question w-full text-left px-6 py-4 flex justify-between items-center font-bold text-slate-800 dark:text-slate-100 text-base md:text-lg cursor-pointer transition-colors duration-200">
                    Can I request custom services?
                    <span class="text-xl text-uitmPurple dark:text-purple-400 transition-transform duration-300">+</span>
                </div>
                <div class="faq-answer px-6 pb-5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-slate-50 dark:border-slate-800/50 pt-4 hidden">
                    Yes, you can discuss custom requests directly with the seller via chat.
                </div>
            </div>
        </div>

        <div class="faq-group hidden" id="support">
            <div class="faq-item border border-slate-100 dark:border-slate-800 rounded-xl mb-4 bg-white dark:bg-slate-900 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="faq-question w-full text-left px-6 py-4 flex justify-between items-center font-bold text-slate-800 dark:text-slate-100 text-base md:text-lg cursor-pointer transition-colors duration-200">
                    How do I report a problem?
                    <span class="text-xl text-uitmPurple dark:text-purple-400 transition-transform duration-300">+</span>
                </div>
                <div class="faq-answer px-6 pb-5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-slate-50 dark:border-slate-800/50 pt-4 hidden">
                    Go to the Feedback page and submit your issue with details.
                </div>
            </div>
            <div class="faq-item border border-slate-100 dark:border-slate-800 rounded-xl mb-4 bg-white dark:bg-slate-900 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="faq-question w-full text-left px-6 py-4 flex justify-between items-center font-bold text-slate-800 dark:text-slate-100 text-base md:text-lg cursor-pointer transition-colors duration-200">
                    What happens after I submit feedback?
                    <span class="text-xl text-uitmPurple dark:text-purple-400 transition-transform duration-300">+</span>
                </div>
                <div class="faq-answer px-6 pb-5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-slate-50 dark:border-slate-800/50 pt-4 hidden">
                    The admin/support team will review your report and take necessary action.
                </div>
            </div>
        </div>

        <div class="faq-group hidden" id="safety">
            <div class="faq-item border border-slate-100 dark:border-slate-800 rounded-xl mb-4 bg-white dark:bg-slate-900 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="faq-question w-full text-left px-6 py-4 flex justify-between items-center font-bold text-slate-800 dark:text-slate-100 text-base md:text-lg cursor-pointer transition-colors duration-200">
                    How do I stay safe while using UiTM STEP?
                    <span class="text-xl text-uitmPurple dark:text-purple-400 transition-transform duration-300">+</span>
                </div>
                <div class="faq-answer px-6 pb-5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-slate-50 dark:border-slate-800/50 pt-4 hidden">
                    Always communicate through the platform, avoid sharing sensitive personal information, and deal with verified users when possible.
                </div>
            </div>
            <div class="faq-item border border-slate-100 dark:border-slate-800 rounded-xl mb-4 bg-white dark:bg-slate-900 shadow-sm hover:shadow-md transition-all duration-300 overflow-hidden">
                <div class="faq-question w-full text-left px-6 py-4 flex justify-between items-center font-bold text-slate-800 dark:text-slate-100 text-base md:text-lg cursor-pointer transition-colors duration-200">
                    Is my data safe?
                    <span class="text-xl text-uitmPurple dark:text-purple-400 transition-transform duration-300">+</span>
                </div>
                <div class="faq-answer px-6 pb-5 text-sm text-slate-600 dark:text-slate-400 leading-relaxed border-t border-slate-50 dark:border-slate-800/50 pt-4 hidden">
                    Yes, user data is stored securely and only used for platform functionality.
                </div>
            </div>
        </div>

    </div>
</div>

<script>
    // Accordion Toggle
    document.querySelectorAll(".faq-question").forEach(item => {
        item.addEventListener("click", () => {
            const parent = item.parentElement;
            const answer = parent.querySelector(".faq-answer");
            const span = item.querySelector("span");
            
            const isActive = parent.classList.contains("border-uitmPurple");
            if (isActive) {
                parent.classList.remove("border-uitmPurple", "dark:border-purple-500", "bg-purple-50/5", "dark:bg-purple-950/5");
                parent.classList.add("border-slate-100", "dark:border-slate-800");
                if (answer) answer.classList.add("hidden");
                if (span) span.classList.remove("rotate-45");
            } else {
                parent.classList.add("border-uitmPurple", "dark:border-purple-500", "bg-purple-50/5", "dark:bg-purple-950/5");
                parent.classList.remove("border-slate-100", "dark:border-slate-800");
                if (answer) answer.classList.remove("hidden");
                if (span) span.classList.add("rotate-45");
            }
        });
    });

    // Category Tabs Logic
    const activeClasses = ['bg-uitmPurple', 'text-white', 'border-uitmPurple', 'dark:bg-purple-600', 'dark:border-purple-600', 'shadow-md', 'shadow-uitmPurple/10'];
    const inactiveClasses = ['bg-white', 'dark:bg-slate-800', 'text-slate-600', 'dark:text-slate-350', 'border-slate-200', 'dark:border-slate-800', 'hover:bg-slate-50', 'dark:hover:bg-slate-700/50'];

    document.querySelectorAll(".tab-btn").forEach(button => {
        button.addEventListener("click", () => {
            // Close any active FAQ items first to make clean transition
            document.querySelectorAll(".faq-item").forEach(item => {
                item.classList.remove("border-uitmPurple", "dark:border-purple-500", "bg-purple-50/5", "dark:bg-purple-950/5");
                item.classList.add("border-slate-100", "dark:border-slate-800");
                const answer = item.querySelector(".faq-answer");
                if (answer) answer.classList.add("hidden");
                const span = item.querySelector("span");
                if (span) span.classList.remove("rotate-45");
            });

            // Toggle active classes on tab buttons
            document.querySelectorAll(".tab-btn").forEach(btn => {
                btn.classList.remove(...activeClasses);
                btn.classList.add(...inactiveClasses);
            });
            button.classList.remove(...inactiveClasses);
            button.classList.add(...activeClasses);

            // Switch active group
            document.querySelectorAll(".faq-group").forEach(group => group.classList.add("hidden"));
            const targetId = button.getAttribute("data-target");
            const targetGroup = document.getElementById(targetId);
            if (targetGroup) {
                targetGroup.classList.remove("hidden");
                targetGroup.classList.add("animate-fade-in-up");
            }
        });
    });
</script>

<?php require_once 'includes/footer.php'; ?>