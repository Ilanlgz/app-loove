-- Base de données Loove - Application de rencontres
-- Création de la base de données et des tables

DROP DATABASE IF EXISTS loove_db;
CREATE DATABASE loove_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE loove_db;

-- Table des utilisateurs
CREATE TABLE users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    date_of_birth DATE,
    age INT,
    gender ENUM('male', 'female', 'other') NOT NULL,
    phone VARCHAR(20),
    location VARCHAR(255),
    occupation VARCHAR(255),
    bio TEXT,
    interests TEXT,
    height INT,
    relationship_status ENUM('single', 'divorced', 'widowed', 'complicated') DEFAULT 'single',
    profile_picture VARCHAR(255),
    is_premium BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    is_verified BOOLEAN DEFAULT FALSE,
    last_active TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Table des abonnements premium
CREATE TABLE premium_subscriptions (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    plan_type ENUM('basic_premium', 'premium_plus', 'premium_gold') NOT NULL,
    start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_date TIMESTAMP NULL,
    price DECIMAL(10,2) NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des crédits premium
CREATE TABLE premium_credits (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    super_likes INT DEFAULT 0,
    boosts INT DEFAULT 0,
    rewinds INT DEFAULT 0,
    last_reset TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des photos de profil
CREATE TABLE user_photos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    photo_url VARCHAR(255) NOT NULL,
    is_primary BOOLEAN DEFAULT FALSE,
    photo_order INT DEFAULT 1,
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des likes/swipes
CREATE TABLE user_likes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    liker_id INT NOT NULL,
    liked_id INT NOT NULL,
    like_type ENUM('like', 'super_like', 'pass') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (liker_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (liked_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_like (liker_id, liked_id)
);

-- Table des matchs
CREATE TABLE matches (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user1_id INT NOT NULL,
    user2_id INT NOT NULL,
    matched_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE,
    last_message_at TIMESTAMP NULL,
    FOREIGN KEY (user1_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (user2_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_match (user1_id, user2_id)
);

-- Table des conversations
CREATE TABLE conversations (
    id INT PRIMARY KEY AUTO_INCREMENT,
    match_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (match_id) REFERENCES matches(id) ON DELETE CASCADE
);

-- Table des messages
CREATE TABLE messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    conversation_id INT NOT NULL,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    message_text TEXT NOT NULL,
    message_type ENUM('text', 'image', 'gif', 'system') DEFAULT 'text',
    is_read BOOLEAN DEFAULT FALSE,
    read_at TIMESTAMP NULL,
    sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des stories
CREATE TABLE stories (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    story_text TEXT NOT NULL,
    story_image VARCHAR(255),
    expires_at TIMESTAMP NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des vues de stories
CREATE TABLE story_views (
    id INT PRIMARY KEY AUTO_INCREMENT,
    story_id INT NOT NULL,
    viewer_id INT NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (story_id) REFERENCES stories(id) ON DELETE CASCADE,
    FOREIGN KEY (viewer_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_view (story_id, viewer_id)
);

-- Table des préférences utilisateur
CREATE TABLE user_preferences (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    min_age INT DEFAULT 18,
    max_age INT DEFAULT 100,
    max_distance INT DEFAULT 50,
    preferred_gender ENUM('male', 'female', 'both') DEFAULT 'both',
    show_me_on_discover BOOLEAN DEFAULT TRUE,
    notifications_enabled BOOLEAN DEFAULT TRUE,
    email_notifications BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des signalements
CREATE TABLE reports (
    id INT PRIMARY KEY AUTO_INCREMENT,
    reporter_id INT NOT NULL,
    reported_id INT NOT NULL,
    reason ENUM('inappropriate_content', 'harassment', 'fake_profile', 'spam', 'other') NOT NULL,
    description TEXT,
    status ENUM('pending', 'reviewed', 'resolved', 'dismissed') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    reviewed_at TIMESTAMP NULL,
    FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reported_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des blocages
CREATE TABLE user_blocks (
    id INT PRIMARY KEY AUTO_INCREMENT,
    blocker_id INT NOT NULL,
    blocked_id INT NOT NULL,
    blocked_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (blocker_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (blocked_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_block (blocker_id, blocked_id)
);

-- Table des boosts de profil
CREATE TABLE profile_boosts (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    boost_type ENUM('regular', 'super_boost') DEFAULT 'regular',
    started_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expires_at TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Table des vues de profil
CREATE TABLE profile_views (
    id INT PRIMARY KEY AUTO_INCREMENT,
    viewer_id INT NOT NULL,
    viewed_id INT NOT NULL,
    viewed_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    view_date DATE GENERATED ALWAYS AS (DATE(viewed_at)) STORED,
    FOREIGN KEY (viewer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (viewed_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_daily_view (viewer_id, viewed_id, view_date)
);

-- Insertion des données de test
INSERT INTO users (email, password, first_name, last_name, date_of_birth, gender, location, occupation, bio, interests, phone, height, relationship_status) VALUES
('test@loove.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Test', 'Utilisateur', '1995-06-15', 'male', 'Paris', 'Développeur', 'Passionné de technologie et de voyages, j\'aime découvrir de nouvelles cultures.', 'Voyages, Technologie, Sport, Cuisine', '06 12 34 56 78', 175, 'single'),
('emma.martin@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Emma', 'Martin', '1998-03-20', 'female', 'Paris', 'Photographe', 'Passionnée de voyages et de photographie', 'Voyages, Photo, Art', '06 11 22 33 44', 165, 'single'),
('julie.bernard@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Julie', 'Bernard', '1995-08-12', 'female', 'Lyon', 'Biologiste', 'Amoureuse de la nature et des randonnées', 'Nature, Randonnée, Science', '06 22 33 44 55', 168, 'single'),
('sophie.dubois@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sophie', 'Dubois', '1999-11-05', 'female', 'Marseille', 'Danseuse', 'Danseuse et artiste dans l\'âme', 'Danse, Art, Musique', '06 33 44 55 66', 162, 'single'),
('clara.moreau@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Clara', 'Moreau', '1993-01-18', 'female', 'Bordeaux', 'Chef', 'Chef cuisinière, j\'adore les bonnes tables', 'Cuisine, Gastronomie, Voyages', '06 44 55 66 77', 170, 'single'),
('lea.lambert@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Léa', 'Lambert', '1997-04-25', 'female', 'Nice', 'Développeuse', 'Développeuse le jour, musicienne le soir', 'Tech, Musique, Innovation', '06 55 66 77 88', 166, 'single'),
('alice.rousseau@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Alice', 'Rousseau', '1994-09-30', 'female', 'Toulouse', 'Écrivaine', 'Passionnée de littérature et de café', 'Lecture, Écriture, Café', '06 66 77 88 99', 163, 'single'),
('marie.leroy@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Marie', 'Leroy', '1996-12-08', 'female', 'Nantes', 'Architecte', 'Créatrice d\'espaces, amoureuse du design', 'Architecture, Design, Art', '06 77 88 99 00', 169, 'single'),
('camille.roux@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Camille', 'Roux', '2000-07-14', 'female', 'Lille', 'Journaliste', 'Curieuse de tout, toujours en mouvement', 'Journalisme, Voyage, Culture', '06 88 99 00 11', 164, 'single'),
('laura.garcia@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Laura', 'Garcia', '1992-02-22', 'female', 'Strasbourg', 'Médecin', 'Passionnée par mon métier et les autres', 'Médecine, Sport, Humanitaire', '06 99 00 11 22', 167, 'single'),
('sarah.petit@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sarah', 'Petit', '1994-05-10', 'female', 'Montpellier', 'Professeure', 'Transmettre et apprendre chaque jour', 'Éducation, Lecture, Théâtre', '06 00 11 22 33', 161, 'single');

-- Insertion des préférences par défaut pour tous les utilisateurs
INSERT INTO user_preferences (user_id, min_age, max_age, max_distance, preferred_gender) 
SELECT id, 18, 35, 50, 'both' FROM users;

-- Insertion de quelques stories de test
INSERT INTO stories (user_id, story_text, expires_at) VALUES
(2, 'Salut à tous ! Je suis nouvelle ici, hâte de faire des rencontres !', DATE_ADD(NOW(), INTERVAL 24 HOUR)),
(3, 'Amoureuse de la nature, je passe mes weekends à explorer des nouveaux sentiers.', DATE_ADD(NOW(), INTERVAL 24 HOUR)),
(4, 'Danseuse professionnelle, je partage mes répétitions et mes spectacles ici.', DATE_ADD(NOW(), INTERVAL 24 HOUR)),
(5, 'Gastronome dans l\'âme, je teste tous les restaurants de la ville !', DATE_ADD(NOW(), INTERVAL 24 HOUR)),
(6, 'Développeuse le jour, musicienne le soir. La créativité avant tout !', DATE_ADD(NOW(), INTERVAL 24 HOUR)),
(7, 'Écrivaine en herbe, je raconte mes histoires et mes poèmes.', DATE_ADD(NOW(), INTERVAL 24 HOUR)),
(8, 'Architecte passionnée, je partage mes projets et mes inspirations.', DATE_ADD(NOW(), INTERVAL 24 HOUR)),
(9, 'Journaliste curieuse, je couvre des sujets variés et passionnants.', DATE_ADD(NOW(), INTERVAL 24 HOUR)),
(10, 'Médecin dévouée, je parle de santé et de bien-être.', DATE_ADD(NOW(), INTERVAL 24 HOUR)),
(11, 'Professeure engagée, j\'enseigne avec passion et créativité.', DATE_ADD(NOW(), INTERVAL 24 HOUR));

-- Insertion de quelques abonnements premium de test
INSERT INTO premium_subscriptions (user_id, plan_type, price, payment_status) VALUES
(1, 'basic_premium', 19.99, 'completed'),
(2, 'premium_plus', 39.99, 'completed'),
(3, 'premium_gold', 99.99, 'completed');

-- Insertion des crédits premium correspondants
INSERT INTO premium_credits (user_id, super_likes, boosts, rewinds) VALUES
(1, 5, 1, 3),
(2, 10, 3, 5),
(3, 999, 999, 999);

-- Création d'index pour optimiser les performances
CREATE INDEX idx_users_email ON users(email);
CREATE INDEX idx_users_location ON users(location);
CREATE INDEX idx_users_age ON users(age);
CREATE INDEX idx_users_gender ON users(gender);
CREATE INDEX idx_users_last_active ON users(last_active);
CREATE INDEX idx_likes_liker_id ON user_likes(liker_id);
CREATE INDEX idx_likes_liked_id ON user_likes(liked_id);
CREATE INDEX idx_matches_users ON matches(user1_id, user2_id);
CREATE INDEX idx_messages_conversation ON messages(conversation_id);
CREATE INDEX idx_messages_sent_at ON messages(sent_at);
CREATE INDEX idx_stories_user_id ON stories(user_id);
CREATE INDEX idx_stories_expires_at ON stories(expires_at);
CREATE INDEX idx_profile_views_viewer ON profile_views(viewer_id);
CREATE INDEX idx_profile_views_viewed ON profile_views(viewed_id);

-- Vues utiles pour les statistiques
CREATE VIEW user_stats AS
SELECT 
    u.id,
    u.first_name,
    u.last_name,
    u.email,
    COUNT(DISTINCT l.id) as total_likes_given,
    COUNT(DISTINCT lr.id) as total_likes_received,
    COUNT(DISTINCT m.id) as total_matches,
    COUNT(DISTINCT msg.id) as total_messages_sent,
    COUNT(DISTINCT pv.id) as profile_views_received
FROM users u
LEFT JOIN user_likes l ON u.id = l.liker_id AND l.like_type IN ('like', 'super_like')
LEFT JOIN user_likes lr ON u.id = lr.liked_id AND lr.like_type IN ('like', 'super_like')
LEFT JOIN matches m ON u.id = m.user1_id OR u.id = m.user2_id
LEFT JOIN messages msg ON u.id = msg.sender_id
LEFT JOIN profile_views pv ON u.id = pv.viewed_id
GROUP BY u.id;

-- Vue pour les matchs actifs avec dernière activité
CREATE VIEW active_matches AS
SELECT 
    m.id as match_id,
    m.user1_id,
    m.user2_id,
    u1.first_name as user1_name,
    u2.first_name as user2_name,
    m.matched_at,
    m.last_message_at,
    COUNT(msg.id) as message_count,
    MAX(msg.sent_at) as last_message_time
FROM matches m
JOIN users u1 ON m.user1_id = u1.id
JOIN users u2 ON m.user2_id = u2.id
LEFT JOIN conversations c ON m.id = c.match_id
LEFT JOIN messages msg ON c.id = msg.conversation_id
WHERE m.is_active = TRUE
GROUP BY m.id;

-- Procédure stockée pour créer un match
DELIMITER //
CREATE PROCEDURE CreateMatch(IN user1 INT, IN user2 INT)
BEGIN
    DECLARE match_exists INT DEFAULT 0;
    
    -- Vérifier si le match existe déjà
    SELECT COUNT(*) INTO match_exists 
    FROM matches 
    WHERE (user1_id = user1 AND user2_id = user2) 
       OR (user1_id = user2 AND user2_id = user1);
    
    -- Si le match n'existe pas, le créer
    IF match_exists = 0 THEN
        INSERT INTO matches (user1_id, user2_id) 
        VALUES (LEAST(user1, user2), GREATEST(user1, user2));
        
        -- Créer la conversation associée
        INSERT INTO conversations (match_id) 
        VALUES (LAST_INSERT_ID());
    END IF;
END //
DELIMITER ;

-- Procédure stockée pour vérifier les matchs mutuels
DELIMITER //
CREATE PROCEDURE CheckMutualLike(IN liker INT, IN liked INT)
BEGIN
    DECLARE mutual_like_exists INT DEFAULT 0;
    
    -- Vérifier si l'autre personne a aussi liké
    SELECT COUNT(*) INTO mutual_like_exists
    FROM user_likes 
    WHERE liker_id = liked AND liked_id = liker AND like_type IN ('like', 'super_like');
    
    -- Si c'est mutuel, créer le match
    IF mutual_like_exists > 0 THEN
        CALL CreateMatch(liker, liked);
        SELECT TRUE as is_match;
    ELSE
        SELECT FALSE as is_match;
    END IF;
END //
DELIMITER ;

-- Fonction pour calculer la distance entre deux utilisateurs (simplifiée)
DELIMITER //
CREATE FUNCTION CalculateCompatibility(user1 INT, user2 INT) 
RETURNS DECIMAL(3,2)
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE age_diff INT;
    DECLARE location_match BOOLEAN DEFAULT FALSE;
    DECLARE compatibility DECIMAL(3,2) DEFAULT 0.50;
    
    -- Calcul basé sur la différence d'âge
    SELECT ABS(u1.age - u2.age), (u1.location = u2.location)
    INTO age_diff, location_match
    FROM users u1, users u2
    WHERE u1.id = user1 AND u2.id = user2;
    
    -- Bonus si même ville
    IF location_match THEN
        SET compatibility = compatibility + 0.20;
    END IF;
    
    -- Malus si grande différence d'âge
    IF age_diff <= 3 THEN
        SET compatibility = compatibility + 0.20;
    ELSEIF age_diff <= 7 THEN
        SET compatibility = compatibility + 0.10;
    ELSEIF age_diff > 15 THEN
        SET compatibility = compatibility - 0.20;
    END IF;
    
    -- Assurer que la compatibilité reste entre 0 et 1
    IF compatibility > 1.00 THEN SET compatibility = 1.00; END IF;
    IF compatibility < 0.00 THEN SET compatibility = 0.00; END IF;
    
    RETURN compatibility;
END //
DELIMITER ;

-- Trigger pour mettre à jour last_message_at dans les matchs
DELIMITER //
CREATE TRIGGER update_match_last_message
AFTER INSERT ON messages
FOR EACH ROW
BEGIN
    UPDATE matches m
    JOIN conversations c ON m.id = c.match_id
    SET m.last_message_at = NEW.sent_at
    WHERE c.id = NEW.conversation_id;
END //
DELIMITER ;

-- Trigger pour supprimer les stories expirées
DELIMITER //
CREATE EVENT cleanup_expired_stories
ON SCHEDULE EVERY 1 HOUR
DO
BEGIN
    DELETE FROM stories WHERE expires_at < NOW();
END //
DELIMITER ;

-- Activer l'event scheduler
SET GLOBAL event_scheduler = ON;

-- Trigger pour calculer automatiquement l'âge à partir de la date de naissance
DELIMITER //
CREATE TRIGGER calculate_age_insert
BEFORE INSERT ON users
FOR EACH ROW
BEGIN
    IF NEW.date_of_birth IS NOT NULL THEN
        SET NEW.age = YEAR(CURDATE()) - YEAR(NEW.date_of_birth) - 
                     (DATE_FORMAT(CURDATE(), '%m%d') < DATE_FORMAT(NEW.date_of_birth, '%m%d'));
    END IF;
END //

CREATE TRIGGER calculate_age_update
BEFORE UPDATE ON users
FOR EACH ROW
BEGIN
    IF NEW.date_of_birth IS NOT NULL THEN
        SET NEW.age = YEAR(CURDATE()) - YEAR(NEW.date_of_birth) - 
                     (DATE_FORMAT(CURDATE(), '%m%d') < DATE_FORMAT(NEW.date_of_birth, '%m%d'));
    END IF;
END //

-- Trigger pour calculer automatiquement la date de fin d'abonnement
CREATE TRIGGER calculate_subscription_end_date
BEFORE INSERT ON premium_subscriptions
FOR EACH ROW
BEGIN
    IF NEW.end_date IS NULL THEN
        CASE NEW.plan_type
            WHEN 'basic_premium' THEN 
                SET NEW.end_date = DATE_ADD(NEW.start_date, INTERVAL 1 MONTH);
            WHEN 'premium_plus' THEN 
                SET NEW.end_date = DATE_ADD(NEW.start_date, INTERVAL 3 MONTH);
            WHEN 'premium_gold' THEN 
                SET NEW.end_date = DATE_ADD(NEW.start_date, INTERVAL 12 MONTH);
        END CASE;
    END IF;
END //

-- Trigger pour calculer automatiquement la date d'expiration des boosts
CREATE TRIGGER calculate_boost_expiration
BEFORE INSERT ON profile_boosts
FOR EACH ROW
BEGIN
    IF NEW.expires_at IS NULL THEN
        CASE NEW.boost_type
            WHEN 'regular' THEN 
                SET NEW.expires_at = DATE_ADD(NEW.started_at, INTERVAL 30 MINUTE);
            WHEN 'super_boost' THEN 
                SET NEW.expires_at = DATE_ADD(NEW.started_at, INTERVAL 3 HOUR);
        END CASE;
    END IF;
END //

-- Trigger pour éviter les doublons de matchs
CREATE TRIGGER prevent_duplicate_matches
BEFORE INSERT ON matches
FOR EACH ROW
BEGIN
    DECLARE existing_match INT DEFAULT 0;
    
    -- Vérifier si un match existe déjà dans les deux sens
    SELECT COUNT(*) INTO existing_match
    FROM matches 
    WHERE (user1_id = NEW.user1_id AND user2_id = NEW.user2_id)
       OR (user1_id = NEW.user2_id AND user2_id = NEW.user1_id);
    
    -- Si un match existe déjà, annuler l'insertion
    IF existing_match > 0 THEN
        SIGNAL SQLSTATE '45000' 
        SET MESSAGE_TEXT = 'Match already exists between these users';
    END IF;
    
    -- S'assurer que user1_id est toujours <= user2_id pour éviter les doublons
    IF NEW.user1_id > NEW.user2_id THEN
        SET @temp = NEW.user1_id;
        SET NEW.user1_id = NEW.user2_id;
        SET NEW.user2_id = @temp;
    END IF;
END //
DELIMITER ;

-- Insertion de quelques photos de test
INSERT INTO user_photos (user_id, photo_url, is_primary, photo_order) VALUES
(1, 'uploads/profiles/user1_1.jpg', TRUE, 1),
(1, 'uploads/profiles/user1_2.jpg', FALSE, 2),
(2, 'uploads/profiles/user2_1.jpg', TRUE, 1),
(2, 'uploads/profiles/user2_2.jpg', FALSE, 2),
(3, 'uploads/profiles/user3_1.jpg', TRUE, 1),
(4, 'uploads/profiles/user4_1.jpg', TRUE, 1),
(5, 'uploads/profiles/user5_1.jpg', TRUE, 1),
(6, 'uploads/profiles/user6_1.jpg', TRUE, 1),
(7, 'uploads/profiles/user7_1.jpg', TRUE, 1),
(8, 'uploads/profiles/user8_1.jpg', TRUE, 1),
(9, 'uploads/profiles/user9_1.jpg', TRUE, 1),
(10, 'uploads/profiles/user10_1.jpg', TRUE, 1),
(11, 'uploads/profiles/user11_1.jpg', TRUE, 1);

-- Insertion de quelques likes de test pour créer des matchs
INSERT INTO user_likes (liker_id, liked_id, like_type) VALUES
(1, 2, 'like'),
(2, 1, 'like'),
(1, 3, 'super_like'),
(3, 1, 'like'),
(1, 4, 'like'),
(2, 3, 'like'),
(3, 2, 'like'),
(4, 2, 'like'),
(5, 1, 'like'),
(6, 1, 'super_like');

-- Insertion de quelques matchs de test (via les likes mutuels)
INSERT INTO matches (user1_id, user2_id) VALUES
(1, 2),
(1, 3);

-- Insertion de conversations pour les matchs
INSERT INTO conversations (match_id) VALUES
(1),
(2);

-- Insertion de quelques messages de test
INSERT INTO messages (conversation_id, sender_id, receiver_id, message_text) VALUES
(1, 1, 2, 'Salut Emma ! Comment ça va ?'),
(1, 2, 1, 'Salut ! Ça va super bien, merci ! Et toi ?'),
(1, 1, 2, 'Très bien aussi ! J\'ai vu que tu fais de la photo, c\'est passionnant !'),
(1, 2, 1, 'Oui j\'adore ça ! Tu développes quoi comme projets ?'),
(2, 1, 3, 'Coucou Julie ! Sympa ton profil sur la nature !'),
(2, 3, 1, 'Merci ! Tu aimes aussi les randonnées ?'),
(2, 1, 3, 'Oui beaucoup ! On pourrait faire une sortie un de ces jours ?');

-- Insertion de quelques vues de profil
INSERT INTO profile_views (viewer_id, viewed_id) VALUES
(1, 2), (1, 3), (1, 4), (1, 5),
(2, 1), (2, 3), (2, 4),
(3, 1), (3, 2), (3, 5),
(4, 1), (4, 2), (4, 3),
(5, 1), (5, 2);

COMMIT;
