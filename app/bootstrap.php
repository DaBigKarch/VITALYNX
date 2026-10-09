<?php
// Simple autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/';
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }
    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';
    if (file_exists($file)) {
        require $file;
    }
});

// The deployment package keeps .env in the project root, alongside index.php.
App\Config\App::loadEnv(dirname(__DIR__) . '/.env');

// Init Router
$router = new App\Core\Router();
require_once __DIR__ . '/routes.php';
$router->dispatch();
