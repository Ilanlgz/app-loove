-- =======================================================
-- PROCÉDURES STOCKÉES ET FONCTIONS UTILES
-- Application Loove - Fonctionnalités avancées
-- =======================================================

USE loove_db;

DELIMITER $$

-- =======================================================
-- FONCTION POUR CALCULER L'ÂGE
-- =======================================================
CREATE FUNCTION CalculateAge(birth_date DATE)
RETURNS INT
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE age INT;
    SET age = TIMESTAMPDIFF(YEAR, birth_date, CURDATE());
    RETURN age;
END$$

-- =======================================================
-- FONCTION POUR CALCULER LA DISTANCE ENTRE DEUX VILLES (SIMPLIFIÉE)
-- =======================================================
CREATE FUNCTION CalculateDistance(location1 VARCHAR(100), location2 VARCHAR(100))
RETURNS INT
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE distance INT DEFAULT 0;
    
    -- Distance simplifiée basée sur les villes principales françaises
    -- En production, on utiliserait une vraie API de géolocalisation
    
    IF location1 = location2 THEN
        SET distance = 0;
    ELSEIF (location1 LIKE '%Paris%' AND location2 LIKE '%Lyon%') OR 
           (location1 LIKE '%Lyon%' AND location2 LIKE '%Paris%') THEN
        SET distance = 465;
    ELSEIF (location1 LIKE '%Paris%' AND location2 LIKE '%Marseille%') OR 
           (location1 LIKE '%Marseille%' AND location2 LIKE '%Paris%') THEN
        SET distance = 775;
    ELSEIF (location1 LIKE '%Lyon%' AND location2 LIKE '%Marseille%') OR 
           (location1 LIKE '%Marseille%' AND location2 LIKE '%Lyon%') THEN
        SET distance = 314;
    ELSE
        SET distance = FLOOR(RAND() * 500 + 50); -- Distance aléatoire entre 50-550km
    END IF;
    
    RETURN distance;
END$$

-- =======================================================
-- PROCÉDURE POUR CRÉER UN MATCH
-- =======================================================
CREATE PROCEDURE CreateMatch(IN user1 INT, IN user2 INT)
BEGIN
    DECLARE match_exists INT DEFAULT 0;
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Vérifier si le match existe déjà
    SELECT COUNT(*) INTO match_exists 
    FROM matches 
    WHERE (user1_id = user1 AND user2_id = user2) 
       OR (user1_id = user2 AND user2_id = user1);
    
    -- Créer le match s'il n'existe pas
    IF match_exists = 0 THEN
        INSERT INTO matches (user1_id, user2_id, matched_at) 
        VALUES (LEAST(user1, user2), GREATEST(user1, user2), NOW());
        
        -- Log de l'événement (optionnel)
        INSERT INTO match_events (user1_id, user2_id, event_type, created_at)
        VALUES (user1, user2, 'MATCH_CREATED', NOW());
    END IF;
    
    COMMIT;
END$$

-- =======================================================
-- PROCÉDURE POUR OBTENIR LES UTILISATEURS RECOMMANDÉS
-- =======================================================
CREATE PROCEDURE GetRecommendedUsers(IN current_user_id INT, IN user_limit INT)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    SELECT 
        u.id,
        u.first_name,
        u.last_name,
        u.bio,
        u.location,
        u.profile_picture,
        u.last_active,
        u.is_premium,
        CalculateAge(u.birth_date) as age,
        CalculateDistance(
            (SELECT location FROM users WHERE id = current_user_id),
            u.location
        ) as distance_km
    FROM users u
    WHERE u.id != current_user_id
    AND u.is_active = TRUE
    AND u.id NOT IN (
        -- Exclure les utilisateurs déjà likés/dislikés
        SELECT liked_id FROM likes WHERE liker_id = current_user_id
    )
    AND u.id NOT IN (
        -- Exclure les utilisateurs bloqués
        SELECT blocked_id FROM blocked_users WHERE blocker_id = current_user_id
    )
    ORDER BY 
        u.is_premium DESC, -- Utilisateurs premium en premier
        u.last_active DESC, -- Utilisateurs actifs récemment
        RAND() -- Ordre aléatoire pour la découverte
    LIMIT user_limit;
END$$

