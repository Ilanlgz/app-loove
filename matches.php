<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once 'config/database.php';
require_once 'classes/Match.php';

// Pour l'instant, on ignore les vrais matches et on affiche juste les utilisateurs réels
$user_matches = []; // Pas de vrais matches pour l'instant

// Récupérer les vrais utilisateurs du site pour la démo
try {
    $conn = getDbConnection();
    $matchSystem = new MatchSystem();
    $real_users = $matchSystem->getDiscoverUsers($_SESSION["user_id"], 6);
    
    // Si pas de résultats avec la classe, essayer directement
    if (empty($real_users)) {
        $stmt = $conn->prepare("
            SELECT id, first_name, last_name, profile_picture, bio, email, created_at
            FROM users 
            WHERE id != ? 
            AND (role IS NULL OR role != 'admin')
            ORDER BY created_at DESC 
            LIMIT 6
        ");
        $stmt->execute([$_SESSION["user_id"]]);
        $real_users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Convertir les vrais utilisateurs en format "match" pour l'affichage
    $demo_matches = [];
    foreach ($real_users as $user) {
        $demo_matches[] = [
            'match_user_id' => $user['id'],
            'match_first_name' => $user['first_name'],
            'match_last_name' => $user['last_name'],
            'match_profile_picture' => $user['profile_picture'],
            'match_bio' => $user['bio'] ?: 'Utilisateur inscrit sur Loove - Découvrez son profil !',
            'matched_at' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 48) . ' hours')), // Date aléatoire récente
            'is_demo' => true,
            'is_real_user' => true
        ];
    }    // Mélanger les vrais matches avec les utilisateurs réels
    $all_matches = array_merge($user_matches, $demo_matches);
    
    // Si vraiment aucun utilisateur, on ajoute des exemples pour la démo
    if (empty($all_matches)) {
        $demo_matches = [
            [
                'match_user_id' => 999,
                'match_first_name' => 'Emma',
                'match_last_name' => 'Martin',
                'match_profile_picture' => '',
                'match_bio' => 'Passionnée de voyage et de photographie. J\'adore découvrir de nouveaux endroits !',
                'matched_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'is_demo' => true,
                'is_real_user' => false
            ],
            [
                'match_user_id' => 998,
                'match_first_name' => 'Lucas',
                'match_last_name' => 'Dubois',
                'match_profile_picture' => '',
                'match_bio' => 'Développeur passionné, amateur de cuisine et de musique.',
                'matched_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'is_demo' => true,
                'is_real_user' => false
            ]
        ];
        $all_matches = $demo_matches;
    }
    
    // Debug pour voir ce qui se passe
    echo "<!-- DEBUG: ";
    echo "Nombre d'utilisateurs trouvés: " . count($real_users) . " | ";
    echo "Nombre de demo_matches: " . count($demo_matches) . " | ";
    echo "Nombre d'all_matches: " . count($all_matches) . " | ";
    echo "User ID actuel: " . $_SESSION["user_id"];
    echo " -->";

} catch (Exception $e) {
    echo "<!-- ERREUR: " . $e->getMessage() . " -->";
    error_log("Erreur dans matches.php: " . $e->getMessage());
    $demo_matches = [];
    $all_matches = [];
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">    <title>Mes Matches - Loove</title>
    <link rel="stylesheet" href="assets/css/footer.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #FF4458 0%, #FF6B81 25%, #FD5068 50%, #FF8A95 75%, #FFB3C1 100%);
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
        }

        @keyframes floatingColors {
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

        /* Responsive navbar */
        @media (max-width: 768px) {
            .nav-container {
                padding: 0 16px;
            }
            
            .user-info span {
                display: none;
            }
            
            .nav-link {
                padding: 6px 12px;
                font-size: 13px;
            }
        }

        /* Contenu principal */
        .matches-container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 24px;
        }

        .matches-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 32px;
            margin-bottom: 32px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .matches-title {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 8px;
        }

        .matches-subtitle {
            font-size: 18px;
            color: #666;
        }

        .matches-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 24px;
        }

        .match-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 24px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: transform 0.3s ease;
        }

        .match-card:hover {
            transform: translateY(-4px);
        }        .match-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin: 0 auto 16px;
            box-shadow: 0 8px 24px rgba(255, 68, 88, 0.3);
            overflow: hidden;
            position: relative;
        }

        .match-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .match-avatar .default-avatar {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 36px;
            font-weight: 700;
        }

        .match-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: #FF4458;
            color: white;
            width: 24px;
            height: 24px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            font-weight: 600;
            box-shadow: 0 2px 8px rgba(255, 68, 88, 0.4);
        }

        .match-name {
            font-size: 20px;
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }

        .match-info {
            color: #666;
            font-size: 14px;
            margin-bottom: 16px;
        }

        .match-actions {
            display: flex;
            gap: 12px;
            justify-content: center;
        }

        .btn-message {
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            color: white;
            padding: 8px 16px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 500;
            font-size: 14px;
            transition: transform 0.3s ease;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .btn-message:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(255, 68, 88, 0.3);
        }

        .empty-state {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 60px 40px;
            text-align: center;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .empty-icon {
            font-size: 64px;
            color: #FF4458;
            margin-bottom: 24px;
        }

        .empty-title {
            font-size: 24px;
            font-weight: 600;
            color: #333;
            margin-bottom: 12px;
        }

        .empty-desc {
            color: #666;
            font-size: 16px;
            margin-bottom: 24px;
        }

        .btn-discover {
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            color: white;
            padding: 12px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: transform 0.3s ease;
        }

        .btn-discover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(255, 68, 88, 0.3);
        }
    </style>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="matches-container">
        <div class="matches-header">            <h1 class="matches-title">
                <i class="fas fa-heart"></i>
                Mes Matches
            </h1>
            <p class="matches-subtitle">
                Découvrez vos compatibilités parfaites
                <?php if (!empty($demo_matches)): ?>
                    <br><span style="font-size: 14px; color: #FF4458; font-weight: 600;">
                        🎓 Démo Oral : <?php echo count($demo_matches); ?> utilisateurs réels du site affichés
                    </span>
                <?php endif; ?>
            </p>
        </div>        <?php if (empty($all_matches)): ?>
            <!-- État vide -->
            <div class="empty-state">
                <div class="empty-icon">
                    <i class="fas fa-heart-broken"></i>
                </div>
                <h2 class="empty-title">Aucun match pour le moment</h2>
                <p class="empty-desc">
                    Commencez à swiper sur des profils pour créer vos premiers matches !<br>
                    Plus vous êtes actif, plus vous avez de chances de trouver l'amour.
                </p>
                <a href="discover.php" class="btn-discover">
                    <i class="fas fa-search"></i>
                    Découvrir des profils
                </a>
            </div>
        <?php else: ?>
            <!-- Grille des matches -->
            <div class="matches-grid">
                <?php foreach ($all_matches as $match): ?>
                    <div class="match-card">
                        <div class="match-avatar">
                            <?php if (!empty($match['match_profile_picture'])): ?>
                                <img src="uploads/profiles/<?php echo htmlspecialchars($match['match_profile_picture']); ?>" 
                                     alt="<?php echo htmlspecialchars($match['match_first_name']); ?>">
                            <?php else: ?>
                                <div class="default-avatar">
                                    <?php echo strtoupper(substr($match['match_first_name'], 0, 1)); ?>
                                </div>
                            <?php endif; ?>
                            <div class="match-badge">
                                <i class="fas fa-heart"></i>
                            </div>
                        </div>
                          <h3 class="match-name">
                            <?php echo htmlspecialchars($match['match_first_name'] . ' ' . $match['match_last_name']); ?>
                            <?php if (isset($match['is_real_user']) && $match['is_real_user']): ?>
                                <span style="font-size: 12px; color: #4CAF50; font-weight: 600;">✓ Utilisateur Réel</span>
                            <?php elseif (isset($match['is_demo']) && $match['is_demo']): ?>
                                <span style="font-size: 12px; color: #999; font-weight: 400;">(Démo)</span>
                            <?php endif; ?>
                        </h3>
                        
                        <p class="match-info">
                            <?php 
                            $match_date = new DateTime($match['matched_at']);
                            echo "Match du " . $match_date->format('d/m/Y à H:i');
                            ?>
                        </p>
                        
                        <?php if (!empty($match['match_bio'])): ?>
                            <p class="match-info">
                                "<?php echo htmlspecialchars(substr($match['match_bio'], 0, 50)); ?><?php echo strlen($match['match_bio']) > 50 ? '...' : ''; ?>"
                            </p>
                        <?php endif; ?>
                          <div class="match-actions">
                            <?php if (isset($match['is_real_user']) && $match['is_real_user']): ?>
                                <a href="messages.php?user_id=<?php echo $match['match_user_id']; ?>" class="btn-message">
                                    <i class="fas fa-comment"></i>
                                    Contacter cet utilisateur
                                </a>
                            <?php elseif (isset($match['is_demo']) && $match['is_demo']): ?>
                                <a href="#" onclick="alert('Ceci est un profil de démonstration pour l\'oral !')" class="btn-message">
                                    <i class="fas fa-comment"></i>
                                    Envoyer un message
                                </a>
                            <?php else: ?>
                                <a href="messages.php?user_id=<?php echo $match['match_user_id']; ?>" class="btn-message">
                                    <i class="fas fa-comment"></i>
                                    Envoyer un message
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>
