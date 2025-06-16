-- Table structure for users

CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    age INT NULL,
    location VARCHAR(100) NULL,
    occupation VARCHAR(100) NULL,
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

-- Insert test user
INSERT IGNORE INTO users (id, first_name, last_name, email, password_hash, age, location, occupation, bio, interests) 
VALUES (
    1, 
    'Test', 
    'Utilisateur', 
    'test@loove.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password
    28,
    'Paris',
    'Développeur',
    'Passionné de technologie et de voyages, j\'aime découvrir de nouvelles cultures.',
    'Voyages, Technologie, Sport, Cuisine'
);
