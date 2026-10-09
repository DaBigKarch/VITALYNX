<?php
namespace App\Config;

class App {
    public static function loadEnv($path) {
        if (!is_file($path) || !is_readable($path)) return;
        $lines = @file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if ($lines === false) return;

        foreach ($lines as $line) {
            $line = trim($line);
            if ($line === '' || strpos($line, '#') === 0 || strpos($line, '=') === false) continue;

            list($name, $value) = explode('=', $line, 2);
            $name = trim($name);
            $value = trim($value);
            if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*$/', $name)) continue;

            // Keep compatibility for code using getenv(), but do not depend on it:
            // some shared hosts disable putenv().
            if (function_exists('putenv')) {
                @putenv($name . '=' . $value);
            }
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}
