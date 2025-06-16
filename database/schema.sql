-- Database schema for Loove Dating App

CREATE DATABASE IF NOT EXISTS loove_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE loove_db;

-- Users table
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    birth_date DATE NOT NULL,
    gender ENUM('homme', 'femme', 'autre') NOT NULL,
    sexual_orientation ENUM('heterosexuel', 'homosexuel', 'bisexuel', 'autre') NOT NULL,
    bio TEXT,
    location VARCHAR(100),
    occupation VARCHAR(100),
    education VARCHAR(100),
    interests TEXT,
    looking_for ENUM('amitie', 'relation_serieuse', 'rencontre_occasionnelle', 'ne_sait_pas') DEFAULT 'relation_serieuse',
    profile_picture VARCHAR(255),
    status ENUM('pending', 'active', 'suspended', 'deleted') DEFAULT 'pending',
    role ENUM('user', 'admin') DEFAULT 'user',
    premium_until DATETIME NULL,
    verification_token VARCHAR(64),
    email_verified_at DATETIME NULL,
    last_login_at DATETIME NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_location (location),
    INDEX idx_birth_date (birth_date),
    INDEX idx_last_login (last_login_at)
);

-- User photos table
CREATE TABLE user_photos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    photo_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    order_index INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id)
);

-- Matches table (swipe results)
CREATE TABLE matches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user1_id INT NOT NULL,
    user2_id INT NOT NULL,
    user1_action ENUM('like', 'pass', 'super_like') NOT NULL,
    user2_action ENUM('like', 'pass', 'super_like', 'pending') DEFAULT 'pending',
    is_match BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    matched_at DATETIME NULL,
    FOREIGN KEY (user1_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user2_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_match (user1_id, user2_id),
    INDEX idx_user1 (user1_id),
    INDEX idx_user2 (user2_id),
    INDEX idx_is_match (is_match)
);

-- Messages table
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    match_id INT NOT NULL,
    sender_id INT NOT NULL,
    content TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_match_id (match_id),
    INDEX idx_sender_id (sender_id),
    INDEX idx_created_at (created_at)
);

-- Reports table
CREATE TABLE reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reporter_id INT NOT NULL,
    reported_user_id INT NOT NULL,
    reason ENUM('inappropriate_content', 'harassment', 'fake_profile', 'spam', 'other') NOT NULL,
    description TEXT,
    status ENUM('pending', 'reviewed', 'resolved', 'dismissed') DEFAULT 'pending',
    admin_notes TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_reporter (reporter_id),
    INDEX idx_reported (reported_user_id),
    INDEX idx_status (status)
);

-- Blocked users table
CREATE TABLE blocked_users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    blocker_id INT NOT NULL,
    blocked_id INT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blocker_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (blocked_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_block (blocker_id, blocked_id),
    INDEX idx_blocker (blocker_id),
    INDEX idx_blocked (blocked_id)
);

-- Notifications table
CREATE TABLE notifications (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    type ENUM('new_match', 'new_message', 'profile_view', 'like_received', 'super_like_received') NOT NULL,
    title VARCHAR(255) NOT NULL,
    content TEXT,
    related_user_id INT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (related_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user_id (user_id),
    INDEX idx_is_read (is_read),
    INDEX idx_created_at (created_at)
);

-- Premium subscriptions table
CREATE TABLE premium_subscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    plan_type ENUM('monthly', 'yearly') NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    start_date DATETIME NOT NULL,
    end_date DATETIME NOT NULL,
    status ENUM('active', 'expired', 'cancelled') DEFAULT 'active',
    payment_method VARCHAR(50),
    transaction_id VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_user_id (user_id),
    INDEX idx_status (status),
    INDEX idx_end_date (end_date)
);

-- App settings table
CREATE TABLE app_settings (
    id INT PRIMARY KEY AUTO_INCREMENT,
    setting_key VARCHAR(100) NOT NULL UNIQUE,
    setting_value TEXT,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Insert default admin user
INSERT INTO users (first_name, last_name, email, password_hash, birth_date, gender, sexual_orientation, status, role, email_verified_at) 
VALUES ('Admin', 'Loove', 'admin@loove.com', '$2y$10$example_hash_replace_this', '1990-01-01', 'autre', 'autre', 'active', 'admin', NOW());

-- Insert default app settings
INSERT INTO app_settings (setting_key, setting_value) VALUES 
('app_name', 'Loove'),
('maintenance_mode', '0'),
('registration_enabled', '1'),
('premium_monthly_price', '19.99'),
('premium_yearly_price', '199.99');
