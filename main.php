<?php
session_start();

// Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once 'config/database.php';

$conn = getDbConnection();

// Récupérer les informations de l'utilisateur
$query = "SELECT * FROM users WHERE id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $_SESSION["user_id"], PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Si l'utilisateur n'existe pas, rediriger vers login
if (!$user) {
    session_destroy();
    header("location: login.php");
    exit;
}

$success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : "";
if($success_message) {
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loove - Page d'accueil</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/footer.css">
    <link rel="stylesheet" href="assets/css/hearts-background.css">
    
    <style>
        :root {
            --primary-color: #FF4458;
            --primary-dark: #E73C4E;
            --secondary-color: #FD5068;
            --text-primary: #2c2c2c;
            --text-secondary: #8E8E93;
            --background: #FAFAFA;
            --white: #FFFFFF;
            --success: #34C759;
            --premium-color: #FFD700;
            --premium-secondary: #FFA500;
        }

        /* Reset complet pour éliminer toutes les marges */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html, body {
            margin: 0 !important;
            padding: 0 !important;
            height: 100%;
            overflow-x: hidden;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #FF4458 0%, #FF6B81 25%, #FD5068 50%, #FF8A95 75%, #FFB3C1 100%) !important;
            min-height: 100vh;
            position: relative;
            overflow-x: hidden;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                radial-gradient(circle at 20% 80%, rgba(255, 68, 88, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 80% 20%, rgba(255, 107, 129, 0.3) 0%, transparent 50%),
                radial-gradient(circle at 40% 40%, rgba(253, 80, 104, 0.2) 0%, transparent 50%);
            z-index: -1;
            animation: floatingColors 20s ease-in-out infinite;
        }        @keyframes floatingColors {
            0%, 100% { 
                background: 
                    radial-gradient(circle at 20% 80%, rgba(255, 68, 88, 0.3) 0%, transparent 50%),
                    radial-gradient(circle at 80% 20%, rgba(255, 107, 129, 0.3) 0%, transparent 50%),
                    radial-gradient(circle at 40% 40%, rgba(253, 80, 104, 0.2) 0%, transparent 50%);
            }
            50% { 
                background: 
                    radial-gradient(circle at 70% 30%, rgba(255, 68, 88, 0.3) 0%, transparent 50%),
                    radial-gradient(circle at 30% 70%, rgba(255, 107, 129, 0.3) 0%, transparent 50%),
                    radial-gradient(circle at 60% 60%, rgba(253, 80, 104, 0.2) 0%, transparent 50%);
            }
        }

        /* Cœurs flottants animés par-dessus */
        .floating-hearts {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 1;
            overflow: hidden;
        }

        .floating-heart {
            position: absolute;
            color: rgba(255, 255, 255, 0.6);
            font-size: 20px;
            animation: floatUp 8s infinite linear;
            opacity: 0;
        }

        .floating-heart:nth-child(odd) {
            color: rgba(255, 68, 88, 0.7);
            animation-duration: 10s;
        }

        .floating-heart:nth-child(3n) {
            color: rgba(255, 107, 129, 0.6);
            animation-duration: 12s;
            font-size: 16px;
        }

        .floating-heart:nth-child(4n) {
            color: rgba(253, 80, 104, 0.5);
            animation-duration: 9s;
            font-size: 24px;
        }

        .floating-heart:nth-child(5n) {
            color: rgba(255, 138, 149, 0.7);
            animation-duration: 11s;
            font-size: 18px;
        }

        @keyframes floatUp {
            0% {
                opacity: 0;
                transform: translateY(100vh) rotate(0deg) scale(0);
            }
            10% {
                opacity: 1;
                transform: translateY(90vh) rotate(45deg) scale(1);
            }
            90% {
                opacity: 1;
                transform: translateY(10vh) rotate(315deg) scale(1);
            }
            100% {
                opacity: 0;
                transform: translateY(-10vh) rotate(360deg) scale(0);
            }
        }

        @keyframes heartPulse {
            0%, 100% {
                transform: scale(1);
            }
            50% {
                transform: scale(1.2);
            }
        }

        /* Améliorer la transparence des cartes sur le nouveau fond */
        .profile-card {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px) !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
        }

        /* Header unifié standardisé */
        .header {
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            padding: 12px 0;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 0 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo {
            font-size: 22px;
            font-weight: 700;
            color: white;
            letter-spacing: 0.5px;
            display: flex;
            align-items: center;
            text-decoration: none;
            transition: opacity 0.3s ease;
        }

        .logo:hover {
            opacity: 0.8;
        }

        .logo i {
            margin-right: 6px;
            font-size: 20px;
        }

        .nav-menu {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 8px 16px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            border-radius: 8px;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
            font-size: 14px;
        }

        .nav-link:hover {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            transform: translateY(-2px);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.2);
        }

        .nav-link i {
            font-size: 16px;
            width: 16px;
            text-align: center;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-left: 20px;
            padding-left: 20px;
            border-left: 1px solid rgba(255, 255, 255, 0.3);
        }

        .user-avatar {
            width: 32px;
            height: 32px;
            background: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #FF4458;
            font-weight: 600;
            font-size: 14px;
        }

        .user-info span {
            font-weight: 500;
            color: white;
        }

        .btn-logout {
            display: flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            background: rgba(255, 255, 255, 0.15);
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
            font-size: 13px;
        }

        .btn-logout:hover {
            background: white;
            color: #FF4458;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 255, 255, 0.3);
        }

        .main-content {
            max-width: 1200px;
            margin: 0 auto;
           
        }

        .main-container {
            /* Styles spécifiques pour la classe main-container */
            margin: 20px auto 0; /* Garde seulement margin-top, supprime margin-bottom */
            margin-top: 20px;
            margin-bottom: 0 !important; /* Enlève complètement la marge en bas */
            flex: 1; /* Prend tout l'espace disponible */
        }

        .success-alert {
            background: rgba(52, 199, 89, 0.1);
            color: var(--success);
            padding: 16px 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 12px;
            border: 1px solid rgba(52, 199, 89, 0.2);
        }

        .welcome-section {
            text-align: center;
            padding: 60px 0;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 20px;
            color: var(--white);
            margin-bottom: 50px;
        }

        .welcome-title {
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .welcome-subtitle {
            font-size: 1.3rem;
            opacity: 0.9;
        }

        .features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 40px;
        }

        .feature-card {
            background: var(--white);
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .feature-card:hover {
            transform: translateY(-5px);
        }

        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: var(--white);
            font-size: 1.5rem;
        }

        .feature-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 15px;
        }

        .feature-desc {
            color: var(--text-secondary);
            line-height: 1.6;
        }

        .feature-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr); /* Force 3 colonnes égales */
            gap: 24px;
            margin-top: 32px;
            max-width: 1000px; /* Limite la largeur pour un meilleur alignement */
            margin-left: auto;
            margin-right: auto;
        }

        /* Styles pour le CTA Premium */
        .premium-cta {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            border-radius: 20px;
            padding: 30px;
            margin: 40px 0;
            box-shadow: 0 16px 32px rgba(255, 215, 0, 0.3);
            animation: premiumGlow 2s ease-in-out infinite alternate;
        }

        @keyframes premiumGlow {
            from { box-shadow: 0 16px 32px rgba(255, 215, 0, 0.3); }
            to { box-shadow: 0 20px 40px rgba(255, 215, 0, 0.5); }
        }

        .premium-content {
            display: flex;
            align-items: center;
            gap: 25px;
            color: var(--text-primary);
        }

        .premium-icon {
            font-size: 3rem;
            color: var(--white);
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .premium-text h3 {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .premium-text p {
            font-weight: 500;
            opacity: 0.9;
        }

        .btn-premium {
            background: var(--white);
            color: var(--primary-color);
            padding: 15px 25px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-left: auto;
        }

        .btn-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }

        .premium-status {
            background: linear-gradient(135deg, var(--premium-color), var(--premium-secondary));
            border-radius: 20px;
            padding: 30px;
            margin: 40px 0;
            box-shadow: 0 16px 32px rgba(255, 215, 0, 0.3);
        }

        .btn-manage-premium {
            background: var(--white);
            color: var(--text-primary);
            padding: 15px 25px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            margin-left: auto;
        }

        .btn-manage-premium:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
        }

        /* Footer collé aux bords */
        .footer {
            background: rgba(30, 41, 59, 0.9);
            backdrop-filter: blur(20px);
            color: white;
            margin: 0;
            margin-top: auto; /* Colle le footer en bas */
            margin-bottom: 0;
            padding: 0;
            padding-bottom: 0 !important;
            width: 100vw; /* Prend toute la largeur de l'écran */
            position: relative;
            left: 50%;
            right: 50%;
            margin-left: -50vw;
            margin-right: -50vw;
            margin-top: 0 !important; /* Supprime l'espace entre le contenu et le footer */
        }

        .footer-container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 10px 24px 0; /* Réduit de 40px à 10px pour enlever encore 30px */
        }

        .footer-bottom {
            text-align: center;
            padding-top: 20px;
            padding-bottom: 0 !important; /* Supprime le padding en bas */
            border-top: 1px solid rgba(148, 163, 184, 0.2);
            margin-bottom: 0 !important;
        }

        .footer-bottom p {
            color: #64748b;
          
        }

        @media (max-width: 768px) {
            .welcome-title { font-size: 2rem; }
            .nav-menu { gap: 15px; }
            .premium-content {
                flex-direction: column;
                text-align: center;
                gap: 20px;
            }
            
            .btn-premium {
                margin-left: 0;
            }

            .feature-grid {
                grid-template-columns: 1fr; /* Une seule colonne sur mobile */
                gap: 20px;
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

    <main class="main-content">
        <?php if($success_message): ?>
            <div class="success-alert">
                <i class="fas fa-check-circle"></i>
                <span><?php echo $success_message; ?></span>
            </div>
        <?php endif; ?>

        <section class="welcome-section">
            <h1 class="welcome-title">Bienvenue sur Loove !</h1>
            <p class="welcome-subtitle">Découvrez l'amour, créez des connexions authentiques</p>
            
            <!-- Info utilisateur avec ID -->
            <div style="margin-top: 20px; background: rgba(255,255,255,0.1); padding: 15px; border-radius: 10px; display: inline-block;">
                <strong>Ton ID :</strong> <?php echo $_SESSION["user_id"]; ?> | 
                <strong>Nom :</strong> <?php echo $_SESSION["first_name"]; ?>
            </div>
        </section>

        <div class="features feature-grid">
            <div class="feature-card" onclick="window.location.href='discover.php'">
                <div class="feature-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="feature-title">Découvrir</h3>
                <p class="feature-desc">Rencontrez des personnes qui partagent vos passions près de chez vous</p>
            </div>

            <div class="feature-card" onclick="window.location.href='messages.php'">
                <div class="feature-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3 class="feature-title">Messages</h3>
                <p class="feature-desc">Discutez en toute sécurité avec vos matchs</p>
            </div>

            <div class="feature-card" onclick="window.location.href='profile.php'">
                <div class="feature-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3 class="feature-title">Mon Profil</h3>
                <p class="feature-desc">Personnalisez votre profil pour attirer l'attention</p>
            </div>
        </div>

        <!-- Section Premium CTA -->
        <?php if (!$user['is_premium']): ?>
        <div class="premium-cta">
            <div class="premium-content">
                <div class="premium-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="premium-text">
                    <h3>Passez en Premium</h3>
                    <p>Débloquez les likes illimités, voyez qui vous a liké et bien plus encore !</p>
                </div>
                <a href="premium.php" class="btn-premium">
                    <i class="fas fa-star"></i>
                    Découvrir Premium
                </a>
            </div>
        </div>
        <?php else: ?>
        <div class="premium-status">
            <div class="premium-content">
                <div class="premium-icon">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="premium-text">
                    <h3>Vous êtes Premium !</h3>
                    <p>Profitez de toutes les fonctionnalités exclusives de Loove Premium</p>
                </div>
                <a href="premium.php" class="btn-manage-premium">
                    <i class="fas fa-cog"></i>
                    Gérer l'abonnement
                </a>
            </div>
        </div>
        <?php endif; ?>
        
            <?php include 'includes/footer.php'; ?>
        

    </main>

    <!-- Scripts simplifiés -->
    <script src="https://js.pusher.com/beams/2.1.0/push-notifications-cdn.js"></script>
    <script>
        console.log('✅ Notifications Loove chargées');
    </script>

    <script>
// Génération dynamique des cœurs flottants romantiques
function createFloatingHearts() {
    const heartsContainer = document.createElement('div');
    heartsContainer.className = 'floating-hearts';
    heartsContainer.id = 'floatingHearts';
    heartsContainer.innerHTML = `
        <i class="fas fa-heart floating-heart"></i>
        <i class="fas fa-heart floating-heart"></i>
        <i class="fas fa-heart floating-heart"></i>
        <i class="fas fa-heart floating-heart"></i>
        <i class="fas fa-heart floating-heart"></i>
    `;
    document.body.appendChild(heartsContainer);

    // Créer des cœurs avec des positions et timings aléatoires
    for (let i = 0; i < 15; i++) {
        setTimeout(() => {
            createHeart();
        }, i * 800); // Délai entre chaque cœur
    }

    // Créer un nouveau cœur toutes les 3 secondes
    setInterval(createHeart, 3000);
}

function createHeart() {
    const heartsContainer = document.getElementById('floatingHearts');
    if (!heartsContainer) return;

    const heart = document.createElement('i');
    heart.className = 'fas fa-heart floating-heart';
    
    // Position horizontale aléatoire
    heart.style.left = Math.random() * 100 + '%';
    
    // Délai d'animation aléatoire
    heart.style.animationDelay = Math.random() * 2 + 's';
    
    // Taille légèrement variable
    const size = 16 + Math.random() * 12;
    heart.style.fontSize = size + 'px';

    // Couleurs aléatoires romantiques
    const colors = [
        'rgba(255, 68, 88, 0.7)',
        'rgba(255, 107, 129, 0.6)', 
        'rgba(253, 80, 104, 0.5)',
        'rgba(255, 138, 149, 0.7)',
        'rgba(255, 255, 255, 0.6)'
    ];
    heart.style.color = colors[Math.floor(Math.random() * colors.length)];

    heartsContainer.appendChild(heart);

    // Supprimer le cœur après l'animation
    setTimeout(() => {
        if (heart.parentNode) {
            heart.parentNode.removeChild(heart);
        }
    }, 12000);
}

// Effet de pulsation sur les cœurs lors du hover des cartes
document.addEventListener('DOMContentLoaded', function() {
    createFloatingHearts();
    
    // Ajouter plus de cœurs lors du like d'un profil
    document.addEventListener('click', function(e) {
        if (e.target.closest('.like-btn') || e.target.closest('.swipe-like')) {
            // Explosion de cœurs lors d'un like ! 💕
            for (let i = 0; i < 5; i++) {
                setTimeout(() => {
                    createHeart();
                }, i * 100);
            }
        }
    });
});
</script>
</body>
</html>
