<?php
// includes/db.php using DigitalOcean Managed MySQL credentials

$host = 'uitm-step-mysql-database-do-user-36196259-0.h.db.ondigitalocean.com';
$db   = 'defaultdb';
$user = 'doadmin';
$pass = 'AVNS_c9oKaVFNMjTwVYxnDmF';
$port = '25060';
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    error_log($e->getMessage());
    die("Database connection failed. Please contact the administrator.");
}

// Quick snippet for includes/db.php to parse DigitalOcean's DATABASE_URL
$url = parse_url(getenv("DATABASE_URL"));

$host = $url["host"];
$user = $url["user"];
$pass = $url["pass"];
$db   = substr($url["path"], 1);
$port = $url["port"] ?? 25060; // Managed MySQL port

$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4";
?>
