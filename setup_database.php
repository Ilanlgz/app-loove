<?php
session_start();
require_once 'config/database.php';

// Vérifier que l'utilisateur est connecté
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php');
    exit;
}

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_tables'])) {
    try {
        $conn = getDbConnection();
        
        // Créer la table messages
        $messagesTable = "
        CREATE TABLE IF NOT EXISTS `messages` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `sender_id` int(11) NOT NULL,
          `receiver_id` int(11) NOT NULL,
          `content` text NOT NULL,
          `sent_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          `is_read` tinyint(1) DEFAULT 0,
          `read_at` timestamp NULL DEFAULT NULL,
          PRIMARY KEY (`id`),
          KEY `sender_id` (`sender_id`),
          KEY `receiver_id` (`receiver_id`),
          KEY `sent_at` (`sent_at`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $conn->exec($messagesTable);
        
        // Créer la table likes
        $likesTable = "
        CREATE TABLE IF NOT EXISTS `likes` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `user_id` int(11) NOT NULL,
          `liked_user_id` int(11) NOT NULL,
          `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
          PRIMARY KEY (`id`),
          UNIQUE KEY `unique_like` (`user_id`, `liked_user_id`),
          KEY `user_id` (`user_id`),
          KEY `liked_user_id` (`liked_user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";
        
        $conn->exec($likesTable);
        
        $message = '✅ Tables créées avec succès ! Tu peux maintenant utiliser les messages et likes avec notifications push.';
        $success = true;
        
    } catch (Exception $e) {
        $message = '❌ Erreur: ' . $e->getMessage();
    }
}

// Vérifier si les tables existent
try {
    $conn = getDbConnection();
    
    $tables = [];
    $checkMessages = $conn->query("SHOW TABLES LIKE 'messages'");
    $tables['messages'] = $checkMessages->rowCount() > 0;
    
    $checkLikes = $conn->query("SHOW TABLES LIKE 'likes'");
    $tables['likes'] = $checkLikes->rowCount() > 0;
    
} catch (Exception $e) {
    $tables = ['messages' => false, 'likes' => false];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuration Base de Données - Loove</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: linear-gradient(135deg, #FF4458, #FD5068);
            min-height: 100vh;
        }
        
        .container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }
        
        h1 {
            color: #FF4458;
            text-align: center;
            margin-bottom: 30px;
        }
        
        .user-info {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .table-status {
            background: #e7f3ff;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        
        .status-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #ddd;
        }
        
        .status-item:last-child {
            border-bottom: none;
        }
        
        .status-ok {
            color: #28a745;
            font-weight: bold;
        }
        
        .status-missing {
            color: #dc3545;
            font-weight: bold;
        }
        
        .btn {
            background: linear-gradient(135deg, #FF4458, #FD5068);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 10px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            width: 100%;
            margin-bottom: 20px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
        }
        
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
        }
        
        .message {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: 600;
        }
        
        .success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .back-link {
            display: block;
            text-align: center;
            color: #FF4458;
            text-decoration: none;
            font-weight: 500;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🛠️ Configuration Base de Données</h1>
        
        <div class="user-info">
            <strong>Connecté en tant que:</strong> <?= htmlspecialchars($_SESSION['first_name']) ?> (ID: <?= $_SESSION['user_id'] ?>)
        </div>
        
        <div class="table-status">
            <h3>📊 État des tables :</h3>
            <div class="status-item">
                <span>Table <code>messages</code> :</span>
                <span class="<?= $tables['messages'] ? 'status-ok' : 'status-missing' ?>">
                    <?= $tables['messages'] ? '✅ Existe' : '❌ Manquante' ?>
                </span>
            </div>
            <div class="status-item">
                <span>Table <code>likes</code> :</span>
                <span class="<?= $tables['likes'] ? 'status-ok' : 'status-missing' ?>">
                    <?= $tables['likes'] ? '✅ Existe' : '❌ Manquante' ?>
                </span>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?= $success ? 'success' : 'error' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <button type="submit" name="create_tables" class="btn" 
                    <?= ($tables['messages'] && $tables['likes']) ? 'disabled' : '' ?>>
                🚀 Créer les tables manquantes
            </button>
        </form>
        
        <?php if ($tables['messages'] && $tables['likes']): ?>
            <div style="text-align: center; margin-top: 20px;">
                <p style="color: #28a745; font-weight: bold;">🎉 Configuration terminée !</p>
                <p>Tu peux maintenant utiliser :</p>
                <ul style="text-align: left; margin: 20px 0;">
                    <li>✅ Messages avec notifications push</li>
                    <li>✅ Likes avec notifications push</li>
                </ul>
            </div>
        <?php endif; ?>
        
        <a href="main.php" class="back-link">← Retour à l'accueil</a>
    </div>
</body>
</html>
