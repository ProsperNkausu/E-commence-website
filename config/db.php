<?php
// Database Connection Configuration
try {
    $host = 'localhost';
    $db_name = 'tematech_innovation';
    $username = 'root';
    $password = '';

    // Create PDO connection
    $pdo = new PDO(
        "mysql:host=$host;dbname=$db_name;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection error: " . $e->getMessage());
    die('Database connection failed. Please try again later.');
}
