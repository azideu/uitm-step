<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';
require_once 'includes/header.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FAQ | UiTM STEP</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <style>
        /* ================= GENERAL RESET ================= */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        /* ================= DARK MODE (DEFAULT) ================= */
        body {
            background: radial-gradient(circle at top, #1a0f3a, #070b1a 60%);
            color: white;
            min-height: 100vh;
        }

        /* CONTAINER LAYOUT */
        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        /* HEADER SECTION */
        .header {
            text-align: center;
            margin-bottom: 35px;
        }

        .header h1 {
            color: #d7b3ff;
            font-size: 32px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .header p {
            color: #aaa;
            font-size: 15px;
            line-height: 1.6;
        }

        /* CATEGORY TABS STYLING */
        .category-tabs {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            justify-content: center;
            margin-bottom: 35px;
        }

        .tab-btn {
            background: rgba(255, 255, 255, 0.05);
            color: #ccc;
            border: 1px solid rgba(255, 255, 255, 0.1);
            padding: 10px 22px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .tab-btn:hover {
            background: rgba(168, 85, 247, 0.2);
            color: #fff;
            border-color: #a855f7;
        }

        .tab-btn.active {
            background: #a855f7;
            color: white;
            border-color: #a855f7;
            box-shadow: 0 0 15px rgba(168, 85, 247, 0.4);
        }

        /* FAQ GROUP LOGIC */
        .faq-group {
            display: none;
        }

        .faq-group.active {
            display: block;
            animation: fadeIn 0.4s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(12px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* FAQ ITEM BOXES */
        .faq-item {
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 14px;
            margin-bottom: 16px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.03);
            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        }

        .faq-question {
            padding: 18px 22px;
            cursor: pointer;
            font-weight: 500;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #fff;
            font-size: 16px;
        }

        .faq-question span {
            font-size: 20px;
            color: #a855f7;
            transition: transform 0.3s ease;
        }

        .faq-answer {
            display: none;
            padding: 18px 22px;
            border-top: 1px solid rgba(255, 255, 255, 0.08);
            color: #ccc;
            font-size: 14px;
            font-weight: 300;
            line-height: 1.7;
        }

        /* ACTIVE FAQ ACCORDION */
        .faq-item.active {
            border: 1px solid #a855f7;
            background: rgba(168, 85, 247, 0.05);
        }

        .faq-item.active .faq-answer {
            display: block;
        }

        .faq-item.active .faq-question span {
            transform: rotate(45deg);
        }

        .faq-item:hover {
            transform: translateY(-2px);
        }


        /* =============================================================
           ====================== LIGHT MODE FIX =======================
           ============================================================= */
        body.light {
            background: #f4f6fb;
            color: #1e293b;
        }

        /* Header Mod Terang */
        body.light .header h1 {
            color: #4b0082 !important;
        }
        body.light .header p {
            color: #64748b !important;
        }

        /* Butang Kategori Mod Terang */
        body.light .tab-btn {
            background: #ffffff;
            color: #475569;
            border: 1px solid #e2e8f0;
        }
        body.light .tab-btn:hover {
            background: #f3e8ff;
            color: #4b0082;
            border-color: #a855f7;
        }
        body.light .tab-btn.active {
            background: #4b0082;
            color: #ffffff;
            border-color: #4b0082;
            box-shadow: 0 4px 12px rgba(75, 0, 130, 0.15);
        }

        /* Box FAQ Timbul Mod Terang */
        body.light .faq-item {
            background: #ffffff !important;
            border: none !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05), 
                        0 2px 4px -1px rgba(0, 0, 0, 0.03);
        }

        body.light .faq-item:hover {
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.08),
                        0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }

        /* Pembetulan Warna Teks Mod Terang */
        body.light .faq-question {
            color: #1e293b !important; /* Kekal gelap & jelas */
        }

        body.light .faq-answer {
            border-top: 1px solid #f1f5f9 !important;
            color: #475569 !important; /* Kekal gelap & jelas */
        }

        body.light .faq-question span {
            color: #4b0082 !important; /* Ikon bertukar warna korporat UiTM */
        }

        /* Box FAQ Aktif Mod Terang */
        body.light .faq-item.active {
            border: 1px solid #4b0082 !important;
            box-shadow: 0 10px 15px -3px rgba(75, 0, 130, 0.08);
        }


        /* RESPONSIVE LAYOUT */
        @media (max-width: 768px) {
            .container {
                width: 100%;
                padding: 25px 15px;
            }
            .header h1 {
                font-size: 26px;
            }
            .category-tabs {
                justify-content: flex-start;
                overflow-x: auto;
                white-space: nowrap;
                padding-bottom: 8px;
                -webkit-overflow-scrolling: touch;
            }
            .tab-btn {
                padding: 8px 16px;
                font-size: 13px;
            }
        }
    </style>
</head>

<body class="light"> <div class="container">

    <div class="header">
        <h1>Frequently Asked Questions</h1>
        <p>Got questions? We’ve got answers. Explore common questions about UiTM STEP and learn how everything works in seconds.</p>
    </div>

    <div class="category-tabs">
        <button class="tab-btn active" data-target="general">1. General</button>
        <button class="tab-btn" data-target="account">2. Account</button>
        <button class="tab-btn" data-target="products">3. Products & Services</button>
        <button class="tab-btn" data-target="ordering">4. Searching & Ordering</button>
        <button class="tab-btn" data-target="chat">5. Chat System</button>
        <button class="tab-btn" data-target="payment">6. Payment</button>
        <button class="tab-btn" data-target="delivery">7. Delivery / Services</button>
        <button class="tab-btn" data-target="support">8. Feedback & Support</button>
        <button class="tab-btn" data-target="safety">9. Safety</button>
    </div>

    <div class="faq-group active" id="general">
        <div class="faq-item">
            <div class="faq-question">What is UiTM STEP? <span>+</span></div>
            <div class="faq-answer">UiTM STEP is a student marketplace platform where UiTM students can buy, sell, and offer services in a safe and simple way.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question">Is UiTM STEP free to use? <span>+</span></div>
            <div class="faq-answer">Yes, the platform is completely free for all UiTM students.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question">Do I need to register before using the platform? <span>+</span></div>
            <div class="faq-answer">Yes, you must create an account using your UiTM student credentials.</div>
        </div>
    </div>

    <div class="faq-group" id="account">
        <div class="faq-item">
            <div class="faq-question">Can I use UiTM STEP without logging in? <span>+</span></div>
            <div class="faq-answer">You can browse products, but you need to log in to chat, buy, or sell.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question">Can I change my profile information? <span>+</span></div>
            <div class="faq-answer">Yes, you can update your profile such as name, phone number, and profile picture anytime.</div>
        </div>
    </div>

    <div class="faq-group" id="products">
        <div class="faq-item">
            <div class="faq-question">What kind of items can be sold? <span>+</span></div>
            <div class="faq-answer">Students can sell physical products (books, food, items) and digital services (design, tutoring, printing, etc.).</div>
        </div>
        <div class="faq-item">
            <div class="faq-question">Are there any restricted items? <span>+</span></div>
            <div class="faq-answer">Yes, illegal items, inappropriate content, or non-academic related prohibited goods are not allowed.</div>
        </div>
    </div>

    <div class="faq-group" id="ordering">
        <div class="faq-item">
            <div class="faq-question">How do I search for items? <span>+</span></div>
            <div class="faq-answer">Use the search bar or filter by category such as food, services, or academic items.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question">Can I cancel an order after placing it? <span>+</span></div>
            <div class="faq-answer">Orders can only be cancelled if the seller has not confirmed it yet.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question">What happens after I place an order? <span>+</span></div>
            <div class="faq-answer">The seller will receive your order and may confirm it or contact you via chat.</div>
        </div>
    </div>

    <div class="faq-group" id="chat">
        <div class="faq-item">
            <div class="faq-question">Why do I need to use chat? <span>+</span></div>
            <div class="faq-answer">Chat allows buyers and sellers to discuss details like pricing, delivery, or service requirements.</div>
        </div>
    </div>

    <div class="faq-group" id="payment">
        <div class="faq-item">
            <div class="faq-question">When will the seller receive payment? <span>+</span></div>
            <div class="faq-answer">Payment is released to the seller after the order is confirmed as completed.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question">What if I pay but don’t receive my item? <span>+</span></div>
            <div class="faq-answer">You should report the issue immediately through feedback or support system.</div>
        </div>
    </div>

    <div class="faq-group" id="delivery">
        <div class="faq-item">
            <div class="faq-question">Do sellers provide delivery? <span>+</span></div>
            <div class="faq-answer">Yes, depending on the seller. Some offer delivery, while others require meet-up.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question">Can I request custom services? <span>+</span></div>
            <div class="faq-answer">Yes, you can discuss custom requests directly with the seller via chat.</div>
        </div>
    </div>

    <div class="faq-group" id="support">
        <div class="faq-item">
            <div class="faq-question">How do I report a problem? <span>+</span></div>
            <div class="faq-answer">Go to the Feedback page and submit your issue with details.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question">What happens after I submit feedback? <span>+</span></div>
            <div class="faq-answer">The admin/support team will review your report and take necessary action.</div>
        </div>
    </div>

    <div class="faq-group" id="safety">
        <div class="faq-item">
            <div class="faq-question">How do I stay safe while using UiTM STEP? <span>+</span></div>
            <div class="faq-answer">Always communicate through the platform, avoid sharing sensitive personal information, and deal with verified users when possible.</div>
        </div>
        <div class="faq-item">
            <div class="faq-question">Is my data safe? <span>+</span></div>
            <div class="faq-answer">Yes, user data is stored securely and only used for platform functionality.</div>
        </div>
    </div>

</div>

<script>
    // 1. Logik Buka/Tutup Setiap Soalan (Accordion)
    document.querySelectorAll(".faq-question").forEach(item => {
        item.addEventListener("click", () => {
            const parent = item.parentElement;
            parent.classList.toggle("active");
        });
    });

    // 2. Logik Tukar Kategori (Tabs)
    document.querySelectorAll(".tab-btn").forEach(button => {
        button.addEventListener("click", () => {
            // Tutup semua soalan yang sedang terbuka sebelum tukar tab (Pilihan - supaya kemas)
            document.querySelectorAll(".faq-item").forEach(item => item.classList.remove("active"));

            // Buang class active dari butang & grup lama
            document.querySelectorAll(".tab-btn").forEach(btn => btn.classList.remove("active"));
            document.querySelectorAll(".faq-group").forEach(group => group.classList.remove("active"));

            // Tambah class active pada yang baru diklik
            button.classList.add("active");
            const targetId = button.getAttribute("data-target");
            document.getElementById(targetId).classList.add("active");
        });
    });
</script>

</body>
</html>>