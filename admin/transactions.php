<?php
session_start();

if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once '../config/database.php';

$conn = getDbConnection();

// Créer la table des transactions si elle n'existe pas
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS premium_transactions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        plan_type VARCHAR(50) NOT NULL,
        amount DECIMAL(10,2) NOT NULL,
        payment_method VARCHAR(50) NOT NULL,
        transaction_id VARCHAR(100) UNIQUE,
        status VARCHAR(20) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NULL,
        INDEX idx_user (user_id),
        INDEX idx_status (status)
    )");
} catch (PDOException $e) {
    // Table existe déjà
}

// Statistiques
$stats_query = "SELECT 
    COUNT(*) as total_transactions,
    SUM(CASE WHEN status = 'completed' THEN amount ELSE 0 END) as total_revenue,
    COUNT(CASE WHEN status = 'completed' THEN 1 END) as completed_transactions,
    COUNT(CASE WHEN status = 'pending' THEN 1 END) as pending_transactions
    FROM premium_transactions";

$stats = $conn->query($stats_query)->fetch(PDO::FETCH_ASSOC);

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Filtres
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';
$period_filter = isset($_GET['period']) ? $_GET['period'] : 'all';

// Construction de la requête
$where_conditions = [];
$params = [];

if ($status_filter !== 'all') {
    $where_conditions[] = "t.status = ?";
    $params[] = $status_filter;
}

if ($period_filter !== 'all') {
    switch($period_filter) {
        case 'today':
            $where_conditions[] = "DATE(t.created_at) = CURDATE()";
            break;
        case 'week':
            $where_conditions[] = "t.created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $where_conditions[] = "t.created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
            break;
    }
}

$where_clause = !empty($where_conditions) ? "WHERE " . implode(' AND ', $where_conditions) : "";

// Compter le total
$count_query = "SELECT COUNT(*) FROM premium_transactions t $where_clause";
$stmt = $conn->prepare($count_query);
$stmt->execute($params);
$total_transactions = $stmt->fetchColumn();
$total_pages = ceil($total_transactions / $limit);

// Récupérer les transactions
$transactions_query = "SELECT t.*, u.first_name, u.last_name, u.email
                      FROM premium_transactions t
                      LEFT JOIN users u ON t.user_id = u.id
                      $where_clause
                      ORDER BY t.created_at DESC 
                      LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($transactions_query);
