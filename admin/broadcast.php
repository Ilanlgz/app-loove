<?php
session_start();

// Vérification admin
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || !isset($_SESSION["role"]) || $_SESSION["role"] !== "admin") {
    header("location: login.php");
    exit;
}

require_once '../config/database.php';

$message = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $announcement = trim($_POST['announcement']);
    $type = $_POST['type'] ?? 'info';
    
    if (!empty($announcement)) {
        $conn = getDbConnection();
        
        // Créer la table announcements si elle n'existe pas
        $conn->exec("
            CREATE TABLE IF NOT EXISTS announcements (
                id INT AUTO_INCREMENT PRIMARY KEY,
                message TEXT NOT NULL,
                type ENUM('info', 'warning', 'success', 'error') DEFAULT 'info',
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");
        
        // Enregistrer l'annonce
        $stmt = $conn->prepare("INSERT INTO announcements (message, type) VALUES (?, ?)");
        $stmt->execute([$announcement, $type]);
        
        $success = "Annonce envoyée avec succès !";
    } else {
        $message = "Veuillez saisir un message.";
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Diffusion - Admin Loove</title>
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

        .broadcast-form {
            background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(20px);
            border-radius: 16px; padding: 32px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            max-width: 800px;
        }

        .form-group { margin-bottom: 24px; }

        .form-label {
            display: block; font-weight: 600; color: #333; margin-bottom: 8px; font-size: 16px;
        }

        .form-select, .form-textarea {
            width: 100%; padding: 14px 16px; border: 2px solid #e1e5e9;
            border-radius: 12px; font-size: 16px; outline: none;
            transition: border-color 0.3s ease; font-family: inherit;
        }

        .form-textarea {
            min-height: 120px; resize: vertical;
        }

        .form-select:focus, .form-textarea:focus {
            border-color: #FF4458;
        }

        .type-options {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 12px; margin-top: 8px;
        }

        .type-option {
            position: relative;
        }

        .type-radio {
            position: absolute; opacity: 0; pointer-events: none;
        }

        .type-label {
            display: block; padding: 12px 16px; border: 2px solid #e1e5e9;
            border-radius: 12px; text-align: center; cursor: pointer;
            transition: all 0.3s ease; font-weight: 500;
        }

        .type-radio:checked + .type-label {
            border-color: #FF4458; background: rgba(255, 68, 88, 0.1); color: #FF4458;
        }

        .submit-btn {
            background: linear-gradient(135deg, #FF4458, #FF6B81); color: white;
            border: none; padding: 14px 32px; border-radius: 12px;
            font-size: 16px; font-weight: 600; cursor: pointer;
            transition: all 0.3s ease; display: flex; align-items: center; gap: 8px;
        }

        .submit-btn:hover {
            transform: translateY(-2px); box-shadow: 0 8px 24px rgba(255, 68, 88, 0.3);
        }

        .alert {
            padding: 12px 16px; border-radius: 12px; margin-bottom: 24px;
            font-weight: 500; display: flex; align-items: center; gap: 8px;
        }

        .alert-success { background: #dcfce7; color: #166534; }
        .alert-error { background: #fee2e2; color: #991b1b; }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; }
            .type-options { grid-template-columns: 1fr; }
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
            <a href="broadcast.php" class="nav-item active">
                <i class="fas fa-bullhorn"></i> Diffusion
            </a>
            <a href="../logout.php" class="nav-item">
                <i class="fas fa-sign-out-alt"></i> Déconnexion
            </a>
        </div>
    </nav>

    <main class="main-content">
        <div class="admin-header">
            <h1 class="page-title">Diffusion d'annonces</h1>
        </div>

        <div class="broadcast-form">
            <?php if (!empty($success)): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($message)): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-triangle"></i>
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
                    <label class="form-label" for="announcement">
                        <i class="fas fa-megaphone"></i> Message d'annonce
                    </label>
                    <textarea id="announcement" name="announcement" class="form-textarea" 
                              placeholder="Rédigez votre annonce pour tous les utilisateurs..." required></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <i class="fas fa-tag"></i> Type d'annonce
                    </label>
                    <div class="type-options">
                        <div class="type-option">
                            <input type="radio" id="info" name="type" value="info" class="type-radio" checked>
                            <label for="info" class="type-label">
                                <i class="fas fa-info-circle"></i> Information
                            </label>
                        </div>
                        <div class="type-option">
                            <input type="radio" id="warning" name="type" value="warning" class="type-radio">
                            <label for="warning" class="type-label">
                                <i class="fas fa-exclamation-triangle"></i> Avertissement
                            </label>
                        </div>
                        <div class="type-option">
                            <input type="radio" id="success" name="type" value="success" class="type-radio">
                            <label for="success" class="type-label">
                                <i class="fas fa-check-circle"></i> Succès
                            </label>
                        </div>
                        <div class="type-option">
                            <input type="radio" id="error" name="type" value="error" class="type-radio">
                            <label for="error" class="type-label">
                                <i class="fas fa-times-circle"></i> Erreur
                            </label>
                        </div>
                    </div>
                </div>

                <button type="submit" class="submit-btn">
                    <i class="fas fa-paper-plane"></i>
                    Envoyer l'annonce
                </button>
            </form>
        </div>
    </main>
</body>
</html>