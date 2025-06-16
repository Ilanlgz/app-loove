<?php
session_start();

if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once '../config/database.php';

$conn = getDbConnection();

// Créer la table reports si elle n'existe pas
try {
    $conn->exec("CREATE TABLE IF NOT EXISTS reports (
        id INT AUTO_INCREMENT PRIMARY KEY,
        reporter_id INT NOT NULL,
        reported_id INT NOT NULL,
        reason VARCHAR(100) NOT NULL,
        description TEXT,
        status VARCHAR(20) DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX idx_reporter (reporter_id),
        INDEX idx_reported (reported_id),
        INDEX idx_status (status)
    )");
} catch (PDOException $e) {
    // Table existe déjà
}

// Traitement des actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action']) && isset($_POST['report_id'])) {
        $report_id = (int)$_POST['report_id'];
        $action = $_POST['action'];
        
        switch ($action) {
            case 'approve':
                // Approuver le signalement et suspendre l'utilisateur signalé
                $stmt = $conn->prepare("SELECT reported_id FROM reports WHERE id = ?");
                $stmt->execute([$report_id]);
                $report = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if ($report) {
                    // Suspendre l'utilisateur signalé
                    $conn->prepare("UPDATE users SET is_active = 0 WHERE id = ?")->execute([$report['reported_id']]);
                    // Marquer le signalement comme approuvé
                    $conn->prepare("UPDATE reports SET status = 'approved' WHERE id = ?")->execute([$report_id]);
                    $success_message = "Signalement approuvé et utilisateur suspendu";
                }
                break;
                
            case 'reject':
                $conn->prepare("UPDATE reports SET status = 'rejected' WHERE id = ?")->execute([$report_id]);
                $success_message = "Signalement rejeté";
                break;
        }
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Filtres
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Construction de la requête
$where_condition = "";
$params = [];

if ($status_filter !== 'all') {
    $where_condition = "WHERE r.status = ?";
    $params[] = $status_filter;
}

// Compter le total
$count_query = "SELECT COUNT(*) FROM reports r $where_condition";
$stmt = $conn->prepare($count_query);
$stmt->execute($params);
$total_reports = $stmt->fetchColumn();
$total_pages = ceil($total_reports / $limit);

// Récupérer les signalements
$reports_query = "SELECT r.*, 
                         u1.first_name as reporter_name, u1.last_name as reporter_lastname,
                         u2.first_name as reported_name, u2.last_name as reported_lastname,
                         u2.is_active as reported_active
                  FROM reports r
                  LEFT JOIN users u1 ON r.reporter_id = u1.id
                  LEFT JOIN users u2 ON r.reported_id = u2.id
                  $where_condition
                  ORDER BY r.created_at DESC 
                  LIMIT $limit OFFSET $offset";

