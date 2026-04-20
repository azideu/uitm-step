<?php
// includes/db.php

/**
 * Database connection for UiTM STEP
 * This script handles both local development and DigitalOcean production environments.
 */

// 1. Default fallback credentials (Local/XAMPP)
$host = 'localhost';
$db   = 'uitm_step';
$user = 'root';
$pass = ''; 
$port = '3306';
$charset = 'utf8mb4';

// 2. Check for DigitalOcean App Platform DATABASE_URL
$db_url = getenv("DATABASE_URL") ?: ($_ENV["DATABASE_URL"] ?? ($_SERVER["DATABASE_URL"] ?? null));

if ($db_url) {
    $url = parse_url($db_url);
    $host = $url["host"] ?? $host;
    $user = $url["user"] ?? $user;
    $pass = $url["pass"] ?? $pass;
    $db   = isset($url["path"]) ? substr($url["path"], 1) : $db;
    $port = $url["port"] ?? 25060;
} 
// 3. Fallback to hardcoded DO credentials if specifically provided (previous developer added these)
else if (str_contains($_SERVER['HTTP_HOST'] ?? '', 'digitalocean.app')) {
    $host = 'uitm-step-mysql-database-do-user-36196259-0.h.db.ondigitalocean.com';
    $db   = 'uitm_step';
    $user = 'doadmin';
    $pass = 'AVNS_c9oKaVFNMjTwVYxnDmF';
    $port = '25060';
}

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// 4. DigitalOcean Managed Databases often require SSL.
// If the connection still fails on DO, ensure you've enabled "SSL" in your App settings.
// PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT is set to false to allow self-signed DO certs without a local CA file.
if (str_contains($host, 'ondigitalocean.com')) {
    $options[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = false;
}

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    // Log error for debugging
    error_log("DB Connection Error: " . $e->getMessage());
    
    // TEMPORARY: Show actual error to debug. REMOVE THIS once resolved for security!
    if (str_contains($_SERVER['HTTP_HOST'] ?? '', 'digitalocean.app')) {
        die("DEBUG: Database connection failed. Error: " . $e->getMessage() . " (Host: $host, User: $user, Port: $port, DB: $db)");
    }

    die("Database connection failed. Please contact the administrator.");
}
?>
