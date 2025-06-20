<?php
session_start();

// Vérification admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("location: login.php");
    exit;
}

require_once '../config/database.php';

$conn = getDbConnection();

// Statistiques pour les graphiques
$stats = [];

// Utilisateurs par mois (6 derniers mois)
$stmt = $conn->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as count
    FROM users 
    WHERE role != 'admin' 
        AND created_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month ASC
");
$stats['users_by_month'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Messages par jour (7 derniers jours)
$stmt = $conn->query("
    SELECT 
        DATE(sent_at) as day,
        COUNT(*) as count
    FROM messages 
    WHERE sent_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)
    GROUP BY DATE(sent_at)
    ORDER BY day ASC
");
$stats['messages_by_day'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Utilisateurs actifs par jour (30 derniers jours)
$stmt = $conn->query("
    SELECT 
        DATE(last_active) as day,
        COUNT(DISTINCT id) as count
    FROM users 
    WHERE role != 'admin' 
        AND last_active >= DATE_SUB(NOW(), INTERVAL 30 DAY)
    GROUP BY DATE(last_active)
    ORDER BY day ASC
");
$stats['active_users_by_day'] = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Top utilisateurs par nombre de messages
$stmt = $conn->query("
    SELECT 
        u.first_name,
        u.last_name,
        COUNT(m.id) as message_count
    FROM users u
    LEFT JOIN messages m ON u.id = m.sender_id
    WHERE u.role != 'admin'
    GROUP BY u.id
    ORDER BY message_count DESC
    LIMIT 10
");
$stats['top_users'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Analytics - Admin Loove</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        .admin-sidebar {
            position: fixed; left: 0; top: 0; width: 280px; height: 100vh;
            background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1); z-index: 1000;
        }

        .sidebar-header {
            padding: 24px; background: linear-gradient(135deg, #FF4458, #FF6B81); color: white;
        }

        .sidebar-logo {
            font-size: 24px; font-weight: 700; display: flex; align-items: center; gap: 8px;
        }

        .sidebar-nav { padding: 20px 0; }

        .nav-item {
            display: block; padding: 12px 24px; color: #333; text-decoration: none;
            transition: all 0.3s ease; border-left: 4px solid transparent;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(255, 68, 88, 0.1); border-left-color: #FF4458; color: #FF4458;
        }

        .nav-item i { width: 20px; margin-right: 12px; }

        .main-content { margin-left: 280px; padding: 24px; min-height: 100vh; }

        .admin-header {
            background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px);
            border-radius: 16px; padding: 20px 24px; margin-bottom: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .page-title { font-size: 28px; font-weight: 700; color: #333; }

        .analytics-grid {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            gap: 24px; margin-bottom: 32px;
        }

        .chart-card {
            background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px);
            border-radius: 16px; padding: 24px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .chart-title {
            font-size: 18px; font-weight: 600; color: #333; margin-bottom: 20px;
            display: flex; align-items: center; gap: 8px;
        }

        .chart-container {
            position: relative; height: 300px;
        }

        .top-users-list {
            display: flex; flex-direction: column; gap: 12px;
        }

        .user-item {
            display: flex; align-items: center; justify-content: between;
            padding: 12px; background: rgba(0, 0, 0, 0.02); border-radius: 8px;
        }

        .user-info {
            flex: 1; display: flex; align-items: center; gap: 12px;
        }

        .user-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 600; font-size: 14px;
        }

        .user-name {
            font-weight: 600; color: #333;
        }

        .message-count {
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            color: white; padding: 4px 12px; border-radius: 20px;
            font-size: 12px; font-weight: 600;
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; }
            .analytics-grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <nav class="admin-sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-heart"></i> Loove Admin
            </div>
        </div>
        <div class="sidebar-nav">
            <a href="dashboard.php" class="nav-item">
                <i class="fas fa-chart-line"></i> Dashboard
            </a>
            <a href="users_clean.php" class="nav-item">
                <i class="fas fa-users"></i> Utilisateurs
            </a>
            <a href="broadcast.php" class="nav-item">
                <i class="fas fa-bullhorn"></i> Diffusion
            </a>
            <a href="analytics.php" class="nav-item active">
                <i class="fas fa-chart-bar"></i> Analytics
            </a>
            <a href="../logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </nav>

    <main class="main-content">
        <div class="admin-header">
            <h1 class="page-title">Analytics & Statistiques</h1>
        </div>

        <div class="analytics-grid">
            <!-- Graphique utilisateurs par mois -->
            <div class="chart-card">
                <h3 class="chart-title">
                    <i class="fas fa-user-plus"></i>
                    Nouveaux utilisateurs (6 derniers mois)
                </h3>
                <div class="chart-container">
                    <canvas id="usersChart"></canvas>
                </div>
            </div>

            <!-- Graphique messages par jour -->
            <div class="chart-card">
                <h3 class="chart-title">
                    <i class="fas fa-envelope"></i>
                    Messages par jour (7 derniers jours)
                </h3>
                <div class="chart-container">
                    <canvas id="messagesChart"></canvas>
                </div>
            </div>

            <!-- Graphique utilisateurs actifs -->
            <div class="chart-card">
                <h3 class="chart-title">
                    <i class="fas fa-clock"></i>
                    Utilisateurs actifs (30 derniers jours)
                </h3>
                <div class="chart-container">
                    <canvas id="activeUsersChart"></canvas>
                </div>
            </div>

            <!-- Top utilisateurs -->
            <div class="chart-card">
                <h3 class="chart-title">
                    <i class="fas fa-trophy"></i>
                    Top utilisateurs (par messages)
                </h3>
                <div class="top-users-list">
                    <?php foreach ($stats['top_users'] as $index => $user): ?>
                        <div class="user-item">
                            <div class="user-info">
                                <div class="user-avatar">
                                    <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                                </div>
                                <div class="user-name">
                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                </div>
                            </div>
                            <div class="message-count">
                                <?php echo $user['message_count']; ?> messages
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        // Configuration des couleurs
        const colors = {
            primary: 'rgba(255, 68, 88, 0.8)',
            primaryBorder: 'rgb(255, 68, 88)',
            secondary: 'rgba(102, 126, 234, 0.8)',
            secondaryBorder: 'rgb(102, 126, 234)',
            success: 'rgba(34, 197, 94, 0.8)',
            successBorder: 'rgb(34, 197, 94)'
        };

        // Données PHP converties en JavaScript
        const usersData = <?php echo json_encode($stats['users_by_month']); ?>;
        const messagesData = <?php echo json_encode($stats['messages_by_day']); ?>;
        const activeUsersData = <?php echo json_encode($stats['active_users_by_day']); ?>;

        // Graphique des nouveaux utilisateurs
        const usersCtx = document.getElementById('usersChart').getContext('2d');
        new Chart(usersCtx, {
            type: 'line',
            data: {
                labels: usersData.map(item => item.month),
                datasets: [{
                    label: 'Nouveaux utilisateurs',
                    data: usersData.map(item => item.count),
                    backgroundColor: colors.primary,
                    borderColor: colors.primaryBorder,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Graphique des messages
        const messagesCtx = document.getElementById('messagesChart').getContext('2d');
        new Chart(messagesCtx, {
            type: 'bar',
            data: {
                labels: messagesData.map(item => item.day),
                datasets: [{
                    label: 'Messages',
                    data: messagesData.map(item => item.count),
                    backgroundColor: colors.secondary,
                    borderColor: colors.secondaryBorder,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        // Graphique des utilisateurs actifs
        const activeUsersCtx = document.getElementById('activeUsersChart').getContext('2d');
        new Chart(activeUsersCtx, {
            type: 'line',
            data: {
                labels: activeUsersData.map(item => item.day),
                datasets: [{
                    label: 'Utilisateurs actifs',
                    data: activeUsersData.map(item => item.count),
                    backgroundColor: colors.success,
                    borderColor: colors.successBorder,
                    borderWidth: 2,
                    fill: true,
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });
    </script>
</body>
</html>