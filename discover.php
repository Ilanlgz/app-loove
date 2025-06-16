<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

// Créer les tables de messages si elles n'existent pas
require_once 'config/database.php';
try {
    $conn = getDbConnection();
    
    // Table des conversations
    $conversationsQuery = "CREATE TABLE IF NOT EXISTS conversations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user1_id INT NOT NULL,
        user2_id INT NOT NULL,
        last_message TEXT,
        last_message_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_user1 (user1_id),
        INDEX idx_user2 (user2_id),
        UNIQUE KEY unique_conversation (user1_id, user2_id)
    )";
    
    // Table des messages
    $messagesQuery = "CREATE TABLE IF NOT EXISTS messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        conversation_id INT NOT NULL,
        from_user_id INT NOT NULL,
        to_user_id INT NOT NULL,
        message_text TEXT NOT NULL,
        sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_read BOOLEAN DEFAULT FALSE,
        INDEX idx_conversation (conversation_id),
        INDEX idx_from_user (from_user_id),
        INDEX idx_to_user (to_user_id)
    )";
    
    $conn->exec($conversationsQuery);
    $conn->exec($messagesQuery);
    
} catch (PDOException $e) {
    // Ignorer les erreurs de création de tables
}

require_once 'classes/Match.php';

$matchSystem = new MatchSystem();
$discover_users = $matchSystem->getDiscoverUsers($_SESSION["user_id"], 10);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Découvrir - Loove</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #FF4458;
            --secondary-color: #FD5068;
            --text-primary: #2c2c2c;
            --text-secondary: #8E8E93;
            --background: #FAFAFA;
            --white: #FFFFFF;
            --success: #34C759;
            --warning: #FF9500;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: var(--background);
            color: var(--text-primary);
        }

        .header {
            background: var(--white);
            padding: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .nav-container {
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
            text-decoration: none;
        }

        .nav-menu {
            display: flex;
            gap: 30px;
            align-items: center;
        }

        .nav-link {
            color: var(--text-primary);
            text-decoration: none;
            font-weight: 500;
            padding: 10px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            color: var(--primary-color);
            background: rgba(255, 68, 88, 0.1);
        }

        .btn-logout {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }

        .main-content {
            max-width: 600px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .discover-container {
            position: relative;
            height: 600px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .card-stack {
            position: relative;
            width: 100%;
            max-width: 400px;
            height: 600px;
        }

        .profile-card {
            position: absolute;
            width: 100%;
            height: 100%;
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 16px 32px rgba(0,0,0,0.15);
            overflow: hidden;
            cursor: grab;
            transition: transform 0.3s ease;
        }

        .profile-card:nth-child(2) {
            transform: scale(0.95) translateY(10px);
            z-index: 1;
        }

        .profile-card:nth-child(3) {
            transform: scale(0.9) translateY(20px);
            z-index: 0;
        }

        .profile-card.active {
            z-index: 2;
        }

        .card-image-gallery {
            height: 60%;
            position: relative;
            overflow: hidden;
            border-radius: 20px 20px 0 0;
        }

        .card-image {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: all 0.5s ease;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 4rem;
            font-weight: 600;
        }

        .card-image.active {
            opacity: 1;
        }

        .photo-navigation {
            position: absolute;
            top: 50%;
            left: 0;
            right: 0;
            transform: translateY(-50%);
            display: flex;
            justify-content: space-between;
            padding: 0 15px;
            opacity: 0;
            transition: opacity 0.3s ease;
            z-index: 10;
        }

        .profile-card:hover .photo-navigation {
            opacity: 1;
        }

        .nav-btn {
            background: rgba(0,0,0,0.6);
            color: var(--white);
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .nav-btn:hover {
            background: rgba(0,0,0,0.8);
            transform: scale(1.1);
        }

        .photo-indicators {
            position: absolute;
            top: 15px;
            left: 50%;
            transform: translateX(-50%);
            display: flex;
            gap: 6px;
            z-index: 10;
        }

        .indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: rgba(255,255,255,0.5);
            transition: all 0.3s ease;
            cursor: pointer;
            border: 1px solid rgba(255,255,255,0.3);
        }

        .indicator.active {
            background: var(--white);
            transform: scale(1.3);
            box-shadow: 0 0 8px rgba(255,255,255,0.5);
        }

        .indicator:hover {
            background: rgba(255,255,255,0.8);
            transform: scale(1.1);
        }

        .no-more-cards {
            text-align: center;
            padding: 100px 20px;
            color: var(--text-secondary);
        }

        .no-more-cards i {
            font-size: 4rem;
            margin-bottom: 20px;
            color: var(--primary-color);
        }

        .match-popup {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.8);
            display: none;
            justify-content: center;
            align-items: center;
            z-index: 1000;
        }

        .match-content {
            background: var(--white);
            padding: 50px;
            border-radius: 20px;
            text-align: center;
            max-width: 400px;
            animation: matchPop 0.5s ease;
        }

        @keyframes matchPop {
            0% { transform: scale(0.5); opacity: 0; }
            100% { transform: scale(1); opacity: 1; }
        }

        .match-title {
            font-size: 2rem;
            color: var(--primary-color);
            margin-bottom: 20px;
        }

        .btn-continue {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            margin-top: 20px;
        }

        /* Animation de chargement */
        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid var(--primary-color);
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Transitions fluides pour les cartes */
        .profile-card {
            transition: transform 0.2s ease-out, opacity 0.2s ease-out;
        }

        .profile-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.2);
        }

        /* Animation des boutons d'action */
        .action-btn {
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .action-btn:active {
            transform: scale(0.95);
        }

        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 30px;
        }

        .action-btn {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            box-shadow: 0 8px 16px rgba(0,0,0,0.1);
        }

        .btn-pass {
            background: var(--white);
            color: var(--text-secondary);
            border: 2px solid var(--text-secondary);
        }

        .btn-like {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
        }

        .btn-super-like {
            background: linear-gradient(135deg, #007AFF, #5AC8FA);
            color: var(--white);
        }

        .action-btn:hover {
            transform: scale(1.1);
        }

        .action-btn:active {
            transform: scale(0.95);
        }

        /* Amélioration de l'affichage mobile */
        @media (max-width: 768px) {
            .main-content {
                padding: 0 10px;
            }
            
            .discover-container {
                height: 70vh;
            }
            
            .action-buttons {
                gap: 20px;
                margin-top: 20px;
            }
            
            .action-btn {
                width: 50px;
                height: 50px;
                font-size: 1.2rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <a href="main.php" class="logo">
                <i class="fas fa-heart"></i> Loove
            </a>
            <nav class="nav-menu">
                <a href="discover.php" class="nav-link active">
                    <i class="fas fa-search"></i> Découvrir
                </a>
                <a href="matches.php" class="nav-link">
                    <i class="fas fa-heart"></i> Matches
                </a>
                <a href="messages.php" class="nav-link">
                    <i class="fas fa-comments"></i> Messages
                </a>
                <a href="profile.php" class="nav-link">
                    <i class="fas fa-user"></i> Profil
                </a>
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </nav>
        </div>
    </header>

    <main class="main-content">
        <div class="discover-container">
            <?php if (empty($discover_users)): ?>
                <div class="no-more-cards">
                    <i class="fas fa-heart-broken"></i>
                    <h2>Plus de profils !</h2>
                    <p>Revenez plus tard pour découvrir de nouveaux profils</p>
                </div>
            <?php else: ?>
                <div class="card-stack" id="cardStack">
                    <?php foreach ($discover_users as $index => $user): ?>
                        <div class="profile-card <?php echo $index === 0 ? 'active' : ''; ?>" data-user-id="<?php echo $user['id']; ?>">
                            <div class="card-image-gallery">
                                <?php 
                                $user_photos = [];
                                if (isset($user['photos']) && $user['photos']) {
                                    $user_photos = explode(',', $user['photos']);
                                }
                                if (empty($user_photos) && $user['profile_picture']) {
                                    $user_photos = [$user['profile_picture']];
                                }
                                if (empty($user_photos)) {
                                    $user_photos = ['default']; // Pour l'avatar par défaut
                                }
                                ?>
                                
                                <?php foreach ($user_photos as $photo_index => $photo): ?>
                                    <div class="card-image <?php echo $photo_index === 0 ? 'active' : ''; ?>" data-photo="<?php echo $photo_index; ?>">
                                        <?php if ($photo === 'default'): ?>
                                            <div class="avatar-placeholder">
                                                <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                                            </div>
                                        <?php else: ?>
                                            <img src="uploads/profiles/<?php echo htmlspecialchars($photo); ?>" alt="<?php echo htmlspecialchars($user['first_name']); ?>" style="width: 100%; height: 100%; object-fit: cover;">
                                        <?php endif; ?>
                                    </div>
                                <?php endforeach; ?>
                                
                                <?php if (count($user_photos) > 1): ?>
                                    <div class="photo-navigation">
                                        <button class="nav-btn nav-prev" onclick="previousPhoto(this)">
                                            <i class="fas fa-chevron-left"></i>
                                        </button>
                                        <button class="nav-btn nav-next" onclick="nextPhoto(this)">
                                            <i class="fas fa-chevron-right"></i>
                                        </button>
                                    </div>
                                    
                                    <div class="photo-indicators">
                                        <?php for ($i = 0; $i < count($user_photos); $i++): ?>
                                            <div class="indicator <?php echo $i === 0 ? 'active' : ''; ?>" data-photo="<?php echo $i; ?>" onclick="goToPhoto(this, <?php echo $i; ?>)"></div>
                                        <?php endfor; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($user['is_premium']): ?>
                                    <div class="premium-badge">
                                        <i class="fas fa-crown"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="online-status <?php echo (strtotime($user['last_active']) > strtotime('-5 minutes')) ? 'online' : 'offline'; ?>"></div>
                            </div>
                            <div class="card-info">
                                <div>
                                    <div class="card-name">
                                        <?php echo htmlspecialchars($user['first_name'] . ' ' . substr($user['last_name'], 0, 1) . '.'); ?>, 
                                        <?php echo $user['calculated_age'] ?: $user['age'] ?: '25'; ?>
                                        <?php if ($user['gender']): ?>
                                            <span class="gender-icon">
                                                <?php echo $user['gender'] === 'male' ? '♂️' : ($user['gender'] === 'female' ? '♀️' : '⚧️'); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="card-details">
                                        <?php if ($user['location']): ?>
                                            <div class="detail-item">
                                                <i class="fas fa-map-marker-alt"></i> 
                                                <?php echo htmlspecialchars($user['location']); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($user['occupation']): ?>
                                            <div class="detail-item">
                                                <i class="fas fa-briefcase"></i> 
                                                <?php echo htmlspecialchars($user['occupation']); ?>
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($user['height']): ?>
                                            <div class="detail-item">
                                                <i class="fas fa-ruler-vertical"></i> 
                                                <?php echo htmlspecialchars($user['height']); ?> cm
                                            </div>
                                        <?php endif; ?>
                                        
                                        <?php if ($user['relationship_status']): ?>
                                            <div class="detail-item">
                                                <i class="fas fa-heart"></i> 
                                                <?php echo htmlspecialchars(ucfirst($user['relationship_status'])); ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <?php if ($user['bio']): ?>
                                    <div class="card-bio">
                                        "<?php echo htmlspecialchars(substr($user['bio'], 0, 120)) . (strlen($user['bio']) > 120 ? '...' : ''); ?>"
                                    </div>
                                <?php endif; ?>
                                
                                <?php if ($user['interests']): ?>
                                    <div class="card-interests">
                                        <?php 
                                        $interests = explode(',', $user['interests']);
                                        foreach (array_slice($interests, 0, 3) as $interest): 
                                        ?>
                                            <span class="interest-tag"><?php echo htmlspecialchars(trim($interest)); ?></span>
                                        <?php endforeach; ?>
                                        <?php if (count($interests) > 3): ?>
                                            <span class="interest-tag more">+<?php echo count($interests) - 3; ?></span>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if (!empty($discover_users)): ?>
            <div class="action-buttons">
                <button class="action-btn btn-pass" onclick="performAction('pass')">
                    <i class="fas fa-times"></i>
                </button>
                <button class="action-btn btn-super-like" onclick="performAction('super_like')">
                    <i class="fas fa-star"></i>
                </button>
                <button class="action-btn btn-like" onclick="performAction('like')">
                    <i class="fas fa-heart"></i>
                </button>
            </div>
        <?php endif; ?>
    </main>

    <!-- Match Popup -->
    <div class="match-popup" id="matchPopup">
        <div class="match-content">
            <h2 class="match-title">🎉 C'est un Match !</h2>
            <p>Vous vous plaisez mutuellement ! Commencez une conversation.</p>
            <button class="btn-continue" onclick="closeMatchPopup()">Continuer</button>
        </div>
    </div>

    <script>
        let currentCardIndex = 0;
        let cards = document.querySelectorAll('.profile-card');
        let isLoading = false;

        function performAction(action) {
            if (currentCardIndex >= cards.length || isLoading) return;

            const currentCard = cards[currentCardIndex];
            const userId = currentCard.dataset.userId;
            
            // Désactiver les boutons temporairement
            document.querySelectorAll('.action-btn').forEach(btn => btn.style.pointerEvents = 'none');

            // Animation fluide selon l'action
            if (action === 'like' || action === 'super_like') {
                // Animation swipe droite (like)
                currentCard.style.transition = 'all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
                currentCard.style.transform = 'translateX(120%) rotate(30deg) scale(0.8)';
                currentCard.style.opacity = '0';
                
                // Effet de particules coeurs
                createHeartParticles(currentCard);
            } else {
                // Animation swipe gauche (pass)
                currentCard.style.transition = 'all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
                currentCard.style.transform = 'translateX(-120%) rotate(-30deg) scale(0.8)';
                currentCard.style.opacity = '0';
            }

            // Envoyer l'action au serveur
            fetch('process_action.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    action: action === 'super_like' ? 'like' : action,
                    user_id: userId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.result === 'match') {
                    setTimeout(() => showMatchPopup(), 400);
                }
            });

            // Animation de la carte suivante
            setTimeout(() => {
                currentCard.style.display = 'none';
                currentCardIndex++;
                
                if (currentCardIndex < cards.length) {
                    // Animer l'apparition de la nouvelle carte
                    const nextCard = cards[currentCardIndex];
                    nextCard.classList.add('active');
                    nextCard.style.transform = 'scale(1.05)';
                    nextCard.style.transition = 'all 0.4s ease-out';
                    
                    setTimeout(() => {
                        nextCard.style.transform = 'scale(1)';
                    }, 50);
                    
                    // Réactiver les boutons
                    setTimeout(() => {
                        document.querySelectorAll('.action-btn').forEach(btn => btn.style.pointerEvents = 'auto');
                    }, 300);
                } else {
                    // Charger plus de profils
                    loadMoreProfiles();
                }
            }, 600);
        }

        function createHeartParticles(card) {
            const rect = card.getBoundingClientRect();
            const centerX = rect.left + rect.width / 2;
            const centerY = rect.top + rect.height / 2;
            
            for (let i = 0; i < 8; i++) {
                setTimeout(() => {
                    const heart = document.createElement('div');
                    heart.innerHTML = '❤️';
                    heart.style.position = 'fixed';
                    heart.style.left = centerX + 'px';
                    heart.style.top = centerY + 'px';
                    heart.style.fontSize = '24px';
                    heart.style.pointerEvents = 'none';
                    heart.style.zIndex = '9999';
                    heart.style.transition = 'all 1.5s ease-out';
                    
                    document.body.appendChild(heart);
                    
                    setTimeout(() => {
                        const angle = (i * 45) * Math.PI / 180;
                        const distance = 150 + Math.random() * 100;
                        heart.style.transform = `translate(${Math.cos(angle) * distance}px, ${Math.sin(angle) * distance}px) scale(0.5)`;
                        heart.style.opacity = '0';
                    }, 50);
                    
                    setTimeout(() => {
                        if (heart.parentNode) {
                            heart.parentNode.removeChild(heart);
                        }
                    }, 1600);
                }, i * 100);
            }
        }

        function loadMoreProfiles() {
            if (isLoading) return;
            isLoading = true;
            
            // Afficher un indicateur de chargement
            const loadingIndicator = document.createElement('div');
            loadingIndicator.className = 'loading-indicator';
            loadingIndicator.innerHTML = `
                <div style="text-align: center; padding: 50px;">
                    <div class="spinner"></div>
                    <p style="margin-top: 20px; color: var(--text-secondary);">Chargement de nouveaux profils...</p>
                </div>
            `;
            document.querySelector('.discover-container').appendChild(loadingIndicator);

            // Charger de nouveaux profils
            fetch('load_more_profiles.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    offset: currentCardIndex,
                    limit: 10
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.profiles && data.profiles.length > 0) {
                    // Ajouter les nouveaux profils
                    addNewProfiles(data.profiles);
                    loadingIndicator.remove();
                    
                    // Activer la première nouvelle carte
                    if (currentCardIndex < cards.length) {
                        const nextCard = cards[currentCardIndex];
                        nextCard.classList.add('active');
                        nextCard.style.transform = 'scale(1.05)';
                        nextCard.style.transition = 'all 0.4s ease-out';
                        
                        setTimeout(() => {
                            nextCard.style.transform = 'scale(1)';
                        }, 50);
                    }
                } else {
                    // Plus de profils disponibles
                    loadingIndicator.innerHTML = `
                        <div class="no-more-cards">
                            <i class="fas fa-heart-broken"></i>
                            <h2>Plus de profils pour le moment !</h2>
                            <p>Revenez plus tard pour découvrir de nouveaux profils</p>
                            <button onclick="location.reload()" style="margin-top: 20px; padding: 10px 20px; background: var(--primary-color); color: white; border: none; border-radius: 8px; cursor: pointer;">
                                🔄 Actualiser
                            </button>
                        </div>
                    `;
                    document.querySelector('.action-buttons').style.display = 'none';
                }
                
                // Réactiver les boutons
                document.querySelectorAll('.action-btn').forEach(btn => btn.style.pointerEvents = 'auto');
                isLoading = false;
            })
            .catch(error => {
                console.error('Erreur:', error);
                loadingIndicator.innerHTML = `
                    <div style="text-align: center; color: var(--error);">
                        ❌ Erreur de chargement
                        <button onclick="loadMoreProfiles()" style="display: block; margin: 10px auto; padding: 8px 16px; background: var(--primary-color); color: white; border: none; border-radius: 6px; cursor: pointer;">
                            Réessayer
                        </button>
                    </div>
                `;
                isLoading = false;
            });
        }

        function addNewProfiles(profiles) {
            const cardStack = document.getElementById('cardStack');
            
            profiles.forEach((user, index) => {
                const card = document.createElement('div');
                card.className = 'profile-card';
                card.setAttribute('data-user-id', user.id);
                
                // Gérer les photos multiples
                let user_photos = [];
                if (user.photos) {
                    user_photos = user.photos.split(',');
                } else if (user.profile_picture) {
                    user_photos = [user.profile_picture];
                } else {
                    user_photos = ['default'];
                }
                
                // Créer la galerie d'images
                let imagesHTML = '';
                user_photos.forEach((photo, photoIndex) => {
                    if (photo === 'default') {
                        imagesHTML += `
                            <div class="card-image ${photoIndex === 0 ? 'active' : ''}" data-photo="${photoIndex}">
                                <div class="avatar-placeholder">${user.first_name.charAt(0).toUpperCase()}</div>
                            </div>
                        `;
                    } else {
                        imagesHTML += `
                            <div class="card-image ${photoIndex === 0 ? 'active' : ''}" data-photo="${photoIndex}">
                                <img src="uploads/profiles/${photo}" alt="${user.first_name}" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        `;
                    }
                });
                
                // Navigation et indicateurs
                let navigationHTML = '';
                let indicatorsHTML = '';
                if (user_photos.length > 1) {
                    navigationHTML = `
                        <div class="photo-navigation">
                            <button class="nav-btn nav-prev" onclick="previousPhoto(this)">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="nav-btn nav-next" onclick="nextPhoto(this)">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    `;
                    
                    indicatorsHTML = '<div class="photo-indicators">';
                    for (let i = 0; i < user_photos.length; i++) {
                        indicatorsHTML += `<div class="indicator ${i === 0 ? 'active' : ''}" data-photo="${i}" onclick="goToPhoto(this, ${i})"></div>`;
                    }
                    indicatorsHTML += '</div>';
                }
                
                const premiumBadge = user.is_premium ? '<div class="premium-badge"><i class="fas fa-crown"></i></div>' : '';
                const onlineStatus = '<div class="online-status offline"></div>';
                const genderIcon = user.gender === 'male' ? '♂️' : (user.gender === 'female' ? '♀️' : '⚧️');
                
                const interests = user.interests ? user.interests.split(',').slice(0, 3).map(interest => 
                    `<span class="interest-tag">${interest.trim()}</span>`
                ).join('') : '';
                
                card.innerHTML = `
                    <div class="card-image-gallery">
                        ${imagesHTML}
                        ${navigationHTML}
                        ${indicatorsHTML}
                        ${premiumBadge}
                        ${onlineStatus}
                    </div>
                    <div class="card-info">
                        <div>
                            <div class="card-name">
                                ${user.first_name} ${user.last_name ? user.last_name.charAt(0) + '.' : ''}, ${user.calculated_age || user.age || '25'}
                                ${user.gender ? `<span class="gender-icon">${genderIcon}</span>` : ''}
                            </div>
                            <div class="card-details">
                                ${user.location ? `<div class="detail-item"><i class="fas fa-map-marker-alt"></i> ${user.location}</div>` : ''}
                                ${user.occupation ? `<div class="detail-item"><i class="fas fa-briefcase"></i> ${user.occupation}</div>` : ''}
                                ${user.height ? `<div class="detail-item"><i class="fas fa-ruler-vertical"></i> ${user.height} cm</div>` : ''}
                            </div>
                        </div>
                        ${user.bio ? `<div class="card-bio">"${user.bio.substring(0, 120)}${user.bio.length > 120 ? '...' : ''}"</div>` : ''}
                        ${interests ? `<div class="card-interests">${interests}</div>` : ''}
                    </div>
                `;
                
                cardStack.appendChild(card);
            });
            
            // Mettre à jour la liste des cartes
            cards = document.querySelectorAll('.profile-card');
        }

        function showMatchPopup() {
            document.getElementById('matchPopup').style.display = 'flex';
            // Animation d'entrée
            const popup = document.querySelector('.match-content');
            popup.style.transform = 'scale(0.5) rotate(-10deg)';
            popup.style.opacity = '0';
            
            setTimeout(() => {
                popup.style.transition = 'all 0.6s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
                popup.style.transform = 'scale(1) rotate(0deg)';
                popup.style.opacity = '1';
            }, 50);
        }

        function closeMatchPopup() {
            const popup = document.querySelector('.match-content');
            popup.style.transition = 'all 0.4s ease-in';
            popup.style.transform = 'scale(0.8)';
            popup.style.opacity = '0';
            
            setTimeout(() => {
                document.getElementById('matchPopup').style.display = 'none';
            }, 400);
        }

        // Gestion du swipe tactile améliorée
        let startX = 0, startY = 0;
        let currentX = 0, currentY = 0;
        let cardBeingDragged = null;
        let isDragging = false;

        document.addEventListener('touchstart', handleTouchStart, { passive: false });
        document.addEventListener('touchmove', handleTouchMove, { passive: false });
        document.addEventListener('touchend', handleTouchEnd, { passive: false });

        function handleTouchStart(e) {
            if (currentCardIndex >= cards.length || isLoading) return;
            startX = e.touches[0].clientX;
            startY = e.touches[0].clientY;
            cardBeingDragged = cards[currentCardIndex];
            isDragging = true;
        }

        function handleTouchMove(e) {
            if (!cardBeingDragged || !isDragging) return;
            e.preventDefault();
            
            currentX = e.touches[0].clientX;
            currentY = e.touches[0].clientY;
            const deltaX = currentX - startX;
            const deltaY = currentY - startY;
            const rotation = deltaX * 0.1;
            const scale = 1 - Math.abs(deltaX) * 0.0005;
            
            cardBeingDragged.style.transform = `translateX(${deltaX}px) translateY(${deltaY * 0.5}px) rotate(${rotation}deg) scale(${scale})`;
            cardBeingDragged.style.opacity = 1 - Math.abs(deltaX) * 0.002;
        }

        function handleTouchEnd(e) {
            if (!cardBeingDragged || !isDragging) return;
            
            const deltaX = currentX - startX;
            isDragging = false;
            
            if (Math.abs(deltaX) > 100) {
                if (deltaX > 0) {
                    performAction('like');
                } else {
                    performAction('pass');
                }
            } else {
                // Animation de retour en place
                cardBeingDragged.style.transition = 'all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275)';
                cardBeingDragged.style.transform = 'translateX(0) translateY(0) rotate(0deg) scale(1)';
                cardBeingDragged.style.opacity = '1';
                
                setTimeout(() => {
                    if (cardBeingDragged) {
                        cardBeingDragged.style.transition = '';
                    }
                }, 400);
            }
            
            cardBeingDragged = null;
        }

        // Animation des boutons au hover
        document.querySelectorAll('.action-btn').forEach(btn => {
            btn.addEventListener('mouseenter', function() {
                this.style.transform = 'scale(1.15)';
                this.style.boxShadow = '0 12px 24px rgba(0,0,0,0.2)';
            });
            
            btn.addEventListener('mouseleave', function() {
                this.style.transform = 'scale(1)';
                this.style.boxShadow = '0 8px 16px rgba(0,0,0,0.1)';
            });
        });

        function nextPhoto(button) {
            event.stopPropagation();
            const card = button.closest('.profile-card');
            const photos = card.querySelectorAll('.card-image');
            const indicators = card.querySelectorAll('.indicator');
            const currentPhoto = card.querySelector('.card-image.active');
            const currentIndex = Array.from(photos).indexOf(currentPhoto);
            const nextIndex = (currentIndex + 1) % photos.length;
            
            photos[currentIndex].classList.remove('active');
            photos[nextIndex].classList.add('active');
            indicators[currentIndex].classList.remove('active');
            indicators[nextIndex].classList.add('active');
        }

        function previousPhoto(button) {
            event.stopPropagation();
            const card = button.closest('.profile-card');
            const photos = card.querySelectorAll('.card-image');
            const indicators = card.querySelectorAll('.indicator');
            const currentPhoto = card.querySelector('.card-image.active');
            const currentIndex = Array.from(photos).indexOf(currentPhoto);
            const prevIndex = currentIndex === 0 ? photos.length - 1 : currentIndex - 1;
            
            photos[currentIndex].classList.remove('active');
            photos[prevIndex].classList.add('active');
            indicators[currentIndex].classList.remove('active');
            indicators[prevIndex].classList.add('active');
        }

        function goToPhoto(indicator, photoIndex) {
            event.stopPropagation();
            const card = indicator.closest('.profile-card');
            const photos = card.querySelectorAll('.card-image');
            const indicators = card.querySelectorAll('.indicator');
            const currentPhoto = card.querySelector('.card-image.active');
            const currentIndex = Array.from(photos).indexOf(currentPhoto);
            
            if (currentIndex !== photoIndex) {
                photos[currentIndex].classList.remove('active');
                photos[photoIndex].classList.add('active');
                indicators[currentIndex].classList.remove('active');
                indicators[photoIndex].classList.add('active');
            }
        }

        // Gestion du swipe tactile pour les photos
        let photoStartX = 0;
        let photoCurrentX = 0;
        let isPhotoSwiping = false;
        let cardBeingPhotoSwiped = null;

        document.addEventListener('touchstart', function(e) {
            const cardImageGallery = e.target.closest('.card-image-gallery');
            if (cardImageGallery && !cardBeingDragged) { // Ne pas interférer avec le swipe de carte
                photoStartX = e.touches[0].clientX;
                isPhotoSwiping = true;
                cardBeingPhotoSwiped = cardImageGallery.closest('.profile-card');
            }
        }, { passive: true });

        document.addEventListener('touchmove', function(e) {
            if (isPhotoSwiping && cardBeingPhotoSwiped) {
                photoCurrentX = e.touches[0].clientX;
                e.preventDefault(); // Empêcher le scroll pendant le swipe photo
            }
        }, { passive: false });

        document.addEventListener('touchend', function(e) {
            if (isPhotoSwiping && cardBeingPhotoSwiped) {
                const deltaX = photoCurrentX - photoStartX;
                const cardImageGallery = cardBeingPhotoSwiped.querySelector('.card-image-gallery');
                
                if (Math.abs(deltaX) > 50) { // Seuil pour déclencher le changement de photo
                    if (deltaX > 0) {
                        // Swipe vers la droite - photo précédente
                        const prevBtn = cardImageGallery.querySelector('.nav-prev');
                        if (prevBtn) previousPhoto(prevBtn);
                    } else {
                        // Swipe vers la gauche - photo suivante
                        const nextBtn = cardImageGallery.querySelector('.nav-next');
                        if (nextBtn) nextPhoto(nextBtn);
                    }
                }
                
                isPhotoSwiping = false;
                cardBeingPhotoSwiped = null;
            }
        }, { passive: true });

        // Modifier la fonction addNewProfiles pour inclure les photos multiples
        function addNewProfiles(profiles) {
            const cardStack = document.getElementById('cardStack');
            
            profiles.forEach((user, index) => {
                const card = document.createElement('div');
                card.className = 'profile-card';
                card.setAttribute('data-user-id', user.id);
                
                // Gérer les photos multiples
                let user_photos = [];
                if (user.photos) {
                    user_photos = user.photos.split(',');
                } else if (user.profile_picture) {
                    user_photos = [user.profile_picture];
                } else {
                    user_photos = ['default'];
                }
                
                // Créer la galerie d'images
                let imagesHTML = '';
                user_photos.forEach((photo, photoIndex) => {
                    if (photo === 'default') {
                        imagesHTML += `
                            <div class="card-image ${photoIndex === 0 ? 'active' : ''}" data-photo="${photoIndex}">
                                <div class="avatar-placeholder">${user.first_name.charAt(0).toUpperCase()}</div>
                            </div>
                        `;
                    } else {
                        imagesHTML += `
                            <div class="card-image ${photoIndex === 0 ? 'active' : ''}" data-photo="${photoIndex}">
                                <img src="uploads/profiles/${photo}" alt="${user.first_name}" style="width: 100%; height: 100%; object-fit: cover;">
                            </div>
                        `;
                    }
                });
                
                // Navigation et indicateurs
                let navigationHTML = '';
                let indicatorsHTML = '';
                if (user_photos.length > 1) {
                    navigationHTML = `
                        <div class="photo-navigation">
                            <button class="nav-btn nav-prev" onclick="previousPhoto(this)">
                                <i class="fas fa-chevron-left"></i>
                            </button>
                            <button class="nav-btn nav-next" onclick="nextPhoto(this)">
                                <i class="fas fa-chevron-right"></i>
                            </button>
                        </div>
                    `;
                    
                    indicatorsHTML = '<div class="photo-indicators">';
                    for (let i = 0; i < user_photos.length; i++) {
                        indicatorsHTML += `<div class="indicator ${i === 0 ? 'active' : ''}" data-photo="${i}" onclick="goToPhoto(this, ${i})"></div>`;
                    }
                    indicatorsHTML += '</div>';
                }
                
                const premiumBadge = user.is_premium ? '<div class="premium-badge"><i class="fas fa-crown"></i></div>' : '';
                const onlineStatus = '<div class="online-status offline"></div>';
                const genderIcon = user.gender === 'male' ? '♂️' : (user.gender === 'female' ? '♀️' : '⚧️');
                
                const interests = user.interests ? user.interests.split(',').slice(0, 3).map(interest => 
                    `<span class="interest-tag">${interest.trim()}</span>`
                ).join('') : '';
                
                card.innerHTML = `
                    <div class="card-image-gallery">
                        ${imagesHTML}
                        ${navigationHTML}
                        ${indicatorsHTML}
                        ${premiumBadge}
                        ${onlineStatus}
                    </div>
                    <div class="card-info">
                        <div>
                            <div class="card-name">
                                ${user.first_name} ${user.last_name ? user.last_name.charAt(0) + '.' : ''}, ${user.calculated_age || user.age || '25'}
                                ${user.gender ? `<span class="gender-icon">${genderIcon}</span>` : ''}
                            </div>
                            <div class="card-details">
                                ${user.location ? `<div class="detail-item"><i class="fas fa-map-marker-alt"></i> ${user.location}</div>` : ''}
                                ${user.occupation ? `<div class="detail-item"><i class="fas fa-briefcase"></i> ${user.occupation}</div>` : ''}
                                ${user.height ? `<div class="detail-item"><i class="fas fa-ruler-vertical"></i> ${user.height} cm</div>` : ''}
                            </div>
                        </div>
                        ${user.bio ? `<div class="card-bio">"${user.bio.substring(0, 120)}${user.bio.length > 120 ? '...' : ''}"</div>` : ''}
                        ${interests ? `<div class="card-interests">${interests}</div>` : ''}
                    </div>
                `;
                
                cardStack.appendChild(card);
            });
            
            // Mettre à jour la liste des cartes
            cards = document.querySelectorAll('.profile-card');
        }
    </script>
</body>
</html>
