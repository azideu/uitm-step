<?php
require_once __DIR__ . '/../includes/config.php';

$migrations = [];

// Migration 0: Update users table for verification and banning
$migrations[] = "ALTER TABLE users ADD COLUMN is_verified TINYINT(1) DEFAULT 0 AFTER role";
$migrations[] = "ALTER TABLE users ADD COLUMN otp_code VARCHAR(10) DEFAULT NULL AFTER is_verified";
$migrations[] = "ALTER TABLE users MODIFY COLUMN role ENUM('student', 'admin', 'banned') DEFAULT 'student'";

// Migration 1: Add image & youtube columns to gigs
$migrations[] = "ALTER TABLE gigs ADD COLUMN image_url VARCHAR(255) DEFAULT NULL AFTER status";
$migrations[] = "ALTER TABLE gigs ADD COLUMN youtube_url VARCHAR(255) DEFAULT NULL AFTER image_url";

// Migration 2: Create reports table for the User Reporting System
$migrations[] = "
CREATE TABLE IF NOT EXISTS reports (
    report_id     INT AUTO_INCREMENT PRIMARY KEY,
    reporter_id   INT NOT NULL,
    reported_id   INT NOT NULL,
    reason        ENUM(
                    'scam',
                    'fake_payment_proof',
                    'non_delivery',
                    'harassment',
                    'inappropriate_content',
                    'other'
                  ) NOT NULL,
    details       TEXT DEFAULT NULL,
    status        ENUM('pending', 'reviewed', 'dismissed', 'banned') DEFAULT 'pending',
    admin_note    TEXT DEFAULT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (reported_id) REFERENCES users(user_id) ON DELETE CASCADE
)
";

// Migration 3: Create ban_appeals table
$migrations[] = "
CREATE TABLE IF NOT EXISTS ban_appeals (
    appeal_id     INT AUTO_INCREMENT PRIMARY KEY,
    user_id       INT NOT NULL,
    content       TEXT NOT NULL,
    status        ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    admin_note    TEXT DEFAULT NULL,
    created_at    TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
)
";

// Migration 4: Create reviews table for gig reviews
$migrations[] = "
CREATE TABLE IF NOT EXISTS reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    gig_id INT NOT NULL,
    buyer_id INT NOT NULL,
    seller_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
    review_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_order_review (order_id),
    FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE CASCADE,
    FOREIGN KEY (gig_id) REFERENCES gigs(gig_id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(user_id) ON DELETE CASCADE
)
";

// Migration 5: Create feedback table
$migrations[] = "
CREATE TABLE IF NOT EXISTS feedback (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100),
    email VARCHAR(100),
    phone VARCHAR(20),
    campus VARCHAR(100),
    nature VARCHAR(50),
    message TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)
";

echo "<h2 style='font-family:monospace'>Running Migrations...</h2><pre>";


foreach ($migrations as $i => $sql) {
    try {
        $pdo->exec(trim($sql));
        echo "  [OK] Migration " . ($i + 1) . " succeeded.\n";
    } catch (Exception $e) {
        // Ignore "Duplicate column" errors for idempotent ALTER TABLE runs
        if (strpos($e->getMessage(), 'Duplicate column') !== false || strpos($e->getMessage(), "already exists") !== false) {
            echo "  [SKIP] Migration " . ($i + 1) . ": already applied.\n";
        } else {
            echo "  [ERROR] Migration " . ($i + 1) . ": " . $e->getMessage() . "\n";
        }
    }
}

echo "\nDone.</pre>";

