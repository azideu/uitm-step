<?php
// Test storage.php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/storage.php';

echo "Testing Storage class...<br>";

// Test that Storage class can be instantiated
if (class_exists('Storage')) {
    echo "[OK] Storage class exists<br>";
} else {
    echo "[ERROR] Storage class not found<br>";
}

// Test upload with dummy file
$testFile = sys_get_temp_dir() . '/test_' . uniqid() . '.txt';
file_put_contents($testFile, 'test content');

if (file_exists($testFile)) {
    echo "[OK] Test file created: $testFile<br>";
    
    $result = Storage::upload($testFile, 'test_receipt_' . uniqid() . '.txt', 'text/plain');
    if ($result) {
        echo "[OK] Storage::upload() returned: " . htmlspecialchars($result) . "<br>";
    } else {
        echo "[ERROR] Storage::upload() returned false<br>";
    }
    
    unlink($testFile);
} else {
    echo "[ERROR] Failed to create test file<br>";
}

echo "Done.";
?>
