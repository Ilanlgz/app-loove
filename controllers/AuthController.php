<?php
session_start();

// Si l'utilisateur n'est pas connecté, rediriger vers la page de connexion
if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
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

        @media (max-width: 768px) {
            .welcome-title { font-size: 2rem; }
            .nav-menu { gap: 15px; }
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
                <a href="#" class="nav-link">
                    <i class="fas fa-users"></i> Découvrir
                </a>
                <a href="#" class="nav-link">
                    <i class="fas fa-comments"></i> Messages
                </a>
                <a href="#" class="nav-link">
                    <i class="fas fa-user"></i> Profil
                </a>
                <div class="user-info">
                    <div class="user-avatar">
                        <?php echo strtoupper(substr($_SESSION["first_name"], 0, 1)); ?>
                    </div>
                    <span>Salut <?php echo htmlspecialchars($_SESSION["first_name"]); ?> !</span>
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
        </section>

        <div class="features">
            <div class="feature-card" onclick="alert('Fonctionnalité Découvrir - En développement')">
                <div class="feature-icon">
                    <i class="fas fa-search"></i>
                </div>
                <h3 class="feature-title">Découvrir</h3>
                <p class="feature-desc">Rencontrez des personnes qui partagent vos passions près de chez vous</p>
            </div>

            <div class="feature-card" onclick="alert('Fonctionnalité Messages - En développement')">
                <div class="feature-icon">
                    <i class="fas fa-envelope"></i>
                </div>
                <h3 class="feature-title">Messages</h3>
                <p class="feature-desc">Discutez en toute sécurité avec vos matchs</p>
            </div>

            <div class="feature-card" onclick="alert('Fonctionnalité Profil - En développement')">
                <div class="feature-icon">
                    <i class="fas fa-heart"></i>
                </div>
                <h3 class="feature-title">Mon Profil</h3>
                <p class="feature-desc">Personnalisez votre profil pour attirer l'attention</p>
            </div>
        </div>
    </main>
</body>
</html>