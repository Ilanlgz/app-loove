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
$user_matches = $matchSystem->getUserMatches($_SESSION["user_id"]);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mes Matches - Loove</title>
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
            max-width: 1200px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 700;
            text-align: center;
            margin-bottom: 20px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .page-subtitle {
            text-align: center;
            color: var(--text-secondary);
            font-size: 1.1rem;
            margin-bottom: 50px;
        }

        .matches-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 30px;
            margin-top: 30px;
        }

        .match-card {
            background: var(--white);
            border-radius: 20px;
            box-shadow: 0 8px 24px rgba(0,0,0,0.1);
            overflow: hidden;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .match-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 16px 32px rgba(0,0,0,0.15);
        }

        .match-avatar {
            height: 200px;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-size: 3rem;
            font-weight: 600;
            position: relative;
        }

        .match-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .match-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(255, 255, 255, 0.9);
            color: var(--primary-color);
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            backdrop-filter: blur(10px);
        }

        .match-info {
            padding: 25px;
        }

        .match-name {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 8px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .match-date {
            color: var(--text-secondary);
            font-size: 0.9rem;
            margin-bottom: 20px;
        }

        .match-actions {
            display: flex;
            gap: 15px;
        }

        .btn-message {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 12px 20px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-message:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(255, 68, 88, 0.3);
        }

        .btn-profile {
            background: var(--white);
            color: var(--text-primary);
            padding: 12px 15px;
            border: 2px solid var(--text-secondary);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-profile:hover {
            border-color: var(--primary-color);
            color: var(--primary-color);
        }

        .no-matches {
            text-align: center;
            padding: 100px 20px;
            color: var(--text-secondary);
        }

        .no-matches i {
            font-size: 5rem;
            margin-bottom: 30px;
            color: var(--primary-color);
            opacity: 0.5;
        }

        .no-matches h2 {
            font-size: 2rem;
            margin-bottom: 15px;
            color: var(--text-primary);
        }

        .no-matches p {
            font-size: 1.1rem;
            margin-bottom: 30px;
            line-height: 1.6;
        }

        .btn-discover {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 15px 30px;
            border: none;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
        }

        .btn-discover:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(255, 68, 88, 0.3);
        }

        .stats-banner {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: var(--white);
            padding: 30px;
            border-radius: 20px;
            text-align: center;
            margin-bottom: 40px;
        }

        .stats-number {
            font-size: 3rem;
            font-weight: 700;
            display: block;
        }

        .stats-label {
            font-size: 1.1rem;
            opacity: 0.9;
        }

        @media (max-width: 768px) {
            .matches-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .page-title {
                font-size: 2rem;
            }
            
            .match-actions {
                flex-direction: column;
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
                <a href="discover.php" class="nav-link">
                    <i class="fas fa-search"></i> Découvrir
                </a>
                <a href="matches.php" class="nav-link active">
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
        <h1 class="page-title">💕 Mes Matches</h1>
        <p class="page-subtitle">Les personnes qui vous ont plu mutuellement</p>

        <?php if (!empty($user_matches)): ?>
            <div class="stats-banner">
                <span class="stats-number"><?php echo count($user_matches); ?></span>
                <span class="stats-label">Match<?php echo count($user_matches) > 1 ? 's' : ''; ?> trouvé<?php echo count($user_matches) > 1 ? 's' : ''; ?></span>
            </div>

            <div class="matches-grid">
                <?php foreach ($user_matches as $match): ?>
                    <div class="match-card" onclick="openProfile(<?php echo $match['match_id']; ?>)">
                        <div class="match-avatar">
                            <?php if ($match['match_picture']): ?>
                                <img src="uploads/profiles/<?php echo htmlspecialchars($match['match_picture']); ?>" alt="<?php echo htmlspecialchars($match['match_name']); ?>">
                            <?php else: ?>
                                <?php echo strtoupper(substr($match['match_name'], 0, 1)); ?>
                            <?php endif; ?>
                            <div class="match-badge">
                                <i class="fas fa-heart"></i> Match
                            </div>
                        </div>
                        <div class="match-info">
                            <div class="match-name">
                                <?php echo htmlspecialchars($match['match_name']); ?>
                                <span style="font-size: 1.2rem;">💕</span>
                            </div>
                            <div class="match-date">
                                Match du <?php echo date('d/m/Y à H:i', strtotime($match['matched_at'])); ?>
                            </div>
                            <div class="match-actions">
                                <button class="btn-message" onclick="event.stopPropagation(); startChat(<?php echo $match['match_id']; ?>)">
                                    <i class="fas fa-comment"></i>
                                    Discuter
                                </button>
                                <button class="btn-profile" onclick="event.stopPropagation(); viewProfile(<?php echo $match['match_id']; ?>)">
                                    <i class="fas fa-user"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="no-matches">
                <i class="fas fa-heart-broken"></i>
                <h2>Aucun match pour le moment</h2>
                <p>Continuez à découvrir des profils pour trouver des personnes qui vous correspondent !</p>
                <a href="discover.php" class="btn-discover">
                    <i class="fas fa-search"></i>
                    Découvrir des profils
                </a>
            </div>
        <?php endif; ?>
    </main>

    <script>
        function startChat(userId) {
            // Rediriger vers la messagerie avec cet utilisateur
            window.location.href = `messages.php?chat=${userId}`;
        }

        function viewProfile(userId) {
            // Ouvrir le profil en modal ou nouvelle page
            alert(`Voir le profil de l'utilisateur ${userId} - Fonctionnalité en développement`);
        }

        function openProfile(userId) {
            viewProfile(userId);
        }

        // Animation au scroll
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.match-card').forEach((card, index) => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(30px)';
            card.style.transition = `all 0.6s ease ${index * 0.1}s`;
            observer.observe(card);
        });
    </script>
</body>
</html>
