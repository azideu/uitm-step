<?php
try {
    $pdo = new PDO('mysql:host=127.0.0.1;port=3307;dbname=uitm_step;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $result = $pdo->query('SHOW TABLES');
    echo 'Tables in uitm_step database:' . PHP_EOL;
    while ($row = $result->fetch(PDO::FETCH_NUM)) {
        echo '- ' . $row[0] . PHP_EOL;
    }

    // Check if reviews table exists
    $result = $pdo->query("SHOW TABLES LIKE 'reviews'");
    if ($result->rowCount() > 0) {
        echo PHP_EOL . 'Reviews table exists!' . PHP_EOL;
    } else {
        echo PHP_EOL . 'Reviews table does NOT exist!' . PHP_EOL;
    }
} catch (Exception $e) {
    echo 'Error: ' . $e->getMessage() . PHP_EOL;
}
?>