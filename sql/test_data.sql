-- =======================================================
-- DONNÉES DE TEST POUR L'APPLICATION LOOVE
-- Jeu de données complet pour la démonstration
-- =======================================================

USE loove_db;

-- =======================================================
-- UTILISATEURS DE TEST
-- =======================================================

-- Mot de passe par défaut pour tous: "password123"
-- Hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi

INSERT IGNORE INTO users (id, first_name, last_name, email, password, birth_date, gender, location, bio, is_premium, is_active, email_verified, last_active) VALUES 
(1, 'Admin', 'Loove', 'admin@loove.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1990-01-01', 'Autre', 'Paris, France', 'Administrateur de l\'application Loove', TRUE, TRUE, TRUE, NOW()),

(2, 'Emma', 'Martin', 'emma@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1998-05-15', 'Femme', 'Paris, France', 'Passionnée de voyage et de photographie 📸✈️ J\'adore découvrir de nouveaux endroits et capturer des moments magiques!', FALSE, TRUE, TRUE, DATE_SUB(NOW(), INTERVAL 2 MINUTE)),

(3, 'Lucas', 'Dubois', 'lucas@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1995-08-22', 'Homme', 'Lyon, France', 'Développeur passionné 💻 Amateur de cuisine et de musique 🎵🍳 Toujours prêt pour une nouvelle aventure!', TRUE, TRUE, TRUE, DATE_SUB(NOW(), INTERVAL 5 MINUTE)),

(4, 'Sophie', 'Bernard', 'sophie@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '2000-12-03', 'Femme', 'Marseille, France', 'Artiste créative 🎨 Yoga enthusiast 🧘‍♀️ À la recherche d\'une âme sœur pour partager la beauté de la vie', FALSE, TRUE, TRUE, DATE_SUB(NOW(), INTERVAL 10 MINUTE)),

(5, 'Thomas', 'Leroy', 'thomas@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1992-03-18', 'Homme', 'Toulouse, France', 'Ingénieur sportif 🏃‍♂️ Fan de randonnée et d\'escalade 🏔️ Cherche quelqu\'un pour partager mes passions!', FALSE, TRUE, TRUE, DATE_SUB(NOW(), INTERVAL 15 MINUTE)),

(6, 'Camille', 'Moreau', 'camille@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1997-09-12', 'Femme', 'Nice, France', 'Architecte passionnée 🏗️ Amoureuse des couchers de soleil et des bons vins 🍷 Vie la vita bella!', TRUE, TRUE, TRUE, DATE_SUB(NOW(), INTERVAL 1 HOUR)),

(7, 'Maxime', 'Roux', 'maxime@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1994-11-27', 'Homme', 'Bordeaux, France', 'Chef cuisinier 👨‍🍳 Passionné de gastronomie et de voyages culinaires 🌍 Let\'s cook together!', FALSE, TRUE, TRUE, DATE_SUB(NOW(), INTERVAL 30 MINUTE)),

(8, 'Léa', 'Petit', 'lea@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1999-07-08', 'Femme', 'Lille, France', 'Étudiante en médecine 👩‍⚕️ Sportive et aventurière 🏊‍♀️ À la recherche de quelqu\'un d\'authentique', FALSE, TRUE, TRUE, DATE_SUB(NOW(), INTERVAL 45 MINUTE)),

(9, 'Antoine', 'Garcia', 'antoine@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1996-02-14', 'Homme', 'Strasbourg, France', 'Photographe indépendant 📷 Amoureux de l\'art et de la nature 🌿 Romantic soul looking for love', FALSE, TRUE, TRUE, DATE_SUB(NOW(), INTERVAL 2 HOUR)),