$stmt = $conn->prepare($reports_query);
$stmt->execute($params);
$reports = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Signalements - Admin Loove</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #FF4458;
            --admin-color: #1a1a2e;
            --admin-secondary: #16213e;
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

        .filters-section {
            background: var(--white);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }

        .filter-select {
            padding: 12px;
            border: 2px solid #E8E8E8;
            border-radius: 8px;
            font-size: 1rem;
        }

        .reports-grid {
            display: grid;
            gap: 20px;
        }

        .report-card {
            background: var(--white);
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            border-left: 4px solid var(--warning);
        }

        .report-card.approved {
            border-left-color: var(--success);
        }

        .report-card.rejected {
            border-left-color: var(--error);
        }

        .report-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .report-info {
            flex: 1;
        }

        .report-users {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 5px;
        }

        .report-reason {
            color: var(--text-secondary);
            margin-bottom: 10px;
        }

        .report-description {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
            font-style: italic;
        }

        .report-meta {
            display: flex;
            gap: 20px;
            color: var(--text-secondary);
            font-size: 0.9rem;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .status-pending {
            background: rgba(255, 149, 0, 0.1);
            color: var(--warning);
        }

        .status-approved {
            background: rgba(52, 199, 89, 0.1);
            color: var(--success);
        }

        .status-rejected {
            background: rgba(255, 59, 48, 0.1);
            color: var(--error);
        }

        .report-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-approve {
            background: var(--success);
            color: var(--white);
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
        }

        .btn-reject {
            background: var(--error);
            color: var(--white);
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
        }

        .user-status {
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .user-active {
            background: rgba(52, 199, 89, 0.1);
            color: var(--success);
        }

        .user-suspended {
            background: rgba(255, 59, 48, 0.1);
            color: var(--error);
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
                <a href="reports.php" class="nav-link active">
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
        <h1 class="page-title">Gestion des signalements</h1>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success" style="background: rgba(52, 199, 89, 0.1); color: var(--success); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <!-- Filtres -->
        <div class="filters-section">
            <form method="GET">
                <select name="status" class="filter-select" onchange="this.form.submit()">
                    <option value="all" <?php echo $status_filter === 'all' ? 'selected' : ''; ?>>Tous les statuts</option>
                    <option value="pending" <?php echo $status_filter === 'pending' ? 'selected' : ''; ?>>En attente</option>
                    <option value="approved" <?php echo $status_filter === 'approved' ? 'selected' : ''; ?>>Approuvés</option>
                    <option value="rejected" <?php echo $status_filter === 'rejected' ? 'selected' : ''; ?>>Rejetés</option>
                </select>
            </form>
        </div>

        <!-- Liste des signalements -->
        <div class="reports-grid">
            <?php if (empty($reports)): ?>
                <div class="report-card">
                    <p>Aucun signalement trouvé.</p>
                </div>
            <?php else: ?>
                <?php foreach ($reports as $report): ?>
                    <div class="report-card <?php echo $report['status']; ?>">
                        <div class="report-header">
                            <div class="report-info">
                                <div class="report-users">
                                    <strong><?php echo htmlspecialchars($report['reporter_name'] . ' ' . $report['reporter_lastname']); ?></strong>
                                    <i class="fas fa-arrow-right" style="margin: 0 10px; color: var(--text-secondary);"></i>
                                    <strong><?php echo htmlspecialchars($report['reported_name'] . ' ' . $report['reported_lastname']); ?></strong>
                                    <span class="user-status <?php echo $report['reported_active'] ? 'user-active' : 'user-suspended'; ?>">
                                        <?php echo $report['reported_active'] ? 'Actif' : 'Suspendu'; ?>
                                    </span>
                                </div>
                                <div class="report-reason">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Motif : <strong><?php echo htmlspecialchars($report['reason']); ?></strong>
                                </div>
                            </div>
                            <span class="status-badge status-<?php echo $report['status']; ?>">
                                <?php 
                                switch($report['status']) {
                                    case 'pending': echo 'En attente'; break;
                                    case 'approved': echo 'Approuvé'; break;
                                    case 'rejected': echo 'Rejeté'; break;
                                }
                                ?>
                            </span>
                        </div>

                        <?php if (!empty($report['description'])): ?>
                            <div class="report-description">
                                "<?php echo htmlspecialchars($report['description']); ?>"
                            </div>
                        <?php endif; ?>

                        <div class="report-meta">
                            <span><i class="fas fa-clock"></i> <?php echo date('d/m/Y à H:i', strtotime($report['created_at'])); ?></span>
                            <span><i class="fas fa-hashtag"></i> ID: <?php echo $report['id']; ?></span>
                        </div>

                        <?php if ($report['status'] === 'pending'): ?>
                            <div class="report-actions">
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn-approve" onclick="return confirm('Approuver ce signalement et suspendre l\'utilisateur ?')">
                                        <i class="fas fa-check"></i> Approuver
                                    </button>
                                </form>
                                <form method="POST" style="display: inline;">
                                    <input type="hidden" name="report_id" value="<?php echo $report['id']; ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn-reject" onclick="return confirm('Rejeter ce signalement ?')">
                                        <i class="fas fa-times"></i> Rejeter
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div style="display: flex; justify-content: center; gap: 10px; margin-top: 30px;">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&status=<?php echo $status_filter; ?>" 
                       style="padding: 10px 15px; background: <?php echo $i === $page ? 'var(--primary-color)' : 'var(--white)'; ?>; color: <?php echo $i === $page ? 'var(--white)' : 'var(--text-primary)'; ?>; border: 1px solid #ddd; border-radius: 6px; text-decoration: none;">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>