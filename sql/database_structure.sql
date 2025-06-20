-- =======================================================
-- SCRIPT DE CRÉATION DE LA BASE DE DONNÉES LOOVE
-- App de rencontres - Structure complète
-- =======================================================

-- Créer la base de données
CREATE DATABASE IF NOT EXISTS loove_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE loove_db;

-- =======================================================
-- TABLE USERS - Gestion des utilisateurs
-- =======================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    birth_date DATE NULL,
    gender ENUM('Homme', 'Femme', 'Autre') NULL,
    location VARCHAR(100) NULL,
    bio TEXT NULL,
    profile_picture VARCHAR(255) NULL,
    is_premium BOOLEAN DEFAULT FALSE,
    role VARCHAR(20) DEFAULT 'user',
    is_active BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    last_active TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_email (email),
    INDEX idx_location (location),
    INDEX idx_gender (gender),
    INDEX idx_is_active (is_active),
    INDEX idx_last_active (last_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- TABLE MESSAGES - Système de messagerie
-- =======================================================
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    content TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_sender (sender_id),
    INDEX idx_receiver (receiver_id),
    INDEX idx_conversation (sender_id, receiver_id),
    INDEX idx_sent_at (sent_at),
    INDEX idx_is_read (is_read)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- TABLE LIKES - Système de likes/dislikes
-- =======================================================
CREATE TABLE IF NOT EXISTS likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    liker_id INT NOT NULL,
    liked_id INT NOT NULL,
    like_type ENUM('like', 'dislike', 'superlike') DEFAULT 'like',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (liker_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (liked_id) REFERENCES users(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_like (liker_id, liked_id),
    INDEX idx_liker (liker_id),
    INDEX idx_liked (liked_id),
    INDEX idx_like_type (like_type),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- TABLE MATCHES - Gestion des matches
-- =======================================================
CREATE TABLE IF NOT EXISTS matches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user1_id INT NOT NULL,
    user2_id INT NOT NULL,
    matched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    
    FOREIGN KEY (user1_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user2_id) REFERENCES users(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_match (LEAST(user1_id, user2_id), GREATEST(user1_id, user2_id)),
    INDEX idx_user1 (user1_id),
    INDEX idx_user2 (user2_id),
    INDEX idx_matched_at (matched_at),
    INDEX idx_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- TABLE USER_PHOTOS - Photos supplémentaires des profils
-- =======================================================
CREATE TABLE IF NOT EXISTS user_photos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    photo_path VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    upload_order INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_is_primary (is_primary),
    INDEX idx_upload_order (upload_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- TABLE PREMIUM_SUBSCRIPTIONS - Abonnements premium
-- =======================================================
CREATE TABLE IF NOT EXISTS premium_subscriptions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    subscription_type ENUM('monthly', 'yearly') DEFAULT 'monthly',
    start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_date TIMESTAMP NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    payment_status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'EUR',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_user_id (user_id),
    INDEX idx_end_date (end_date),
    INDEX idx_is_active (is_active),
    INDEX idx_payment_status (payment_status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- TABLE REPORTS - Signalements d'utilisateurs
-- =======================================================
CREATE TABLE IF NOT EXISTS reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    reporter_id INT NOT NULL,
    reported_id INT NOT NULL,
    reason ENUM('spam', 'harassment', 'fake_profile', 'inappropriate_content', 'other') NOT NULL,
    description TEXT NULL,
    status ENUM('pending', 'reviewed', 'resolved', 'dismissed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_id) REFERENCES users(id) ON DELETE CASCADE,
    
    INDEX idx_reporter (reporter_id),
    INDEX idx_reported (reported_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- TABLE BLOCKED_USERS - Utilisateurs bloqués
-- =======================================================
CREATE TABLE IF NOT EXISTS blocked_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    blocker_id INT NOT NULL,
    blocked_id INT NOT NULL,
    blocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (blocker_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (blocked_id) REFERENCES users(id) ON DELETE CASCADE,
    
    UNIQUE KEY unique_block (blocker_id, blocked_id),
    INDEX idx_blocker (blocker_id),
    INDEX idx_blocked (blocked_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =======================================================
-- DONNÉES DE TEST POUR LA DÉMONSTRATION
-- =======================================================

-- Insérer un utilisateur admin pour les tests
INSERT IGNORE INTO users (first_name, last_name, email, password, role, is_active, email_verified) VALUES 
('Admin', 'Loove', 'admin@loove.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', TRUE, TRUE);

-- Insérer quelques utilisateurs de test
INSERT IGNORE INTO users (first_name, last_name, email, password, birth_date, gender, location, bio, is_active, email_verified) VALUES 
('Emma', 'Martin', 'emma@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1998-05-15', 'Femme', 'Paris, France', 'Passionnée de voyage et de photographie 📸✈️', TRUE, TRUE),
('Lucas', 'Dubois', 'lucas@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1995-08-22', 'Homme', 'Lyon, France', 'Développeur passionné 💻 Amateur de cuisine et de musique 🎵🍳', TRUE, TRUE),
('Sophie', 'Bernard', 'sophie@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2000-12-03', 'Femme', 'Marseille, France', 'Artiste créative 🎨 Yoga enthusiast 🧘‍♀️', TRUE, TRUE);

-- =======================================================
-- VUES UTILES POUR L'APPLICATION
-- =======================================================

-- Vue pour les statistiques utilisateurs
CREATE OR REPLACE VIEW user_stats AS
SELECT 
    u.id,
    u.first_name,
    u.last_name,
    u.email,
    u.created_at,
    COUNT(DISTINCT l1.id) as likes_sent,
    COUNT(DISTINCT l2.id) as likes_received,
    COUNT(DISTINCT m1.id) as messages_sent,
    COUNT(DISTINCT m2.id) as messages_received,
    COUNT(DISTINCT ma.id) as total_matches
FROM users u
LEFT JOIN likes l1 ON u.id = l1.liker_id AND l1.like_type = 'like'
LEFT JOIN likes l2 ON u.id = l2.liked_id AND l2.like_type = 'like'
LEFT JOIN messages m1 ON u.id = m1.sender_id
LEFT JOIN messages m2 ON u.id = m2.receiver_id
LEFT JOIN matches ma ON u.id = ma.user1_id OR u.id = ma.user2_id
WHERE u.is_active = TRUE
GROUP BY u.id;

-- Vue pour les conversations récentes
CREATE OR REPLACE VIEW recent_conversations AS
SELECT 
    m.*,
    u1.first_name as sender_name,
    u1.profile_picture as sender_picture,
    u2.first_name as receiver_name,
    u2.profile_picture as receiver_picture
FROM messages m
JOIN users u1 ON m.sender_id = u1.id
JOIN users u2 ON m.receiver_id = u2.id
WHERE m.sent_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
ORDER BY m.sent_at DESC;

-- =======================================================
-- PROCÉDURES STOCKÉES UTILES
-- =======================================================

DELIMITER $$

-- Procédure pour créer un match automatique
CREATE PROCEDURE CreateMatch(IN user1 INT, IN user2 INT)
BEGIN
    DECLARE match_exists INT DEFAULT 0;
    
    -- Vérifier si le match existe déjà
    SELECT COUNT(*) INTO match_exists 
    FROM matches 
    WHERE (user1_id = user1 AND user2_id = user2) 
       OR (user1_id = user2 AND user2_id = user1);
    
    -- Créer le match s'il n'existe pas
    IF match_exists = 0 THEN
        INSERT INTO matches (user1_id, user2_id) 
        VALUES (LEAST(user1, user2), GREATEST(user1, user2));
    END IF;
END$$

-- Procédure pour nettoyer les anciens messages non lus
CREATE PROCEDURE CleanOldMessages()
BEGIN
    DELETE FROM messages 
    WHERE sent_at < DATE_SUB(NOW(), INTERVAL 6 MONTH) 
    AND is_read = FALSE;
END$$

DELIMITER ;

-- =======================================================
-- TRIGGERS POUR AUTOMATISER CERTAINES ACTIONS
-- =======================================================

DELIMITER $$

-- Trigger pour créer automatiquement un match quand deux utilisateurs se likent mutuellement
CREATE TRIGGER after_like_insert 
AFTER INSERT ON likes
FOR EACH ROW
BEGIN
    DECLARE mutual_like_exists INT DEFAULT 0;
    
    -- Vérifier si il y a un like mutuel
    IF NEW.like_type = 'like' THEN
        SELECT COUNT(*) INTO mutual_like_exists
        FROM likes 
        WHERE liker_id = NEW.liked_id 
        AND liked_id = NEW.liker_id 
        AND like_type = 'like';
        
        -- Si like mutuel, créer un match
        IF mutual_like_exists > 0 THEN
            CALL CreateMatch(NEW.liker_id, NEW.liked_id);
        END IF;
    END IF;
END$$

-- Trigger pour mettre à jour last_active automatiquement
CREATE TRIGGER update_last_active
BEFORE UPDATE ON users
FOR EACH ROW
BEGIN
    SET NEW.last_active = NOW();
END$$

DELIMITER ;

-- =======================================================
-- INDEX SUPPLÉMENTAIRES POUR LES PERFORMANCES
-- =======================================================

-- Index composés pour optimiser les requêtes fréquentes
CREATE INDEX idx_messages_conversation_time ON messages(sender_id, receiver_id, sent_at);
CREATE INDEX idx_likes_user_type ON likes(liker_id, like_type, created_at);
CREATE INDEX idx_users_active_location ON users(is_active, location);
CREATE INDEX idx_matches_user_active ON matches(user1_id, user2_id, is_active);

-- =======================================================
-- COMMENTAIRES ET DOCUMENTATION
-- =======================================================

-- Ajout de commentaires sur les tables principales
ALTER TABLE users COMMENT = 'Table principale des utilisateurs de l\'application Loove';
ALTER TABLE messages COMMENT = 'Système de messagerie entre utilisateurs matchés';
ALTER TABLE likes COMMENT = 'Système de likes/dislikes pour le matching';
ALTER TABLE matches COMMENT = 'Table des matches confirmés entre utilisateurs';

COMMIT;

-- =======================================================
-- FIN DU SCRIPT - BASE DE DONNÉES LOOVE CRÉÉE
-- =======================================================