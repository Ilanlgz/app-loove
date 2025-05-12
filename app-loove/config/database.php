<?php
// Database configuration settings
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Par défaut pour XAMPP
define('DB_PASS', ''); // Par défaut pour XAMPP
define('DB_NAME', 'app_loove');

// Create a connection to the database
function getDatabaseConnection() {
    $connection = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Check for connection errors
    if ($connection->connect_error) {
        die("Connection failed: " . $connection->connect_error);
    }
    
    // Set charset to ensure proper handling of special characters
    $connection->set_charset("utf8mb4");

    return $connection;
}

// Initialize database if it doesn't exist yet
function initializeDatabase() {
    // First, connect without selecting database
    $connection = new mysqli(DB_HOST, DB_USER, DB_PASS);
    
    // Create database if it doesn't exist
    $sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME . " CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $connection->query($sql);
    
    // Select the database
    $connection->select_db(DB_NAME);
    
    // Create users table
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        profile_picture VARCHAR(255) DEFAULT NULL,
        bio TEXT DEFAULT NULL,
        gender ENUM('male', 'female', 'other') DEFAULT NULL,
        birthdate DATE DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    
    $connection->query($sql);
    
    // Create conversations table
    $sql = "CREATE TABLE IF NOT EXISTS conversations (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $connection->query($sql);
    
    // Create conversation_participants table
    $sql = "CREATE TABLE IF NOT EXISTS conversation_participants (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        conversation_id INT(11) UNSIGNED NOT NULL,
        user_id INT(11) UNSIGNED NOT NULL,
        FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_participant (conversation_id, user_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $connection->query($sql);
    
    // Create messages table
    $sql = "CREATE TABLE IF NOT EXISTS messages (
        id INT(11) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        conversation_id INT(11) UNSIGNED NOT NULL,
        sender_id INT(11) UNSIGNED NOT NULL,
        content TEXT NOT NULL,
        is_read TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
        FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
    $connection->query($sql);
    
    $connection->close();
}

// Run initialization
initializeDatabase();
?>