-- =======================================================
-- PROCÉDURE POUR OBTENIR LES CONVERSATIONS D'UN UTILISATEUR
-- =======================================================
CREATE PROCEDURE GetUserConversations(IN user_id INT)
BEGIN
    SELECT DISTINCT
        CASE 
            WHEN m.sender_id = user_id THEN m.receiver_id 
            ELSE m.sender_id 
        END as other_user_id,
        u.first_name,
        u.last_name,
        u.profile_picture,
        u.last_active,
        (SELECT content FROM messages m2 
         WHERE (m2.sender_id = user_id AND m2.receiver_id = other_user_id)
            OR (m2.sender_id = other_user_id AND m2.receiver_id = user_id)
         ORDER BY m2.sent_at DESC LIMIT 1) as last_message,
        (SELECT sent_at FROM messages m2 
         WHERE (m2.sender_id = user_id AND m2.receiver_id = other_user_id)
            OR (m2.sender_id = other_user_id AND m2.receiver_id = user_id)
         ORDER BY m2.sent_at DESC LIMIT 1) as last_message_time,
        (SELECT COUNT(*) FROM messages m2 
         WHERE m2.sender_id = other_user_id 
         AND m2.receiver_id = user_id 
         AND m2.is_read = FALSE) as unread_count
    FROM messages m
    JOIN users u ON (
        CASE 
            WHEN m.sender_id = user_id THEN m.receiver_id 
            ELSE m.sender_id 
        END = u.id
    )
    WHERE m.sender_id = user_id OR m.receiver_id = user_id
    ORDER BY last_message_time DESC;
END$$

-- =======================================================
-- PROCÉDURE POUR MARQUER LES MESSAGES COMME LUS
-- =======================================================
CREATE PROCEDURE MarkMessagesAsRead(IN sender_id INT, IN receiver_id INT)
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    UPDATE messages 
    SET is_read = TRUE 
    WHERE sender_id = sender_id 
    AND receiver_id = receiver_id 
    AND is_read = FALSE;
    
    COMMIT;
END$$

-- =======================================================
-- PROCÉDURE POUR NETTOYER LES ANCIENNES DONNÉES
-- =======================================================
CREATE PROCEDURE CleanupOldData()
BEGIN
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        RESIGNAL;
    END;
    
    START TRANSACTION;
    
    -- Supprimer les messages non lus de plus de 6 mois
    DELETE FROM messages 
    WHERE sent_at < DATE_SUB(NOW(), INTERVAL 6 MONTH) 
    AND is_read = FALSE;
    
    -- Supprimer les likes de plus d'1 an qui n'ont pas abouti à un match
    DELETE l FROM likes l
    LEFT JOIN matches m ON (
        (l.liker_id = m.user1_id AND l.liked_id = m.user2_id) OR
        (l.liker_id = m.user2_id AND l.liked_id = m.user1_id)
    )
    WHERE l.created_at < DATE_SUB(NOW(), INTERVAL 1 YEAR)
    AND m.id IS NULL
    AND l.like_type = 'dislike';
    
    -- Désactiver les abonnements premium expirés
    UPDATE premium_subscriptions 
    SET is_active = FALSE 
    WHERE end_date < NOW() 
    AND is_active = TRUE;
    
    -- Mettre à jour le statut premium des utilisateurs
    UPDATE users u
    LEFT JOIN premium_subscriptions ps ON u.id = ps.user_id AND ps.is_active = TRUE
    SET u.is_premium = CASE WHEN ps.id IS NOT NULL THEN TRUE ELSE FALSE END;
    
    COMMIT;
END$$

-- =======================================================
-- PROCÉDURE POUR OBTENIR LES STATISTIQUES D'UN UTILISATEUR
-- =======================================================
CREATE PROCEDURE GetUserStats(IN user_id INT)
BEGIN
    SELECT 
        u.id,
        u.first_name,
        u.last_name,
        u.created_at,
        u.last_active,
        u.is_premium,
        COUNT(DISTINCT l1.id) as likes_sent,
        COUNT(DISTINCT l2.id) as likes_received,
        COUNT(DISTINCT CASE WHEN l1.like_type = 'superlike' THEN l1.id END) as superlikes_sent,
        COUNT(DISTINCT CASE WHEN l2.like_type = 'superlike' THEN l2.id END) as superlikes_received,
        COUNT(DISTINCT m1.id) as messages_sent,
        COUNT(DISTINCT m2.id) as messages_received,
        COUNT(DISTINCT ma.id) as total_matches,
        COUNT(DISTINCT up.id) as photos_uploaded
    FROM users u
    LEFT JOIN likes l1 ON u.id = l1.liker_id
    LEFT JOIN likes l2 ON u.id = l2.liked_id
    LEFT JOIN messages m1 ON u.id = m1.sender_id
    LEFT JOIN messages m2 ON u.id = m2.receiver_id
    LEFT JOIN matches ma ON u.id = ma.user1_id OR u.id = ma.user2_id
    LEFT JOIN user_photos up ON u.id = up.user_id
    WHERE u.id = user_id
    GROUP BY u.id;
