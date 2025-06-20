<?php
// Nettoyer tout buffer potentiel
if (ob_get_level()) ob_end_clean();
ob_start();

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Vérification de l'authentification
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'config/database.php';
require_once 'classes/Match.php';
require_once 'classes/User.php';

// Récupération des profils
$conn = getDbConnection();
$matchSystem = new MatchSystem();
$userSystem = new UserSystem($conn);

// Récupérer les vrais utilisateurs du site
try {
    // Utiliser d'abord la méthode de la classe MatchSystem
    $real_users = $matchSystem->getDiscoverUsers($_SESSION["user_id"], 10);
    
    // Si pas de résultats, essayer une requête directe
    if (empty($real_users)) {
        $stmt = $conn->prepare("
            SELECT id, first_name, last_name, profile_picture, bio, email, created_at,
                   gender, location, last_active
            FROM users 
            WHERE id != ? 
            AND (role IS NULL OR role != 'admin')
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        $stmt->execute([$_SESSION["user_id"]]);
        $real_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    // Convertir en format attendu par l'affichage
    $user_profiles = [];
    foreach ($real_users as $user) {
        $user_profiles[] = [
            'id' => $user['id'],
            'first_name' => $user['first_name'],
            'last_name' => $user['last_name'],
            'age' => rand(20, 35),
            'calculated_age' => rand(20, 35),
            'gender' => $user['gender'] ?: 'Non spécifié',
            'location' => $user['location'] ?: 'France',
            'bio' => $user['bio'] ?: 'Utilisateur inscrit sur Loove - Découvrez son profil ! 💫',
            'description' => $user['bio'] ?: 'Nouveau membre',
            'last_active' => $user['last_active'] ?: date('Y-m-d H:i:s', strtotime('-' . rand(5, 120) . ' minutes')),
            'profile_picture' => $user['profile_picture'],
            'profile_pictures' => []
        ];
    }

} catch (Exception $e) {
    error_log("Erreur récupération utilisateurs: " . $e->getMessage());
    $user_profiles = [];
}

// Vérifier si l'utilisateur a un abonnement premium
$isPremium = $userSystem->isPremiumUser($_SESSION["user_id"]);

// Valeurs par défaut pour les profils
$default_user_profile = [
    'id' => 0, 
    'first_name' => '', 
    'last_name' => '',
    'age' => '', 
    'calculated_age' => '', 
    'gender' => '',
    'location' => '', 
    'bio' => '', 
    'description' => '', 
    'last_active' => '',
    'profile_picture' => '',
    'profile_pictures' => []
];
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Découvrir - Loove</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/navbar.css">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/hearts-background.css">
    <style>
        /* Styles spécifiques à la page discover */
        /* Reset CSS pour éliminer tous les espaces par défaut */
        body, html {
            margin: 0;
            padding: 0;
        }        /* Header unifié avec taille cohérente - FORCE OVERRIDE */
        .header {
            background: linear-gradient(135deg, #FF4458, #FF6B81) !important;
            backdrop-filter: blur(20px) !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2) !important;
            padding: 12px 0 !important;
            position: sticky !important;
            top: 0 !important;
            z-index: 1000 !important;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1) !important;
        }

        .nav-container {
            max-width: 1400px !important;
            margin: 0 auto !important;
            padding: 0 24px !important;
            display: flex !important;
            justify-content: space-between !important;
            align-items: center !important;
        }

        .logo {
            font-size: 22px !important;
            font-weight: 700 !important;
            color: white !important;
            letter-spacing: 0.5px !important;
            display: flex !important;
            align-items: center !important;
            text-decoration: none !important;
            transition: opacity 0.3s ease !important;
        }

        .logo:hover {
            opacity: 0.8 !important;
        }

        .logo i {
            margin-right: 6px !important;
            font-size: 20px !important;
        }

        .nav-menu {
            display: flex !important;
            align-items: center !important;
            gap: 8px !important;
        }

        .nav-link {
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
            padding: 8px 16px !important;
            color: rgba(255, 255, 255, 0.9) !important;
            text-decoration: none !important;
            border-radius: 8px !important;
            font-weight: 500 !important;
            transition: all 0.3s ease !important;
            position: relative !important;
            font-size: 14px !important;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.15) !important;
            color: white !important;
            transform: translateY(-2px) !important;
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.25) !important;
            color: white !important;
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.2) !important;
        }

        .nav-link i {
            font-size: 16px !important;
            width: 16px !important;
            text-align: center !important;
        }

        .user-info {
            display: flex !important;
            align-items: center !important;
            gap: 12px !important;
            margin-left: 20px !important;
            padding-left: 20px !important;
            border-left: 1px solid rgba(255, 255, 255, 0.3) !important;
        }

        .user-avatar {
            width: 32px !important;
            height: 32px !important;
            background: white !important;
            border-radius: 50% !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #FF4458 !important;
            font-weight: 600 !important;
            font-size: 14px !important;
        }

        .user-info span {
            font-weight: 500 !important;
            color: white !important;
        }

        .btn-logout {
            display: flex !important;
            align-items: center !important;
            gap: 6px !important;
            padding: 6px 12px !important;
            background: rgba(255, 255, 255, 0.15) !important;
            color: white !important;
            text-decoration: none !important;
            border-radius: 6px !important;
            font-weight: 500 !important;
            transition: all 0.3s ease !important;
            font-size: 13px !important;
        }

        .btn-logout:hover {
            background: white !important;
            color: #FF4458 !important;
            transform: translateY(-2px) !important;
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.3) !important;
        }

        /* Responsive navbar */
        @media (max-width: 768px) {
            .nav-container {
                padding: 0 16px !important;
            }
            
            .user-info span {
                display: none !important;
            }
            
            .nav-link {
                padding: 6px 12px !important;
                font-size: 13px !important;
            }
        }
        
        /* CSS pour la page discover */
        .discover-container {
            max-width: 500px;
            margin: 30px auto;
            height: calc(100vh - 180px);
            position: relative;
        }
        
        .discover-stack {
            width: 100%;
            height: 100%;
            position: relative;
        }
        
        .user-card {
            position: absolute;
            width: 100%;
            height: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.15);
            overflow: hidden;
            transition: transform 0.5s ease, opacity 0.5s ease;
            transform-origin: center center;
        }
        
        .user-card.hidden {
            display: none;
        }
        
        .user-card.active {
            z-index: 10;
        }
        
        .user-card.next {
            z-index: 9;
            transform: scale(0.95) translateY(10px);
            opacity: 0.8;
        }
        
        .user-card.nextnext {
            z-index: 8;
            transform: scale(0.9) translateY(20px);
            opacity: 0.6;
        }
        
        .user-card.swiped-left {
            animation: swipeLeft 0.6s forwards;
        }
        
        .user-card.swiped-right {
            animation: swipeRight 0.6s forwards;
        }
        
        .user-card.swiped-up {
            animation: swipeUp 0.6s forwards;
        }
        
        @keyframes swipeLeft {
            to { transform: translateX(-200%) rotate(-30deg); opacity: 0; }
        }
        
        @keyframes swipeRight {
            to { transform: translateX(200%) rotate(30deg); opacity: 0; }
        }
        
        @keyframes swipeUp {
            to { transform: translateY(-200%) scale(0.8); opacity: 0; }
        }
        
        .user-photos {
            width: 100%;
            height: 70%;
            position: relative;
        }
        
        .swiper {
            width: 100%;
            height: 100%;
        }
        
        .swiper-slide {
            background-position: center;
            background-size: cover;
        }
        
        .photo-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(to bottom, rgba(0,0,0,0.1), rgba(0,0,0,0.5));
            z-index: 1;
        }
        
        .user-details {
            position: relative;
            padding: 20px;
            background: white;
            height: 30%;
            overflow-y: auto;
        }
        
        .user-status {
            position: absolute;
            top: 15px;
            right: 15px;
            font-size: 14px;
            color: white;
            background-color: rgba(0,0,0,0.5);
            padding: 5px 10px;
            border-radius: 20px;
            z-index: 2;
            display: flex;
            align-items: center;
        }
        
        .user-status::before {
            content: '';
            display: inline-block;
            width: 8px;
            height: 8px;
            border-radius: 50%;
            margin-right: 5px;
            background: #ccc;
        }
        
        .user-status.online::before {
            background: #4CAF50;
        }
        
        .user-details h2 {
            margin: 0 0 5px 0;
            font-size: 24px;
            font-weight: 700;
            color: #333;
        }
        
        .user-location {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
            font-size: 15px;
            color: #777;
        }
        
        .user-location i {
            color: #FF4458;
            margin-right: 5px;
        }
        
        .user-bio {
            font-size: 14px;
            color: #555;
            line-height: 1.5;
            margin-bottom: 15px;
        }
        
        .user-actions {
            position: absolute;
            bottom: 15px;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: center;
            gap: 30px;
            z-index: 20;
        }
        
        .action-button {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            background: white;
            box-shadow: 0 5px 15px rgba(0,0,0,0.2);
        }
        
        .action-button:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.2);
        }
        
        .action-button.dislike {
            color: #FF4458;
        }
        
        .action-button.dislike i {
            font-size: 30px;
        }
        
        .action-button.like {
            background: #FF4458;
            color: white;
        }
        
        .action-button.like i {
            font-size: 30px;
        }
        
        .action-button.superlike {
            background: #00D1FF;
            color: white;
            opacity: <?= $isPremium ? '1' : '0.5' ?>;
            cursor: <?= $isPremium ? 'pointer' : 'not-allowed' ?>;
        }
        
        .action-button.superlike i {
            font-size: 24px;
        }
          .premium-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            z-index: 2;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            display: <?= $isPremium ? 'flex' : 'none' ?>;
            align-items: center;
        }
        
        .demo-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: linear-gradient(135deg, #4CAF50, #45a049);
            color: white;
            padding: 5px 10px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 12px;
            z-index: 2;
            box-shadow: 0 2px 8px rgba(0,0,0,0.2);
            display: flex;
            align-items: center;
        }
        
        .demo-badge i {
            margin-right: 5px;
        }
        
        .premium-badge i {
            margin-right: 5px;
        }
        
        .no-more-users {
            text-align: center;
            padding: 50px 20px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
        }
        
        .no-more-users i {
            font-size: 80px;
            color: #FF4458;
            margin-bottom: 30px;
        }
        
        .no-more-users h2 {
            margin: 0 0 20px;
            color: #333;
            font-size: 28px;
        }
        
        .no-more-users p {
            color: #666;
            font-size: 16px;
            max-width: 300px;
            margin: 0 auto;
        }
        
        .keyboard-shortcuts {
            position: absolute;
            bottom: 15px;
            right: 15px;
            color: #999;
            font-size: 12px;
            z-index: 2;
        }
        
        .toast {
            position: fixed;
            top: 80px;
            left: 50%;
            transform: translateX(-50%);
            background-color: rgba(0,0,0,0.75);
            color: white;
            padding: 12px 25px;
            border-radius: 50px;
            z-index: 9999;
            opacity: 0;
            transition: opacity 0.3s ease;
            font-weight: 500;
        }
        
        .toast.show {
            opacity: 1;
        }
          .toast.match {
            background-color: rgba(255, 68, 88, 0.9);
            padding: 15px 30px;
            font-weight: 600;
            box-shadow: 0 5px 20px rgba(255, 68, 88, 0.4);
        }
        
        /* Modal de match */
        .match-modal {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.8);
            display: none;
            align-items: center;
            justify-content: center;
            z-index: 10000;
            animation: fadeIn 0.3s ease;
        }
        
        .match-modal.show {
            display: flex;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
        
        .match-content {
            background: white;
            border-radius: 20px;
            padding: 40px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            position: relative;
            animation: slideUp 0.4s ease;
        }
        
        @keyframes slideUp {
            from { transform: translateY(50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
        
        .match-hearts {
            font-size: 60px;
            color: #FF4458;
            margin-bottom: 20px;
            animation: heartBeat 1s ease infinite;
        }
        
        @keyframes heartBeat {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }
        
        .match-title {
            font-size: 28px;
            font-weight: 700;
            color: #FF4458;
            margin-bottom: 10px;
        }
        
        .match-subtitle {
            font-size: 16px;
            color: #666;
            margin-bottom: 20px;
        }
        
        .matched-user {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .matched-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            overflow: hidden;
            border: 3px solid #FF4458;
        }
        
        .matched-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .matched-avatar .default-avatar {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 30px;
            font-weight: 700;
        }
        
        .match-vs {
            font-size: 24px;
            color: #FF4458;
            font-weight: 700;
        }
        
        .matched-name {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-top: 10px;
        }
        
        .match-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        .btn-start-chat {
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-start-chat:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(255, 68, 88, 0.3);
        }
        
        .btn-continue {
            background: transparent;
            color: #666;
            border: 2px solid #ddd;
            padding: 10px 20px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-continue:hover {
            border-color: #FF4458;
            color: #FF4458;
        }
        
        /* Ajustements pour mobile */
        @media (max-width: 768px) {
            .discover-container {
                height: calc(100vh - 150px);
                margin: 20px 15px;
            }
            
            .user-actions {
                gap: 15px;
            }
            
            .action-button {
                width: 55px;
                height: 55px;
            }
        }
    </style>
</head>
<body>
    <!-- Container des coeurs flottants -->
    <div class="hearts-container">
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
        <i class="fas fa-heart heart"></i>
    </div>

    <?php include 'includes/navbar.php'; ?>
    
    <div class="discover-container">
        <?php if (empty($user_profiles)) { ?>
            <div class="no-more-users">
                <i class="fas fa-users-slash"></i>
                <h2>Plus de profils disponibles pour le moment</h2>
                <p>Revenez plus tard ou modifiez vos critères de recherche</p>
            </div>
        <?php } else { ?>
            <div class="discover-stack">
                <?php 
                // Classes pour les cartes
                $cardClasses = ['active', 'next', 'nextnext'];
                
                foreach ($user_profiles as $index => $user_profile) { 
                    $user_profile = array_merge($default_user_profile, $user_profile);
                    $cardClass = $index < 3 ? $cardClasses[$index] : 'hidden';
                    
                    // Récupérer les photos supplémentaires si disponibles
                    $profile_pictures = [];
                    if (!empty($user_profile['profile_picture'])) {
                        $profile_pictures[] = $user_profile['profile_picture'];
                    }
                    
                    if (!empty($user_profile['profile_pictures']) && is_array($user_profile['profile_pictures'])) {
                        $profile_pictures = array_merge($profile_pictures, $user_profile['profile_pictures']);
                    }
                    
                    // Si aucune photo, utiliser une image par défaut
                    if (empty($profile_pictures)) {
                        $profile_pictures[] = 'assets/img/default-profile.jpg';
                    }
                ?>
                <div class="user-card <?php echo $cardClass; ?>" data-user-id="<?php echo $user_profile['id']; ?>">                    <?php if ($isPremium) { ?>
                        <div class="premium-badge">
                            <i class="fas fa-crown"></i> Premium
                        </div>
                    <?php } ?>
                    
                    <div class="user-status <?php echo (isset($user_profile['last_active']) && (time() - strtotime($user_profile['last_active']) < 600)) ? 'online' : ''; ?>">
                        <?php echo (isset($user_profile['last_active']) && (time() - strtotime($user_profile['last_active']) < 600)) ? 'En ligne' : 'Hors ligne'; ?>
                    </div>
                    
                    <div class="user-photos">
                        <div class="swiper">
                            <div class="swiper-wrapper">
                                <?php foreach ($profile_pictures as $photo) { ?>
                                    <div class="swiper-slide" style="background-image: url('<?php echo htmlspecialchars(strpos($photo, 'http') === 0 ? $photo : 'uploads/profiles/' . $photo); ?>')"></div>
                                <?php } ?>
                            </div>
                            <div class="swiper-pagination"></div>
                        </div>
                        <div class="photo-overlay"></div>
                    </div>
                    
                    <div class="user-details">
                        <h2>
                            <?php echo htmlspecialchars($user_profile['first_name']); ?> 
                            <?php echo htmlspecialchars($user_profile['last_name'] ? substr($user_profile['last_name'], 0, 1) . '.' : ''); ?>, 
                            <?php 
                            echo isset($user_profile['age']) ? htmlspecialchars($user_profile['age']) : 
                                (isset($user_profile['calculated_age']) ? htmlspecialchars($user_profile['calculated_age']) : '?'); 
                            ?>
                        </h2>
                        
                        <div class="user-location">
                            <i class="fas fa-map-marker-alt"></i> 
                            <?php echo htmlspecialchars($user_profile['location'] ?? ''); ?>
                        </div>
                        
                        <div class="user-bio">
                            <?php echo htmlspecialchars($user_profile['bio'] ?? ($user_profile['description'] ?? '')); ?>
                        </div>
                    </div>
                </div>
                <?php } ?>
            </div>
            
            <div class="user-actions">
                <button class="action-button dislike">
                    <i class="fas fa-times"></i>
                </button>
                <button class="action-button superlike" <?php echo !$isPremium ? 'title="Fonction premium uniquement"' : ''; ?>>
                    <i class="fas fa-star"></i>
                </button>
                <button class="action-button like">
                    <i class="fas fa-heart"></i>
                </button>
            </div>
            
            <div class="keyboard-shortcuts">
                ← Passer | Aimer →
            </div>
        <?php } ?>
    </div>
      <?php include 'includes/footer.php'; ?>
    
    <div class="toast" id="toast"></div>
    
    <!-- Modal de Match -->
    <div class="match-modal" id="matchModal">
        <div class="match-content">
            <div class="match-hearts">
                <i class="fas fa-heart"></i>
            </div>
            <h2 class="match-title">C'est un Match ! 🎉</h2>
            <p class="match-subtitle">Vous vous êtes plu mutuellement !</p>
            
            <div class="matched-user">
                <div class="matched-avatar" id="currentUserAvatar">
                    <div class="default-avatar">
                        <?php echo strtoupper(substr($_SESSION["first_name"], 0, 1)); ?>
                    </div>
                </div>
                <div class="match-vs">💕</div>
                <div class="matched-avatar" id="matchedUserAvatar">
                    <!-- L'avatar sera ajouté dynamiquement -->
                </div>
            </div>
            
            <div class="matched-name" id="matchedUserName">
                <!-- Le nom sera ajouté dynamiquement -->
            </div>
            
            <div class="match-actions">
                <a href="#" class="btn-start-chat" id="startChatBtn">
                    <i class="fas fa-comment"></i>
                    Commencer à discuter
                </a>
                <a href="#" class="btn-continue" onclick="closeMatchModal()">
                    Continuer à découvrir
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialiser le carrousel Swiper pour chaque carte
        document.querySelectorAll('.user-card').forEach(function(card) {
            new Swiper(card.querySelector('.swiper'), {
                pagination: {
                    el: card.querySelector('.swiper-pagination'),
                },
            });
        });
          // Fonction pour afficher un toast
        function showToast(message, isMatch = false) {
            const toast = document.getElementById('toast');
            toast.textContent = message;
            
            if (isMatch) {
                toast.classList.add('match');
            } else {
                toast.classList.remove('match');
            }
            
            toast.classList.add('show');
            
            setTimeout(() => {
                toast.classList.remove('show');
            }, 3000);
        }
        
        // Fonction pour afficher le modal de match
        function showMatchModal(matchedUser, redirectUrl) {
            const modal = document.getElementById('matchModal');
            const matchedUserAvatar = document.getElementById('matchedUserAvatar');
            const matchedUserName = document.getElementById('matchedUserName');
            const startChatBtn = document.getElementById('startChatBtn');
            
            // Mettre à jour les informations de l'utilisateur matché
            if (matchedUser.profile_picture) {
                matchedUserAvatar.innerHTML = `<img src="uploads/profiles/${matchedUser.profile_picture}" alt="${matchedUser.first_name}">`;
            } else {
                matchedUserAvatar.innerHTML = `<div class="default-avatar">${matchedUser.first_name.charAt(0).toUpperCase()}</div>`;
            }
            
            matchedUserName.textContent = `${matchedUser.first_name} ${matchedUser.last_name}`;
            startChatBtn.href = redirectUrl;
            
            // Afficher le modal
            modal.classList.add('show');
            
            // Ajouter des confettis d'animation
            createConfetti();
        }
        
        // Fonction pour fermer le modal de match
        function closeMatchModal() {
            const modal = document.getElementById('matchModal');
            modal.classList.remove('show');
            showNextCard();
        }
        
        // Fonction pour créer des confettis
        function createConfetti() {
            const colors = ['#FF4458', '#FF6B81', '#FFD700', '#FFA500', '#FF69B4'];
            const confettiCount = 50;
            
            for (let i = 0; i < confettiCount; i++) {
                setTimeout(() => {
                    const confetti = document.createElement('div');
                    confetti.style.position = 'fixed';
                    confetti.style.width = '10px';
                    confetti.style.height = '10px';
                    confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.left = Math.random() * 100 + '%';
                    confetti.style.top = '-10px';
                    confetti.style.zIndex = '10001';
                    confetti.style.pointerEvents = 'none';
                    confetti.style.animation = 'confettiFall 3s linear forwards';
                    
                    document.body.appendChild(confetti);
                    
                    setTimeout(() => {
                        confetti.remove();
                    }, 3000);
                }, i * 50);
            }
        }
        
        // Ajouter le CSS pour l'animation des confettis
        const style = document.createElement('style');
        style.textContent = `
            @keyframes confettiFall {
                to {
                    transform: translateY(100vh) rotate(360deg);
                    opacity: 0;
                }
            }
        `;
        document.head.appendChild(style);
        
        // Fonction pour passer à la carte suivante
        function showNextCard() {
            const currentCard = document.querySelector('.user-card.active');
            const nextCard = document.querySelector('.user-card.next');
            const nextNextCard = document.querySelector('.user-card.nextnext');
            const hiddenCards = document.querySelectorAll('.user-card.hidden');
            
            if (currentCard) {
                currentCard.remove();
            }
            
            if (nextCard) {
                nextCard.classList.remove('next');
                nextCard.classList.add('active');
            }
            
            if (nextNextCard) {
                nextNextCard.classList.remove('nextnext');
                nextNextCard.classList.add('next');
            }
            
            if (hiddenCards.length > 0) {
                hiddenCards[0].classList.remove('hidden');
                hiddenCards[0].classList.add('nextnext');
            }
            
            // Vérifier s'il reste des cartes
            if (!document.querySelector('.user-card')) {
                // Recharger la page pour obtenir plus de profils
                window.location.reload();
            }
        }
        
        // Fonction pour gérer le like
        function handleLike() {
            const currentCard = document.querySelector('.user-card.active');
            if (!currentCard) return;            const userId = currentCard.getAttribute('data-user-id');
            currentCard.classList.add('swiped-right');
            
            fetch('ajax/like_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'user_id=' + userId
            })            .then(response => response.json())
            .then(data => {
                if (data.match) {
                    // C'est un match ! Afficher le modal de match
                    showMatchModal(data.matched_user, data.redirect_url);
                } else if (data.success) {
                    showToast("Like envoyé ! 💖");
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
            
            setTimeout(showNextCard, 600);
        }
        
        // Fonction pour gérer le dislike
        function handleDislike() {
            const currentCard = document.querySelector('.user-card.active');
            if (!currentCard) return;            const userId = currentCard.getAttribute('data-user-id');
            currentCard.classList.add('swiped-left');
            
            fetch('ajax/dislike_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'user_id=' + userId
            });
            
            setTimeout(showNextCard, 600);
        }
        
        // Fonction pour gérer le superlike
        function handleSuperLike() {
            const isPremium = <?php echo $isPremium ? 'true' : 'false' ?>;
            if (!isPremium) {
                showToast("Le Super Like est une fonctionnalité premium");
                return;
            }
            
            const currentCard = document.querySelector('.user-card.active');
            if (!currentCard) return;            const userId = currentCard.getAttribute('data-user-id');
            currentCard.classList.add('swiped-up');
            
            fetch('ajax/superlike_user.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: 'user_id=' + userId
            })
            .then(response => response.json())
            .then(data => {
                if (data.match) {
                    showToast("Super Match! ⭐✨", true);
                } else {
                    showToast("Super Like envoyé! ⭐");
                }
            })
            .catch(error => {
                console.error('Erreur:', error);
            });
            
            setTimeout(showNextCard, 600);
        }
        
        // Ajouter les événements aux boutons
        document.querySelector('.action-button.like')?.addEventListener('click', handleLike);
        document.querySelector('.action-button.dislike')?.addEventListener('click', handleDislike);
        document.querySelector('.action-button.superlike')?.addEventListener('click', handleSuperLike);
        
        // Ajouter les raccourcis clavier
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowLeft') {
                handleDislike();
            } else if (e.key === 'ArrowRight') {
                handleLike();
            } else if (e.key === 'ArrowUp') {
                handleSuperLike();
            }
        });
        
        // Gestion du drag & swipe
        document.querySelectorAll('.user-card').forEach(card => {
            card.addEventListener('touchstart', function(e) {
                startX = e.touches[0].clientX;
                startY = e.touches[0].clientY;
            });
            
            card.addEventListener('touchmove', function(e) {
                if (!card.classList.contains('active')) return;
                
                const currentX = e.touches[0].clientX;
                const currentY = e.touches[0].clientY;
                const diffX = currentX - startX;
                const diffY = currentY - startY;
                const rotation = diffX * 0.1; // Rotation proportionnelle au swipe
                
                // Appliquer la transformation
                card.style.transform = `translateX(${diffX}px) translateY(${diffY}px) rotate(${rotation}deg)`;
                
                // Changer l'opacité en fonction de la direction
                if (diffX > 50) {
                    card.style.boxShadow = '0 10px 30px rgba(76, 175, 80, 0.3)';
                } else if (diffX < -50) {
                    card.style.boxShadow = '0 10px 30px rgba(255, 69, 88, 0.3)';
                } else if (diffY < -50) {
                    card.style.boxShadow = '0 10px 30px rgba(0, 209, 255, 0.3)';
                }
            });
            
            card.addEventListener('touchend', function(e) {
                if (!card.classList.contains('active')) return;
                
                const currentX = e.changedTouches[0].clientX;
                const currentY = e.changedTouches[0].clientY;
                const diffX = currentX - startX;
                const diffY = currentY - startY;
                
                // Réinitialiser la transformation
                card.style.transform = '';
                card.style.boxShadow = '';
                
                // Déterminer l'action en fonction du swipe
                if (diffX > 100) {
                    handleLike();
                } else if (diffX < -100) {
                    handleDislike();
                } else if (diffY < -100) {
                    handleSuperLike();
                }
            });
        });
    });
    </script>
</body>
</html>
<?php
// S'assurer qu'aucun contenu supplémentaire ne sera affiché
ob_end_flush();
?>

