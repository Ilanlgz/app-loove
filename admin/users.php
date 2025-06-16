<?php
session_start();

if (!isset($_SESSION["admin_loggedin"]) || $_SESSION["admin_loggedin"] !== true) {
    header("location: login.php");
    exit;
}

require_once '../config/database.php';

$conn = getDbConnection();

// Traitement des actions
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        $user_id = (int)$_POST['user_id'];
        
        switch ($_POST['action']) {
            case 'suspend':
                $conn->prepare("UPDATE users SET is_active = 0 WHERE id = ? AND role != 'admin'")->execute([$user_id]);
                $success_message = "Utilisateur suspendu avec succès";
                break;
            case 'activate':
                $conn->prepare("UPDATE users SET is_active = 1 WHERE id = ? AND role != 'admin'")->execute([$user_id]);
                $success_message = "Utilisateur activé avec succès";
                break;
            case 'make_premium':
                $expires_at = date('Y-m-d H:i:s', strtotime('+1 month'));
                $conn->prepare("UPDATE users SET is_premium = 1, premium_expires_at = ? WHERE id = ?")->execute([$expires_at, $user_id]);
                $success_message = "Premium accordé pour 1 mois";
                break;
            case 'remove_premium':
                $conn->prepare("UPDATE users SET is_premium = 0, premium_expires_at = NULL WHERE id = ?")->execute([$user_id]);
                $success_message = "Premium retiré";
                break;
        }
    }
}

// Pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 20;
$offset = ($page - 1) * $limit;

// Filtres
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// Construction de la requête
$where_conditions = ["role != 'admin'"];
$params = [];

if ($search) {
    $where_conditions[] = "(first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($filter === 'premium') {
    $where_conditions[] = "is_premium = 1";
} elseif ($filter === 'suspended') {
    $where_conditions[] = "is_active = 0";
}

$where_clause = implode(' AND ', $where_conditions);

// Compter le total
$count_query = "SELECT COUNT(*) FROM users WHERE $where_clause";
$stmt = $conn->prepare($count_query);
$stmt->execute($params);
$total_users = $stmt->fetchColumn();
$total_pages = ceil($total_users / $limit);

