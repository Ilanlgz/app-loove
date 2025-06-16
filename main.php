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

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

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

        .nav-link:hover {
            color: var(--primary-color);
            background: rgba(255, 68, 88, 0.1);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 600;
        }

        .btn-logout {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
            transition: transform 0.3s ease;
        }

        .btn-logout:hover {
            transform: translateY(-2px);
        }

        .main-content {
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
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
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="nav-container">
            <div class="logo">
                <i class="fas fa-heart"></i> Loove
            </div>
            <nav class="nav-menu">
                <a href="discover.php" class="nav-link">
                    <i class="fas fa-users"></i> Découvrir
                </a>
                <a href="messages.php" class="nav-link">
                    <i class="fas fa-comments"></i> Messages
                </a>
                <a href="profile.php" class="nav-link">
                    <i class="fas fa-user"></i> Profil
                </a>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php 
                        if ($user['profile_picture']) {
                            echo '<img src="uploads/profiles/' . htmlspecialchars($user['profile_picture']) . '" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">';
                        } else {
                            echo strtoupper(substr($_SESSION["first_name"], 0, 1));
                        }
                        ?>
                    </div>
                    <span>Bienvenue <?php echo htmlspecialchars($_SESSION["first_name"]); ?> !</span>
                    <a href="logout.php" class="btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Déconnexion
                    </a>
                </div>
            </nav>
        </div>
    </header>

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
            
            <!-- Bouton de test notifications -->
            <div style="margin-top: 20px;">
                <a href="fix_database.php" style="background: rgba(255,255,255,0.15); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; margin-right: 10px;">
                    🔧 Réparer BDD
                </a>
                <a href="test_notification.php" style="background: rgba(255,255,255,0.2); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500; margin-right: 10px;">
                    🧪 Tester les notifications push
                </a>
                <a href="simple_message_test.php" style="background: rgba(255,255,255,0.3); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 500;">
                    📲 Test Notifications Direct
                </a>
            </div>
        </section>

        <div class="features">
            <div class="feature-card" onclick="window.location.href='discover.php'">
                <div class="feature-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="feature-title">Découvrir</h3>
                <p class="feature-desc">Rencontrez des personnes qui partagent vos passions près de chez vous</p>
            </div>

            <div class="feature-card" onclick="window.location.href='matches.php'">
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
        
    </main>

    <!-- NOTIFICATIONS LOOVE ACTIVÉES -->
    <script>
        console.log('🎯 LOOVE NOTIFICATIONS - Chargé');
        
        // Auto-initialisation des notifications push
        let beamsClient;
        
        async function initPushNotifications() {
            try {
                if (typeof PusherPushNotifications !== 'undefined') {
                    beamsClient = new PusherPushNotifications.Client({
                        instanceId: '4bbe0180-fd1d-4834-84c3-128c682c923d',
                    });
                    
                    await beamsClient.start();
                    await beamsClient.addDeviceInterest('hello');
                    await beamsClient.addDeviceInterest('user-<?php echo $_SESSION["user_id"]; ?>');
                    
                    console.log('✅ Notifications push activées pour <?php echo $_SESSION["first_name"]; ?>!');
                    
                    // Demander permission si nécessaire
                    if (Notification.permission === 'default') {
                        const permission = await Notification.requestPermission();
                        if (permission === 'granted') {
                            console.log('✅ Permission notifications accordée');
                        }
                    }
                }
            } catch (error) {
                console.error('❌ Erreur init push:', error);
            }
        }
        
        // Fonction de test notification simple
        function testSimple() {
            console.log('🧪 Test notification...');
            
            if ('Notification' in window) {
                if (Notification.permission === 'granted') {
                    new Notification('🎉 Test Loove', {
                        body: 'Salut <?php echo $_SESSION["first_name"]; ?>! Ça marche!',
                        icon: 'https://cdn-icons-png.flaticon.com/512/2190/2190552.png'
                    });
                    console.log('✅ Notification envoyée!');
                } else {
                    Notification.requestPermission().then(permission => {
                        if (permission === 'granted') {
                            new Notification('🎉 Autorisé!', {
                                body: 'Notifications OK pour <?php echo $_SESSION["first_name"]; ?>!',
                                icon: 'https://cdn-icons-png.flaticon.com/512/2190/2190552.png'
                            });
                        }
                    });
                }
            }
        }
        
        window.testSimple = testSimple;
        console.log('✅ Fonction testSimple() prête');
        console.log('👤 Utilisateur: <?php echo $_SESSION["first_name"]; ?> (ID: <?php echo $_SESSION["user_id"]; ?>)');
    </script>
    
    <script src="https://js.pusher.com/beams/2.1.0/push-notifications-cdn.js"></script>
    <script>
        console.log('🚀 Pusher SDK chargé');
        
        // Initialiser automatiquement les notifications
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(initPushNotifications, 500);
        });
        
        function testPusher() {
            console.log('🧪 Test Pusher...');
            
            if (typeof PusherPushNotifications !== 'undefined') {
                const beamsClient = new PusherPushNotifications.Client({
                    instanceId: '4bbe0180-fd1d-4834-84c3-128c682c923d',
                });
                
                beamsClient.start()
                    .then(() => beamsClient.addDeviceInterest('hello'))
                    .then(() => beamsClient.addDeviceInterest('user-<?php echo $_SESSION["user_id"]; ?>'))
                    .then(() => {
                        console.log('✅ Pusher configuré!');
                        console.log('📤 Commande cURL pour tester:');
                        console.log('curl -H "Content-Type: application/json" -H "Authorization: Bearer 07255B4D6A282E46CB5CE36FAB1F71B1CE604D2ABC9F597334F5298AF755126A" -X POST "https://4bbe0180-fd1d-4834-84c3-128c682c923d.pushnotifications.pusher.com/publish_api/v1/instances/4bbe0180-fd1d-4834-84c3-128c682c923d/publishes" -d \'{"interests":["user-<?php echo $_SESSION["user_id"]; ?>"],"web":{"notification":{"title":"💕 Test <?php echo $_SESSION["first_name"]; ?>","body":"Message depuis cURL!"}}}\'');
                    })
                    .catch(console.error);
            }
        }
        
        window.testPusher = testPusher;
        console.log('✅ Fonction testPusher() prête');
    </script>
</body>
</html>
