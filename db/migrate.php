<?php
require_once __DIR__ . '/../includes/config.php';

$migrations = [];

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

