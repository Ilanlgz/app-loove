<?php
session_start();

// Vérification admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    header("location: ../login.php");
    exit;
}

require_once '../config/database.php';

$conn = getDbConnection();

// Statistiques générales
$stmt = $conn->query("SELECT COUNT(*) as total_users FROM users WHERE role != 'admin'");
$total_users = $stmt->fetch()['total_users'];

$stmt = $conn->query("SELECT COUNT(*) as total_messages FROM messages");
$total_messages = $stmt->fetch()['total_messages'];

$stmt = $conn->query("SELECT COUNT(*) as active_users FROM users WHERE last_active > DATE_SUB(NOW(), INTERVAL 24 HOUR)");
$active_users = $stmt->fetch()['active_users'];

$stmt = $conn->query("SELECT COUNT(*) as new_users FROM users WHERE created_at > DATE_SUB(NOW(), INTERVAL 7 DAY)");
$new_users = $stmt->fetch()['new_users'];

// Utilisateurs récents
$stmt = $conn->query("SELECT * FROM users WHERE role != 'admin' ORDER BY created_at DESC LIMIT 10");
$recent_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Messages récents
$stmt = $conn->query("
    SELECT m.*, u1.first_name as sender_name, u2.first_name as receiver_name 
    FROM messages m
    JOIN users u1 ON m.sender_id = u1.id
    JOIN users u2 ON m.receiver_id = u2.id
    ORDER BY m.sent_at DESC LIMIT 10
");
$recent_messages = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Loove</title>
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
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            color: #333;
        }

        /* Sidebar Admin */
        .admin-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            transition: transform 0.3s ease;
        }

        .sidebar-header {
            padding: 24px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            color: white;
        }

        .sidebar-logo {
            font-size: 24px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .sidebar-subtitle {
            font-size: 12px;
            opacity: 0.8;
            margin-top: 4px;
        }

        .sidebar-nav {
            padding: 20px 0;
        }

        .nav-item {
            display: block;
            padding: 12px 24px;
            color: #333;
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .nav-item:hover, .nav-item.active {
            background: rgba(255, 68, 88, 0.1);
            border-left-color: #FF4458;
            color: #FF4458;
        }

        .nav-item i {
            width: 20px;
            margin-right: 12px;
        }

        /* Main Content */
        .main-content {
            margin-left: 280px;
            padding: 24px;
            min-height: 100vh;
        }

        .admin-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 20px 24px;
            margin-bottom: 24px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .page-title {
            font-size: 28px;
            font-weight: 700;
            color: #333;
        }

        .admin-user {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .admin-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        /* Stats Cards */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 32px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-4px);
        }

        .stat-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 16px;
        }

        .stat-title {
            font-size: 14px;
            font-weight: 500;
            color: #666;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
        }

        .stat-icon.users { background: linear-gradient(135deg, #667eea, #764ba2); }
        .stat-icon.messages { background: linear-gradient(135deg, #FF4458, #FF6B81); }
        .stat-icon.active { background: linear-gradient(135deg, #10b981, #059669); }
        .stat-icon.new { background: linear-gradient(135deg, #f59e0b, #d97706); }

        .stat-number {
            font-size: 32px;
            font-weight: 700;
            color: #333;
            margin-bottom: 4px;
        }

        .stat-change {
            font-size: 12px;
            font-weight: 500;
            color: #10b981;
        }

        /* Data Tables */
        .data-section {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
            margin-bottom: 32px;
        }

        .data-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 16px;
            padding: 24px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .data-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid rgba(0, 0, 0, 0.1);
        }

        .data-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
        }

        .view-all-btn {
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            color: white;
            padding: 6px 12px;
            border-radius: 8px;
            text-decoration: none;
            font-size: 12px;
            font-weight: 500;
            transition: transform 0.3s ease;
        }

        .view-all-btn:hover {
            transform: translateY(-2px);
        }

        .data-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .data-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: rgba(0, 0, 0, 0.02);
            border-radius: 8px;
            transition: background 0.3s ease;
        }

        .data-item:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        .item-avatar {
            width: 36px;
            height: 36px;
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }

        .item-info {
            flex: 1;
        }

        .item-name {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin-bottom: 2px;
        }

        .item-detail {
            font-size: 12px;
            color: #666;
        }

        .item-time {
            font-size: 11px;
            color: #999;
        }

        /* Action Buttons */
        .action-buttons {
            display: flex;
            gap: 12px;
            margin-bottom: 32px;
        }

        .action-btn {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 2px solid rgba(255, 68, 88, 0.2);
            color: #FF4458;
            padding: 12px 24px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .action-btn:hover {
            background: #FF4458;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(255, 68, 88, 0.3);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .admin-sidebar {
                transform: translateX(-100%);
            }

            .admin-sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
                padding: 16px;
            }

            .data-section {
                grid-template-columns: 1fr;
            }

            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        .mobile-menu-btn {
            display: none;
            background: none;
            border: none;
            font-size: 20px;
            color: #333;
            cursor: pointer;
        }

        @media (max-width: 768px) {
            .mobile-menu-btn {
                display: block;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar Admin -->
    <nav class="admin-sidebar" id="sidebar">
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <i class="fas fa-heart"></i>
                Loove Admin
            </div>
            <div class="sidebar-subtitle">Panneau d'administration</div>
        </div>
        <div class="sidebar-nav">
            <a href="dashboard.php" class="nav-item active">
                <i class="fas fa-chart-line"></i>
                Dashboard
            </a>
            <a href="users_clean.php" class="nav-item">
                <i class="fas fa-users"></i>
                Utilisateurs
            </a>
            <a href="../logout.php" class="nav-item" style="margin-top: 20px; color: #dc2626;">
                <i class="fas fa-sign-out-alt"></i>
                Déconnexion
            </a>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <div class="admin-header">
            <div>
                <button class="mobile-menu-btn" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h1 class="page-title">Dashboard</h1>
            </div>
            <div class="admin-user">
                <div class="admin-avatar">A</div>
                <span>Admin</span>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Total Utilisateurs</span>
                    <div class="stat-icon users">
                        <i class="fas fa-users"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo number_format($total_users); ?></div>
                <div class="stat-change">+12% ce mois</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Messages Envoyés</span>
                    <div class="stat-icon messages">
                        <i class="fas fa-envelope"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo number_format($total_messages); ?></div>
                <div class="stat-change">+8% cette semaine</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Actifs 24h</span>
                    <div class="stat-icon active">
                        <i class="fas fa-clock"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo number_format($active_users); ?></div>
                <div class="stat-change">+5% aujourd'hui</div>
            </div>

            <div class="stat-card">
                <div class="stat-header">
                    <span class="stat-title">Nouveaux 7j</span>
                    <div class="stat-icon new">
                        <i class="fas fa-user-plus"></i>
                    </div>
                </div>
                <div class="stat-number"><?php echo number_format($new_users); ?></div>
                <div class="stat-change">+18% cette semaine</div>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <a href="users.php" class="action-btn">
                <i class="fas fa-user-cog"></i>
                Gérer les utilisateurs
            </a>
            <a href="broadcast.php" class="action-btn">
                <i class="fas fa-bullhorn"></i>
                Envoyer une annonce
            </a>
            <a href="analytics.php" class="action-btn">
                <i class="fas fa-chart-bar"></i>
                Voir les analyses
            </a>
        </div>

        <!-- Data Tables -->
        <div class="data-section">
            <!-- Utilisateurs récents -->
            <div class="data-card">
                <div class="data-header">
                    <h3 class="data-title">Utilisateurs récents</h3>
                    <a href="users.php" class="view-all-btn">Voir tout</a>
                </div>
                <div class="data-list">
                    <?php foreach ($recent_users as $user): ?>
                        <div class="data-item">
                            <div class="item-avatar">
                                <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                            </div>
                            <div class="item-info">
                                <div class="item-name">
                                    <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                                </div>
                                <div class="item-detail"><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>
                            <div class="item-time">
                                <?php echo date('d/m', strtotime($user['created_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Messages récents -->
            <div class="data-card">
                <div class="data-header">
                    <h3 class="data-title">Messages récents</h3>
                    <a href="messages.php" class="view-all-btn">Voir tout</a>
                </div>
                <div class="data-list">
                    <?php foreach ($recent_messages as $message): ?>
                        <div class="data-item">
                            <div class="item-avatar">
                                <i class="fas fa-comment"></i>
                            </div>
                            <div class="item-info">
                                <div class="item-name">
                                    <?php echo htmlspecialchars($message['sender_name']); ?> → 
                                    <?php echo htmlspecialchars($message['receiver_name']); ?>
                                </div>
                                <div class="item-detail">
                                    <?php echo htmlspecialchars(substr($message['content'], 0, 50)); ?>...
                                </div>
                            </div>
                            <div class="item-time">
                                <?php echo date('H:i', strtotime($message['sent_at'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            sidebar.classList.toggle('open');
        }

        // Fermer la sidebar sur mobile quand on clique ailleurs
        document.addEventListener('click', function(e) {
            const sidebar = document.getElementById('sidebar');
            const menuBtn = document.querySelector('.mobile-menu-btn');
            
            if (window.innerWidth <= 768 && 
                !sidebar.contains(e.target) && 
                !menuBtn.contains(e.target)) {
                sidebar.classList.remove('open');
            }
        });

        // Animation des stats au chargement
        window.addEventListener('load', function() {
            const statNumbers = document.querySelectorAll('.stat-number');
            
            statNumbers.forEach(stat => {
                const finalValue = parseInt(stat.textContent.replace(/,/g, ''));
                let currentValue = 0;
                const increment = finalValue / 50;
                
                const timer = setInterval(() => {
                    currentValue += increment;
                    if (currentValue >= finalValue) {
                        currentValue = finalValue;
                        clearInterval(timer);
                    }
                    stat.textContent = Math.floor(currentValue).toLocaleString();
                }, 30);
            });
        });
    </script>
</body>
</html>
             
    </script>
</body>
</html>
