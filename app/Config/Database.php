<?php
namespace App\Config;

use PDO;
use PDOException;

class Database {
    private static $instance = null;

    public static function getConnection() {
        if (self::$instance === null) {
            $configValue = static function ($key, $default) {
                if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];
                if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
                if (function_exists('getenv')) {
                    $value = getenv($key);
                    if ($value !== false && $value !== '') return $value;
                }
                return $default;
            };

            $host = $configValue('DB_HOST', '127.0.0.1');
            $port = $configValue('DB_PORT', '3306');
            $db   = $configValue('DB_NAME', 'vitalynx');
            $user = $configValue('DB_USER', 'root');
            $pass = $configValue('DB_PASS', '');
            $charset = 'utf8mb4';

            $dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            try {
                self::$instance = new PDO($dsn, $user, $pass, $options);
            } catch (PDOException $e) {
                error_log('VITALYNX database connection failed: ' . $e->getCode());
                throw new \RuntimeException('Database connection unavailable.', 0, $e);
            }
        }

        return self::$instance;
    }
}
