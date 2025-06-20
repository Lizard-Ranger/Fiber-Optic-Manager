<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'fiberuser');
define('DB_PASS', 'fiberpass123');
define('DB_NAME', 'webapp_db');

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Create PDO instance
    $conn = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        )
    );
    
    // Set charset
    $conn->exec("SET NAMES utf8");
} catch(PDOException $e) {
    // Log the error
    error_log("Connection failed: " . $e->getMessage());
    die("Connection failed: " . $e->getMessage());
}
?>
