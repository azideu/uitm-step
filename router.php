<?php
// router.php
// Routing script for PHP's built-in web server to mimic .htaccess rewrites.

$uri = $_SERVER['REQUEST_URI'];
$path = parse_url($uri, PHP_URL_PATH);

// Decode URL-encoded characters (like %20 for spaces)
$path = rawurldecode($path);

// If the path corresponds to a real file or directory (that isn't a directory), serve it as-is
if ($path !== '/' && file_exists(__DIR__ . $path) && !is_dir(__DIR__ . $path)) {
    return false;
}

// Map home/root requests to index.php
if ($path === '/' || $path === '/home') {
    chdir(__DIR__);
    require __DIR__ . '/index.php';
    exit;
}

// Clean URL rewrite: check if a corresponding .php file exists (e.g. /login -> /login.php)
$php_file = __DIR__ . $path . '.php';
if (file_exists($php_file)) {
    chdir(dirname($php_file));
    require $php_file;
    exit;
}

// If the path is a directory, check for an index.php file inside it (e.g. /admin -> /admin/index.php)
if (is_dir(__DIR__ . $path)) {
    $index_file = __DIR__ . rtrim($path, '/') . '/index.php';
    if (file_exists($index_file)) {
        chdir(dirname($index_file));
        require $index_file;
        exit;
    }
}

// Let PHP's built-in web server handle it as a default fallback (shows 404 or serves static content)
return false;