// Récupérer les utilisateurs
$users_query = "SELECT * FROM users WHERE $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$stmt = $conn->prepare($users_query);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Utilisateurs - Admin Loove</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Reprendre les styles du dashboard -->
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

        .filters-row {
            display: grid;
            grid-template-columns: 1fr auto auto;
            gap: 15px;
            align-items: end;
        }

        .search-input {
            padding: 12px;
            border: 2px solid #E8E8E8;
            border-radius: 8px;
            font-size: 1rem;
        }

        .filter-select {
            padding: 12px;
            border: 2px solid #E8E8E8;
            border-radius: 8px;
            font-size: 1rem;
        }

        .btn-search {
            background: var(--primary-color);
            color: var(--white);
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
        }

        .users-table {
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

        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary-color), #FD5068);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--white);
            font-weight: 600;
        }

        .premium-badge {
            background: linear-gradient(135deg, #FFD700, #FFA500);
            color: var(--white);
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7rem;
            font-weight: 600;
        }

        .status-active {
            color: var(--success);
            font-weight: 600;
        }

        .status-suspended {
            color: var(--error);
            font-weight: 600;
        }

        .actions-dropdown {
            position: relative;
            display: inline-block;
        }

        .dropdown-btn {
            background: var(--text-secondary);
            color: var(--white);
            padding: 8px 12px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .dropdown-content {
            display: none;
            position: absolute;
            right: 0;
            background: var(--white);
            min-width: 160px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.2);
            border-radius: 8px;
            z-index: 1;
        }

        .dropdown-content.show {
            display: block;
        }

        .dropdown-item {
            display: block;
            width: 100%;
            padding: 10px 15px;
            border: none;
            background: none;
            text-align: left;
            cursor: pointer;
            transition: background 0.3s;
        }

        .dropdown-item:hover {
            background: #f5f5f5;
        }

        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }

        .page-link {
            padding: 10px 15px;
            background: var(--white);
            border: 1px solid #ddd;
            border-radius: 6px;
            text-decoration: none;
            color: var(--text-primary);
        }

        .page-link.active {
            background: var(--primary-color);
            color: var(--white);
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
                <a href="users.php" class="nav-link active">
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
        <h1 class="page-title">Gestion des utilisateurs</h1>

        <?php if (isset($success_message)): ?>
            <div class="alert alert-success" style="background: rgba(52, 199, 89, 0.1); color: var(--success); padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <!-- Filtres -->
        <div class="filters-section">
            <form method="GET" class="filters-row">
                <input type="text" name="search" class="search-input" placeholder="Rechercher par nom ou email..." value="<?php echo htmlspecialchars($search); ?>">
                
                <select name="filter" class="filter-select">
                    <option value="all" <?php echo $filter === 'all' ? 'selected' : ''; ?>>Tous</option>
                    <option value="premium" <?php echo $filter === 'premium' ? 'selected' : ''; ?>>Premium</option>
                    <option value="suspended" <?php echo $filter === 'suspended' ? 'selected' : ''; ?>>Suspendus</option>
                </select>
                
                <button type="submit" class="btn-search">
                    <i class="fas fa-search"></i> Rechercher
                </button>
            </form>
        </div>

        <!-- Table des utilisateurs -->
        <div class="users-table">
            <table>
                <thead>
                    <tr>
                        <th>Utilisateur</th>
                        <th>Email</th>
                        <th>Statut</th>
                        <th>Premium</th>
                        <th>Inscription</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td>
                                <div class="user-info">
                                    <div class="user-avatar">
                                        <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                                    </div>
                                    <div>
                                        <div><?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?></div>
                                        <small><?php echo $user['age']; ?> ans - <?php echo htmlspecialchars($user['location']); ?></small>
                                    </div>
                                </div>
                            </td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td>
                                <span class="<?php echo $user['is_active'] ? 'status-active' : 'status-suspended'; ?>">
                                    <?php echo $user['is_active'] ? 'Actif' : 'Suspendu'; ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($user['is_premium']): ?>
                                    <span class="premium-badge">Premium</span>
                                <?php else: ?>
                                    <span>Gratuit</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <div class="actions-dropdown">
                                    <button class="dropdown-btn" onclick="toggleDropdown(this)">
                                        <i class="fas fa-ellipsis-v"></i>
                                    </button>
                                    <div class="dropdown-content">
                                        <?php if ($user['is_active']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="suspend">
                                                <button type="submit" class="dropdown-item" onclick="return confirm('Suspendre cet utilisateur ?')">
                                                    <i class="fas fa-ban"></i> Suspendre
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="activate">
                                                <button type="submit" class="dropdown-item">
                                                    <i class="fas fa-check"></i> Activer
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                        
                                        <?php if (!$user['is_premium']): ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="make_premium">
                                                <button type="submit" class="dropdown-item">
                                                    <i class="fas fa-crown"></i> Accorder Premium
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <form method="POST" style="display: inline;">
                                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                                <input type="hidden" name="action" value="remove_premium">
                                                <button type="submit" class="dropdown-item" onclick="return confirm('Retirer le Premium ?')">
                                                    <i class="fas fa-times"></i> Retirer Premium
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
            <div class="pagination">
                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <a href="?page=<?php echo $i; ?>&search=<?php echo urlencode($search); ?>&filter=<?php echo $filter; ?>" 
                       class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>
            </div>
        <?php endif; ?>
    </main>

    <script>
        function toggleDropdown(button) {
            // Fermer tous les autres dropdowns
            document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                if (dropdown !== button.nextElementSibling) {
                    dropdown.classList.remove('show');
                }
            });
            
            // Toggle le dropdown actuel
            button.nextElementSibling.classList.toggle('show');
        }

        // Fermer les dropdowns quand on clique ailleurs
        window.onclick = function(event) {
            if (!event.target.matches('.dropdown-btn')) {
                document.querySelectorAll('.dropdown-content').forEach(dropdown => {
                    dropdown.classList.remove('show');
                });
            }
        }
    </script>
</body>
</html>
