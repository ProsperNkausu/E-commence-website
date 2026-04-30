<?php
require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

// Rename the function to avoid conflict with Laravel
if (!function_exists('get_env')) {
    function get_env($key, $default = null)
    {
        return $_ENV[$key]
            ?? $_SERVER[$key]
            ?? getenv($key)
            ?? $default;
    }
}
