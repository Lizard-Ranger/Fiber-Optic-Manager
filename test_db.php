<?php
require_once 'config/database.php';

try {
    // Test database connection
    echo "Testing database connection...<br>";
    if ($conn) {
        echo "Database connection successful!<br><br>";
    }

    // Check if users table exists
    echo "Checking users table...<br>";
    $stmt = $conn->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "Users table exists!<br><br>";
        
        // Check table structure
        echo "Table structure:<br>";
        $stmt = $conn->query("DESCRIBE users");
        echo "<pre>";
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            print_r($row);
        }
        echo "</pre>";
        
        // Check if there are any users
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "Number of users in database: " . $result['count'] . "<br>";
    } else {
        echo "Users table does not exist!<br>";
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 