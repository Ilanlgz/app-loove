<?php
session_start();

if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["role"] !== "admin") {
    header("location: ../login.php");
    exit;
}

require_once '../config/database.php';
$conn = getDbConnection();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = (int)$_POST['user_id'];
    $action = $_POST['action'];
    
    if ($action === 'ban') {
        $stmt = $conn->prepare("UPDATE users SET status = 'banned' WHERE id = ?");
        $stmt->execute([$user_id]);
    } elseif ($action === 'unban') {
        $stmt = $conn->prepare("UPDATE users SET status = 'active' WHERE id = ?");
        $stmt->execute([$user_id]);
    } elseif ($action === 'delete') {
        $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
    }
    
    header("Location: users_clean.php");
    exit;
}

$search = $_GET['search'] ?? '';
$status_filter = $_GET['status'] ?? '';

$sql = "SELECT * FROM users WHERE role != 'admin'";
$params = [];

if ($search) {
    $sql .= " AND (first_name LIKE ? OR last_name LIKE ? OR email LIKE ?)";
    $params = ["%$search%", "%$search%", "%$search%"];
}

if ($status_filter) {
    $sql .= " AND status = ?";
    $params[] = $status_filter;
}

$sql .= " ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestion Utilisateurs - Loove Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
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

        .users-table {
            background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px);
            border-radius: 16px; overflow: hidden; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }

        .table-header {
            background: linear-gradient(135deg, #FF4458, #FF6B81); color: white;
            padding: 20px; display: grid;
            grid-template-columns: 1fr 200px 150px 120px 150px;
            gap: 16px; align-items: center; font-weight: 600;
        }

        .user-row {
            padding: 16px 20px; display: grid;
            grid-template-columns: 1fr 200px 150px 120px 150px;
            gap: 16px; align-items: center;
            border-bottom: 1px solid rgba(0, 0, 0, 0.05);
            transition: background 0.3s ease;
        }

        .user-row:hover { background: rgba(255, 68, 88, 0.05); }

        .user-info { display: flex; align-items: center; gap: 12px; }

        .user-avatar {
            width: 40px; height: 40px; border-radius: 50%;
            background: linear-gradient(135deg, #FF4458, #FF6B81);
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 600;
        }

        .user-details { flex: 1; }

        .user-name { font-weight: 600; color: #333; margin-bottom: 2px; }
        .user-email { font-size: 12px; color: #666; }

        .status-badge {
            padding: 4px 12px; border-radius: 20px; font-size: 12px;
            font-weight: 600; text-align: center;
        }

        .status-active { background: #dcfce7; color: #166534; }
        .status-banned { background: #fee2e2; color: #991b1b; }

        .actions-group { display: flex; gap: 8px; }

        .action-btn {
            padding: 6px 12px; border: none; border-radius: 6px;
            font-size: 12px; font-weight: 500; cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-ban { background: #fee2e2; color: #991b1b; }
        .btn-unban { background: #dcfce7; color: #166534; }
        .btn-delete { background: #fef2f2; color: #dc2626; }

        .action-btn:hover { transform: translateY(-1px); }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; }
            .table-header, .user-row { grid-template-columns: 1fr; gap: 8px; }
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
            <a href="users_clean.php" class="nav-item active">
                <i class="fas fa-users"></i> Utilisateurs
            </a>
            <a href="../logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </nav>

    <main class="main-content">
        <div class="admin-header">
            <h1 class="page-title">Gestion des Utilisateurs</h1>
        </div>

        <div class="users-table">
            <div class="table-header">
                <div>Utilisateur</div>
                <div>Email</div>
                <div>Inscription</div>
                <div>Statut</div>
                <div>Actions</div>
            </div>

            <?php foreach ($users as $user): ?>
                <div class="user-row">
                    <div class="user-info">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr($user['first_name'], 0, 1)); ?>
                        </div>
                        <div class="user-details">
                            <div class="user-name">
                                <?php echo htmlspecialchars($user['first_name'] . ' ' . $user['last_name']); ?>
                            </div>
                            <div class="user-email"><?php echo htmlspecialchars($user['email']); ?></div>
                        </div>
                    </div>
                    <div><?php echo htmlspecialchars($user['email']); ?></div>
                    <div><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></div>
                    <div>
                        <span class="status-badge status-<?php echo $user['status'] ?? 'active'; ?>">
                            <?php echo ucfirst($user['status'] ?? 'active'); ?>
                        </span>
                    </div>
                    <div class="actions-group">
                        <?php if (($user['status'] ?? 'active') === 'active'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="action" value="ban">
                                <button type="submit" class="action-btn btn-ban" 
                                        onclick="return confirm('Bannir cet utilisateur ?')">
                                    Bannir
                                </button>
                            </form>
                        <?php else: ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                <input type="hidden" name="action" value="unban">
                                <button type="submit" class="action-btn btn-unban">
                                    Débannir
                                </button>
                            </form>
                        <?php endif; ?>
                        
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                            <input type="hidden" name="action" value="delete">
                            <button type="submit" class="action-btn btn-delete" 
                                    onclick="return confirm('Supprimer définitivement cet utilisateur ?')">
                                Supprimer
                            </button>
                        </form>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>