$stmt->execute($params);
$transactions = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transactions - Admin Loove</title>
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

        .page-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 30px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: var(--white);
            padding: 20px;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            text-align: center;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 700;
            color: var(--primary-color);
        }

        .stat-label {
            color: var(--text-secondary);
            font-weight: 500;
            margin-top: 5px;
        }

        .filters-section {
            background: var(--white);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .filters-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .filter-select {
            padding: 12px;
            border: 2px solid #E8E8E8;
            border-radius: 8px;
            font-size: 1rem;
        }

        .transactions-table {
            background: var(--white);
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            overflow: hidden;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }

        th {
            background: #f8f9fa;
            font-weight: 600;
        }

        .status-completed {
            background: rgba(52, 199, 89, 0.1);
            color: var(--success);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending {
            background: rgba(255, 149, 0, 0.1);
            color: var(--warning);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-cancelled {
            background: rgba(255, 59, 48, 0.1);
            color: var(--error);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .amount {
            font-weight: 600;
            color: var(--success);
        }

        .plan-badge {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: var(--white);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="admin-header">
        <div class="header-container">
            <div class="admin-logo">
                <i class="fas fa-shield-alt"></i>
                <h1>Admin Loove</h1>
            </div>
            
            <nav class="admin-nav">
                <a href="dashboard.php" class="nav-link">
                    <i class="fas fa-tachometer-alt"></i> Dashboard
                </a>
                <a href="users.php" class="nav-link">
                    <i class="fas fa-users"></i> Utilisateurs
                </a>
                <a href="reports.php" class="nav-link">
                    <i class="fas fa-flag"></i> Signalements
                </a>
                <a href="transactions.php" class="nav-link active">
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
        <h1 class="page-title">Gestion des transactions</h1>

        <!-- Statistiques -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total_transactions']); ?></div>
                <div class="stat-label">Total transactions</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['total_revenue'], 2); ?>€</div>
                <div class="stat-label">Revenus totaux</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['completed_transactions']); ?></div>
                <div class="stat-label">Transactions réussies</div>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo number_format($stats['pending_transactions']); ?></div>
                <div class="stat-label">En attente</div>
            </div>
        </div>

        <!-- Filtres -->
        <div class="filters-section">
            <form method="GET" class="filters-row">
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Tous les statuts</option>
                    <option value="completed" <?php echo $status_filter === 'completed' ? 'selected' : ''; ?>>Complétées</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>En attente</option>
                    <option value="cancelled" <?php echo $status_filter === 'cancelled' ? 'selected' : ''; ?>>Annulées</option>
                </select>
                
                <select name="period" class="filter-select" onchange="this.form.submit()">
                    <option value="all" <?php echo $period_filter === 'all' ? 'selected' : ''; ?>>Toutes les périodes</option>
                    <option value="today" <?php echo $period_filter === 'today' ? 'selected' : ''; ?>>Aujourd'hui</option>
                    <option value="week" <?php echo $period_filter === 'week' ? 'selected' : ''; ?>>7 derniers jours</option>
                    <option value="month" <?php echo $period_filter === 'month' ? 'selected' : ''; ?>>30 derniers jours</option>
                </select>
            </form>
        </div>

        <!-- Table des transactions -->
        <div class="transactions-table">
            <table>
                <thead>
                    <tr>
                        <th>Transaction ID</th>
                        <th>Utilisateur</th>
                        <th>Plan</th>
                        <th>Montant</th>
                        <th>Méthode</th>
                        <th>Statut</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($transactions)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 40px;">
                                Aucune transaction trouvée.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($transaction['transaction_id']); ?></strong>
                                </td>
                                <td>
                                    <div>
                                        <strong><?php echo htmlspecialchars($transaction['first_name'] . ' ' . $transaction['last_name']); ?></strong>
                                        <br>
                                        <small><?php echo htmlspecialchars($transaction['email']); ?></small>
                                    </div>
                                </td>
                                <td>
                                    <span class="plan-badge">
                                        <?php 
                                        switch($transaction['plan_type']) {
                                            case 'weekly': echo 'Hebdomadaire'; break;
                                            case 'monthly': echo 'Mensuel'; break;
                                            case 'yearly': echo 'Annuel'; break;
                                            default: echo ucfirst($transaction['plan_type']); break;
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="amount"><?php echo number_format($transaction['amount'], 2); ?>€</span>
                                </td>
                                <td>
                                    <i class="fas fa-credit-card"></i>
                                    <?php echo ucfirst($transaction['payment_method']); ?>
                                </td>
                                <td>
                                    <span class="status-<?php echo $transaction['status']; ?>">
                                        <?php 
                                        switch($transaction['status']) {
                                            case 'completed': echo 'Complétée'; break;
                                            case 'pending': echo 'En attente'; break;
                                            case 'cancelled': echo 'Annulée'; break;
                                            default: echo ucfirst($transaction['status']); break;
                                        }
                                        ?>
                                    </span>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y H:i', strtotime($transaction['created_at'])); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div style="display: flex; justify-content: center; gap: 10px; margin-top: 30px;">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>&period=<?php echo $period_filter; ?>" 
                       style="padding: 10px 15px; background: <?php echo $i === $page ? 'var(--primary-color)' : 'var(--white)'; ?>; color: <?php echo $i === $page ? 'var(--white)' : 'var(--text-primary)'; ?>; border: 1px solid #ddd; border-radius: 6px; text-decoration: none;">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>