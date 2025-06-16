<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($title ?? 'Dashboard - Loove'); ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo APP_URL; ?>/css/app.css">
    <style>
        body {
            background: #f5f7fa;
            font-family: 'Poppins', sans-serif;
            margin: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        .welcome-section {
            text-align: center;
            padding: 2rem 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            margin-bottom: 2rem;
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            font-weight: bold;
            margin: 0 auto 1rem;
        }
        
        .hero-section {
            background: linear-gradient(135deg, #e94057 0%, #ff6b6b 100%);
            color: white;
            border-radius: 20px;
            margin: 2rem 0;
            padding: 3rem;
            text-align: center;
        }
        
        .hero-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 1rem;
        }
        
        .hero-subtitle {
            font-size: 1.1rem;
            margin-bottom: 2rem;
            opacity: 0.9;
        }
        
        .hero-cta {
            background: white;
            color: #e94057;
            padding: 1rem 2rem;
            border-radius: 25px;
            text-decoration: none;
            font-weight: bold;
            display: inline-block;
            transition: transform 0.3s ease;
        }
        
        .hero-cta:hover {
            transform: translateY(-2px);
        }
        
        .stats-section {
            margin: 2rem 0;
        }
        
        .section-title {
            font-size: 1.5rem;
            margin-bottom: 1rem;
            color: #2d3748;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-top: 1.5rem;
        }
        
        .stat-card {
            background: white;
            border: 2px solid #e2e8f0;
            border-radius: 15px;
            padding: 2rem 1.5rem;
            text-align: center;
            transition: all 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(233, 64, 87, 0.1);
            border-color: #e94057;
        }
        
        .stat-number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #e94057;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #64748b;
            font-weight: 500;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <?php include BASE_PATH . '/app/views/layout/header.php'; ?>
    <?php include BASE_PATH . '/app/views/layout/navbar.php'; ?>

    <div class="container">
        <h1 style='text-align: center; margin-bottom: 2rem;'>Bonjour <?php echo htmlspecialchars($_SESSION['first_name']); ?> !</h1>
        
        <?php if($success): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
        <?php endif; ?>

        <!-- Contenu du dashboard principal -->
        <div class="hero-section">
            <div class="hero-content">
                <div class="hero-text">
                    <h1 class="hero-title">L'amour n'attend pas</h1>
                    <p class="hero-subtitle">Découvrez des connexions authentiques avec des personnes qui partagent vos valeurs et vos passions.</p>
                    <a href="/loove/public/discover" class="hero-cta">Commencer à découvrir</a>
                </div>
            </div>
        </div>

        <!-- Statistiques -->
        <div class="stats-section">
            <h2 class="section-title">Vos statistiques</h2>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-number">0</div>
                    <div class="stat-label">Profils likés</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">0</div>
                    <div class="stat-label">Matchs réalisés</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">0</div>
                    <div class="stat-label">Conversations</div>
                </div>
                <div class="stat-card">
                    <div class="stat-number">127</div>
                    <div class="stat-label">Vues de profil</div>
                </div>
            </div>
        </div>
    </div>

    <?php include BASE_PATH . '/app/views/layout/footer.php'; ?>
</body>
</html>
