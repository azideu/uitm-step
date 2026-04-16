<?php
// includes/db.php

$host = '127.0.0.1';
$db   = 'uitm_step';
$user = 'root'; // Update with actual DB user
$pass = '';     // Update with actual DB password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Throw exceptions on error
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,       // Fetch associative arrays
    PDO::ATTR_EMULATE_PREPARES   => false,                  // Use true prepared statements
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // In production, do not expose $e->getMessage() directly to the user
    error_log($e->getMessage());
    die("Database connection failed. Please contact the administrator.");
}
?>
