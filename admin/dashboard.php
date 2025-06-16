<?php
session_start();

if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once '../config/database.php';

$conn = getDbConnection();

// Statistiques
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM users WHERE role = 'user') as total_users,
    (SELECT COUNT(*) FROM users WHERE role = 'user' AND is_premium = 1) as premium_users,
    (SELECT COUNT(*) FROM matches) as total_matches,
    (SELECT COUNT(*) FROM messages) as total_messages,
    (SELECT COUNT(*) FROM premium_transactions WHERE status = 'completed') as total_transactions,
    (SELECT SUM(amount) FROM premium_transactions WHERE status = 'completed') as total_revenue";

$stats = $conn->query($stats_query)->fetch(PDO::FETCH_ASSOC);

// Utilisateurs récents
$recent_users_query = "SELECT id, first_name, last_name, email, created_at, is_premium 
                       FROM users WHERE role = 'user' 
                       ORDER BY created_at DESC LIMIT 5";
$recent_users = $conn->query($recent_users_query)->fetchAll(PDO::FETCH_ASSOC);

// Signalements récents
$reports_query = "SELECT r.*, 
                         u1.first_name as reporter_name, 
                         u2.first_name as reported_name 
                  FROM reports r
                  JOIN users u1 ON r.reporter_id = u1.id
                  JOIN users u2 ON r.reported_id = u2.id
                  WHERE r.status = 'pending'
                  ORDER BY r.created_at DESC LIMIT 5";
$reports = $conn->query($reports_query)->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Loove</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #FF4458;
            --admin-color: #1a1a2e;
            --admin-secondary: #16213e;
            --admin-accent: #0f3460;
            --white: #FFFFFF;
            --text-primary: #2c2c2c;
            --text-secondary: #8E8E93;
            --success: #34C759;
            --warning: #FF9500;
            --error: #FF3B30;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Poppins', sans-serif;
            background: #f5f7fa;
            color: var(--text-primary);
        }

        .admin-header {
            background: linear-gradient(135deg, var(--admin-color), var(--admin-secondary));
            color: var(--white);
            padding: 20px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .header-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .admin-logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .admin-logo i {
            font-size: 2rem;
            color: var(--primary-color);
        }

        .admin-nav {
            display: flex;
            gap: 30px;
        }

        .nav-link {
            color: var(--white);
            text-decoration: none;
            font-weight: 500;
            padding: 10px 15px;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .btn-logout {
            background: var(--primary-color);
            color: var(--white);
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 600;
        }

        .main-content {
            max-width: 1400px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 40px;
        }

        .stat-card {
            background: var(--white);
            padding: 30px;
            border-radius: 16px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            font-size: 1.5rem;
            color: var(--white);
        }

        .stat-icon.users { background: linear-gradient(135deg, #667eea, #764ba2); }
        .stat-icon.premium { background: linear-gradient(135deg, #FFD700, #FFA500); }
        .stat-icon.matches { background: linear-gradient(135deg, var(--primary-color), #FD5068); }
        .stat-icon.messages { background: linear-gradient(135deg, #667eea, #764ba2); }
        .stat-icon.revenue { background: linear-gradient(135deg, var(--success), #28a745); }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: var(--text-primary);
        }

        .stat-label {
            color: var(--text-secondary);
            font-weight: 500;
        }

        .dashboard-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .dashboard-section {
            background: var(--white);
            border-radius: 16px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 600;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-item, .report-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .user-item:last-child, .report-item:last-child {
            border-bottom: none;
        }

        .user-info, .report-info {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .user-name, .report-title {
            font-weight: 600;
        }

        .user-email, .report-reason {
            font-size: 0.9rem;
            color: var(--text-secondary);
        }

        .premium-badge {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: var(--white);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .status-pending {
            background: var(--warning);
            color: var(--white);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        @media (max-width: 768px) {
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .admin-nav {
                display: none;
            }
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="header-container">
            <div class="admin-logo">
                <i class="fas fa-shield-alt"></i>
                <h1>Admin Loove</h1>
            </div>
            
            <nav class="admin-nav">
                <a href="dashboard.php" class="nav-link active">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="users.php" class="nav-link">
                    <i class="fas fa-users"></i> Utilisateurs
                </a>
                <a href="reports.php" class="nav-link">
                    <i class="fas fa-flag"></i> Signalements
                </a>
                <a href="transactions.php" class="nav-link">
                    <i class="fas fa-credit-card"></i> Transactions
                </a>
            </nav>
            
            <div class="admin-user">
                <span>Bonjour <?php echo htmlspecialchars($_SESSION["admin_name"]); ?></span>
                <a href="logout.php" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i> Déconnexion
                </a>
            </div>
        </div>
    </header>

    <main class="main-content">
        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon users">
                    <i class="fas fa-users"></i>
                </div>
                <div class="stat-number"><?php echo number_format($stats['total_users']); ?></div>
                <div class="stat-label">Utilisateurs</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon premium">
                    <i class="fas fa-crown"></i>
                </div>
                <div class="stat-number"><?php echo number_format($stats['premium_users']); ?></div>
                <div class="stat-label">Premium</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon matches">
                    <i class="fas fa-heart"></i>
                </div>
                <div class="stat-number"><?php echo number_format($stats['total_matches']); ?></div>
                <div class="stat-label">Matches</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon messages">
                    <i class="fas fa-comments"></i>
                </div>
                <div class="stat-number"><?php echo number_format($stats['total_messages']); ?></div>
                <div class="stat-label">Messages</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon revenue">
                    <i class="fas fa-euro-sign"></i>
                </div>
                <div class="stat-number"><?php echo number_format($stats['total_revenue'], 2); ?>€</div>
                <div class="stat-label">Revenus</div>
            </div>
        </div>

        <!-- Dashboard sections -->
        <div class="dashboard-grid">
            <!-- Utilisateurs récents -->
            <div class="dashboard-section">
                <h3 class="section-title">
                    <i class="fas fa-user-plus"></i>
                    Nouveaux utilisateurs
                </h3>
                
                <?php foreach ($recent_users as $user): ?>
                    <div class="user-item">
                        <div class="user-info">
                            <div class="user-name">
                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                <?php if ($user['is_premium']): ?>
                                    <span class="premium-badge">Premium</span>
                                <?php endif; ?>
                            </div>
                            <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                        <div class="user-date">
                            <?php echo date('d/m/Y', strtotime($user['created_at'])); ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Signalements récents -->
            <div class="dashboard-section">
                <h3 class="section-title">
                    <i class="fas fa-flag"></i>
                    Signalements en attente
                </h3>
                
                <?php if (empty($reports)): ?>
                    <p class="text-muted">Aucun signalement en attente</p>
                <?php else: ?>
                    <?php foreach ($reports as $report): ?>
                        <div class="report-item">
                            <div class="report-info">
                                <div class="report-title">
                                    <?php echo htmlspecialchars($report['reporter_name']); ?> → 
                                    <?php echo htmlspecialchars($report['reported_name']); ?>
                                </div>
                                <div class="report-reason"><?php echo htmlspecialchars($report['reason']); ?></div>
                            </div>
                            <span class="status-pending">En attente</span>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</body>
</html>
