<?php
require_once __DIR__ . '/includes/config.php';

try {
    $pdo->exec("ALTER TABLE gigs ADD COLUMN image_url VARCHAR(255) DEFAULT NULL AFTER status");
    $pdo->exec("ALTER TABLE gigs ADD COLUMN youtube_url VARCHAR(255) DEFAULT NULL AFTER image_url");
    echo "Migration successful.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