(10, 'Clara', 'Lefebvre', 'clara@test.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '1993-10-25', 'Femme', 'Nantes, France', 'Designer graphique ✨ Passionnée de mode et de créativité 👗 Looking for my creative partner in crime!', TRUE, TRUE, TRUE, DATE_SUB(NOW(), INTERVAL 20 MINUTE));

-- =======================================================
-- LIKES ET INTERACTIONS
-- =======================================================

INSERT IGNORE INTO likes (liker_id, liked_id, like_type, created_at) VALUES 
-- Emma (2) likes several people
(2, 3, 'like', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(2, 5, 'like', DATE_SUB(NOW(), INTERVAL 2 DAYS)),
(2, 7, 'superlike', DATE_SUB(NOW(), INTERVAL 3 DAYS)),

-- Lucas (3) likes back Emma (creates match)
(3, 2, 'like', DATE_SUB(NOW(), INTERVAL 23 HOURS)),
(3, 4, 'like', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3, 6, 'like', DATE_SUB(NOW(), INTERVAL 2 DAYS)),

-- Sophie (4) interactions
(4, 3, 'like', DATE_SUB(NOW(), INTERVAL 22 HOURS)),
(4, 5, 'like', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4, 9, 'superlike', DATE_SUB(NOW(), INTERVAL 2 DAYS)),

-- Thomas (5) interactions
(5, 2, 'like', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(5, 4, 'like', DATE_SUB(NOW(), INTERVAL 23 HOURS)),
(5, 8, 'like', DATE_SUB(NOW(), INTERVAL 2 DAYS)),

-- Plus d'interactions pour créer des matches
(6, 3, 'like', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(7, 2, 'like', DATE_SUB(NOW(), INTERVAL 2 DAYS)),
(8, 5, 'like', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(9, 4, 'like', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(10, 3, 'like', DATE_SUB(NOW(), INTERVAL 3 DAYS));

-- =======================================================
-- MATCHES AUTOMATIQUES (créés par les likes mutuels)
-- =======================================================

INSERT IGNORE INTO matches (user1_id, user2_id, matched_at) VALUES 
(2, 3, DATE_SUB(NOW(), INTERVAL 23 HOURS)),  -- Emma & Lucas
(3, 4, DATE_SUB(NOW(), INTERVAL 22 HOURS)),  -- Lucas & Sophie  
(4, 5, DATE_SUB(NOW(), INTERVAL 23 HOURS)),  -- Sophie & Thomas
(2, 5, DATE_SUB(NOW(), INTERVAL 1 DAY)),     -- Emma & Thomas
(5, 8, DATE_SUB(NOW(), INTERVAL 1 DAY)),     -- Thomas & Léa
(4, 9, DATE_SUB(NOW(), INTERVAL 1 DAY));     -- Sophie & Antoine

-- =======================================================
-- MESSAGES DE CONVERSATION
-- =======================================================

INSERT IGNORE INTO messages (sender_id, receiver_id, content, sent_at) VALUES 
-- Conversation Emma (2) & Lucas (3)
(2, 3, 'Salut Lucas! J\'ai vu qu\'on avait matché 😊', DATE_SUB(NOW(), INTERVAL 23 HOURS)),
(3, 2, 'Hey Emma! Oui, j\'ai adoré tes photos de voyage!', DATE_SUB(NOW(), INTERVAL 22 HOURS)),
(2, 3, 'Merci! Tu développes dans quel domaine?', DATE_SUB(NOW(), INTERVAL 22 HOURS)),
(3, 2, 'Je fais principalement du web, et toi tu voyages souvent?', DATE_SUB(NOW(), INTERVAL 21 HOURS)),
(2, 3, 'Dès que je peux! Mon prochain trip c\'est l\'Italie 🇮🇹', DATE_SUB(NOW(), INTERVAL 20 HOURS)),

-- Conversation Sophie (4) & Thomas (5)  
(4, 5, 'Hello Thomas! Sympa ton profil sportif 🏃‍♂️', DATE_SUB(NOW(), INTERVAL 22 HOURS)),
(5, 4, 'Merci Sophie! Tu fais du yoga depuis longtemps?', DATE_SUB(NOW(), INTERVAL 21 HOURS)),
(4, 5, 'Environ 3 ans, ça m\'aide beaucoup pour ma créativité', DATE_SUB(NOW(), INTERVAL 20 HOURS)),
(5, 4, 'C\'est génial! Moi le sport m\'aide à décompresser du boulot', DATE_SUB(NOW(), INTERVAL 19 HOURS)),

-- Conversation Emma (2) & Thomas (5)
(2, 5, 'Hey Thomas! On dirait qu\'on a des passions communes 😄', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(5, 2, 'Salut Emma! Oui j\'ai vu ça, tu fais de la rando?', DATE_SUB(NOW(), INTERVAL 23 HOURS)),
(2, 5, 'Pas encore mais ça me tente! Tu connais des bons spots?', DATE_SUB(NOW(), INTERVAL 22 HOURS)),

-- Conversation Sophie (4) & Antoine (9)
(9, 4, 'Salut Sophie! Tes créations artistiques sont magnifiques 🎨', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4, 9, 'Merci Antoine! J\'ai vu que tu étais photographe, on pourrait collaborer!', DATE_SUB(NOW(), INTERVAL 23 HOURS)),
(9, 4, 'Avec plaisir! Tu travailles dans quel style?', DATE_SUB(NOW(), INTERVAL 22 HOURS)),

-- Conversation Thomas (5) & Léa (8)
(5, 8, 'Hello Léa! Future docteure, respect! 👩‍⚕️', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(8, 5, 'Merci Thomas! Et toi ingénieur sportif, c\'est original!', DATE_SUB(NOW(), INTERVAL 23 HOURS)),
(5, 8, 'Oui je développe des équipements sportifs, passion oblige 😄', DATE_SUB(NOW(), INTERVAL 22 HOURS)),

-- Messages plus récents
(3, 2, 'Au fait, tu serais dispo pour un café cette semaine?', DATE_SUB(NOW(), INTERVAL 2 HOURS)),
(2, 3, 'Avec plaisir! Mercredi après-midi ça te va?', DATE_SUB(NOW(), INTERVAL 1 HOUR)),
(3, 2, 'Parfait! Je connais un super café près de République', DATE_SUB(NOW(), INTERVAL 30 MINUTE)),
(2, 3, 'Super! Hâte de te rencontrer en vrai 😊', DATE_SUB(NOW(), INTERVAL 15 MINUTE));

-- =======================================================
-- ABONNEMENTS PREMIUM
-- =======================================================

INSERT IGNORE INTO premium_subscriptions (user_id, subscription_type, start_date, end_date, amount, payment_status) VALUES 
(3, 'monthly', DATE_SUB(NOW(), INTERVAL 15 DAY), DATE_ADD(NOW(), INTERVAL 15 DAY), 9.99, 'completed'),
(6, 'yearly', DATE_SUB(NOW(), INTERVAL 2 MONTH), DATE_ADD(NOW(), INTERVAL 10 MONTH), 99.99, 'completed'),
(10, 'monthly', DATE_SUB(NOW(), INTERVAL 5 DAY), DATE_ADD(NOW(), INTERVAL 25 DAY), 9.99, 'completed');

-- =======================================================
-- PHOTOS UTILISATEURS (simulées)
-- =======================================================

INSERT IGNORE INTO user_photos (user_id, photo_path, is_primary, upload_order) VALUES 
(2, 'emma_photo1.jpg', TRUE, 1),
(2, 'emma_photo2.jpg', FALSE, 2),
(3, 'lucas_photo1.jpg', TRUE, 1),
(4, 'sophie_photo1.jpg', TRUE, 1),
(4, 'sophie_photo2.jpg', FALSE, 2),
(5, 'thomas_photo1.jpg', TRUE, 1),
(6, 'camille_photo1.jpg', TRUE, 1),
(7, 'maxime_photo1.jpg', TRUE, 1),
(8, 'lea_photo1.jpg', TRUE, 1),
(9, 'antoine_photo1.jpg', TRUE, 1),
(10, 'clara_photo1.jpg', TRUE, 1);

-- =======================================================
-- MISE À JOUR DES STATUTS PREMIUM
-- =======================================================

UPDATE users SET is_premium = TRUE WHERE id IN (3, 6, 10);

-- =======================================================
-- STATISTIQUES FINALES
-- =======================================================

-- Afficher un résumé des données créées
SELECT 
    'Utilisateurs créés' as Type,
    COUNT(*) as Nombre
FROM users
WHERE id > 1

UNION ALL

SELECT 
    'Likes créés' as Type,
    COUNT(*) as Nombre
FROM likes

UNION ALL

SELECT 
    'Matches créés' as Type,
    COUNT(*) as Nombre
FROM matches

UNION ALL

SELECT 
    'Messages créés' as Type,
    COUNT(*) as Nombre
FROM messages

UNION ALL

SELECT 
    'Abonnements premium' as Type,
    COUNT(*) as Nombre
FROM premium_subscriptions
WHERE is_active = TRUE;

COMMIT;

-- =======================================================
-- FIN DU SCRIPT - DONNÉES DE TEST INSÉRÉES
-- =======================================================