END$$

-- =======================================================
-- PROCÉDURE POUR OBTENIR LES MATCHES D'UN UTILISATEUR
-- =======================================================
CREATE PROCEDURE GetUserMatches(IN user_id INT)
BEGIN
    SELECT 
        m.id as match_id,
        m.matched_at,
        CASE 
            WHEN m.user1_id = user_id THEN m.user2_id 
            ELSE m.user1_id 
        END as matched_user_id,
        u.first_name,
        u.last_name,
        u.profile_picture,
        u.bio,
        u.last_active,
        u.location,
        CalculateAge(u.birth_date) as age,
        -- Vérifier s'il y a eu des messages échangés
        CASE WHEN msg.id IS NOT NULL THEN TRUE ELSE FALSE END as has_messages
    FROM matches m
    JOIN users u ON (
        CASE 
            WHEN m.user1_id = user_id THEN m.user2_id 
            ELSE m.user1_id 
        END = u.id
    )
    LEFT JOIN messages msg ON (
        (msg.sender_id = user_id AND msg.receiver_id = u.id) OR
        (msg.sender_id = u.id AND msg.receiver_id = user_id)
    )
    WHERE (m.user1_id = user_id OR m.user2_id = user_id)
    AND m.is_active = TRUE
    GROUP BY m.id, u.id
    ORDER BY m.matched_at DESC;
END$$

-- =======================================================
-- FONCTION POUR VÉRIFIER SI DEUX UTILISATEURS SONT MATCHÉS
-- =======================================================
CREATE FUNCTION AreUsersMatched(user1_id INT, user2_id INT)
RETURNS BOOLEAN
READS SQL DATA
DETERMINISTIC
BEGIN
    DECLARE match_count INT DEFAULT 0;
    
    SELECT COUNT(*) INTO match_count
    FROM matches 
    WHERE ((user1_id = user1_id AND user2_id = user2_id) OR 
           (user1_id = user2_id AND user2_id = user1_id))
    AND is_active = TRUE;
    
    RETURN match_count > 0;
END$$

DELIMITER ;

-- =======================================================
-- CRÉATION D'ÉVÉNEMENTS AUTOMATIQUES (OPTIONNEL)
-- =======================================================

-- Événement pour nettoyer automatiquement les anciennes données
-- CREATE EVENT IF NOT EXISTS cleanup_old_data
-- ON SCHEDULE EVERY 1 WEEK
-- DO CALL CleanupOldData();

-- =======================================================
-- VUES UTILES POUR L'APPLICATION
-- =======================================================

-- Vue des utilisateurs actifs avec statistiques
CREATE OR REPLACE VIEW active_users_stats AS
SELECT 
    u.id,
    u.first_name,
    u.last_name,
    u.email,
    u.location,
    u.is_premium,
    u.last_active,
    CalculateAge(u.birth_date) as age,
    COUNT(DISTINCT l1.id) as likes_sent,
    COUNT(DISTINCT l2.id) as likes_received,
    COUNT(DISTINCT m.id) as total_matches
FROM users u
LEFT JOIN likes l1 ON u.id = l1.liker_id
LEFT JOIN likes l2 ON u.id = l2.liked_id
LEFT JOIN matches m ON u.id = m.user1_id OR u.id = m.user2_id
WHERE u.is_active = TRUE
AND u.role != 'admin'
GROUP BY u.id;

-- Vue des conversations avec le dernier message
CREATE OR REPLACE VIEW latest_conversations AS
SELECT 
    m.sender_id,
    m.receiver_id,
    m.content as last_message,
    m.sent_at as last_message_time,
    u1.first_name as sender_name,
    u2.first_name as receiver_name,
    ROW_NUMBER() OVER (
        PARTITION BY LEAST(m.sender_id, m.receiver_id), GREATEST(m.sender_id, m.receiver_id) 
        ORDER BY m.sent_at DESC
    ) as rn
FROM messages m
JOIN users u1 ON m.sender_id = u1.id
JOIN users u2 ON m.receiver_id = u2.id
WHERE u1.is_active = TRUE AND u2.is_active = TRUE;

-- Vue finale avec seulement les derniers messages
CREATE OR REPLACE VIEW conversations_summary AS
SELECT 
    sender_id,
    receiver_id,
    last_message,
    last_message_time,
    sender_name,
    receiver_name
FROM latest_conversations
WHERE rn = 1;

COMMIT;

-- =======================================================
-- FIN DU SCRIPT - PROCÉDURES ET FONCTIONS CRÉÉES
-- =======================================================