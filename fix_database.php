<?php
session_start();
require_once 'config/database.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header('location: login.php');
    exit;
}

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn = getDbConnection();
        
        // Supprimer les tables si elles existent (pour recommencer proprement)
        $conn->exec("DROP TABLE IF EXISTS messages");
        $conn->exec("DROP TABLE IF EXISTS likes");
        
        // Créer la table messages avec tous les champs nécessaires
        $createMessages = "
        CREATE TABLE messages (
            id INT PRIMARY KEY AUTO_INCREMENT,
            sender_id INT NOT NULL,
            receiver_id INT NOT NULL,
            content TEXT NOT NULL,
            sent_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            is_read TINYINT(1) DEFAULT 0,
            read_at TIMESTAMP NULL
        )";
        
        $conn->exec($createMessages);
        
        // Créer la table likes
        $createLikes = "
        CREATE TABLE likes (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            liked_user_id INT NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            UNIQUE KEY unique_like (user_id, liked_user_id)
        )";
        
        $conn->exec($createLikes);
        
        // Insérer quelques messages de test
        $insertTestMessage = "
        INSERT INTO messages (sender_id, receiver_id, content) VALUES 
        (1, 2, 'Salut ! Comment ça va ?'),
        (2, 1, 'Ça va très bien, merci ! Et toi ?')";
        
        try {
            $conn->exec($insertTestMessage);
        } catch (Exception $e) {
            // Pas grave si ça échoue (les utilisateurs n'existent peut-être pas)
        }
        
        $message = "✅ Base de données réparée ! Tables créées avec succès.";
        $success = true;
        
    } catch (Exception $e) {
        $message = "❌ Erreur: " . $e->getMessage();
    }
}

// Vérifier l'état actuel des tables
$tablesStatus = [];
try {
    $conn = getDbConnection();
    
    // Vérifier si la table messages existe
    $result = $conn->query("SHOW TABLES LIKE 'messages'");
    $tablesStatus['messages'] = $result->rowCount() > 0;
    
    // Vérifier si la table likes existe
    $result = $conn->query("SHOW TABLES LIKE 'likes'");
    $tablesStatus['likes'] = $result->rowCount() > 0;
    
    // Si la table messages existe, vérifier ses colonnes
    if ($tablesStatus['messages']) {
        $result = $conn->query("DESCRIBE messages");
        $columns = $result->fetchAll(PDO::FETCH_COLUMN);
        $tablesStatus['columns'] = $columns;
    }
    
} catch (Exception $e) {
    $tablesStatus = ['messages' => false, 'likes' => false];
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Réparation Base de Données - Loove</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            max-width: 800px;
            margin: 30px auto;
            padding: 20px;
            background: linear-gradient(135deg, #FF4458, #FD5068);
            color: white;
        }
        
        .container {
            background: white;
            color: #333;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
        }
        
        h1 {
            color: #FF4458;
            text-align: center;
            margin-bottom: 20px;
        }
        
        .status {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        
        .status-item {
            display: flex;
            justify-content: space-between;
            padding: 5px 0;
        }
        
        .ok { color: #28a745; font-weight: bold; }
        .error { color: #dc3545; font-weight: bold; }
        
        button {
            background: #FF4458;
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            width: 100%;
            margin: 20px 0;
        }
        
        .message {
            padding: 15px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: center;
            font-weight: bold;
        }
        
        .success { background: #d4edda; color: #155724; }
        .error-msg { background: #f8d7da; color: #721c24; }
        
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #FF4458;
            text-decoration: none;
            font-weight: 500;
        }
        
        .columns {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Réparation Base de Données</h1>
        
        <div class="status">
            <h3>📊 État actuel :</h3>
            <div class="status-item">
                <span>Table 'messages' :</span>
                <span class="<?= $tablesStatus['messages'] ? 'ok' : 'error' ?>">
                    <?= $tablesStatus['messages'] ? '✅ Existe' : '❌ Manquante' ?>
                </span>
            </div>
            
            <?php if (isset($tablesStatus['columns'])): ?>
            <div class="columns">
                Colonnes détectées: <?= implode(', ', $tablesStatus['columns']) ?>
            </div>
            <?php endif; ?>
            
            <div class="status-item">
                <span>Table 'likes' :</span>
                <span class="<?= $tablesStatus['likes'] ? 'ok' : 'error' ?>">
                    <?= $tablesStatus['likes'] ? '✅ Existe' : '❌ Manquante' ?>
                </span>
            </div>
        </div>
        
        <?php if ($message): ?>
            <div class="message <?= $success ? 'success' : 'error-msg' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <button type="submit">
                🚀 Recréer toutes les tables (supprime et recrée)
            </button>
        </form>
        
        <p style="text-align: center; color: #666; font-size: 14px;">
            ⚠️ Cette action va supprimer toutes les données existantes dans les tables messages et likes.
        </p>
        
        <?php if ($tablesStatus['messages'] && $tablesStatus['likes']): ?>
            <div style="text-align: center; margin-top: 30px;">
                <h3 style="color: #28a745;">🎉 Tables prêtes !</h3>
                <p>Tu peux maintenant utiliser les notifications push.</p>
                
                <div style="margin-top: 20px;">
                    <a href="send_message_with_notification.php" style="background: #28a745; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; margin-right: 10px;">
                        💬 Tester les messages
                    </a>
                    <a href="test_notification.php" style="background: #17a2b8; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none;">
                        🧪 Tester les notifications
                    </a>
                </div>
            </div>
        <?php endif; ?>
        
        <a href="main.php" class="back-link">← Retour à l'accueil</a>
    </div>
</body>
</html>
