-- Create the users table with all profile fields

CREATE DATABASE IF NOT EXISTS loove_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE loove_db;

CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    age INT NULL,
    phone VARCHAR(20) NULL,
    location VARCHAR(100) NULL,
    height INT NULL,
    occupation VARCHAR(100) NULL,
    relationship_status VARCHAR(50) NULL,
    bio TEXT NULL,
    interests TEXT NULL,
    profile_picture VARCHAR(255) NULL,
    status ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_location (location)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert test user with complete profile
INSERT INTO users (
    id, first_name, last_name, email, password_hash, 
    age, phone, location, height, occupation, relationship_status,
    bio, interests
) VALUES (
    1, 
    'Test', 
    'Utilisateur', 
    'test@loove.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password
    28,
    '06 12 34 56 78',
    'Paris',
    175,
    'Développeur',
    'Célibataire',
    'Passionné de technologie et de voyages, j\'aime découvrir de nouvelles cultures.',
    'Voyages, Technologie, Sport, Cuisine'
) ON DUPLICATE KEY UPDATE
    first_name = VALUES(first_name),
    last_name = VALUES(last_name),
    age = VALUES(age),
    phone = VALUES(phone),
    location = VALUES(location),
    height = VALUES(height),
    occupation = VALUES(occupation),
    relationship_status = VALUES(relationship_status),
    bio = VALUES(bio),
    interests = VALUES(interests);
