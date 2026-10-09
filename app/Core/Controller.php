<?php
namespace App\Core;

class Controller {
    public function __construct() {
        header("X-Frame-Options: DENY");
        header("X-Content-Type-Options: nosniff");
        header("X-XSS-Protection: 1; mode=block");
        header("Referrer-Policy: strict-origin-when-cross-origin");
    }

    protected function view($view, $data = []) {
        extract($data);
        require_once __DIR__ . '/../Views/layouts/main.php';
    }
    
    protected function redirect($url) {
        header("Location: " . $url);
        exit();
    }
}
