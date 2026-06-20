<?php
// includes/db.php

/**
 * Database connection for UiTM STEP
 * This script handles both local development and DigitalOcean production environments.
 */

// Load local .env if it exists
$env_path = __DIR__ . '/../.env';
if (file_exists($env_path)) {
    $lines = file($env_path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) {
            continue;
        }
        if (strpos($line, '=') !== false) {
            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            $value = trim($value, '"\''); // strip surrounding quotes
            
            putenv("{$name}={$value}");
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// 1. Default fallback credentials (Local/XAMPP)
$host = getenv('DB_HOST') ?: '127.0.0.1';
$db   = getenv('DB_DATABASE') ?: 'uitm_step';
$user = getenv('DB_USERNAME') ?: 'root';
$pass = getenv('DB_PASSWORD') ?: ''; 
$port = getenv('DB_PORT') ?: '3307';
$charset = 'utf8mb4';

// 2. Check for DigitalOcean App Platform DATABASE_URL
$db_url = getenv("DATABASE_URL") ?: ($_ENV["DATABASE_URL"] ?? ($_SERVER["DATABASE_URL"] ?? null));

// Only parse if it looks like a valid connection string (starts with mysql://)
if ($db_url && str_starts_with($db_url, 'mysql://')) {
    $url = parse_url($db_url);
    $host = $url["host"] ?? $host;
    $user = $url["user"] ?? $user;
    $pass = $url["pass"] ?? $pass;
    $db   = isset($url["path"]) ? substr($url["path"], 1) : $db;
    $port = $url["port"] ?? 25060;
} 
// 3. Fallback to local if no DATABASE_URL is present
// (Local credentials at the top of file will be used)

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
    
    die("Database connection failed. Please contact the administrator.");
}
?>
